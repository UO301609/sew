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
    archivoXML = os.path.join(directorio_actual, "circuitoEsquema.xml")

    try:  
        arbol = ET.parse(archivoXML)   
    except IOError:
        print ('Archivo no encontrado: ', archivoXML)
        exit()
    except ET.ParseError:
        print("Error de procesamiento: ", archivoXML)
        exit()
       
    KML = Kml()
    raiz = arbol.getroot()

    namespaces = {'u': 'http://www.uniovi.es'}

    longitud_inicial = raiz.find(".//u:origen/u:longitud", namespaces=namespaces).text
    latitud_inicial = raiz.find(".//u:origen/u:latitud", namespaces=namespaces).text
    altitud_inicial = raiz.find(".//u:origen/u:altitud", namespaces=namespaces).text

    KML.addPlacemark("Circuito Automotodrom Brno", 
                     "Punto inicial", 
                     longitud_inicial,
                     latitud_inicial,
                     altitud_inicial,
                     "relativeToGround")
    
    coordenadas = f"{longitud_inicial},{latitud_inicial},20.0"
    sectores = raiz.findall(".//sector")

    for longitud, latitud, altitud in zip(
        raiz.findall(".//u:sector/u:final/u:longitud", namespaces=namespaces),
        raiz.findall(".//u:sector/u:final/u:latitud", namespaces=namespaces),
        raiz.findall(".//u:sector/u:final/u:altitud", namespaces=namespaces)):
            coordenadas += f"\n{longitud.text},{latitud.text},20.0"   

    coordenadas += "\n"
    KML.addLineString("Circuito Automotodrom Brno","1","1",
                           coordenadas,'relativeToGround',
                           '#ff0000ff',"5")
    
    KML.escribir("circuito.kml")

if __name__ == "__main__":
    main()   
