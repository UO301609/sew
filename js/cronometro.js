class Cronometro{

    #tiempo;
    #inicio;
    #corriendo;

    constructor(){
        this.#tiempo = 0
        this.#establecerEventos()
    }

    #establecerEventos(){
        const botones = document.querySelectorAll("input")
        if(botones.length > 0){
            botones[0].addEventListener("click", this.arrancar.bind(this))
            botones[1].addEventListener("click", this.parar.bind(this))
            botones[2].addEventListener("click", this.#reiniciar.bind(this))
        }
    }

    arrancar(){
        try{
            this.#inicio = Temporal.Now.instant().epochMiliSeconds
        }catch(err){
            this.#inicio = Date.now()
        }
        this.#corriendo = setInterval(this.#actualizar.bind(this), 100)
    }

    #actualizar(){
        let actual = null
        try{
            actual = Temporal.now.plainTimeISO().epochMiliSeconds
        } catch(err) {
            actual = Date.now()
        }
        this.#tiempo = actual - this.#inicio
        this.#mostrar()
    }

    #mostrar(){
        let minutos = parseInt(this.#tiempo / 60000)
        let resto = this.#tiempo % 60000
        let segundos = parseInt(resto / 1000)
        resto = resto % 1000
        let decimas = parseInt(resto / 100)
        let cadena = minutos.toString().padStart(2,"0") + ":" +
                segundos.toString().padStart(2,"0") + "." +
                decimas.toString()
        let parrafo = document.querySelector("main p")
        parrafo.textContent = cadena
        
    }

    parar(){
        clearInterval(this.#corriendo)
        this.#mostrar()
    }

    #reiniciar(){
        clearInterval(this.#corriendo)
        this.#tiempo = 0
        this.#mostrar()
    }

}

const cronometro = new Cronometro()