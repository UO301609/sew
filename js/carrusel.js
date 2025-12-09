class Carrusel {

    #busqueda
    #actual
    #maximo
    #fotografias

    constructor() {
        this.#busqueda = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";
        this.#actual = 0
        this.#maximo = 4
        this.#fotografias = [];
    }

    getFotografias() {
        $.ajax({
            dataType: "jsonp", 
            url: this.#busqueda,
            data: {
                format: "json",
                tags: "Automotodrom Brno"
            },
            jsonp: "jsoncallback",
            success: (data) => {
                this.#procesarJSONFotografias(data)
                this.#mostrarFotografias()
            },
            error: function (err) {
                console.error("Error en la obtención de las fotos")
            }
        });
    }

    #procesarJSONFotografias(data) {
        let indice = 0
        data.items.forEach(foto => {
            if( indice <= this.#maximo){
                let url = foto.media.m.replace("_m.jpg", "_z.jpg")
                this.#fotografias[indice] = url
            }
            indice++
        });
    }

    #mostrarFotografias() {
        var titulo = $("<h2></h2>").text("Imágenes del circuito de Automotodrom Brno")
        var imagen = $("<img>")
        imagen.attr("src", this.#fotografias[this.#actual])
        imagen.attr("alt", "Imagen del circuito Automotodrom Brno")
        $("main").append("<article></article>")
        $("article").append(titulo)
        $("article").append(imagen)
        setInterval(this.#cambiarFotografia.bind(this), 3000)
    }

    #cambiarFotografia() {
        this.#actual = this.#actual == this.#maximo ? 0 : this.#actual + 1
        $("img").attr("src", this.#fotografias[this.#actual])
    }

}

const carrusel = new Carrusel()
carrusel.getFotografias()