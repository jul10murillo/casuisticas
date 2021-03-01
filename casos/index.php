<?php

/**
 * Estabilizador de estados
 */
class StabilizerStates
{

    /**
     * Obtener los datos del archivo csv
     * @return Array $new_data los datos tomados del archivo csv
     */
    public function getData()
    {
        $csv_archivo = fopen(__DIR__ . '/assets/import/Extraccion.csv', 'r');
        $i = 0;
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

        $new_data = [];
        $new_data['people'] = [];
        foreach ($data as $row) {
            if (is_numeric($row[0])) {
                $new_data['cases'][$row[0]] = [$row[1], $row[2], $this->classifyCases($row[4]), $this->classifyCases($row[5])];

                if (!isset($new_data['people'][$row[1]])) {
                    $persona = [
                        title($row[7]), $row[6], title($row[8]), title($row[9]), title($row[10] . ' - ' . $row[11]), title($row[13])
                    ];
                    $new_data['people'][$row[1]] = $persona;
                }
            }
        }
        sort($new_data['cases']);

        return $new_data;
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
            } else {
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
        $orderCase = [];
        $orderDates = [];

        $result = [];

        foreach ($cases as $case => $status) {
            $last_date = "";
            $id = null;
            foreach ($status as $value) {
                if ($id != $case) {
                    $num = 0;
                }
                $num = $this->checkDates($value[0], $num, $last_date);
                $orderCase[$case] .= $value[1];
                $orderDates[$case] .= $num;
                $id = $case;
                $last_date = $value[0];
            }
            $result[$case] = $orderCase[$case] . '_' . $orderDates[$case];
        }

        return $result;
    }

    /**
     * Comprobar el orden y estado (bueno, malo, por dividir) de los casos 
     * @param Array $check_history los estados comprobados anteriormente
     * @return Array resultado ordenado de los estados
     */
    public function checkOrder($check_history)
    {
        $good_cases = [];
        $bad_cases = [];
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
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSDSD_123456':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSDS_12345':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSD_1123':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSD_1233':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSD_1234':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SCRSCR_112334':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SCRS_1234':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSCR_12334':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSCR_12345':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSC_1234':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSDSCR_1234556':
                    $cases_to_divide[$case] = $history;
                    break;
                case 'SDSDSCR_1234567':
                    $cases_to_divide[$case] = $history;
                    break;
                default:
                    $bad_cases[$case] = $history;
                    break;
            }
        }
        return ['casos_malos' => $bad_cases, 'casos_para_dividir' => $cases_to_divide, 'casos_buenos' => $good_cases];
    }


    /**
     * Dividir los casos de estado por dividir
     * @param String $case el caso por dividir
     * @param Array $new_case el ultimo array del caso dividido
     * @return Array
     */
    private function divide($case, $new_case = [])
    {
        $options = ['SCR', 'SD'];
        $explode_case = explode('_', $case);
        foreach ($options as $option) {
            $cantidad = substr_count($explode_case[0], $option);
            if ($cantidad > 0) {
                $temp_case = $explode_case[0];
                $temp_date = $explode_case[1];
                for ($i = 0; $i < $cantidad; $i++) {
                    $legnth = strlen($option);
                    if (substr($explode_case[0], 0, $legnth) == $option) {
                        $sub_case = substr($temp_case, 0, $legnth);
                        $sub_date = substr($temp_date, 0, $legnth);

                        array_push($new_case, $sub_case . '_' . $sub_date);
                        $temp_case = substr($temp_case, $legnth);
                        $temp_date = substr($temp_date, $legnth);
                        $case = $temp_case . '_' . $temp_date;
                    }
                }
            }
        }
        return [$case, $new_case];
    }


    /**
     * revalidar los casos por dividir
     * @param String $case el caso que se procesará por medio de la función divide
     * @return Array new_case el ultimo array del caso dividido
     */
    private function divideCases($case)
    {
        $new_case = [];
        for ($i = 1; $i <= 6; $i++) {
            $divide = $this->divide($case, $new_case);
            $case = $divide[0];
            $new_case = $divide[1];
        }

        if (trim($case) != '' && $case != '_') {
            array_push($new_case, $case);
        }

        return $new_case;
    }

    /**
     * Agrupación de los casos por tipo de historia
     * @param Array $check_status casos ordenados por estado
     * @return Array casos agrupados por historia
     */
    public function groupByStatus($check_status)
    {
        $good_cases = [];
        $bad_cases = [];
        $cases_to_divide = [];
        foreach ($check_status['casos_malos'] as $key => $value) {
            if (isset($bad_cases[$value])) {
                array_push($bad_cases[$value], $key);
            } else {
                $bad_cases[$value] = [];
                array_push($bad_cases[$value], $key);
            }
        }
        foreach ($check_status['casos_buenos'] as $key => $value) {
            if (isset($good_cases[$value])) {
                array_push($good_cases[$value], $key);
            } else {
                $good_cases[$value] = [];
                array_push($good_cases[$value], $key);
            }
        }
        foreach ($check_status['casos_para_dividir'] as $key => $value) {
            if (isset($cases_to_divide[$value])) {
                array_push($cases_to_divide[$value], $key);
            } else {
                $cases_to_divide[$value] = [];
                array_push($cases_to_divide[$value], $key);
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
        $good_cases = [];
        $bad_cases = [];
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
     * Descripciones de las posibles soluciones para los casos
     * @param String $type para identificar tipo de solución que desea obtener
     * @return String $desc descripción del tipo de solución requerida
     */
    public function descSolutions($type = 1)
    {
        $desc = '<b>Posibles estados</b><br>' .
            '<b>S</b> = Sospechoso,   <b>D</b> = Descartado,   <b>C</b> = Contagiado,   <b>R</b> = Recuperado,   <b>F</b> = Fallecido </b><br><br>';
        switch ($type) {
            case 1:
                $desc .= '<b>Casos correctos por estado de salud (posibles divisiones) </b><br>' .
                    '<b>SCR, SD, SCF</b>. Después de validar los casos anteriores, es posible que sobre un estado <b>S</b> </b><br><br>' .
                    '<b>El orden de las fechas se representará con números consecutivos.</b><br>' .
                    '<b>ejm:</b> 2020-01-01 = <b>1</b>, 2020-01-08 = <b>2</b><br>' .
                    '<h4>La solución para estos casos se presenta en el siguiente orden específico:</h4><br>' .
                    '<ul style="margin-top: 0;">' .
                    '<li> Se valida que el estado de salud histórico y el orden de las fechas sea correcto. <br><br> ' .
                    '<b>ejm:</b> <b>S</b> 2020-01-01, <b>D</b> el mismo día o en una fecha superior <b>(SD_11 ó SD_12).</b> <br> </li>' .
                    '<li> Se identifica si el orden histórico se repite o si contiene varias posibles<br> ' .
                    'divisiones con diferentes fechas en ambos casos.  <br><br> ' .
                    '<b>ejm</b>, repeticiones consecutivas: <b>SDSD_1234</b>, <b>SDSD_1122</b>, <b>SDSD_1123</b> ó <b>SDSDS_12334</b> <br><br> ' .
                    '<b>ejm</b>, repeticiones no consecutivas: <b>SDSCR_12345</b>, <b>SDSCR_11234</b>, <b>SDSCR_12334</b> ó <b>SDSCRS_112234</b> <br> ' .
                    '<li> Se procede a crear nuevos casos de acuerdo a los resultantes de las divisiones <br> ' .
                    'Siempre y cuando el último caso identificado no sea <b>SCF</b>, se podrá seguir dividiendo.<br><br> ' .
                    'Teniendo en cuenta los ejemplos anteriores, procemos a dividir, se separan los casos<br> ' .
                    'correctos y cómo resultado obtenemos varios casos de acuerdo al número de <br> ' .
                    'frecuencias y en ocasiones un sobrante <b>(S)</b>.</b><br><br> ' .
                    '<b>ejm:</b>  <b>SDSD_1234</b> = <b>SD_12</b> y <b>SD_34</b> <br>       ' .
                    '<b>SDSD_1122</b> = <b>SD_11</b> y <b>SD_22</b><br>       ' .
                    '<b>SDSDS_12334</b> = <b>SD_12</b> , <b>SD_33</b> y <b>S_4</b>(caso sobrante)<br>       ' .
                    '<b>SDSCR_12345</b> = <b>SD_12</b> y <b>SCR_345</b><br>       ' .
                    '<b>SDSCR_12334</b> = <b>SD_12</b> y <b>SCR_334</b><br>       ' .
                    '<b>SDSCRS_112234</b> = <b>SD_11</b> , <b>SCR_223</b> y <b>S_4</b>(caso sobrante)</li>' .
                    '</ul><br><br>';
                break;
            case 2:
                $desc .=
                    '<b>Casos erróneos por estado de salud (posibles soluciones) </b><br>' .
                    '<b>CR, D, C, CRCR</b>. Después de validar los casos anteriores, es posible que se realice una división <b>S</b> </b><br><br>' .
                    '<b>El orden de las fechas se representará con números consecutivos.</b><br>' .
                    '<b>ejm:</b> 2020-01-01 = <b>1</b>, 2020-01-08 = <b>2</b><br>' .
                    '<h4>La solución para estos casos se presenta en el siguiente orden específico:</h4><br>' .
                    '<ul style="margin-top: 0;">' .
                    '<li> Teniendo en cuenta los casos anteriores, se valida que el estado de salud histórico<br> ' .
                    'y el orden de las fechas serían correctos si los precediera un estado <b>S</b>, este tendría <br> ' .
                    'la fecha del estado que le prosigue.<br><br> ' .
                    '<b>ejm:</b> <b>C</b> 2020-01-01, <b>R</b> en una fecha superior <b>(CR_12 = SCR_112).</b> <br> </li>' .
                    '<li> Se identifica si el orden histórico se repite con diferentes fechas.  <br><br> ' .
                    '<b>ejm:</b> <b>CRCR_1234</b> <br> ' .
                    '<li> Se procede a crear nuevos casos de acuerdo a los resultantes de las divisiones (si son fechas distintas) <br><br> ' .
                    'Teniendo en cuenta el ejemplo anterior, procemos a dividir, se separan los casos<br> ' .
                    'y cómo resultado obtenemos varios casos de acuerdo al número de frecuencias y agregamos <br> ' .
                    'el estado <b>(S)</b> al inicio de cada caso.</b><br><br> ' .
                    '<b>ejm:</b>  <b>CRCR_1234</b> = <b>SCR_112</b> y <b>SCR_334</b><br> </li>' .
                    '<li>Para los otros casos la solución sería simplemente agregar el estado <b>(S)</b> antes de y con la misma fecha</b><br><br> ' .
                    '<b>ejm:</b>  <b>D</b> = <b>SD_11</b> <br>       ' .
                    '<b>C</b> = <b>SC_11</b> <br></li>' .
                    '</ul><br><br>';
            default:
                # code...
                break;
        }
        return $desc;
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
        $equals = false;
        $last_date = str_replace(array('\'', '"'), '', $last_date);
        $initial_date = date("Y-m-d", strtotime($last_date));
        if ($first_date != "") {
            $first_date = str_replace(array('\'', '"'), '', $first_date);
            $end_date = date("Y-m-d", strtotime($first_date));
            if ($end_date == $initial_date) {
                $equals = true;
            }
        }

        $num = (isset($num)) ? $num : 0;
        return $equals ? $num : $num + 1;
    }




    public function getPeople($status, $people)
    {
        $tigo = [
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
                $template .= trim($value[$i]) != '' &&  trim($value[$i]) != '-' ? '"' . $value[$i] . '";' : '"No";';
            }
            $template = rtrim($template, ';');
            $template .= '<br>';
        }

        return $template;
    }
}

/**
 * Comparar las fechas para ordenar
 * @param $element1 fecha inicial
 * @param $element2 fecha final
 * @return Boolean
 */
function dateCompare($element1, $element2)
{
    $datetime1 = strtotime($element1[0]);
    $datetime2 = strtotime($element2[0]);
    return $datetime1 - $datetime2;
}

function title($str = '')
{
    $str = str_replace('/', ' ', $str);
    $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
    return ucwords($str);
}

// Objeto del estabilizador
$stabilizer = new StabilizerStates();

// se obtienen los datos en un array formateador desde el csv
$data = $stabilizer->getData();

//Grupo de estados (Casos)
$cases = $stabilizer->groupHistory($data['cases']);

//Comprobamos el orden de la historia de los casos
$check_history =  $stabilizer->checkHistory($cases);

//Comprobamos si el caso está bien o hay que corregirlo
$check_status =  $stabilizer->checkOrder($check_history);

//Agrupamos los casos por estado
$group_status =  $stabilizer->groupByStatus($check_status);

//Contamos los casos por estado
$count_status =  $stabilizer->countByStatus($group_status);

//personas por casos 
$people = $stabilizer->getPeople($check_status, $data['people']);

echo "<pre>";
switch ($_GET['option']) {
    case 'casos':
        print_r($cases);
        $count = count($cases);
        break;

    case 'historias':
        print_r($check_history);
        $count = count($check_history);
        break;

    case 'estado':
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
        switch ($tipo) {
            case 'buenos':
                print_r($check_status['casos_buenos']);
                $count = count($check_status['casos_buenos']);
                break;
            case 'malos':
                echo $stabilizer->descSolutions(2);
                print_r($check_status['casos_malos']);
                $count = count($check_status['casos_malos']);
                break;
            case 'dividir':
                echo $stabilizer->descSolutions(1);
                print_r($check_status['casos_para_dividir']);
                $count = count($check_status['casos_para_dividir']);
                break;
            case 'personas':
                print_r($people);
                $count = count($people);

                break;

            default:
                print_r($check_status);
                $count = count($check_status['casos_malos']);
                $count += count($check_status['casos_para_dividir']);
                $count += count($check_status['casos_buenos']);
                break;
        }

        break;
    case 'cantidad':
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
        $total = isset($_GET['total']) ? $_GET['total'] : null;
        if ($total === 'si') {
            print_r($count_status);
            $count = count($count_status['casos_malos']);
            $count += count($count_status['casos_para_dividir']);
            $count += count($count_status['casos_buenos']);
        } else {
            switch ($tipo) {
                case 'buenos':
                    print_r($group_status['casos_buenos']);
                    $count = count($group_status['casos_buenos']);
                    break;
                case 'malos':
                    print_r($group_status['casos_malos']);
                    $count = count($group_status['casos_malos']);
                    break;
                case 'dividir':
                    /* print_r($group_status['casos_para_dividir']);
                    $count = count($group_status['casos_para_dividir']); */
                    echo "hola";
                    break;

                default:
                    print_r($group_status);
                    $count = count($group_status['casos_malos']);
                    $count += count($group_status['casos_para_dividir']);
                    $count += count($group_status['casos_buenos']);
                    break;
            }
        }

        break;

    default:
        print_r($data);
        $count = count($data);
        break;
}
echo 'catidad: ' . $count;
echo "</pre>";
