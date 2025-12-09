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
        mainDestino.appendChild(svg.cloneNode(true))
    }

}

class CargadorKML {

}

const circuito = new Circuito()
const SVG = new CargadorSVG()