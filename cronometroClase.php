<?php 

class Cronometro {

    private $tiempo;        
    private $inicio;        
    private $activo;        

    public function __construct() {
        $this->tiempo = 0;
        $this->activo = false;
    }

    public function arrancar() {
        if (!$this->activo) {
            $this->inicio = microtime(true);
            $this->activo = true;
        }
    }

    public function parar() {
        if ($this->activo) {
            $actual = microtime(true);
            $this->tiempo = $actual - $this->inicio;
            $this->activo = false;
        }
    }

    public function mostrar() {

        if (!$this->activo && $this->tiempo == 0) {
                return "00:00.0";
        }       

        $tiempoMostrar = $this->tiempo;

        if ($this->activo) {
            $tiempoMostrar += (microtime(true) - $this->inicio);
        }

        $milisegundos = intval($tiempoMostrar * 1000);
        $minutos = intdiv($milisegundos, 60000);
        $resto = $milisegundos % 60000;
        $segundos = intdiv($resto, 1000);
        $resto = $resto % 1000;
        $decimas = intdiv($resto, 100);

        return str_pad($minutos, 2, "0", STR_PAD_LEFT) . ":" .
               str_pad($segundos, 2, "0", STR_PAD_LEFT) . "." .
               $decimas;
    }
}

?>