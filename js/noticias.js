class Noticias {

    #busqueda
    #url

    constructor() {
        this.#busqueda = "motogp"
        this.#url = `https://api.thenewsapi.com/v1/news/all?api_token=VSaMm7xjBrTukj6zssQmE9AbKxKyr3Gr2YcRkXBA&` +
        `search=${this.#busqueda}&language=es`
    }

    async buscar() {
        try {
            const respuesta = await fetch(this.#url)
            if (!respuesta.ok) {
                console.error("Error en la petición")
            }
            const data = await respuesta.json()
            console.log("Noticias MotoGP:", data)
            let datos = this.#procesarInformacion(data)
            this.#mostrarNoticias(datos)
        } catch (error) {
            console.error("Error al obtener noticias:", error);
        }
    }

    #procesarInformacion(data) {
        let seccion = $("<section></section>")
        for(let indice = 0; indice < data.data.length; indice++ ){
            let titulo = data.data[indice].title
            let val = $("<h3></h3>").text(titulo)
            seccion.append(val)
            let entradilla = data.data[indice].description
            val = $("<p></p>").text(entradilla)
            seccion.append(val)
            let enlace = data.data[indice].url
            val = $("<a></a>").attr("href", "https://www.motogp.com").text("https://www.motogp.com")
            seccion.append(val)
            let fuente = data.data[indice].source
            val = $("<p></p>").text(`Fuente : ${fuente}`)
            seccion.append(val)
        }
        return seccion
    }

    #mostrarNoticias(datos) {
        let titulo = $("<h2></h2>").text("Noticias relacionadas con la MotoGP")
        $("main").append(titulo)
        $("main").append(datos)
    }

}

const noticias = new Noticias()
noticias.buscar()