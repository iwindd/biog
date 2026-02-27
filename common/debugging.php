<?php

use yii\helpers\VarDumper;

if (!function_exists('dd')) {
    function dd($data){
        echo '<pre style="color: #fff; background-color: #000;">';
        echo VarDumper::dumpAsString($data, 10, true);
        echo '</pre>';
        die();
    }

    function dd_html($data){
        echo '<pre style="color: #fff; background-color: #000;">';
        echo VarDumper::dumpAsString($data, 10, true);
        echo '</pre>';
    }
}