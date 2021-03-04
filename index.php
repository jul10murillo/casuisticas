<?php

//namespace solverases;
require_once __DIR__ . "/controller/DataController.php";
require_once __DIR__ . "/controller/CasesController.php";

$dataController = new DataController();
$dataController->init();
exit;
