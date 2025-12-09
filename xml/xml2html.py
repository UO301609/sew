import xml.etree.ElementTree as ET
import os
from html import escape

class HtmlElement:
    def __init__(self, tag: str, value=None, **attrs):
        self.tag = tag
        self.value = value
        self.attrs = attrs

    def _render_attrs(self) -> str:
        return " ".join(f'{k}="{escape(str(v))}"' for k, v in self.attrs.items() if v is not None and v != "")

    def render(self, indent: int = 0) -> str:
        pad = " " * (indent * 2)
        attrs_str = self._render_attrs()
        attrs_str = f" {attrs_str}" if attrs_str else ""
        void_elements = {"meta", "link", "br", "hr", "img", "input", "source"}
        if self.tag in void_elements:
            return f"{pad}<{self.tag}{attrs_str}>"
        if self.value is not None and not isinstance(self.value, list):
            return f"{pad}<{self.tag}{attrs_str}>{escape(str(self.value))}</{self.tag}>"
        inner_html = ""
        if isinstance(self.value, list):
            for child in self.value:
                if isinstance(child, HtmlElement):
                    inner_html += "\n" + child.render(indent + 1)
                else:
                    inner_html += "\n" + (" " * ((indent + 1) * 2)) + escape(str(child))
            inner_html += "\n" + pad
        return f"{pad}<{self.tag}{attrs_str}>{inner_html}</{self.tag}>"

class HtmlDocument:

    def __init__(self):
        self.lang = 'es'
        self.head = []
        self.head.append(HtmlElement('meta',None, charset="UTF-8"))
        self.head.append(HtmlElement('title', 'MotoGP-Información del circuito'))
        self.head.append(HtmlElement('meta', None, name="author", content="Hugo Suárez Palicio"))
        self.head.append(HtmlElement('meta', None, name="description", content="Información sobre el circuito Automotodrom Brno"))
        self.head.append(HtmlElement('meta', None, name="keywords", content="circuito.MotoGP"))
        self.head.append(HtmlElement('meta', None, name="viewport", content="width=device-width, inicial-sacle=1.0"))
        self.head.append(HtmlElement('link', None, rel="stylesheet", type="text/css", href="estilo/estilo.css"))
        self.head.append(HtmlElement('link', None, rel="stylesheet", type="text/css", href="estilo/layout.css"))
        self.head.append(HtmlElement('link', None, rel="icon", href="multimedia/favicon.ico"))
        self.body = []
        self.header = []
        self.main = []

    def add_header(self, links: list[tuple[str, str]]):
        header_html = [f"<header>", f" <h1>MotoGP Desktop</h1>", " <nav>"]
        for link in links:
            href = escape(link.get('href', '#'))
            text = escape(link.get('text', ''))
            title_attr = escape(link.get('title', ''))
            class_attr = link.get('class_', '')
            class_part = f' class="{escape(class_attr)}"' if class_attr else ''
            header_html.append(f' <a href="{href}" title="{title_attr}"{class_part}>{text}</a>')
        header_html.extend([" </nav>", "</header>"])
        block = "\n".join(header_html)
        self.header.append(block)
        return block
    
    def add_section(self, title: str, contents: list[dict], level : int):
        section_html = [f"<section>", f" <h{level}>{escape(title)}</h{level}>"]
        for item in contents:
            tipo = item.get("type")
            if tipo == "paragraph":
                section_html.append(f" <p>{escape(item.get('text', ''))}</p>")
            elif tipo == "list":
                section_html.append(" <ol>")
                for elem in item.get('items', []):
                    section_html.append(f" <li>{escape(elem)}</li>")
                section_html.append(" </ol>")
            elif tipo == "reference":
                href = escape(item.get('href', '#'))
                text = escape(item.get('text', href))
                section_html.append(f" <a href=\"{href}\">{text}</a>")
            elif tipo == "image":
                src = item.get('src', '')
                src = escape(src[1:])
                alt = escape(item.get('alt', ''))
                title_attr = f' title="{escape(item.get("title", ""))}"' if item.get("title") else ""
                section_html.append(f"  <img src=\"{src}\" alt=\"{alt}\"{title_attr} />")
        section_html.append("</section>")
        block = "\n".join(section_html)
        self.main.append(block)
        return block
    
    def add_video(self, src):
        attrs = [f'src="{escape(src[1:])}"']
        attrs.append('controls')
        tag = f"<video {' '.join(attrs)}></video>"
        self.main.append(tag)
        return tag

    def render(self) -> str:
        head_html = ["  <head>"]
        for element in self.head:
            if isinstance(element, HtmlElement):
                head_html.append("    " + element.render())
            else:
                head_html.append("    " + str(element))
        head_html.append("  </head>")
        header_html = "\n".join(["  " + h for h in self.header]) if self.header else ""
        main_html = ["  <main>"] + ["  " + m for m in self.main] + ["  </main>"]
        migas = "<p> Estas en: <a href=""index.html"" title=""Inicio"">Inicio</a> >> <strong>InfoCircuito</strong> <p>"
        body_html = "  <body>\n" + migas + "\n" + header_html + "\n" + "\n".join(main_html) + "\n  </body>"
        return f"<!DOCTYPE html>\n<html lang=\"{escape(self.lang)}\">\n" + "\n".join(head_html) + "\n" + body_html + "\n</html>"


    def write(self, filename: str, encoding: str = 'utf-8') -> None:
        html_text = self.render()
        with open(filename, 'w', encoding=encoding) as f:
            f.write(html_text)

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
       
    HTML = HtmlDocument()
    raiz = arbol.getroot()

    namespaces = {'u': 'http://www.uniovi.es'}

    HTML.add_header([
    {"href": "index.html", "title": "Inicio", "text": "Inicio"},
    {"href": "piloto.html", "title": "Información del piloto", "text": "Piloto", "class_": "active"},
    {"href": "circuito.html", "title": "Información del circuito", "text": "Circuito"},
    {"href": "meteorologia.html", "title": "Información de la meteorología", "text": "Meteorología"},
    {"href": "clasificaciones.php", "title": "Información de las clasificaciones", "text": "Clasificaciones"},
    {"href": "juegos.html", "title": "Información de los juegos", "text": "Juegos"},
    {"href": "ayuda.html", "title": "Información de la ayuda", "text": "Ayuda"}
    ])

    nombre = raiz.find(".//u:nombre", namespaces=namespaces).text
    pais = raiz.find(".//u:pais", namespaces=namespaces).text

    imagenes = raiz.findall(".//u:foto", namespaces=namespaces)
    video = raiz.find(".//u:video", namespaces=namespaces).text

    HTML.add_section(
    "Automodrom Brno",
    [
        {"type": "paragraph", 
         "text": f"El circuito {nombre} es uno de los actuales circuitos de la MotoGP situado en {pais}."},
        {"type": "image",
         "src" : f"{imagenes[0].text}", "alt" : "Imagen de Automotodrom Brno"},
    ], 2)

    HTML.add_video(f"{video}")

    longitud = raiz.find(".//u:longitud", namespaces=namespaces)
    anchura = raiz.find(".//u:anchura", namespaces=namespaces)

    HTML.add_section(
    "Características",
    [
        {"type": "paragraph", 
         "text": f"El circuito tiene una longitud aproximada de {longitud.text} {longitud.attrib["unidades"]} y una anchura de {anchura.text} {anchura.attrib["unidades"]} aproximadamente."},
        {"type": "image",
         "src" : f"{imagenes[1].text}", "alt" : "Imagen de Automotodrom Brno"},
    ], 3)

    fecha = raiz.find(".//u:fecha", namespaces=namespaces).text
    hora = raiz.find(".//u:hora", namespaces=namespaces).text
    vueltas = raiz.find(".//u:vueltas", namespaces=namespaces).text
    patrocinador = raiz.find(".//u:patrocinador", namespaces=namespaces).text
    ganador = raiz.find(".//u:ganador/u:piloto", namespaces=namespaces).text
    tiempo = raiz.find(".//u:ganador/u:tiempo", namespaces=namespaces)

    HTML.add_section(
    "Trazado del ",
    [
        {"type": "paragraph", 
         "text": f"El circuito {nombre} es uno de los actuales circuitos de la MotoGP situado en {pais}."},
        {"type": "image",
         "src" : f"{imagenes[0].text}", "alt" : "Imagen de Automotodrom Brno"},
         {"type": "video",
         "src" : f"{video}"}
    ], 3)

    HTML.add_section(
    "Carrera 2025",
    [
        {"type": "paragraph", 
         "text": f"El circuito albergó la carrera correspondiente al {fecha} a las {hora}."},
        {"type": "paragraph", 
         "text": f"La carrera tuvo un total de {vueltas} vueltas. El patrocinador principal de la carrera fue {patrocinador}"},
        {"type": "image",
         "src" : f"{imagenes[2].text}", "alt" : "Imagen de Automotodrom Brno"},
        {"type": "paragraph", 
         "text": f"El ganador de la carrera fue {ganador}, con un tiempo de {tiempo.text} {tiempo.attrib["unidades"]}"},
    ], 3)

    podio = raiz.findall(".//u:clasificado", namespaces=namespaces)

    HTML.add_section(
    "Podio",
    [
        {"type": "list",
         "items": [
            f"{podio[0].text}",
            f"{podio[1].text}",
            f"{podio[2].text}"
        ]}
    ], 3)

    referencia = raiz.findall(".//u:referencia", namespaces=namespaces)

    HTML.add_section(
    "Enlaces externos",
    [
        {"type": "reference", 
         "href": f"{referencia[0].text}",
         "text": f"{referencia[0].text}"},
         {"type": "reference", 
         "href": f"{referencia[1].text}",
         "text": f"{referencia[1].text}"},
         {"type": "reference", 
         "href": f"{referencia[2].text}",
         "text": f"{referencia[2].text}"}
    ], 3)

    HTML.write("InfoCircuito.html")


if __name__ == "__main__":
    main()   
