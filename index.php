<?php

//namespace solverases;
require_once __DIR__ . "/controller/DataController.php";
require_once __DIR__ . "/controller/CasesController.php";

$dataController = new DataController();
$data = $dataController->init();

main($group_status);
exit;

function main($group_status) {
    $response = inicializateData();
    $response = startSolveBadCases($group_status['casos_malos']);
}

function inicializateData() {
    
}

function startSolveBadCases($badCases) {
    try {
        $caseController = new CasesController();
        $countNoFunctions = 0;
        $countFunctions = 0;

        foreach ($badCases as $key => $badCase) {
            $function = 'solve' . $key;
            if (method_exists($caseController, $function)) {
                foreach ($badCase as $key => $idCase) {
                    try {
                        $response = $caseController->$function($idCase);
                    } catch (\Throwable $th) {
                        $response = 'Error en la función :' . $function . ' id: ' . $idCase;
                    }
                }
                print_r($key);
                echo '<br>';
                print_r($response);
                echo '<hr>';
                echo '<br>';
                $countFunctions++;
            } else {
                echo 'No existe la funcion para el caso: ';
                print_r($key);
                echo '<br>';
                print_r($badCase);
                echo '<hr>';
                echo '<br>';
                $countNoFunctions++;
            }
        }
        echo '<hr>';
        echo '<br>';
        echo ' Total Casos sin funciones: ';
        print_r($countNoFunctions);
        echo '<hr>';
        echo '<br>';
        echo ' Total Casos solucionados: ';
        print_r($countFunctions);
    } catch (\Throwable $th) {
        return 'Error';
    }
    return 'finalizado';
}