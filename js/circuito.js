class Circuito {

    #contenido

    constructor(){
        this.#contenido = ""
        this.#comprobarApiFile()
        this.#establecerEventos()
    }
    
    #comprobarApiFile(){
        if(!window.File || !window.FileReader || !window.FileList || !window.Blob) {
            console.error("El navegador no implementa FileAPI")
        }
    }

    #establecerEventos(){
        let input = document.querySelector("main input")
        input.addEventListener("change", () => {
                this.#leerArchivoHTML(input.files[0])
        });        
    }

    #leerArchivoHTML(archivo){
        let tipoTexto = /text.*/
        if(archivo && archivo.type.match(tipoTexto)){
            let lector = new FileReader()
            lector.onload = (evento) => {
                this.#contenido = evento.target.result;
                this.#mostrarContenido()
            }
            lector.readAsText(archivo)
        }else{
            console.error("Archivo no válido")
        }
    }

    #mostrarContenido() {
        let parser = new DOMParser()
        let doc = parser.parseFromString(this.#contenido, "text/html")
        let elementos = Array.from(doc.querySelectorAll("main section"))
        let video = doc.querySelector("main video")
        let mainDestino = document.querySelector("main");
        elementos.forEach((seccion, index) => {
            let copia = seccion.cloneNode(true);
            mainDestino.appendChild(copia);
            if(index == 0){
                mainDestino.appendChild(video.cloneNode(true))
            }
        });
    }

}

class CargadorSVG {

    #contenido

    constructor(){
        this.#contenido = ""
        this.#comprobarApiFile()
        this.#establecerEventos()
    }

    #comprobarApiFile(){
        if(!window.File || !window.FileReader || !window.FileList || !window.Blob) {
            console.error("El navegador no implementa FileAPI")
        }
    }

    #establecerEventos(){
        let input = document.querySelectorAll("main input")[1]
        input.addEventListener("change", () => {
                console.log(input.files[0])
                this.#leerArchivoSVG(input.files[0])
        });        
    }

    #leerArchivoSVG(archivo) {
        if(archivo && archivo.type == "image/svg+xml"){
            let lector = new FileReader()
            lector.onload = (evento) => {
                this.#contenido = evento.target.result;
                this.#insertarSVG()
            }
            lector.readAsText(archivo)
        }else{
            console.error("Archivo no válido")
        }
    }

    #insertarSVG() {
        let parser = new DOMParser()
        let doc = parser.parseFromString(this.#contenido, "image/svg+xml")
        let mainDestino = document.querySelector("main")
        let titulo = document.createElement("h3")
        titulo.innerHTML = "Altimetría del circuito"
        let svg = doc.documentElement
        svg.setAttribute("viewBox", "-20 -20 1000 390")
        let seccion = document.createElement("section")
        seccion.appendChild(titulo)
        seccion.appendChild(svg.cloneNode(true))
        mainDestino.appendChild(seccion)
    }

}

class CargadorKML {

    #contenido

    constructor(){
        this.#contenido = ""
        this.#comprobarApiFile()
        this.#establecerEventos()
    }

    #comprobarApiFile(){
        if(!window.File || !window.FileReader || !window.FileList || !window.Blob) {
            console.error("El navegador no implementa FileAPI")
        }
    }

    #establecerEventos(){
        let input = document.querySelectorAll("main input")[2]
        input.addEventListener("change", () => {
            console.log(input.files[0])
            this.#leerArchivoKML(input.files[0])
        });        
    }

    #leerArchivoKML(archivo) {
        if(archivo && archivo && archivo.name.toLowerCase().endsWith(".kml")){
            const lector = new FileReader()
                lector.onload = (evento) => {
                    this.#contenido = evento.target.result;
                    this.#insertarCapaKML()
                }
                lector.readAsText(archivo)
        }else{
            console.error("Archivo no válido")
        }
    }

    #insertarCapaKML() {

        let parser = new DOMParser()
        let doc = parser.parseFromString(this.#contenido, "application/xml")

        let pointNode = doc.querySelector("Point > coordinates")
        let lineNode = doc.querySelector("LineString > coordinates")

        if(!pointNode || !lineNode){
            console.error("El KML no contiene los nodos esperados")
            return
        }

        this.#mostrarMapa(doc)
    }

    #mostrarMapa(doc) {
        let circuito = { lat: 49.20525, lng: 16.452388889 };
        let mapaCircuito = new google.maps.Map(document.getElementById("mapa"),{zoom: 8,center:circuito});

        let inicio = doc.querySelector("Point > coordinates").textContent.split(",")
        let pos_marcador = new google.maps.LatLng(parseFloat(inicio[1]), parseFloat(inicio[0]));
        new google.maps.Marker({
          position: pos_marcador,
          map: mapaCircuito,
          title: 'Origen del circuito'
        });

        let tramos = doc.querySelector("LineString > coordinates").textContent.trim().split(/\s+/)
        let path = []
        tramos.forEach(tramo => {
            let coordenadas = tramo.split(",")
            path.push({lat : parseFloat(coordenadas[1]), lng : parseFloat(coordenadas[0])})
        });

        let polylinea = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: "#ff0000ff",
            strokeOpacity: 0.8,
            strokeWeight: 4,
        });

        polylinea.setMap(mapaCircuito);
    }

}

const circuito = new Circuito()
const SVG = new CargadorSVG()

function initMap() {
    window.KML = new CargadorKML()
}