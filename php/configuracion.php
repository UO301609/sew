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
        $this -> database = "UO301609_DB";
    }

    public function borarrBD() {

        $db = new mysqli($servername,$username,$password,$database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        } else {echo "<p>Conexión establecida con " . $db->host_info . "</p>";}
        
        $consulta  = "DROP DATABASE agenda ;";

        if($db->query($consulta))
            echo "<p>Eliminada la base de datos 'agenda'</p>";
        else
            echo "<p>No se ha podido eliminar la base de datos 'agenda'. Error: " . $db->error . "</p>";

        $db->close();
    }

    public function eliminarTablas() {

        $db = new mysqli($servername,$username,$password,$database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        } else {echo "<p>Conexión establecida con " . $db->host_info . "</p>";}
        
        $consulta  = "
            DROP TABLE IF EXISTS comentario_usuario;
            DROP TABLE IF EXISTS propuesta_usuario;
            DROP TABLE IF EXISTS respuesta_usuario;
            DROP TABLE IF EXISTS comentario_facilitador;
            DROP TABLE IF EXISTS resultado_test;
            DROP TABLE IF EXISTS usuario;
        ";

        if($db->query($consulta))
            echo "<p>Eliminadas las tablas de la BD</p>";
        else
            echo "<p>No se ha podido eliminar la base de datos 'agenda'. Error: " . $db->error . "</p>";

        $db->close();

    }

    public function reiniciarBD() {

        $db = new mysqli($servername,$username,$password,$database);

        if($db->connect_error) {
            exit ("<p>ERROR de conexión:".$db->connect_error."</p>");  
        } else {echo "<p>Conexión establecida con " . $db->host_info . "</p>";}
        
        $consulta  = "
            TRUNCATE TABLE comentario_facilitador;
            TRUNCATE TABLE comentario_usuario;
            TRUNCATE TABLE propuesta_usuario;
            TRUNCATE TABLE respuesta_usuario;
            TRUNCATE TABLE resultado_test;
            TRUNCATE TABLE usuario;
        ";

        if($db->query($consulta))
            echo "<p>Vaciadas las tablas de la BD</p>";
        else
            echo "<p>No se ha podido eliminar la base de datos 'agenda'. Error: " . $db->error . "</p>";

        $db->close();

    }

    public function exportarCSV() {

    }


}
?>