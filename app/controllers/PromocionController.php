<?php
/*
|--------------------------------------------------------------------------
| Controlador de Paquetes (Público)
|--------------------------------------------------------------------------
|
| Muestra las páginas públicas de "Cumpleaños" y "Entradas".
|
*/
class PromocionController extends Controller {

 

/**
 * Retorna promociones en formato JSON
 * URL: /promocion/promocionesJson
 */
public function promocionesJson() {

 

    $resultado = [ 
            Array("id" => 1,
            "nombreoferta" => "5 minutos gratis",
            "puntos_necesarios" => 200
        ),
            Array("id" => 2,
            "nombreoferta" => "10 minutos gratis",
            "puntos_necesarios" => 250
        ),
        Array("id" => 3,
            "nombreoferta" => "20 minutos gratis",
            "puntos_necesarios" => 400
        ),
        Array("id" => 4,
            "nombreoferta" => "30 minutos gratis",
            "puntos_necesarios" => 450
        ),
        ];
 
    // Cabecera JSON
    header('Content-Type: application/json');

    echo json_encode($resultado);
}
}
?>