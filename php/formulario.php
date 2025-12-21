<!DOCTYPE HTML>

<html lang="es">
<head>
    
    <meta charset="UTF-8" />
    <title>MotoGP-Ayuda</title>
    <meta name="author" content="Hugo Suárez Palicio" />
    <meta name="description" content="Formulario de usabilidad del proyecto MotoGP-Desktop" />
    <meta name="keywords" content="usabilidad" />
    <meta name="viewport" content="width=device-width, inicial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" href="multimedia/favicon.ico" />
</head>

<body>
<main>

    <h1>Test de usabilidad de MOTOGP Desktop</h1>

    <?php 
    include "configuracionClase.php";
    include "../cronometroClase.php";
    session_start();

    if(!isset( $_SESSION['cronometro'] ) ) {
        $_SESSION['cronometro'] = new Cronometro();
        $_SESSION['cronometro']->arrancar();
    }

    if(!isset( $_SESSION['configuracion'] ) ) {
        $_SESSION['configuracion'] = new Configuracion();
    }
   
    if(isset($_POST['enviar'])){
        $dni = $_POST['DNI'];
        $edad = $_POST['Edad'];
        $genero = $_POST['genero'] ?? "";
        $profesion = $_POST['Profesion'];
        $pericia = $_POST['Pericia'];
        $dispositivo = $_POST['dispositivo'] ?? "Ordenador";
        $valoracion = $_POST['valoracion'] ?? 0;
        $comentario_usuario = trim($_POST['comentarioUS'] ?? "");
        $propuesta = trim($_POST['propuesta'] ?? "");
        $comentario_facilitador = trim($_POST['comentarioFC'] ?? "");
        $completado = (count($_SESSION['respuestas']) === 10);

        if ($dni == "" || $edad == "" || $genero == "" || $profesion == "") {
            echo "<span> Se deben aportar todos los datos <span>";
            $_SESSION['estado'] = "pedir_datos_usuario";
        }else{
            $_SESSION['configuracion']->insertarUsuario($dni, $edad, $genero, $profesion, $pericia);

            $codigo_usuario = $_SESSION['configuracion']->obtenerUsuario($dni);

            $_SESSION['configuracion']->insertarResultado($codigo_usuario, $dispositivo, $_SESSION['tiempo_test'], $completado, $valoracion);

            $codigo_resultado = $_SESSION['configuracion']->obtenerResultado($codigo_usuario);

            foreach ($_SESSION['respuestas'] as $num => $respuesta) {
                $_SESSION['configuracion']->insertarRespuesta($codigo_resultado, $num, $respuesta);
            }

            if($comentario_usuario != ""){
                $_SESSION['configuracion']->insertarComentarioUsuario($codigo_resultado, $comentario_usuario);
            }

            if($comentario_facilitador != ""){
                $_SESSION['configuracion']->insertarComentarioFacilitador($dni, $comentario_facilitador);
            }

            if($propuesta != ""){
                $_SESSION['configuracion']->insertarPropuestaUsuario($codigo_resultado, $propuesta);
            }
        }

        unset($_SESSION['estado']);

    }

    if (isset($_POST['terminar'])) {
        $_SESSION['respuestas'] = [];
        $_SESSION['cronometro']->parar();
        $_SESSION['tiempo_test'] = $_SESSION['cronometro']->mostrar();

        for ($i = 1; $i <= 10; $i++) {
            if ($_POST["p$i"] != ""){
                $respuesta = $_POST["p$i"];
                $_SESSION['respuestas'][$i] = $respuesta;
            }else{
                $_SESSION['respuestas'][$i] = "No_respondida";
            }
        }

        $_SESSION['estado'] = "pedir_datos_usuario";
    }

    if (isset($_SESSION['estado']) && $_SESSION['estado'] === "pedir_datos_usuario") {
    ?>

        <form action='#' method='post'>
            <p>DNI</p> 
            <p>
                <input type='text' name='DNI'/>
            </p>
            <p>Edad</p>
            <p>
                <input type='text' name='Edad'/>
            </p>
            <p>Género</p>
            <p>
                <input type='radio' name='genero' value='Hombre'/>Hombre
                <input type='radio' name='genero' value='Mujer'/>Mujer
                <input type='radio' name='genero' value='Otro'/>Otro
            </p>
            <p>Profesión</p>
            <p>
                <input type='text' name='Profesion'/>
            </p>
            <p>Dispositivo <select name='dispositivo'>
                <option value='Ordenador'>Ordenador</option>
                <option value='Tableta'>Tableta</option>
                <option value='Teléfono'>Teléfono</option>
                </select>
            </p>
            <p>Pericia <select name='Pericia'>
                <option value='Muy poca'>Muy poca</option>
                <option value='Poca'>Poca</option>
                <option value='Normal'>Normal</option>
                <option value='Buena'>Buena</option>
                <option value='Muy buena'>Muy buena</option>
                </select>
            </p>
            <p>Comentario del usuario</p>
            <p>
                <textarea name='comentarioUS' rows='5' cols='40'>
                </textarea>
            </p>
            <p>Propuestas del usuario</p>
            <p>
                <textarea name='propuesta' rows='5' cols='40'>
                </textarea>
            </p>
            <p>Valoración de la aplicación</p>
            <p>
                <input type="number" name="valoracion" min="0" max="10" step="1"/>
            </p>
            <p>Comentario del facilitador</p>
            <p>
                <textarea name='comentarioFC' rows='5' cols='40'>
                </textarea>
            </p>
            <p>
                <input type='submit' name= 'enviar' value='Enviar datos'/>
            </p>
        </form>

    <?php
            exit;
        }
        if (!isset($_POST['iniciar']) && !isset($_POST['terminar'])) {

    ?>
        <form action='#' method='post'>
            <input type="submit" name="iniciar" value="Iniciar prueba">
        </form>

    <?php 
            exit;
        }
        $_SESSION['cronometro']->arrancar();
    ?>

        <form action='#' method='post'>
            <p>1º ¿Se pueden visualizar correctamente las fotos del carrusel?</p>
            <p><input type='text' name='p1'/></p>
            <p>2º ¿Se pueden visualizar correctamente las noticias?</p>
            <p><input type='text' name='p2'/></p>
            <p>3º ¿Se muestra correctamente el tiempo para todas las franjas horarias solicitadas
                y la media diaria de cada estadística?</p>
            <p><input type='text' name='p3'/></p>
            <p>4º ¿Existe algún problema al seleccionar y mostrar el archivo altimetria.svg?</p>
            <p><input type='text' name='p4'/></p>
            <p>5º ¿En qué país se encuentra el circuito Automotodrom Brno?</p>
            <p><input type='text' name='p5'/></p>
            <p>6º ¿El documento de ayuda está completo?</p>
            <p><input type='text' name='p6'/></p>
            <p>7º ¿Funciona correctamente el juego de memoria?</p>
            <p><input type='text' name='p7'/></p>
            <p>8º ¿Funciona correctamente el juego del cronómetro?</p>
            <p><input type='text' name='p8'/></p>
            <p>9º ¿Se puede visualizar correctamente el mapa y el trazado del circuito?</p>
            <p><input type='text' name='p9'/></p>
            <p>10º ¿Existe algún problema con el video y el audio del circuito?</p>
            <p><input type='text' name='p10'/></p>
            <input type="submit" name="terminar" value="Terminar prueba">
        </form>

</main>
</body>
</html>