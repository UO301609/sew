class Memoria{

    #tablero_bloqueado = true;
    #primera_carta = null;
    #segunda_carta = null;
    #cronometro;

    constructor(){
        this.#barajarCartas()
        this.#establecerEventos()
        this.#tablero_bloqueado = false     
        this.#cronometro = new Cronometro()
        this.#cronometro.arrancar()
    }

    #establecerEventos(){
        let cartas = document.querySelectorAll("main article")
        for(let indice = 0; indice < cartas.length; indice++){
            cartas[indice].addEventListener("click", () => {
                this.#voltearCarta(cartas[indice]);
            });        
        }
    }

    #barajarCartas(){
        let cartas = Array.from(document.querySelectorAll("main article"))
        
        while(cartas.length > 0){
            let indice = parseInt(Math.random() * cartas.length);
            let carta = cartas.splice(indice, 1)[0]
            document.querySelector("main").appendChild(carta)
        }
    }

    #voltearCarta(carta){
        if(carta.dataset.estado != "revelada" && carta.dataset.estado != "volteado" && !this.#tablero_bloqueado){
            carta.dataset.estado = "volteada"
            if(this.#primera_carta == null){
                this.#primera_carta = carta
            }else{
                this.#segunda_carta = carta
                this.#comprobarPareja()
            }
        }
    }

    #comprobarPareja(){
        const img1 = this.#primera_carta.querySelector("img").src;
        const img2 = this.#segunda_carta.querySelector("img").src;
        img1 == img2 ? this.#deshabilitarCartas() : this.#cubrirCartas()
    }

    #cubrirCartas(){
        this.#tablero_bloqueado = true
        setTimeout(function() {
            this.#primera_carta.dataset.estado = "";
            this.#segunda_carta.dataset.estado = "";
            this.#reiniciarAtributos();
        }.bind(this), 750);
    }

    #reiniciarAtributos(){
        this.#tablero_bloqueado = false
        this.#primera_carta = null
        this.#segunda_carta = null
    }

    #deshabilitarCartas(){
        this.#primera_carta.dataset.estado = "revelada"
        this.#segunda_carta.dataset.estado = "revelada"
        this.#comprobarJuego()
        this.#reiniciarAtributos()
    }

    #comprobarJuego(){
        let cartas = Array.from(document.querySelectorAll("main article"))  
        let final = cartas.every(carta => carta.dataset.estado == "revelada")
        if(final){
            this.#cronometro.parar()
        }
    }

}

const memoria = new Memoria()