<!DOCTYPE HTML>

<html lang="es">
<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <title>MotoGP-Clasificaciones</title>
    <meta name="author" content="Hugo Suárez Palicio" />
    <meta name="description" content="Información sobre la clasificación actual" />
    <meta name="keywords" content="clasificación,MOTOGP" />
    <meta name="viewport" content="width=device-width, inicial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <link rel="icon" href="multimedia/favicon.ico" />
</head>

<?php

session_start();

class Clasificaciones {

    private $documento;
    private $xml;

    public function __construct() {
        $this->documento = "xml/circuitoEsquema.xml";
    }

    public function consultar() {
        $datos = file_get_contents($this->documento);
        if($datos!=null) {
            $datos = preg_replace("/>\s*</",">\n<",$datos);
            $this -> xml = new SimpleXMLElement($datos);
            $this -> mostrarGanador();
            $this -> mostrarPodio();
        }
    }

    private function mostrarGanador() {
        echo "<p> Ganador : {$this -> xml -> ganador -> piloto}, Tiempo = {$this -> xml -> ganador ->  tiempo} {$this -> xml -> ganador -> tiempo['unidades']}</p>";
    }

    private function mostrarPodio() {
        foreach ($this->xml->podio->clasificado as $piloto) {            
            $posicion = (string) $piloto['posicion']; 
            $nombre   = (string) $piloto;             
            echo "Posición $posicion: $nombre <br>";
        }
    }

}
?>

<body>
    <!--BLQOUE HEADER-->
    <header>
        <h1>
            <a href="index.html" title="Inicio">MotoGP Desktop</a>
        </h1>
        <nav>
            <a href="index.html" title="Inicio">Inicio</a>
            <a href="piloto.html" title="Información del piloto">Piloto</a>
            <a href="circuito.html" title="Información del circuito">Circuito</a>
            <a href="meteorologia.html" title="Información de la meteorología">Meteorología</a>
            <a href="clasificaciones.php" title="Información de las clasificaciones" class="active">Clasificaciones</a>
            <a href="juegos.html" title="Información de los juegos">Juegos</a>
            <a href="ayuda.html" title="Información de la ayuda">Ayuda</a>
        </nav>
    </header>
    <p>Estas en:
        <a href="index.html" title="Inicio">Inicio</a> >> <strong>Clasificaciones</strong>
    </p>
    <!--BLOQUE MAIN-->
    <main>
        <!--BLOQUE SECTION-->
        <section>
            <h2>Clasificaciones de MotoGP-Desktop</h2>
            
            <?php
                if(!isset( $_SESSION['clasificaciones'] ) ) {
                    $clasificaciones = new Clasificaciones();
                    $_SESSION['clasificaciones'] = $clasificaciones->consultar();
                }

                $_SESSION['clasificaciones'];
            ?>

        </section>
    </main>
</body>
</html>