<?php

return [

    /*
    | Porcentaje de comisión de la plataforma sobre el valor pagado.
    | En el MVP solo se registra para reportes (el pago se hace por fuera de la app).
    | Valor por confirmar con el modelo financiero.
    */
    'comision_porcentaje' => (float) env('BELLEZA_COMISION_PORCENTAJE', 15),

];
