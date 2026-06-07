import xml.etree.ElementTree as ET
import os

class Svg(object):
    
    def __init__(self):

        self.raiz = ET.Element('svg', xmlns="http://www.w3.org/2000/svg", version="1.1")

    
    def addRect(self,x,y,width,height,fill,stroke):

        ET.SubElement(self.raiz,'rect',
                      x=x,
                      y=y,
                      width=width,
                      height=height,
                      fill=fill, 
                      stroke=stroke)
        
    def addLine(self,x1,y1,x2,y2,stroke):

        ET.SubElement(self.raiz,'line',
                      x1=x1,
                      y1=y1,
                      x2=x2,
                      y2=y2,
                      stroke=stroke)

    def addPolyline(self,points,stroke,fill):

        ET.SubElement(self.raiz,'polyline',
                      points=points,
                      stroke=stroke,
                      fill=fill)
        
    def addText(self,texto,x,y,style):

        ET.SubElement(self.raiz,'text',
                      x=x,
                      y=y,
                      style=style).text=texto

    def escribir(self,nombreArchivoSVG):

        arbol = ET.ElementTree(self.raiz)
        
        ET.indent(arbol)
        
        arbol.write(nombreArchivoSVG, 
                    encoding='utf-8', 
                    xml_declaration=True
                    )
    
    def ver(self):

        print("\nElemento raiz = ", self.raiz.tag)

        if self.raiz.text != None:
            print("Contenido = "    , self.raiz.text.strip('\n'))
        else:
            print("Contenido = "    , self.raiz.text)
        
        print("Atributos = "    , self.raiz.attrib)

        for hijo in self.raiz.findall('.//'):
            print("\nElemento = " , hijo.tag)
            if hijo.text != None:
                print("Contenido = ", hijo.text.strip('\n'))
            else:
                print("Contenido = ", hijo.text)    
            print("Atributos = ", hijo.attrib)

def main():

    directorio_actual = os.path.dirname(os.path.abspath(__file__))
    archivoXML = os.path.join(directorio_actual, "rutasSchema.xml")
    ns = {'u': 'http://www.uniovi.es'}

    try:  
        arbol = ET.parse(archivoXML)   
    except IOError:
        print ('Archivo no encontrado: ', archivoXML)
        exit()
    except ET.ParseError:
        print("Error de procesamiento: ", archivoXML)
        exit()
       
    raiz = arbol.getroot()

    # COLORES
    colores_linea = ["blue", "red", "green"]
    colores_relleno = ["rgba(0,0,255,0.2)", "rgba(255,0,0,0.2)", "rgba(0,128,0,0.2)"]

    # DIMENSIONES
    ancho_svg = 1000
    alto_svg = 400
    margen_izq = 60
    margen_der = 50
    margen_inf = 80
    margen_sup = 50

    datos_rutas = []

    for ruta in raiz.findall(".//u:ruta", ns):
        distancias = []
        altitudes = []
        hitos_nombrados = []
        distancia_acumulada = 0.0

        for hito in ruta.findall("./u:hitos/u:hito", ns):

            distancia = float(hito.find("./u:distancia", ns).text)
            altitud = float(hito.find("./u:coordenadas/u:altitud", ns).text)

            distancia_acumulada += distancia
            distancias.append(distancia_acumulada)
            altitudes.append(altitud)

            nombre_elem = hito.find("./u:nombre", ns)
            if nombre_elem is not None and nombre_elem.text:
                hitos_nombrados.append({
                    "nombre": nombre_elem.text,
                    "distancia": distancia_acumulada
                })

        nombre = ruta.find("./u:nombre", ns).text
        datos_rutas.append({
            "nombre": nombre,
            "distancias": distancias,
            "altitudes": altitudes,
            "hitos": hitos_nombrados
        })

    if not datos_rutas:
        print("No se encontraron rutas en el archivo XML.")
        exit()

    for i, datos in enumerate(datos_rutas):

        SVG = Svg()

        distancias = datos["distancias"]
        altitudes = datos["altitudes"]
        nombre = datos["nombre"]
        color_linea = colores_linea[i % len(colores_linea)]
        color_relleno = colores_relleno[i % len(colores_relleno)]

        # ESCALAS
        max_dist = distancias[-1]
        min_alt = min(altitudes)
        max_alt = max(altitudes)

        escala_x = (ancho_svg - margen_izq - margen_der) / max_dist
        escala_y = (alto_svg - margen_inf - margen_sup) / (max_alt - min_alt) if max_alt != min_alt else 1

        SVG.addRect(str(0), str(0), str(ancho_svg), str(alto_svg), fill="white", stroke="none")

        # POLILÍNEA
        puntos = []
        for d, a in zip(distancias, altitudes):
            x = round(margen_izq + d * escala_x, 2)
            y = round(alto_svg - margen_inf - ((a - min_alt) * escala_y), 2)
            puntos.append(f"{x},{y}")

        x_inicio = round(margen_izq + distancias[0] * escala_x, 2)
        x_final = round(margen_izq + distancias[-1] * escala_x, 2)
        y_base = alto_svg - margen_inf

        puntos.insert(0, f"{x_inicio},{y_base}")
        puntos.append(f"{x_final},{y_base}")

        puntos_str = " ".join(puntos)
        SVG.addPolyline(points=puntos_str, stroke=color_linea, fill=color_relleno)

        # EJES
        eje_x_y = alto_svg - margen_inf
        eje_y_x = margen_izq

        SVG.addLine(str(eje_y_x), str(margen_sup), str(eje_y_x), str(eje_x_y), "black")
        SVG.addLine(str(eje_y_x), str(eje_x_y), str(ancho_svg - margen_der), str(eje_x_y), "black")

       # EJE VERTICAL
        pasos_alt = 5
        for j in range(pasos_alt + 1):
            alt = min_alt + j * (max_alt - min_alt) / pasos_alt
            y = round(alto_svg - margen_inf - ((alt - min_alt) * escala_y), 2)
            SVG.addLine(str(eje_y_x - 5), str(y), str(eje_y_x + 5), str(y), "black")
            SVG.addText(f"{alt:.0f} m", str(eje_y_x - 50), str(y + 5),
                        style="font-size:10px;fill:black")

        # EJE HORIZONTAL
        pasos_dist = 5
        paso_km = max_dist / pasos_dist
        magnitud = 10 ** (len(str(int(paso_km))) - 1)
        paso_km = max(1, round(paso_km / magnitud) * magnitud)

        dist_actual = 0
        while dist_actual <= max_dist + paso_km * 0.01: 
            x = round(margen_izq + dist_actual * escala_x, 2)
            SVG.addLine(str(x), str(eje_x_y - 5), str(x), str(eje_x_y + 5), "black")
            SVG.addText(f"{ dist_actual*1000} m", str(x - 10), str(eje_x_y + 20),
                        style="font-size:10px;fill:black")
            dist_actual = round(dist_actual + paso_km, 10)

        # TÍTULO
        SVG.addText(nombre, str(ancho_svg // 2 - len(nombre) * 3), str(margen_sup - 25),
                    style="font-size:13px;fill:black;font-weight:bold")

       # HITOS
        hitos = datos["hitos"]

        hitos_calculados = []
        for hito in hitos:
            dist_hito = hito["distancia"]
            nombre_hito = hito["nombre"]

            if dist_hito > max_dist:
                continue

            altitud_hito = altitudes[0]
            for k in range(len(distancias) - 1):
                if distancias[k] <= dist_hito <= distancias[k + 1]:
                    t = (dist_hito - distancias[k]) / (distancias[k + 1] - distancias[k])
                    altitud_hito = altitudes[k] + t * (altitudes[k + 1] - altitudes[k])
                    break
                elif dist_hito <= distancias[0]:
                    altitud_hito = altitudes[0]

            x_hito = round(margen_izq + dist_hito * escala_x, 2)
            y_hito = round(alto_svg - margen_inf - ((altitud_hito - min_alt) * escala_y), 2)
            hitos_calculados.append({"nombre": nombre_hito, "x": x_hito, "y": y_hito})

        min_separacion = 90
        etiqueta_y_offset = [-28] * len(hitos_calculados) 

        for k in range(1, len(hitos_calculados)):
            for j in range(k):
                dx = abs(hitos_calculados[k]["x"] - hitos_calculados[j]["x"])
                if dx < min_separacion:
                    etiqueta_y_offset[k] = etiqueta_y_offset[j] - 20

        hitos_eje = []  
        for k, hito in enumerate(hitos_calculados):
            x_hito = hito["x"]
            y_hito = hito["y"]
            nombre_hito = hito["nombre"]

            if x_hito <= margen_izq:
               hitos_eje.append((nombre_hito, y_hito))
            else:
                SVG.addLine(str(x_hito), str(y_hito - 15), str(x_hito), str(y_hito),
                            "black")
                y_label = y_hito + etiqueta_y_offset[k]
                x_pos = x_hito - len(nombre_hito) * 2
                if x_hito > (ancho_svg - margen_der - 120):
                    if len(nombre_hito) > 20:
                        x_pos -= 45
                    else:
                        x_pos -= 10
                SVG.addText(nombre_hito, str(x_pos), str(y_label),
                            style="font-size:9px;fill:black")

        for idx, (nombre_hito, y_hito) in enumerate(hitos_eje):
            SVG.addLine(str(eje_y_x - 5), str(y_hito), str(eje_y_x + 5), str(y_hito), "black")
            SVG.addText(nombre_hito, str(eje_y_x + 10), str(y_hito + 4),
                        style="font-size:9px;fill:black")

        # GUARDAR
        nombre_archivo = "altimetria_ruta_" + str(i + 1)
        archivoSVG = os.path.join(directorio_actual, f"{nombre_archivo}.svg")
        SVG.escribir(archivoSVG)
        print(f"Archivo SVG generado: {archivoSVG}")

if __name__ == "__main__":
    main()