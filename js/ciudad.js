class Ciudad {

    #nombre
    #pais
    #gentilicio
    #poblacion
    #coordenadas

    constructor(nombre, pais, gentilicio){
        this.#nombre = nombre
        this.#pais = pais
        this.#gentilicio = gentilicio
    }

    setRest(poblacion, coordenadas){
        this.#poblacion = poblacion
        this.#coordenadas = coordenadas
    }

    getNombre(){
        return this.#nombre
    }

    getPais(){
        return this.#pais
    }

    getInformacionSecundaria(){
        return [this.#gentilicio, this.#poblacion]
    }

    escribirCoordenadas(){
        let parrafo = "\n\tCordernadas: " + this.#coordenadas + "\n"
        let elemento = document.createElement("p")
        elemento.innerHTML = parrafo
        document.querySelector('main').appendChild(elemento)
    }

    getMeteorologiaCarrera() {
        let url = "https://archive-api.open-meteo.com/v1/archive?latitude=49.20278&longitude=16.44528" + 
        "&start_date=2025-07-20&end_date=2025-07-20&daily=sunrise,sunset&hourly=temperature_2m,relative_humidity_2m," + 
        "wind_speed_10m,wind_direction_10m,rain,apparent_temperature&timezone=Europe%2FBerlin"
        $.ajax({
            dataType: "json",
            url: url,
            success: (data) => {
                let datos = this.#procesarJSONCarrera(data)
                this.#mostrarContenido(datos)
            },
            error: function (err) {
                console.error("Error en la obtención de los datos meteorológicos de la carrera", err)
        }});
    }

    #procesarJSONCarrera(data) {
        let salida_sol = $("<p></p>").text(`Amanecer : ${data.daily.sunrise[0].split("T")[1]}`)
        let puesta_sol = $("<p></p>").text(`Anochecer : ${data.daily.sunset[0].split("T")[1]}`)
        let horas = $("<ul></ul>")
        for(let indice = 0; indice < 24; indice++) {
            let hora = data.hourly.time[indice].split("T")[1]
            let sensacion = data.hourly.apparent_temperature[indice]
            let lluvia = data.hourly.rain[indice]
            let humedad = data.hourly.relative_humidity_2m[indice]
            let temperatura = data.hourly.temperature_2m[indice]
            let direccion_viento = data.hourly.wind_direction_10m[indice]
            let velocidad_viento = data.hourly.wind_speed_10m[indice]
            let contenido = $("<li></li>").text(`${hora} : Sensación térmica = ${sensacion}, Temperatura = ${temperatura}, ` + 
                `Lluvia = ${lluvia}, Humedad = ${humedad}, ` + 
                `Dirección del viento = ${direccion_viento}, Velocidad del viento = ${velocidad_viento}`)
            horas.append(contenido)
        }
        return [salida_sol, puesta_sol, horas]
    }

    #mostrarContenido(datos) {
        let titulo = $("<h3></h3>").text("Datos meteorológicos del día de la carrera (20-07-2025)")
        $("main").append(titulo)
        for(let indice = 0; indice < 3; indice++){
            $("main").append(datos[indice])
        }
    }

    getMeteorologiaEntrenos() {
        let url = "https://archive-api.open-meteo.com/v1/archive?latitude=49.20278&longitude=16.44528&start_date=2025-07-17" + 
        "&end_date=2025-07-19&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m,rain&timezone=Europe%2FBerlin"
        $.ajax({
            dataType: "json",
            url: url,
            success: (data) => {
                let datos = this.#procesarJSONEntrenos(data)
                this.#mostrarContenidoEntrenos(datos)
            },
            error: function (err) {
                console.error("Error en la obtención de los datos meteorológicos de los entrenos", err)
        }});
    }

    #procesarJSONEntrenos(data) {
        return [this.#rellenarLista(0, data), this.#rellenarLista(24, data), this.#rellenarLista(48, data)]
    }

    #rellenarLista(comienzo, data) {
        let horas = $("<ul></ul>")
        for(let indice = comienzo; indice < comienzo + 24; indice++) {
            let hora = data.hourly.time[indice].split("T")[1]
            let lluvia = data.hourly.rain[indice]
            let humedad = data.hourly.relative_humidity_2m[indice]
            let temperatura = data.hourly.temperature_2m[indice]
            let velocidad_viento = data.hourly.wind_speed_10m[indice]
            let contenido = $("<li></li>").text(`${hora} : Temperatura = ${temperatura}, ` + 
                `Lluvia = ${lluvia}, Humedad = ${humedad}, Velocidad del viento = ${velocidad_viento}`)
            horas.append(contenido)
        }
        return horas
    }

    #mostrarContenidoEntrenos(datos) {
        let titulo = $("<h3></h3>").text("Datos meteorológicos de los días de entrenamiento (Del 17-07-2025 al 19-07-2025)")
        $("main").append(titulo)
        let dia = $("<h4></h4>").text("17-07-2025")
        $("main").append(dia)
        $("main").append(datos[0])
        dia = $("<h4></h4>").text("18-07-2025")
        $("main").append(dia)
        $("main").append(datos[1])
        dia = $("<h4></h4>").text("19-07-2025")
        $("main").append(dia)
        $("main").append(datos[2])
    }

}

const c = new Ciudad("Brno","Republica Checa","brunense");
c.setRest("400.000 habitantes","49.1953N,16.6083E")

let cadena = "\n\t" + c.getNombre() + " es una ciudad situada en " + c.getPais() + "\n"
let elemento = document.createElement("p")
elemento.innerHTML = cadena
document.querySelector("main").appendChild(elemento)

let lista = document.createElement("ul")
for(let contador = 0; contador <= 1; contador++){
    let elemento_lista = document.createElement("li")
    elemento_lista.innerHTML = c.getInformacionSecundaria()[contador]
    lista.appendChild(elemento_lista)
}
document.querySelector("main").appendChild(lista)

c.escribirCoordenadas()

c.getMeteorologiaCarrera()
c.getMeteorologiaEntrenos()