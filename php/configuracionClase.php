<?php
class Configuracion {

    private $servername;
    private $username;
    private $password;
    private $database;

    public function __construct() {
        $this -> servername = "localhost";
        $this -> username = "DBUSER2025";
        $this -> password = "DBPSWD2025";    
        $this -> database = "uo301609_db";
    }

    public function borrarBD() {

        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        } else {echo "<p>Conexión establecida con " . $db->host_info . "</p>";}
        
        $consulta = "DROP DATABASE IF EXISTS ".$this->database.";";

        if($db->query($consulta))
            echo "<p>Eliminada la base de datos </p>";
        else
            echo "<p>No se ha podido eliminar la base de datos. Error: " . $db->error . "</p>";

        $db->close();
    }

    public function eliminarTablas() {

        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        } else {echo "<p>Conexión establecida con " . $db->host_info . "</p>";}
        
        $tablas = [
            "comentario_usuario",
            "propuesta_usuario",
            "respuesta_usuario",
            "comentario_facilitador",
            "resultado_test",
            "usuario"
        ];

        foreach ($tablas as $t) {
            $db->query("DROP TABLE IF EXISTS $t;");
        }

        if($db->query($consulta))
            echo "<p>Eliminadas las tablas de la BD</p>";
        else
            echo "<p>No se ha podido eliminar la base de datos. Error: " . $db->error . "</p>";

        $db->close();

    }

    public function reiniciarBD() {

        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        } else {echo "<p>Conexión establecida con " . $db->host_info . "</p>";}
        
        $tablas = [
            "comentario_facilitador",
            "comentario_usuario",
            "propuesta_usuario",
            "respuesta_usuario",
            "resultado_test",
            "usuario"
        ];

        foreach ($tablas as $tabla) {
            $db->query("DELETE FROM $tabla;");
        }

        $db->close();

    }

    public function insertarUsuario($dni, $edad, $genero, $profesion, $pericia){

        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "INSERT INTO usuario (DNI, Edad, Genero, Profesion, Pericia) 
            VALUES ('$dni', $edad, '$genero', '$profesion', '$pericia')";

        $db->query($sql);
        $db->close();

    }

    public function insertarComentarioFacilitador($DNI_usuario, $comentario){
        
        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "INSERT INTO comentario_facilitador (DNI_usuario, Comentario) 
            VALUES ('$DNI_usuario', '$comentario')";

        $db->query($sql);
        $db->close();
    }

    public function insertarResultado($codigo_usuario, $dispositivo, $tiempo, $completado, $valoracion){
        
        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "INSERT INTO resultado_test (Codigo_usuario, Dispositivo, Tiempo, Completado, Valoracion) 
            VALUES ($codigo_usuario, '$dispositivo', '$tiempo', $completado, $valoracion)";

        $db->query($sql);
        $db->close();
    }

    public function insertarComentarioUsuario($codigo_resultado, $comentario){
        
        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "INSERT INTO comentario_usuario (Codigo_resultado, Comentario) 
            VALUES ($codigo_resultado, '$comentario')";

        $db->query($sql);
        $db->close();
    }

    public function insertarPropuestaUsuario($codigo_resultado, $propuesta){
        
        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "INSERT INTO propuesta_usuario (Codigo_resultado, Propuesta) 
            VALUES ($codigo_resultado, '$propuesta')";

        $db->query($sql);
        $db->close();
    }

    public function insertarRespuesta($codigo_resultado, $num_pregunta, $respuesta){
        
        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "INSERT INTO respuesta_usuario (Codigo_resultado, Num_pregunta, Respuesta) 
        VALUES ($codigo_resultado, $num_pregunta, '$respuesta')";

        $db->query($sql);
        $db->close();
    }

    public function obtenerUsuario($dni){
        
        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "SELECT Codigo FROM usuario WHERE DNI = '$dni'";

        $resultado = $db->query($sql);
        $fila = $resultado->fetch_assoc();
        $db->close();
        return $fila['Codigo']; 
    }

    public function obtenerResultado($codigo_usuario){
        
        $db = new mysqli($this->servername,
            $this->username,
            $this->password,
            $this->database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        }

        $sql = "SELECT Codigo FROM resultado_test WHERE Codigo_usuario = $codigo_usuario";

        $resultado = $db->query($sql);
        $fila = $resultado->fetch_assoc();
        $db->close();
        return $fila['Codigo'];
    }

    public function exportarCSV() {

    $db = new mysqli(
        $this->servername,
        $this->username,
        $this->password,
        $this->database
    );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=UO301609-DatosPruebasUsabilidad.csv');

    ob_end_clean();

    $salida = fopen('php://output', 'w');
    fwrite($salida, "\xEF\xBB\xBF");

    fputcsv($salida, [
        "DNI", "Edad", "Genero",
        "Profesion", "Pericia", "Dispositivo",
        "Tiempo", "Completado", "Valoracion",
        "Comentario Usuario", "Propuesta Usuario",
        "Comentario Facilitador",
        "1ºPregunta", "2ºPregunta", "3ºPregunta",
        "4ºPregunta", "5ºPregunta", "6ºPregunta",
        "7ºPregunta", "8ºPregunta", "9ºPregunta",
        "10ºPregunta"
    ], ';');

    $sql = "
        SELECT 
            u.DNI,
            u.Edad,
            u.Genero,
            u.Profesion,
            u.Pericia,

            r.Dispositivo,
            r.Tiempo,
            r.Completado,
            r.Valoracion,

            cu.Comentario AS ComentarioUsuario,
            pu.Propuesta AS PropuestaUsuario,
            cf.Comentario AS ComentarioFacilitador,

            r1.Respuesta AS P1,
            r2.Respuesta AS P2,
            r3.Respuesta AS P3,
            r4.Respuesta AS P4,
            r5.Respuesta AS P5,
            r6.Respuesta AS P6,
            r7.Respuesta AS P7,
            r8.Respuesta AS P8,
            r9.Respuesta AS P9,
            r10.Respuesta AS P10

        FROM usuario u
        LEFT JOIN resultado_test r 
            ON u.Codigo = r.Codigo_usuario
        LEFT JOIN comentario_usuario cu 
            ON r.Codigo = cu.Codigo_resultado
        LEFT JOIN propuesta_usuario pu 
            ON r.Codigo = pu.Codigo_resultado
        LEFT JOIN comentario_facilitador cf 
            ON u.DNI = cf.DNI_usuario
           
        LEFT JOIN respuesta_usuario r1  ON r.Codigo = r1.Codigo_resultado  AND r1.Num_pregunta = 1 
        LEFT JOIN respuesta_usuario r2  ON r.Codigo = r2.Codigo_resultado  AND r2.Num_pregunta = 2 
        LEFT JOIN respuesta_usuario r3  ON r.Codigo = r3.Codigo_resultado  AND r3.Num_pregunta = 3 
        LEFT JOIN respuesta_usuario r4  ON r.Codigo = r4.Codigo_resultado  AND r4.Num_pregunta = 4 
        LEFT JOIN respuesta_usuario r5  ON r.Codigo = r5.Codigo_resultado  AND r5.Num_pregunta = 5 
        LEFT JOIN respuesta_usuario r6  ON r.Codigo = r6.Codigo_resultado  AND r6.Num_pregunta = 6 
        LEFT JOIN respuesta_usuario r7  ON r.Codigo = r7.Codigo_resultado  AND r7.Num_pregunta = 7 
        LEFT JOIN respuesta_usuario r8  ON r.Codigo = r8.Codigo_resultado  AND r8.Num_pregunta = 8 
        LEFT JOIN respuesta_usuario r9  ON r.Codigo = r9.Codigo_resultado  AND r9.Num_pregunta = 9 
        LEFT JOIN respuesta_usuario r10  ON r.Codigo = r10.Codigo_resultado  AND r10.Num_pregunta = 10 

        ORDER BY u.Codigo ASC
    ";

    $res = $db->query($sql);

    while ($fila = $res->fetch_assoc()) {
        fputcsv($salida, $fila, ';');
    }

    fclose($salida);
    $db->close();
    exit;
}

}
?>