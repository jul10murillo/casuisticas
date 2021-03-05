<?php

class TestController
{

    private $status;
    static private
        $decode = [
            12   => "N",
            8    => "Y",
            2    => "C",
            14   => "H",
            3    => "U",
            10   => "D",
            7    => "X",
            13   => "M",
            11   => "F",
            9    => "Z",
            5    => "A",
            6    => "P",
            4    => "R",
            16   => "R",
            17   => "R",
            15   => "V",
            1    => "S",
            null => null
        ];

    static function codeCase($code, $caseDTO)
    {
        $implodeCode =  explode('_', $code);

        $casesCode = $implodeCode[0];
        $casesCode = str_split($casesCode);
        $datesCode = $implodeCode[1];

        if (is_array($caseDTO)) {
            $caseDTO = $caseDTO[0];
        }

        if (count($casesCode) != count($caseDTO->getFollowings())) {
            return 'Cantidad letras Code vs followings: ' . count($casesCode) . ' != ' . count($caseDTO->getFollowings());
        }

        foreach ($caseDTO->getFollowings() as $key => $following) {
            if (self::$decode[($following->getStatus())] != $casesCode[$key]) {
                return 'Codificación no coincide: ' . self::$decode[($following->getStatus())] . " != " . $casesCode[$key];
            }
        }

        try {
            $firstDate = null;
            foreach ($caseDTO->getFollowings() as $key => $following) {
                $dateObj = new DateTime($following->getCreated_at());
                $dateDay = $dateObj->format('Y-m-d');
                if ($firstDate != $dateDay) {
                    $firstDate = $dateDay;
                    if ($firstDate == 0) {
                        return 'Valor de fecha incorrecta: ' . $firstDate;
                    }
                    $date[] = end($date) + 1;
                } else {
                    $date[] = end($date);
                }
            }

            if ($datesCode != implode($date)) {
                return 'Codificación de fecha no coincide Code vs Followings :  ' . $datesCode . ' != ' . implode($date);
            }
        } catch (\Throwable $th) {
            return 'Error en la obtención de fechas';
        }

        return true;
    }
}
