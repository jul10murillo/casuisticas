<?php

//namespace solverases;
require_once __DIR__ . "/controller/DataController.php";
require_once __DIR__ . "/controller/CasesController.php";

$requestUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$baseUrl = "http://127.0.0.1/CASOS/";
$requestString = substr($requestUrl, strlen($baseUrl));
$urlParams = explode('/', $requestString);
$controller = ltrim(array_shift($urlParams), '?');
$controllerName = ucfirst($controller) . 'Controller';
$actionName = strtolower(array_shift($urlParams));

$dataController = new DataController();
$data = $dataController->getData();
$cases = $dataController->groupHistory($data['cases']);               //Grupo de estados (Casos)
$check_history = $dataController->checkHistory($cases);                       //Comprobamos el orden de la historia de los casos
$check_status = $dataController->checkOrder($check_history);                 //Comprobamos si el caso está bien o hay que corregirlo
$group_status = $dataController->groupByStatus($check_status);               //Agrupamos los casos por estado
$count_status = $dataController->countByStatus($group_status);               //Contamos los casos por estado
$people = $dataController->getPeople($check_status, $data['people']);  //personas por casos 

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
                // echo '<br>';
                // print_r($badCase);
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

/*
  require_once __DIR__ . "/controller/CasesController.php";
  $controller = new CasesController();
  $controller->index();

  function dd($x)
  {
  array_map(function ($x) {
  var_dump($x);
  }, func_get_args());
  die;
  }
  function d($x)
  {
  echo '<pre>';
  echo var_dump($x);
  exit;
  }
 */

function dd($x) {
    echo '<pre>';
    echo var_dump($x);
    exit;
}
