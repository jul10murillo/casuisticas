<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DataController
{

    public function init()
    {
        $data          = $this->getData();
        $cases         = $this->groupHistory($data['cases']); //Grupo de estados (Casos)
        $check_history = $this->checkHistory($cases); //Comprobamos el orden de la historia de los casos
        $check_status  = $this->checkOrder($check_history); //Comprobamos si el caso está bien o hay que corregirlo
        $group_status  = $this->groupByStatus($check_status); //Agrupamos los casos por estado
        $this->startSolveBadCases($badCases);
    }

    /**
     * Obtener los datos del archivo csv
     * @return Array $new_data los datos tomados del archivo csv
     */
    public function getData()
    {
        $csv_archivo  = fopen(dirname(__DIR__, 1) . '/assets/import/Extraccion.csv', 'r');
        $i            = 0;
        while (($registro_csv = fgetcsv($csv_archivo)) !== false) {
            $i++;
            if ($i > 1) {
                $data[] = $registro_csv;
            }
        }
        fclose($csv_archivo);

        foreach ($data as $row => $value) {
            $data[$row] = explode(';', $value[0]);
        }

        $new_data           = [];
        $new_data['people'] = [];
        foreach ($data as $row) {
            if (is_numeric($row[0])) {
                $new_data['cases'][$row[0]] = [$row[1], $row[2], $this->classifyCases($row[4]), $this->classifyCases($row[5])];
                if (!isset($new_data['people'][$row[1]])) {
                    $persona                     = [
                        $this->title($row[7]), $row[6], $this->title($row[8]), $this->title($row[9]), $this->title($row[10] . ' - ' . $row[11]), $this->title($row[13])
                    ];
                    $new_data['people'][$row[1]] = $persona;
                }
            }
        }
        sort($new_data['cases']);
        return $new_data;
    }

    function title($str = '')
    {
        $str = str_replace('/', ' ', $str);
        $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
        return ucwords($str);
    }

    /**
     * Clasificación de los casos a una sola letra
     * @param String $status estado recibido
     * @return String $letter letra del estado
     */
    private function classifyCases($status)
    {
        $status = strtolower($status);

        $classification = [
            "nuevo"           => "N",
            "cerrado"         => "Y",
            "confirmado"      => "C",
            "confirmado-hosp" => "H",
            "confirmado-uci"  => "U",
            "descartado"      => "D",
            "diligenciado"    => "X",
            "en seguimiento"  => "M",
            "fallecido"       => "F",
            "finalizado"      => "Z",
            "activo"          => "A",
            "progamado"       => "P",
            "recuperado"      => "R",
            "reinfección"     => "V",
            "sospechoso"      => "S",
            "\\N"             => null,
        ];

        $status = str_replace(array('\'', '"'), '', $status);
        return $classification[$status];
    }

    /**
     * Agrupación de las historias por caso
     * @param Array $data los datos de las historias
     * @return $group las historias agrupadas por caso
     */
    public function groupHistory(array $data)
    {
        $group = [];

        foreach ($data as $value) {
            if (isset($group[$value[0]])) {
                array_push($group[$value[0]], [$value[1], $value[2], $value[3]]);
            }
            else {
                $group[$value[0]] = [];
                array_push($group[$value[0]], [$value[1], $value[2], $value[3]]);
            }
        }

        foreach ($group as $follow) {
            usort($follow, 'dateCompare');
        }

        return $group;
    }

    /**
     * Comprobar el orden de las historias de los casos por fechas
     * @param Array $cases son los casos que se esperan comprobar
     * @return Array $resultado de las historias organizadas por fecha
     * 
     */
    public function checkHistory($cases)
    {
        $orderCase  = [];
        $orderDates = [];

        $result = [];

        foreach ($cases as $case => $status) {
            $last_date = "";
            $id        = null;
            foreach ($status as $value) {
                if ($id != $case) {
                    $num = 0;
                }
                $num               = $this->checkDates($value[0], $num, $last_date);
                $orderCase[$case]  .= $value[1];
                $orderDates[$case] .= $num;
                $id                = $case;
                $last_date         = $value[0];
            }
            $result[$case] = $orderCase[$case] . '_' . $orderDates[$case];
        }

        return $result;
    }

    /**
     * Comprobar si una fecha es anterior a otra
     * @param $first_date fecha inicial
     * @param $last_date fecha final
     * @param $num número del ultimo consecutivo de la fecha
     * @return Int
     */
    private function checkDates($first_date, $num, $last_date = "")
    {
        $equals       = false;
        $last_date    = str_replace(array('\'', '"'), '', $last_date);
        $initial_date = date("Y-m-d", strtotime($last_date));
        if ($first_date != "") {
            $first_date = str_replace(array('\'', '"'), '', $first_date);
            $end_date   = date("Y-m-d", strtotime($first_date));
            if ($end_date == $initial_date) {
                $equals = true;
            }
        }

        $num = (isset($num)) ? $num : 0;
        return $equals ? $num : $num + 1;
    }

    /**
     * Agrupación de los casos por tipo de historia
     * @param Array $check_status casos ordenados por estado
     * @return Array casos agrupados por historia
     */
    public function groupByStatus($check_status)
    {
        $good_cases      = [];
        $bad_cases       = [];
        $cases_to_divide = [];
        foreach ($check_status['casos_malos'] as $key => $value) {
            if (isset($bad_cases[$value])) {
                array_push($bad_cases[$value], $key);
            }
            else {
                $bad_cases[$value] = [];
                array_push($bad_cases[$value], $key);
            }
        }
        foreach ($check_status['casos_buenos'] as $key => $value) {
            if (isset($good_cases[$value])) {
                array_push($good_cases[$value], $key);
            }
            else {
                $good_cases[$value] = [];
                array_push($good_cases[$value], $key);
            }
        }
        foreach ($check_status['casos_para_dividir'] as $key => $value) {
            if (isset($cases_to_divide[$value])) {
                array_push($cases_to_divide[$value], $key);
            }
            else {
                $cases_to_divide[$value] = [];
                array_push($cases_to_divide[$value], $key);
            }
        }

        return ['casos_malos' => $bad_cases, 'casos_para_dividir' => $cases_to_divide, 'casos_buenos' => $good_cases];
    }

    /**
     * Comprobar el orden y estado (bueno, malo, por dividir) de los casos 
     * @param Array $check_history los estados comprobados anteriormente
     * @return Array resultado ordenado de los estados
     */
    public function checkOrder($check_history)
    {
        $good_cases      = [];
        $bad_cases       = [];
        $cases_to_divide = [];

        foreach ($check_history as $case => $history) {
            switch ($history) {
                case 'S_1':
                    $good_cases[$case] = $history;
                    break;
                case 'SCR_112':
                    $good_cases[$case] = $history;
                    break;
                case 'SCR_123':
                    $good_cases[$case] = $history;
                    break;
                case 'SC_11':
                    $good_cases[$case] = $history;
                    break;
                case 'SC_12':
                    $good_cases[$case] = $history;
                    break;
                case 'SD_11':
                    $good_cases[$case] = $history;
                    break;
                case 'SD_12':
                    $good_cases[$case] = $history;
                    break;
                case 'SHC_111':
                    $good_cases[$case] = $history;
                    break;
                case 'SDSDSD_123455':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSDSD_123456':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSDS_12345':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSD_1123':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSD_1233':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSD_1234':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SCRSCR_112334':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SCRS_1234':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSCR_12334':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSCR_12345':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSC_1234':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSDSCR_1234556':
                    $bad_cases[$case]  = $history;
                    break;
                case 'SDSDSCR_1234567':
                    $bad_cases[$case]  = $history;
                    break;
                case 'CR_12':
                    $good_cases[$case] = $history;
                    break;
                default:
                    $bad_cases[$case]  = $history;
                    break;
            }
        }
        return ['casos_malos' => $bad_cases, 'casos_para_dividir' => $cases_to_divide, 'casos_buenos' => $good_cases];
    }

    /**
     * Contar los casos por tipo de historia
     * @param Array $check_status casos ordenados por estado
     * @return Array casos contados por historia
     */
    public function countByStatus($check_status)
    {
        $good_cases      = [];
        $bad_cases       = [];
        $cases_to_divide = [];

        foreach ($check_status['casos_malos'] as $id => $case) {
            $sum = 0;
            foreach ($case as $value) {
                $sum++;
                $bad_cases[$id] = $sum;
            }
        }
        foreach ($check_status['casos_para_dividir'] as $id => $case) {
            $sum = 0;
            foreach ($case as $value) {
                $sum++;
                $cases_to_divide[$id] = $sum;
            }
        }
        foreach ($check_status['casos_buenos'] as $id => $case) {
            $sum = 0;
            foreach ($case as $value) {
                $sum++;
                $good_cases[$id] = $sum;
            }
        }

        arsort($cases_to_divide);
        arsort($good_cases);
        arsort($bad_cases);
        return ['casos_malos' => $bad_cases, 'casos_para_dividir' => $cases_to_divide, 'casos_buenos' => $good_cases];
    }

    /**
     * getPeople
     *
     * @param  mixed $status
     * @param  mixed $people
     * @return void
     */
    public function getPeople($status, $people)
    {
        $tigo     = [
            'SR_12', 'SCRSCR_112334', 'CD_12', 'CSRDR_12345',
            'SCDR_1123', 'SCDR_1234', 'SDCS_1233', 'SCDRSR_112234',
            'CRCRDRDR_11234567', 'SCRSRV_112345', 'UCR_123',
            'SCHC_1234', 'CRDR_1233', 'CRD_123', 'CRCVR_12345',
            'CSCDCDCR_12344556', 'CS_11', 'CSCSR_11234',
            'CSRSRSR_1123456', 'CSDS_1234', 'DCDR_1233',
            'SDCSCR_122345', 'DCR_123', 'FCFCR_12345',
            'RCRCHCR_1234567', 'RSD_122', 'SCDRDR_123445', 'SRCR_1223',
            'SDRSD_12233', 'SRSDSD_123345', 'SNDSDSCHCR_1223456789',
        ];
        $personas = [];
        foreach ($status['casos_malos'] as $key => $value) {
            if (in_array($value, $tigo)) {
                array_unshift($people[$key], $value, $key);
                array_push($personas, $people[$key]);
            }
        }
        foreach ($status['casos_para_dividir'] as $key => $value) {
            if (in_array($value, $tigo)) {
                array_unshift($people[$key], $value, $key);
                array_push($personas, $people[$key]);
            }
        }
        $template = '';
        foreach ($personas as $value) {
            for ($i = 0; $i < 7; $i++) {
                $value[$i] = str_replace('"', '', $value[$i]);
                $template  .= trim($value[$i]) != '' && trim($value[$i]) != '-' ? '"' . $value[$i] . '";' : '"No";';
            }
            $template = rtrim($template, ';');
            $template .= '<br>';
        }

        return $template;
    }

    /**
     * 
     * @param type $badCases
     * @return string
     */
    function startSolveBadCases($badCases)
    {
        try {
            $caseController   = new CasesController();
            $countNoFunctions = 0;
            $countFunctions   = 0;

            foreach ($badCases as $key => $badCase) {
                $function = 'solve' . $key;
                if (method_exists($caseController, $function)) {
                    foreach ($badCase as $key => $idCase) {
                        try {
                            $response = $caseController->$function($idCase);
                        }
                        catch (\Throwable $th) {
                            $response = 'Error en la función :' . $function . ' id: ' . $idCase;
                        }
                    }
                    print_r($key);
                    echo '<br>';
                    print_r($response);
                    echo '<hr>';
                    echo '<br>';
                    $countFunctions++;
                }
                else {
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
        }
        catch (\Throwable $th) {
            return 'Error';
        }
        return 'finalizado';
    }

}
