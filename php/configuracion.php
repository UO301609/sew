<!DOCTYPE HTML>

<html lang="es">
<head>

    <meta charset="UTF-8" />
    <title>MotoGP-Cronometro</title>
    <meta name="author" content="Hugo Suárez Palicio" />
    <meta name="description" content="Configuración" />
    <meta name="keywords" content="configuración" />
    <meta name="viewport" content="width=device-width, inicial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" href="multimedia/favicon.ico" />
</head>
<body>

    <main>

        <h1>Configuración</h1>

        <?php 
        include "configuracionClase.php";
        session_start();

        if(!isset( $_SESSION['configuracion'] ) ) {
            $_SESSION['configuracion'] = new Configuracion();
        }

        if(isset($_POST['reiniciar'])){
            $_SESSION['configuracion']->reiniciarBD();
        }elseif (isset($_POST['exportar'])){
            $_SESSION['configuracion']->exportarCSV();
        }elseif (isset($_POST['eliminar'])){
            $_SESSION['configuracion']->eliminarTablas();
        }
        ?>

        <form action='#' method='post'>
            <input type="submit" name="reiniciar" value="Reiniciar BD">
            <input type="submit" name="exportar" value="Exportar BD a .csv">
            <input type="submit" name="eliminar" value="Borrar BD">
        </form>
    </main>

</body>
</html>