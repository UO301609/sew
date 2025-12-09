<!DOCTYPE HTML>

<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>MotoGP-Cronometro</title>
    <meta name="author" content="Hugo Suárez Palicio" />
    <meta name="description" content="Cronómetro" />
    <meta name="keywords" content="cronómetro,MOTOGP" />
    <meta name="viewport" content="width=device-width, inicial-sacle=1.0" />
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <link rel="icon" href="multimedia/favicon.ico" />
</head>

<?php

session_start();

class Cronometro {

    private $tiempo;
    private $inicio;

    public function __construct() {
        $this->tiempo = 0;
    }

    public function arrancar() {
        $this->inicio = microtime(true);
    }

    public function parar() {
        $actual = microtime(true);
        $this->tiempo = $actual - $this->inicio;
    }

    public function mostrar() {
        $milisegundos = intval($this->tiempo * 1000);
        $minutos = intdiv($milisegundos, 60000);
        $resto = $milisegundos % 60000;
        $segundos = intdiv($resto, 1000);
        $resto = $resto % 1000;
        $decimas = intdiv($resto, 100);
        return str_pad(strval($minutos), 2, "0", STR_PAD_LEFT) . ":" .
        str_pad(strval($segundos), 2, "0", STR_PAD_LEFT) . "." . 
        strval($decimas);
    }

}
?>

<body>
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
        <a href="index.html" title="Inicio">Inicio</a> >> 
        <a href="juegos.html" title="Juegos">Juegos</a> >>
        <strong> Juego del cronómetro </strong>
    </p>
    <main>

        <h2>Cronómetro</h2>

        <form action='#' method='post'>
            <input type="submit" name="Arrancar" value="Arrancar">
            <input type="submit" name="Parar" value="Parar">
            <input type="submit" name="Mostrar" value="Mostrar">
        </form>

        <?php
        if(!isset( $_SESSION['cronometro'] ) ) {
            $_SESSION['cronometro'] = new Cronometro();
        }

        if (count($_POST) > 0) {   
            if(isset($_POST['Arrancar']))$_SESSION['cronometro']->arrancar();
            if(isset($_POST['Parar'])) $_SESSION['cronometro']->parar();
            if(isset($_POST['Mostrar'])) {
                echo "<p>" . $_SESSION['cronometro']->mostrar() . "</p>";
            }
        }
        ?>
        
    </main>
</body>
</html>