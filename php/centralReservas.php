<?php

class CentralReservas {

    private $servername;
    private $username;
    private $password;
    private $database;

    public function __construct() {
        $this->servername = "localhost";
        $this->username   = "DBUSER2026";
        $this->password   = "DBPWD2026";
        $this->database   = "uo301609_db";
    }

    // INICIO CONEXION A LA BASE DE DATOS
    private function conectar() {
        $db = new mysqli(
            $this->servername,
            $this->username,
            $this->password,
            $this->database
        );
        if ($db->connect_error) {
            exit("<p>ERROR de conexión: " . $db->connect_error . "</p>");
        }
        $db->set_charset("utf8mb4");
        return $db;
    }

    //CREACIÓN E INICIALIZACIÓN DE LA BASE DE DATOS
    public function inicializarBD() {

        $db = new mysqli($this->servername, $this->username, $this->password);

        if ($db->connect_error) {
            exit("<p>ERROR de conexión: " . $db->connect_error . "</p>");
        }

        if ($this->debeImportarBaseDatos($db)) {
            $this->ejecutarScriptSQL($db);
        }

        $db->select_db($this->database);
        $db->set_charset("utf8mb4");

        $res = $db->query("SELECT COUNT(*) AS total FROM tipo_recurso");
        $fila = $res->fetch_assoc();

        if ($fila['total'] == 0) {
            $this->importarCSV($db, "php/tipo_recurso.csv", "tipo_recurso");
            $this->importarCSV($db, "php/recursos_turisticos.csv", "recursos_turisticos");
        }

        $db->close();
    }

    private function debeImportarBaseDatos(mysqli $db): bool
    {
        $debeImportar = false;
        $result = $db->query(
            "SELECT SCHEMA_NAME
            FROM INFORMATION_SCHEMA.SCHEMATA
            WHERE SCHEMA_NAME = '{$this->database}'"
        );
        if ($result->num_rows === 0) {
            $debeImportar = true;
        } else {
            $db->select_db($this->database);
            $tablasNecesarias = [
                'tipo_recurso',
                'usuarios',
                'recursos_turisticos',
                'reservas',
                'presupuestos'
            ];
            foreach ($tablasNecesarias as $tabla) {
                $res = $db->query("
                    SELECT TABLE_NAME
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = '{$this->database}'
                    AND TABLE_NAME = '$tabla'
                ");
                if ($res->num_rows === 0) {
                    $debeImportar = true;
                    break;
                }
            }
        }
        return $debeImportar;
    }

    private function ejecutarScriptSQL(mysqli $db) {
        $rutaSQL = __DIR__ . '/uo301609_db.sql';

        if (!file_exists($rutaSQL)) {
            exit("<p>No se encontró el fichero SQL: {$rutaSQL}</p>");
        }

        $sql = file_get_contents($rutaSQL);

        if (!$db->multi_query($sql)) {
            exit("<p>Error ejecutando el SQL: {$db->error}</p>");
        }

        while ($db->more_results() && $db->next_result()) {
            if ($result = $db->store_result()) {
                $result->free();
            }
        }
    }

    private function importarCSV($db, $archivo, $tabla) {
        if (!file_exists($archivo)) {
            echo "<p>Archivo $archivo no encontrado.</p>";
            return;
        }

        $fichero = fopen($archivo, 'r');
        $cabecera = fgetcsv($fichero);

        while (($fila = fgetcsv($fichero)) !== false) {
            $columnas = implode(', ', array_map(fn($c) => "`$c`", $cabecera));
            $valores  = implode(', ', array_map(
                fn($v) => "'" . $db->real_escape_string($v) . "'",
                $fila
            ));
            $db->query("INSERT IGNORE INTO `$tabla` ($columnas) VALUES ($valores)");
        }

        fclose($fichero);
    }

    // -- GESTIÓN DE USUARIOS ------------------------------------------------------------  

    // REGISTRAR USUARIO
    public function registrarUsuario($nombre, $apellidos, $correo, $contraseña, $telefono) {
        if (!preg_match('/^[A-ZÁÉÍÓÚÑ]/', $nombre)) {
            return "El nombre no es correcto.";
        }
        if (!preg_match('/^[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+ [A-ZÁÉÍÓÚÑ][a-záéíóúñ]+$/', $apellidos)) {
            return "Los apellidos no son válidos.";
        }

        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $correo)) {
            return "El correo no sigue el formato correcto.";
        }
        if (!preg_match('/^[0-9]{9}$/', $telefono)) {
            return "El teléfono debe tener 9 dígitos.";
        }
        if (strlen($contraseña) < 6) {
            return "La contraseña debe tener al menos 6 caracteres.";
        }
        if ($this->comprobarCorreoExistente($correo)) {
            return "El correo ya está registrado.";
        }
        $db = $this->conectar();
        $sql = "INSERT INTO usuarios (nombre, apellidos, correo, contraseña, telefono)
                VALUES ('$nombre', '$apellidos', '$correo', '$contraseña', '$telefono')";
        $db->query($sql);
        $db->close();
        return true;
    }

    // LOGIN USUARIO
    public function loginUsuario($correo, $contraseña) {
        $db = $this->conectar();
        $sql = "SELECT *
                FROM usuarios
                WHERE correo = '$correo'
                AND contraseña = '$contraseña'";
        $res = $db->query($sql);
        $usuario = $res->fetch_assoc();
        $db->close();
        if (!$usuario) {
            return "Correo o contraseña incorrectos.";
        }
        return $usuario;
    }

    // COMPROBAR CORREO EXISTENTE
    public function comprobarCorreoExistente($correo) {
        $db = $this->conectar();
        $sql = "SELECT COUNT(*) AS total
                FROM usuarios
                WHERE correo = '$correo'";
        $res = $db->query($sql);
        $fila = $res->fetch_assoc();
        $db->close();
        return $fila['total'] > 0;
    }

    // -- GESTIÓN DE RECURSOS TURÍSTICOS ------------------------------------------------  

    // OBTENER RECURSOS
    public function obtenerRecursos($usuario_id) {
        $db  = $this->conectar();
        $sql = "SELECT r.*, t.nombre AS tipo
                FROM recursos_turisticos r
                JOIN tipo_recurso t ON r.tipo_id = t.id
                WHERE r.id NOT IN (
                    SELECT res.recurso_id
                    FROM reservas res
                    JOIN presupuestos p ON p.reserva_id = res.id
                    WHERE res.usuario_id = $usuario_id
                    AND p.estado IN ('pendiente', 'aceptado')
                )
                ORDER BY r.nombre ASC";
        $res      = $db->query($sql);
        $recursos = $res->fetch_all(MYSQLI_ASSOC);
        $db->close();
        return $recursos;
    }

    // OBTENER UN RECURSO
    public function obtenerRecurso($id) {
        $db = $this->conectar();
        $sql = "SELECT r.*, t.nombre AS tipo
                FROM recursos_turisticos r
                JOIN tipo_recurso t ON r.tipo_id = t.id
                WHERE r.id = $id";
        $res = $db->query($sql);
        $recurso = $res->fetch_assoc();
        $db->close();
        return $recurso;
    }

    // OBTENER TIPOS DE RECURSO
    public function obtenerTiposRecurso() {
        $db   = $this->conectar();
        $res  = $db->query("SELECT * FROM tipo_recurso ORDER BY nombre ASC");
        $tipos = $res->fetch_all(MYSQLI_ASSOC);
        $db->close();
        return $tipos;
    }

    // -- GESTIÓN DE RESERVAS ------------------------------------------------  

    // CREAR RESERVA
    public function crearReserva($usuario_id, $recurso_id, $num_plazas) {
        if (!$this->comprobarPlazasDisponibles($recurso_id, $num_plazas)) {
            return "No hay plazas suficientes para este recurso.";
        }
        $db = $this->conectar();
        $sql = "INSERT INTO reservas (usuario_id, recurso_id, num_plazas)
                VALUES ($usuario_id, $recurso_id, $num_plazas)";
        $db->query($sql);
        $reserva_id = $db->insert_id;
        $sqlPrecio = "SELECT precio
                    FROM recursos_turisticos
                    WHERE id = $recurso_id";
        $resPrecio = $db->query($sqlPrecio);
        $fila = $resPrecio->fetch_assoc();
        $precio_unitario = $fila['precio'];
        $total = $precio_unitario * $num_plazas;
        $sqlPres = "INSERT INTO presupuestos
                    (reserva_id, precio_unitario, num_plazas, total)
                    VALUES ($reserva_id, $precio_unitario, $num_plazas, $total)";
        $db->query($sqlPres);
        $db->close();
        return $reserva_id;
    }

    // CONFIRMAR RESERVA
    public function confirmarReserva($reserva_id) {
        $db = $this->conectar();
        $sql = "UPDATE presupuestos
                SET estado = 'aceptado'
                WHERE reserva_id = $reserva_id";
        $resultado = $db->query($sql);
        $db->close();
        return $resultado;
    }

    // RECHAZAR RESERVA
    public function rechazarReserva($reserva_id) {
        $db = $this->conectar();
        $sql = "UPDATE presupuestos
                SET estado = 'rechazado'
                WHERE reserva_id = $reserva_id";
        $resultado = $db->query($sql);
        $db->close();
        return $resultado;
    }

    // ANULAR RESERVA
    public function anularReserva($reserva_id) {
        $db = $this->conectar();
        $sql = "UPDATE presupuestos
                SET estado = 'anulado'
                WHERE reserva_id = $reserva_id";
        $resultado = $db->query($sql);
        $db->close();
        return $resultado;
    }

    // OBTENER RESERVAS DE UN USUARIO
    public function obtenerReservasUsuario($usuario_id) {
        $db = $this->conectar();
        $sql = "SELECT r.id AS reserva_id,
            r.fecha_reserva,
            r.num_plazas,
            rt.nombre AS recurso,
            rt.fecha_inicio,
            rt.fecha_fin,
            p.precio_unitario,
            p.total,
            p.estado
        FROM reservas r
        JOIN recursos_turisticos rt ON r.recurso_id = rt.id
        JOIN presupuestos p ON p.reserva_id = r.id
        WHERE r.usuario_id = $usuario_id
        ORDER BY r.fecha_reserva DESC";
        $res = $db->query($sql);
        $reservas = $res->fetch_all(MYSQLI_ASSOC);
        $db->close();
        return $reservas;
    }

    // OBTENER PRESUPUESTO
    public function obtenerPresupuesto($reserva_id) {
        $db = $this->conectar();
        $sql = "SELECT *
                FROM presupuestos
                WHERE reserva_id = $reserva_id";

        $res = $db->query($sql);
        $presupuesto = $res->fetch_assoc();
        $db->close();
        return $presupuesto;
    }

    // -- GESTIÓN DE PLAZAS ------------------------------------------------  

    public function obtenerPlazasOcupadas($recurso_id) {
        $db = $this->conectar();
        $sql = "SELECT COALESCE(SUM(r.num_plazas), 0) AS ocupadas
                FROM reservas r
                JOIN presupuestos p ON p.reserva_id = r.id
                WHERE r.recurso_id = $recurso_id
                AND p.estado IN ('pendiente', 'aceptado')";
        $res = $db->query($sql);
        $fila = $res->fetch_assoc();
        $db->close();
        return $fila['ocupadas'];
    }
    
    // COMPROBAR PLAZAS DISPONIBLES
    public function comprobarPlazasDisponibles($recurso_id, $num_plazas) {
        $db = $this->conectar();
        $sql = "SELECT capacidad_max FROM recursos_turisticos WHERE id = $recurso_id";
        $res = $db->query($sql);
        $fila = $res->fetch_assoc();
        $db->close();
        $capacidad_max = $fila['capacidad_max'];
        return ($this->obtenerPlazasOcupadas($recurso_id) + $num_plazas) <= $capacidad_max;
    }
}
?>