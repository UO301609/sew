class Juego {

    #respuestas_correctas

    constructor() {

        this.#respuestas_correctas = [
            ["¿Cuáles de las siguientes ciudades son próximas a la provincia de Burgos?", 4],
            ["¿Cuál es uno de los monumentos más representativos de Burgos?", 2],
            ["¿Qué productos son típicos de Burgos?", 2],
            ["¿Qúe es la olla podrida?", 5],
            ["Uno de los restaurantes más conocidos de la provincia es: ", 1],
            ["¿Dónde comienza la ruta de las ermitas?", 3],
            ["¿Cuál es la altitud máxima en la ruta por el casco antiguo?", 4],
            ["¿Qué monumento se visita en la ruta por el casco antiguo?", 2],
            ["¿Cuántos kilómetros (aprximadamente) es la senda del Ventanón?", 1],
            ["¿En cuál de los siguientes recursos turísticos se puede hacer una reserva?", 5]
        ]

        this.#establecerEventos();
        this.#mostrarQuizz();

    }

    #establecerEventos(){
        let finalizar = document.querySelector("main > form").children[0]
        finalizar.addEventListener("click", () => this.#comprobarRespuestas())
        let reiniciar = document.querySelector("main > form").children[1]
        reiniciar.addEventListener("click", () => this.#reiniciar())
    }

    #mostrarQuizz(){
        let form = document.querySelector("main > form")
        let quizz = Array.from(document.querySelectorAll("main article"))
        let contador = 1
        while(quizz.length > 0){
            let indice = parseInt(Math.random() * quizz.length)
            let pregunta = quizz.splice(indice, 1)[0]
            let titulo = document.createElement("h3")
            pregunta.insertBefore(titulo, pregunta.firstChild)
            titulo.textContent = "Pregunta " + contador++
            document.querySelector("main").insertBefore(pregunta, form)
        }
        this.#restablecerBotones()
    }

    #comprobarRespuestas(){
        const preguntas = document.querySelectorAll('main > article');
        let contador = 0
        for (let i = 0; i < 10; i++) {
            const radios = preguntas[i].querySelectorAll('input');
            const seleccionada = Array.from(radios).find(radio => radio.checked);
            if (seleccionada === undefined) {
                document.querySelector("main > p").innerHTML = `<strong>Nos has respondido a ${10 - i} preguntas.<strong>`
                return;
            }
            for (let j = 0; j < 10; j++) {
                if( preguntas[i].querySelector("p").textContent == this.#respuestas_correctas[j][0] &&
                    parseInt(seleccionada.value) == this.#respuestas_correctas[j][1]){
                        contador += 1
                        break
                    }
            }
        }
        this.#gestionarQuizz(true)
        document.querySelector("main > p").innerHTML = `<strong>El resultado final es de ${contador}/10 preguntas correctas.<strong>`
        document.querySelector("main > form").querySelectorAll("input")[0].hidden = true
    }

    #reiniciar(){
        this.#gestionarQuizz(false)
        this.#restablecerBotones();

        document.querySelector('main > p').innerHTML = '';
        document.querySelector("main > form").querySelectorAll("input")[0].hidden = false
        this.#mostrarQuizz();
    }

    #restablecerBotones() {
        const radios = document.querySelectorAll('main > article input');
        radios.forEach(r => r.checked = false);
    }

    #gestionarQuizz(estado){
        const inputs = document.querySelectorAll('main > article input');
        inputs.forEach(input => input.disabled = estado);
    }

}

const juego = new Juego()


