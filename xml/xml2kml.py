import xml.etree.ElementTree as ET
import os

class Kml(object):

    def __init__(self):

        self.raiz = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.raiz,'Document')

    def addPlacemark(self,nombre,descripcion,long,lat,alt, modoAltitud):

        pm = ET.SubElement(self.doc,'Placemark')
        ET.SubElement(pm,'name').text = nombre
        ET.SubElement(pm,'description').text = descripcion
        punto = ET.SubElement(pm,'Point')
        ET.SubElement(punto,'coordinates').text = '{},{},{}'.format(long,lat,alt)
        ET.SubElement(punto,'altitudeMode').text = modoAltitud

    def addLineString(self,nombre,extrude,tesela, listaCoordenadas, modoAltitud, color, ancho):

        ET.SubElement(self.doc,'name').text = nombre
        pm = ET.SubElement(self.doc,'Placemark')
        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls,'extrude').text = extrude
        ET.SubElement(ls,'tessellation').text = tesela
        ET.SubElement(ls,'coordinates').text = listaCoordenadas
        ET.SubElement(ls,'altitudeMode').text = modoAltitud 

        estilo = ET.SubElement(pm, 'Style')
        linea = ET.SubElement(estilo, 'LineStyle')
        ET.SubElement (linea, 'color').text = color
        ET.SubElement (linea, 'width').text = ancho

    def escribir(self,nombreArchivoKML):

        arbol = ET.ElementTree(self.raiz)

        ET.indent(arbol)
        arbol.write(nombreArchivoKML, encoding='utf-8', xml_declaration=True)
    
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

    try:  
        arbol = ET.parse(archivoXML)   
    except IOError:
        print ('Archivo no encontrado: ', archivoXML)
        exit()
    except ET.ParseError:
        print("Error de procesamiento: ", archivoXML)
        exit()
       
    raiz = arbol.getroot()

    ns = {'u': 'http://www.uniovi.es'}

    colores_linea = ["ffff0000", "ff0000ff", "ff00ff00"]

    for i, ruta in enumerate(raiz.findall(".//u:ruta", ns)):

        KML = Kml()
        coordenadas = ""

        for hito in ruta.findall("./u:hitos/u:hito", ns):

            longitud = hito.find("./u:coordenadas/u:longitud", ns)
            latitud = hito.find("./u:coordenadas/u:latitud", ns)
            coordenadas += f"\n{longitud.text},{latitud.text},20.0"

            nombre_elem = hito.find("./u:nombre", ns)
            if nombre_elem is not None and nombre_elem.text:

                KML.addPlacemark(nombre_elem.text, 
                                hito.find("./u:descripcion", ns).text, 
                                longitud.text,
                                latitud.text,
                                "20.0",
                                "relativeToGround")

        coordenadas += "\n"
        KML.addLineString(ruta.find("./u:nombre", ns).text,"1","1",
                            coordenadas,'relativeToGround',
                            colores_linea[i % len(colores_linea)],"5")
        
        nombre_archivo = "planimetria_ruta_" + str(i + 1)
        archivoKML = os.path.join(directorio_actual, f"{nombre_archivo}.kml")
        KML.escribir(archivoKML)

if __name__ == "__main__":
    main()   
