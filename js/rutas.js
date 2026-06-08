class Rutas {

    #contenido

    constructor(){
        this.#contenido = ""
        this.#cargarArchivoXML()
    }

    #cargarArchivoXML(){
        $.get("xml/rutas.xml", (contenido) => {
            this.#contenido = new XMLSerializer().serializeToString(contenido)
            this.#mostrarContenido()
        }).fail(() => {
            console.error("Error al cargar rutas.xml")
        })
    }

    #mostrarContenido() {
        let doc = $.parseXML(this.#contenido)
        const rutas = $(doc).find("rutas > ruta")
        rutas.each((i, xmlRuta) => {
            const ruta = $(xmlRuta)
            const inicio = ruta.find("inicio")
            const referencias = ruta.find("referencias")
            const mapa = $("main div").eq(i)

            let seccion = this.#obtenerInformacionGeneral(ruta)
            seccion.insertBefore(mapa)

            seccion = this.#obtenerInformacionInicio(inicio)
            seccion.insertBefore(mapa)

            seccion = this.#obtenerReferencias(referencias)
            seccion.insertBefore(mapa)

            let distancia_acumulada = 0
            ruta.find("hitos > hito").each((i, xmlHito) => {
                const hito = $(xmlHito)
                const distancia = hito.find("distancia").text()
                distancia_acumulada += parseFloat(distancia)
                if(($(hito).find("nombre").length > 0)){
                    seccion = this.#obtenerInformacionHito(distancia_acumulada, hito)
                    seccion.insertBefore(mapa)
                    distancia_acumulada = 0
                }
            })

            const svg = new CargadorSVG(ruta.find("altimetria").text(), i, mapa)
        })
    }

    #obtenerInformacionHito(distancia_acumulada, hito) {
        const formatearMedida = (valor) => Number(valor).toFixed(2);

        const nombre = hito.find("nombre").text()
        const descripcion = hito.find("descripcion").text()
        const distancia_cadena = formatearMedida(distancia_acumulada) + " km"
        const fotografia = hito.find("fotografia").text()
        const latitud = formatearMedida(hito.find("latitud").text()) + "°"
        const longitud = formatearMedida(hito.find("longitud").text()) + "°"
        const altitud = formatearMedida(hito.find("altitud").text()) + " m"

        let seccion = $("<section></section>")
        seccion.append(
            $("<h4></h4>").text(nombre)
        )
        seccion.append(
            $("<p></p>").text(descripcion)
        )
        seccion.append(
            $("<table></table>").append(
                $("<thead></thead>").append(
                    $("<tr></tr>").append(
                        $("<th></th>").text("Distancia al hito anterior"),
                        $("<th></th>").text("Latitud"),
                        $("<th></th>").text("Longitud"),
                        $("<th></th>").text("Altitud")
                    )
                )
            ).append(
                $("<tbody></tbody>").append(
                    $("<tr></tr>").append(
                        $("<td></td>").text(distancia_cadena),
                        $("<td></td>").text(latitud),
                        $("<td></td>").text(longitud),
                        $("<td></td>").text(altitud)
                    )
                )
            )
        )
        seccion.append($("<img></img>").attr("src", fotografia).attr("alt", "Imagen de" + nombre))
        return seccion
    }

    #obtenerReferencias(referencias) {
        let seccion = $("<section></section>")
        seccion.append(
            $("<h4></h4>").text("Referencias externas")
        )
        referencias.find("referencia").each((i, xmlReferencia) => {
            const referencia = $(xmlReferencia)
            seccion.append($("<a></a>").attr("href", referencia.text()).text(referencia.text()))
        })
        return seccion
    }

    #obtenerInformacionInicio(inicio) {
        const formatearMedida = (valor) => Number(valor).toFixed(2);

        let seccion = $("<section></section>")
        seccion.append(
            $("<h4></h4>").text("Inicio de la ruta")
        )
        let lista = $("<ul></ul>")
        lista.append($("<li></li>").html("<strong>Nombre: </strong>" + inicio.find("nombre").text()))
        lista.append($("<li></li>").html("<strong>Direccion: </strong>" + inicio.find("direccion").text()))
        let latitud = formatearMedida(inicio.find("latitud").text()) + "°"
        let longitud = formatearMedida(inicio.find("longitud").text()) + "°"
        let altitud = formatearMedida(inicio.find("altitud").text()) + " m"
        lista.append($("<li></li>").html(
            "<strong>Coordenadas: </strong>" +
            "Latitud = " + latitud + ", " +
            "Longitud = " + longitud + ", " +
            "Altitud = " + altitud))
        seccion.append(lista)
        return seccion
    }

    #obtenerInformacionGeneral(ruta) {
        const nombre = ruta.find("nombre").first().text()
        const tipo = ruta.find("tipo").text()
        const medio = ruta.find("medio").text()
        const fecha = ruta.find("fecha").text()
        const hora = ruta.find("hora").text()
        const tiempo = ruta.find("tiempo")
        const unidades = tiempo.attr("unidades")
        const agencia = ruta.find("agencia").text()
        const descripcion = ruta.find("descripcion").text()
        const personas = ruta.find("personas").text()
        const recomendacion = ruta.find("recomendacion").text()

        let seccion = $("<section></section>")
        seccion.append($("<h3></h3>").text(nombre))
        seccion.append($("<p></p>").text(descripcion))

        let lista = $("<ul></ul>")
        lista.append($("<li></li>").html(`<strong>Tipo:</strong> ${tipo}`))
        lista.append($("<li></li>").html(`<strong>Medio:</strong> ${medio}`))
        lista.append($("<li></li>").html(`<strong>Fecha:</strong> ${fecha} a las ${hora}`))
        lista.append($("<li></li>").html(`<strong>Duración:</strong> ${tiempo.text()} ${unidades}`))
        lista.append($("<li></li>").html(`<strong>Agencia:</strong> ${agencia}`))
        lista.append($("<li></li>").html(`<strong>Recomendación:</strong> ${recomendacion}`))
        lista.append($("<li></li>").html(`<strong>Personas:</strong> ${personas}`))

        seccion.append(lista)
        return seccion
    }
}

class CargadorSVG {

    #contenido

    constructor(rutaSVG, i, mapa){
        this.#contenido = ""
        this.#cargarSVG(rutaSVG, i, mapa)
    }

    #cargarSVG(rutaSVG, i, mapa){
        $.get(rutaSVG, (contenido) => {
            this.#contenido = new XMLSerializer().serializeToString(contenido)
            this.#insertarSVG(i, mapa)
        }).fail(() => {
            console.error("Error al cargar el SVG: " + rutaSVG)
        })
    }

    #insertarSVG(i, mapa) {
        let parser = new DOMParser()
        let doc = parser.parseFromString(this.#contenido, "image/svg+xml")
        let svg = doc.documentElement
        svg.setAttribute("viewBox", "-20 -20 1000 390")
        let seccion = $("<section></section>")
        seccion.append($("<h4></h4>").text("Altimetría de la ruta " + (i + 1)))
        seccion.append(svg.cloneNode(true))
        seccion.insertBefore(mapa)
    }

}

class CargadorKML {

    #contenido

    constructor(){
        this.#contenido = ""
        this.#cargarArchivoXML()
    }

    #cargarArchivoXML(){
        $.get("xml/rutas.xml", (contenido) => {
            this.#contenido = new XMLSerializer().serializeToString(contenido)
            this.#procesarXML()
        }).fail(() => {
            console.error("Error al cargar rutas.xml")
        })
    }

    #procesarXML(){
        let doc = $.parseXML(this.#contenido)
        $(doc).find("rutas > ruta").each((i, ruta) => {
            const rutaKML = $(ruta).find("planimetria").text()
            this.#cargarKML(rutaKML, i)
        })
    }

    #cargarKML(rutaKML, i){
        $.get(rutaKML, (contenido) => {
            this.#contenido = new XMLSerializer().serializeToString(contenido)
            this.#insertarCapaKML(i)
        }).fail(() => {
            console.error("Error al cargar el KML: " + rutaKML)
        })
    }

    #insertarCapaKML(i) {
        let doc = $.parseXML(this.#contenido)
        let pointNode = $(doc).find("Point > coordinates")
        let lineNode = $(doc).find("LineString > coordinates")

        if(pointNode.length === 0 || lineNode.length === 0){
            console.error("El KML no contiene los nodos esperados")
            return
        }
        this.#mostrarMapa($(doc), i)
    }

    #mostrarMapa(doc, i) {
        let inicio = doc.find("Point").find("coordinates").first().text().trim().split(",")
        let centro = { lat: parseFloat(inicio[1]), lng: parseFloat(inicio[0]) }
        const mapa = $("main > div").eq(i)[0]
        $("<h4></h4>").text("Planimetría de la ruta " + (i + 1)).insertBefore(mapa)
        let mapaRuta = new google.maps.Map(mapa, { zoom: 8, center: centro })

        doc.find("Placemark Point coordinates").each((i, punto) => {
            const coords = $(punto).text().trim().split(",");
            new google.maps.Marker({
                position: {
                    lat: parseFloat(coords[1]),
                    lng: parseFloat(coords[0])
                },
                map: mapaRuta
            });

        });

        let tramos = doc.find("LineString > coordinates").text().trim().split(/\s+/)
        let path = tramos.map(tramo => {
            let coordenadas = tramo.split(",")
            return { lat: parseFloat(coordenadas[1]), lng: parseFloat(coordenadas[0]) }
        })

        new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: "#ff0000ff",
            strokeOpacity: 0.8,
            strokeWeight: 4
        }).setMap(mapaRuta)

        const bounds = new google.maps.LatLngBounds();

        bounds.extend(centro);

        path.forEach(punto => {
            bounds.extend(punto);
        });

        mapaRuta.fitBounds(bounds);

        console.log("Ruta " + i)
        console.log(mapa)
        console.log(mapaRuta)
    }


}

const rutas = new Rutas()
function initMap() {
    window.KML = new CargadorKML()
}