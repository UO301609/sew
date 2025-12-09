import xml.etree.ElementTree as ET
import os

class Svg(object):
    """
    Genera archivos SVG con rectángulos, círculos, líneas, polilíneas y texto
    @version 1.0 18/Octubre/2024
    @author: Juan Manuel Cueva Lovelle. Universidad de Oviedo
    """
    def __init__(self):
        """
        Crea el elemento raíz, el espacio de nombres y la versión
        """
        self.raiz = ET.Element('svg', xmlns="http://www.w3.org/2000/svg", version="2.0")

    
    def addRect(self,x,y,width,height,fill, strokeWidth,stroke):
        """
        Añade un elemento rect
        """
        ET.SubElement(self.raiz,'rect',
                      x=x,
                      y=y,
                      width=width,
                      height=height,
                      fill=fill, 
                      strokeWidth=strokeWidth,
                      stroke=stroke)
        
    def addCircle(self,cx,cy,r,fill):
        """
        Añade un elemento circle
        """
        ET.SubElement(self.raiz,'circle',
                      cx=cx,
                      cy=cy,
                      r=r,
                      fill=fill)
        
    def addLine(self,x1,y1,x2,y2,stroke,strokeWith):
        """
        Añade un elemento line
        """
        ET.SubElement(self.raiz,'line',
                      x1=x1,
                      y1=y1,
                      x2=x2,
                      y2=y2,
                      stroke=stroke,
                      strokeWith=strokeWith)

    def addPolyline(self,points,stroke,strokeWith,fill):
        """
        Añade un elemento polyline
        """
        ET.SubElement(self.raiz,'polyline',
                      points=points,
                      stroke=stroke,
                      strokeWith=strokeWith,
                      fill=fill)
        
    def addText(self,texto,x,y,fontFamily,fontSize,style):
        """
        Añade un elemento texto
        """
        ET.SubElement(self.raiz,'text',
                      x=x,
                      y=y,
                      fontFamily=fontFamily,
                      fontSize=fontSize,
                      style=style).text=texto

    def escribir(self,nombreArchivoSVG):
        """ de
        Escribe el archivo SVG con declaración y codificación
        """
        arbol = ET.ElementTree(self.raiz)
        
        """
        Introduce indentacióon y saltos de línea
        para generar XML en modo texto
        """
        ET.indent(arbol)
        
        arbol.write(nombreArchivoSVG, 
                    encoding='utf-8', 
                    xml_declaration=True
                    )
    
    def ver(self):
        """
        Muestra el archivo SVG. Se utiliza para depurar
        """
        print("\nElemento raiz = ", self.raiz.tag)

        if self.raiz.text != None:
            print("Contenido = "    , self.raiz.text.strip('\n')) #strip() elimina los '\n' del string
        else:
            print("Contenido = "    , self.raiz.text)
        
        print("Atributos = "    , self.raiz.attrib)

        # Recorrido de los elementos del árbol
        for hijo in self.raiz.findall('.//'): # Expresión XPath
            print("\nElemento = " , hijo.tag)
            if hijo.text != None:
                print("Contenido = ", hijo.text.strip('\n')) #strip() elimina los '\n' del string
            else:
                print("Contenido = ", hijo.text)    
            print("Atributos = ", hijo.attrib)

def main():

    directorio_actual = os.path.dirname(os.path.abspath(__file__))
    archivoXML = os.path.join(directorio_actual, "circuitoEsquema.xml")
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
    SVG = Svg()

    distancias = []
    altitudes = []
    sectores = []

    distancia_acumulada = 0.0

    for i, sector in enumerate(raiz.findall(".//u:sector", ns), start=1):
        distancia = float(sector.find("./u:distancia", ns).text)
        altitud = float(sector.find("./u:final/u:altitud", ns).text)

        distancia_acumulada += distancia
        distancias.append(distancia_acumulada)
        altitudes.append(altitud)
        sectores.append(i)

    ancho_svg = 1000
    alto_svg = 400
    margen_izq = 60
    margen_inf = 50

    max_dist = max(distancias)
    min_alt = min(altitudes)
    max_alt = max(altitudes)

    escala_x = (ancho_svg - margen_izq) / max_dist
    escala_y = (alto_svg - margen_inf) / (max_alt - min_alt)

    # Generar puntos (x,y)
    puntos = []
    for d, a in zip(distancias, altitudes):
        x = round(margen_izq + d * escala_x, 2)
        y = round(alto_svg - margen_inf - ((a - min_alt) * escala_y), 2)
        puntos.append(f"{x},{y}")

    # Cerrar el polígono para rellenar el perfil
    puntos.insert(0, f"{margen_izq},{alto_svg - margen_inf}") 
    puntos.append(f"{margen_izq + distancias[-1] * escala_x},{alto_svg - margen_inf}") 

    puntos_str = " ".join(puntos)

    # Polilínea rellena (relleno azul claro)
    SVG.addPolyline(points=puntos_str, stroke="blue", strokeWith="1", fill="lightblue")

    # Ejes
    eje_x_y = alto_svg - margen_inf
    eje_y_x = margen_izq
    SVG.addLine(str(eje_y_x), str(0), str(eje_y_x), str(eje_x_y), "black", "1")  # eje Y
    SVG.addLine(str(eje_y_x), str(eje_x_y), str(ancho_svg), str(eje_x_y), "black", "1")  # eje X

    # Medidas en eje Y (altitud)
    pasos_alt = 5
    for i in range(pasos_alt + 1):
        alt = min_alt + i * (max_alt - min_alt) / pasos_alt
        y = round(alto_svg - margen_inf - ((alt - min_alt) * escala_y), 2)
        SVG.addLine(str(eje_y_x - 5), str(y), str(eje_y_x + 5), str(y), "black", "1")
        SVG.addText(f"{alt:.1f}", str(eje_y_x - 45), str(y + 5),
                    fontFamily="Arial", fontSize="12", style="fill:black")

    for i, d in enumerate(distancias):
            x = round(margen_izq + d * escala_x, 2)
            SVG.addLine(str(x), str(eje_x_y - 5), str(x), str(eje_x_y + 5), "black", "1")

    SVG.escribir("altimetria.svg")

if __name__ == "__main__":
    main()   