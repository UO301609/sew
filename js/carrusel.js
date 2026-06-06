class Carrusel {

    #actual
    #maximo
    #fotografias

    constructor() {
        this.#actual = 0
        this.#maximo = 4
        this.#fotografias = [
            "multimedia/mapa1.jpg",
            "multimedia/mapa2.jpg",
            "multimedia/recurso1.jpg",
            "multimedia/recurso2.jpg",
            "multimedia/recurso3.jpg",
            "multimedia/recurso4.jpg"
        ];
    }

    getFotografias() {
       this.#mostrarFotografias()
    }

    #mostrarFotografias() {
        var imagen = $("<img>")
        imagen.attr("src", this.#fotografias[this.#actual])
        imagen.attr("alt", "Recurso turístico de Burgos")
        $("main").append("<article></article>")
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