<?php
    session_start();
    include "php/centralReservas.php";
    $bd = new CentralReservas();
    $bd->inicializarBD();

    $accion = $_GET['accion'] ?? 'login';

    // REGISTRO
    if (isset($_POST['registrar'])) {
        $resultado = $bd->registrarUsuario(
            $_POST['nombre'],
            $_POST['apellidos'],
            $_POST['correo'],
            $_POST['contraseña'],
            $_POST['telefono']
        );
        if ($resultado === true) {
            $_SESSION['usuario'] = $bd->loginUsuario($_POST['correo'], $_POST['contraseña']);
        } else {
            $_SESSION['error'] = $resultado;
        }
    }

    // LOGIN
    if (isset($_POST['login'])) {
        $resultado = $bd->loginUsuario($_POST['correo'], $_POST['contraseña']);
        if (is_array($resultado)) {
            $_SESSION['usuario'] = $resultado;
        } else {
            $_SESSION['error'] = $resultado;
        }
    }

    // CREAR RESERVA
    if (isset($_POST['reservar'])) {
        $reserva_id = $bd->crearReserva(
            $_SESSION['usuario']['id'],
            $_POST['recurso'],
            $_POST['plazas']
        );
        if (is_numeric($reserva_id)) {
            $_SESSION['reserva_pendiente'] = $reserva_id;
        } else {
            $_SESSION['error'] = $reserva_id;
        }
    }

    // CONFIRMAR RESERVA
    if (isset($_POST['confirmar'])) {
        $bd->confirmarReserva($_SESSION['reserva_pendiente']);
        unset($_SESSION['reserva_pendiente']);
    }

    // RECHAZAR RESERVA
    if (isset($_POST['rechazar'])) {
        $bd->rechazarReserva($_SESSION['reserva_pendiente']);
        unset($_SESSION['reserva_pendiente']);
    }

    // ANULAR RESERVA
    if (isset($_POST['anular'])) {
        $bd->anularReserva($_POST['reserva_id']);
    }

    // CERRAR SESIÓN
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: reservas.php");
        exit();
    }
?>

<!DOCTYPE html>

<html lang="es">
<head>

    <meta charset="UTF-8" />
    <title>Burgos-Reservas</title>
    <meta name="author" content="Hugo Suárez Palicio" />
    <meta name="description" content="Reservas de BURGOS-DESKTOP" />
    <meta name="keywords" content="Burgos,reservas" />
    <meta name="viewport" content="width=device-width, inicial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <link rel="icon" href="multimedia/favicon.ico" />
</head>

<body>

    <header>
        <h1>
            <a href="index.html" title="Inicio">Burgos Desktop</a>
        </h1>
        <nav>
            <a href="index.html" title="Inicio">Inicio</a>
            <a href="gastronomia.html" title="Información de la gastronomía">Gastronomía</a>
            <a href="rutas.html" title="Información de las rutas">Rutas</a>
            <a href="meteorologia.html" title="Información de la meteorología">Meteorología</a>
            <a href="juego.html" title="Juego">Juego</a>
            <a href="reservas.php" title="Infromación sobre las reservas" class="active">Reservas</a>
            <a href="ayuda.html" title="Información de la ayuda">Ayuda</a>
        </nav>
    </header>

    <p>Estas en:
        <a href="index.html" title="Inicio">Inicio</a> >> <strong>Reservas</strong>
    </p>

    <main>

    <?php if (!isset($_SESSION['usuario'])) { ?>

        <?php if ($accion == 'registro') { ?>

            <section>

                <h2>Registro de usuario</h2>

                <?php if (isset($_SESSION['error'])) { ?>
                    <p><strong>Error: <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></strong></p>
                <?php } ?>

                <form action='#' method="post">
                    <p>Nombre:</p>
                    <input type="text" name="nombre" required>
                    <p>Apellidos:</p>
                    <input type="text" name="apellidos" required>
                    <p>Correo electrónico:</p>
                    <input type="text" name="correo" required>
                    <p>Teléfono:</p>
                    <input type="text" name="telefono" required>
                    <p>Contraseña:</p>
                    <input type="password" name="contraseña" required>
                    <input type="submit" name="registrar" value="Registrarse">
                </form>

                <p>¿Ya tienes cuenta?</p>
                <form action='#' method="get">
                    <input type="submit" value="Iniciar sesión">
                </form>

            </section>

        <?php } else { ?>

            <section>

                <h2>Iniciar sesión</h2>

                <?php if (isset($_SESSION['error'])) { ?>
                    <p><strong>Error: <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></strong></p>
                <?php } ?>

                <form action='#' method="post">
                    <p>Correo electrónico:</p>
                    <input type="text" name="correo" required>
                    <p>Contraseña:</p>
                    <input type="password" name="contraseña" required>
                    <input type="submit" name="login" value="Entrar">
                </form>  

                <p>¿Es tu primera vez aquí?</p>
                <form action='#' method="get">
                    <input type="hidden" name="accion" value="registro">
                    <input type="submit" value="Registrarse">
                </form>

            </section>

        <?php } ?>

    <?php } else { ?>

        <h2>Menú principal</h2>
        <form action='#' method="get">
            <input type="hidden" name="accion" value="nueva">
            <input type="submit" value="Nueva reserva">
        </form>
        <form action='#' method="get">
            <input type="hidden" name="accion" value="misreservas">
            <input type="submit" value="Mis reservas">
        </form>
        <form action='#' method="get">
            <input type="hidden" name="logout" value="1">
            <input type="submit" value="Cerrar sesión">
        </form>

        <?php

        $accion = $_GET['accion'] ?? '';

        if ($accion == 'nueva') {

            $recursos = $bd->obtenerRecursos($_SESSION['usuario']['id']);

            ?>

            <?php if (isset($_SESSION['reserva_pendiente'])) {
                $presupuesto = $bd->obtenerPresupuesto($_SESSION['reserva_pendiente']);
            ?>

                <section>
                    <h3>Resumen del presupuesto</h3>
                    <p>Precio unitario: <?php echo $presupuesto['precio_unitario']; ?> €</p>
                    <p>Plazas: <?php echo $presupuesto['num_plazas']; ?></p>
                    <p>Total: <?php echo $presupuesto['total']; ?> €</p>
                    <form method="post">
                        <input type="submit" name="confirmar" value="Confirmar">
                        <input type="submit" name="rechazar" value="Rechazar">
                    </form>
                </section>

            <?php } else { ?>

                <section>
                    <h3>Reservar recurso turístico</h3>
                    <form method="post">
                        <p>Recurso turístico</p>
                        <select name="recurso">
                            <?php foreach ($recursos as $recurso) { ?>
                                <option value="<?php echo $recurso['id']; ?>">
                                    <?php echo $recurso['nombre']; ?>
                                    <?php echo " - "; ?>
                                    <?php echo $recurso['precio']; ?>
                                    <?php echo "€"; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <p>Número de plazas</p>
                        <input type="number" name="plazas" min="1" value="1" required>
                        <input type="submit" name="reservar" value="Reservar">
                    </form>
                </section>

                <?php if (isset($_SESSION['error'])) { ?>
                    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                <?php } ?>

                <section>
                    <h3>Recursos disponibles</h3>
                    <table>
                        <thead>
                            <tr>
                                <th id="nombre" scope="col">Nombre</th>
                                <th id="descripcion" scope="col">Descripción</th>
                                <th id="plazas" scope="col">Plazas</th>
                                <th id="precio" scope="col">Precio</th>
                                <th id="horario" scope="col">Horario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recursos as $recurso) { 
                                $ocupadas = $bd->obtenerPlazasOcupadas($recurso['id']);
                                $recurso['plazas_disponibles'] = $recurso['capacidad_max'] - $ocupadas;
                                $fecha = explode(" ", $recurso['fecha_inicio'])[0];
                                $hora_inicio = explode(":", $recurso['fecha_inicio'])[1] ?? explode(" ", $recurso['fecha_inicio'])[1];
                                $hora_inicio = substr($recurso['fecha_inicio'], 11, 2) . "h";
                                $hora_fin = substr($recurso['fecha_fin'], 11, 2) . "h";
                            ?>
                                <tr>
                                    <td headers="nombre"><?php echo $recurso['nombre']; ?></td>
                                    <td headers="descripcion"><?php echo $recurso['descripcion']; ?></td>
                                    <td headers="plazas"><?php echo $recurso['plazas_disponibles'] . "/" . $recurso['capacidad_max']; ?></td>
                                    <td headers="precio"><?php echo $recurso['precio']; ?> €</td>
                                    <td headers="horario"><?php echo $fecha . " " . $hora_inicio . "-" . $hora_fin; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </section>

            <?php } ?>

            <?php

        } elseif ($accion == 'misreservas') {

            $reservas = $bd->obtenerReservasUsuario($_SESSION['usuario']['id']);

            ?>

            <section>

                <h3>Mis reservas</h3>

                <?php

                if (count($reservas) == 0) {

                    echo "<p>No existen reservas.</p>";

                } else {

                    foreach ($reservas as $reserva) {

                        ?>

                        <section>

                            <h4><?php echo $reserva['recurso']; ?> (<?php echo $reserva['fecha_reserva']; ?>)</h4>
                            <p><strong>Fecha:</strong> <?php echo $reserva['fecha_inicio']; ?> --- <?php echo $reserva['fecha_fin']; ?></p>
                            <p><strong>Total:</strong> <?php echo $reserva['precio_unitario']; ?> €/persona x <?php echo $reserva['num_plazas']; ?> plazas = <?php echo $reserva['total']; ?> €</p>
                            <p><strong>Estado:</strong> <?php echo $reserva['estado']; ?></p>

                            <?php
                            if (
                                $reserva['estado'] != 'anulado' &&
                                $reserva['estado'] != 'rechazado'
                            ) {
                            ?>

                            <form action='#'method="post">
                                <input type="hidden" name="reserva_id" value="<?php echo $reserva['reserva_id']; ?>">
                                <input type="submit" name="anular" value="Anular reserva">
                            </form>

                            <?php } ?>

                        </section>

                        <?php
                    }
                }

                ?>

            </section>

            <?php
        }
        ?>

    <?php } ?>

    </main>

</body>

</html>