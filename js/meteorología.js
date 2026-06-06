class Meteorología {

    getMeteorologiaActual() {
        let url = "https://api.open-meteo.com/v1/forecast?latitude=42.3411&longitude=-3.7018&current=temperature_2m,apparent_temperature,relative_humidity_2m,is_day,wind_speed_10m,wind_direction_10m,rain"
        $.ajax({
            dataType: "json",
            url: url,
            success: (data) => {
                let datos = this.#procesarJSONMeteorologiaActual(data)
                this.#mostrarDatosMeteorologiaActual(datos)
            },
            error: function (err) {
                console.error("Error en la obtención de los datos meteorológicos actuales", err)
            }
        });
    }

    #procesarJSONMeteorologiaActual(data) {
        let partes = data.current.time.split("T")
        let luz = data.current.is_day == 1 ? "Día" : "Noche"

        let parrafo = $("<ul></ul>").html(
            `<li><strong>Fecha:</strong> ${partes[0]} </li>` +
            `<li><strong>Hora:</strong> ${partes[1]} </li>` +
            `<li><strong>${luz}</strong></li>`
        )

        let tabla = $("<table></table>")
        tabla.append(this.#obtenerCabecerasMeteorologiaActual())
        tabla.append(this.#obtenerDatosMeteorologiaActual(data))

        return { parrafo, tabla }
    }

    #obtenerCabecerasMeteorologiaActual() {
        let cabecera = $("<tr></tr>")
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", "temperatura_act").text("Temperatura / Sensación"))
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", "lluvia_act").text("Lluvia"))
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", "humedad_act").text("Humedad"))
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", "viento_act").text("Viento / Dirección"))
        return $("<thead></thead>").append(cabecera)
    }

    #obtenerDatosMeteorologiaActual(data) {
        let datos = $("<tr></tr>")
        datos.append($("<td></td>").attr("headers", "temperatura_act").text(
            data.current.temperature_2m + " / " + data.current.apparent_temperature + " " + data.current_units.temperature_2m
        ))
        datos.append($("<td></td>").attr("headers", "lluvia_act").text(
            data.current.rain + " " + data.current_units.rain
        ))
        datos.append($("<td></td>").attr("headers", "humedad_act").text(
            data.current.relative_humidity_2m + " " + data.current_units.relative_humidity_2m
        ))
        datos.append($("<td></td>").attr("headers", "viento_act").text(
            data.current.wind_speed_10m + " " + data.current_units.wind_speed_10m +
            " / " + data.current.wind_direction_10m + " " + data.current_units.wind_direction_10m
        ))
        return $("<tbody></tbody>").append(datos)
    }

    #mostrarDatosMeteorologiaActual(datos) {
        let seccion = $("<section></section>")
        seccion.append($("<h3></h3>").text("Información meteorológica actual"))
        seccion.append(datos.parrafo)
        seccion.append(datos.tabla)
        $("main").append(seccion)
    }

    getMeteorologiaProximaSemana() {
        let url = "https://api.open-meteo.com/v1/forecast?latitude=42.3411&longitude=-3.7018&" +
            "daily=sunrise,sunset,temperature_2m_max,temperature_2m_min,rain_sum,wind_speed_10m_max&" +
            "hourly=temperature_2m,relative_humidity_2m,wind_speed_10m&timezone=auto"
        $.get(url, (data) => {
            let dias = this.#procesarJSONMeteorologiaProximaSemana(data)
            this.#mostrarDatosMeteorologiaProximaSemana(dias)
        }).fail((err) => {
            console.error("Error en la obtención de los datos meteorológicos de los próximos 7 días", err)
        })
    }

    #procesarJSONMeteorologiaProximaSemana(data) {
        let dias = []

        for (let dia = 0; dia < 7; dia++) {
            let comienzo = dia * 24
            let sumTemp = 0
            let sumHumedad = 0

            for (let indice = comienzo; indice < comienzo + 24; indice++) {
                sumTemp += data.hourly.temperature_2m[indice]
                sumHumedad += data.hourly.relative_humidity_2m[indice]
            }

            let tabla = $("<table></table>")
            tabla.append(this.#obtenerCabecerasMeteorologiaDia(dia))

            let fila = $("<tr></tr>")
            let id = "temp_max_" + dia
            fila.append($("<td></td>").attr("headers", id).text(data.daily.temperature_2m_max[dia] + " °C"))
            id = "temp_med_" + dia
            fila.append($("<td></td>").attr("headers", id).text((sumTemp / 24).toFixed(2) + " °C"))
            id = "temp_min_" + dia
            fila.append($("<td></td>").attr("headers", id).text(data.daily.temperature_2m_min[dia] + " °C"))
            id = "lluvia_" + dia
            fila.append($("<td></td>").attr("headers", id).text(data.daily.rain_sum[dia] + " mm"))
            id = "humedad_" + dia
            fila.append($("<td></td>").attr("headers", id).text((sumHumedad / 24).toFixed(2) + " %"))
            id = "viento_" + dia
            fila.append($("<td></td>").attr("headers", id).text(data.daily.wind_speed_10m_max[dia] + " km/h"))
            tabla.append($("<tbody></tbody>").append(fila))

            dias.push({
                fecha: data.daily.time[dia],
                amanecer: data.daily.sunrise[dia].split("T")[1],
                anochecer: data.daily.sunset[dia].split("T")[1],
                tabla: tabla
            })
        }

        return dias
    }

    #obtenerCabecerasMeteorologiaDia(dia) {
        let cabecera = $("<tr></tr>")
        let id = "temp_max_" + dia
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", id).text("Temp. máx."))
        id = "temp_med_" + dia
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", id).text("Temp. med."))
        id = "temp_min_" + dia
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", id).text("Temp. mín."))
        id = "lluvia_" + dia
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", id).text("Lluvia"))
        id = "humedad_" + dia
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", id).text("Humedad media"))
        id = "viento_" + dia
        cabecera.append($("<th></th>").attr("scope", "col").attr("id", id).text("Viento máx."))
        return $("<thead></thead>").append(cabecera)
    }

    #mostrarDatosMeteorologiaProximaSemana(dias) {
        let seccionGeneral = $("<section></section>")
        seccionGeneral.append($("<h3></h3>").text("Previsión meteorológica para los próximos 7 días"))

        dias.forEach((dia) => {
            let seccion = $("<section></section>")
            seccion.append($("<h4></h4>").text(dia.fecha))
            seccion.append($("<p></p>").html(
                `<strong>Amanecer:</strong> ${dia.amanecer} &nbsp; <strong>Anochecer:</strong> ${dia.anochecer}`
            ))
            seccion.append(dia.tabla)
            seccionGeneral.append(seccion)
        })

        $("main").append(seccionGeneral)
    }

}

const m = new Meteorología();

m.getMeteorologiaActual()
m.getMeteorologiaProximaSemana()
