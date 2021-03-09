<?php

include 'dao/CaseMySqlDAO.php';
include 'dao/FollowingMySqlDAO.php';
include 'dao/ActivitiesMySqlDAO.php';
include 'dto/CaseDTO.php';
include 'TestController.php';
include 'utils/Constants.php';

class CasesController
{
    /**
     *
     * @var CaseMySqlDAO  
     */
    private $caseDAO;
    /**
     *
     * @var FollowingMySqlDAO 
     */
    private $followingDAO;
    /**
     *
     * @var ActivitiesMySqlDAO 
     */
    private $activitiesDAO;


    public function __construct()
    {
        $this->caseDAO       = new CaseMySqlDAO();
        $this->followingDAO  = new FollowingMySqlDAO();
        $this->activitiesDAO = new ActivitiesMySqlDAO();
    }

    /**
     * __call
     * * Hace un catch de los casos y los redirecciona a las funciones correspondientes,
     * * antes de ello se aplican las validaciones correspondientes.
     *
     * @param  string $name  -> nombre del caso
     * @param  CaseDTO $arguments
     * @return object
     * @author Regmy Nieves
     */
    public function __call($name, $arguments)
    {
        $arguments  = $arguments[0];
        $function   = 'solve' . $name;
        try {
            if (method_exists($this, $function)) {
                $resultTest = TestController::codeCase($name, $arguments);
                if (is_string($resultTest)) {
                    $response = [
                        'status'   => false,
                        'caseIn'   => $arguments->getId(),
                        'caseName' => $name,
                        'message'  => $resultTest,
                    ];
                    return json_encode($response);
                }
                $caseOut  = $this->$function($arguments);
                if (is_array($caseOut)) {
                    foreach ($caseOut as $key => $out) {
                        if (is_object($out)) {
                            $caseOutIds[] = $out->getId();
                        } else {
                            $caseOutIds[] = 0;
                        }
                    }
                    $response = [
                        'status'     => true,
                        'message'    => 'Caso Resuelto',
                        'casuistica' => $name,
                        'caseIn'     => $arguments->getId(),
                        'caseOut'    => implode(" , ", $caseOutIds)
                    ];
                } else {
                    if (is_object($caseOut)) {
                        $response = [
                            'status'     => true,
                            'message'    => 'Caso Resuelto',
                            'casuistica' => $name,
                            'caseIn'     => $arguments->getId(),
                            'caseOut'    => $caseOut->getId()
                        ];
                    } else {
                        $response = [
                            'status'     => true,
                            'message'    => 'Caso Resuelto',
                            'casuistica' => $name,
                            'caseIn'     => $arguments->getId(),
                            'caseOut'    => 'null, función no retorna el caso'
                        ];
                    }
                }
            } else {
                $response = [
                    'status'   => false,
                    'caseIn'   => $arguments->getId(),
                    'caseName' => $name,
                    'message'  => 'La función: ' . $function . ' no existe',
                ];
            }
        } catch (\Throwable $th) {
            $response = [
                'status'   => false,
                'caseIn'   => $arguments->getId(),
                'caseName' => $name,
                'message'  => 'Error a ejecutar la función: ' . $function,
                'error'    => ' File: ' . $th->getFile() . ' Linea: ' . (string)$th->getLine() . ' Mensaje: ' . (string)$th->getMessage()
            ];
        }
        if (is_null($response)) {
            echo 'stop here';
        }
        if (is_null(json_encode($response))) {
            echo 'stop here';
        }

        return json_encode($response);
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param  CaseDTO $oldCase
     * @return CaseDTO[]
     */
    public function solveSDSD_1234($oldCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($oldCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Crear un estado de confirmado anterior a la fecha actual.
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveR_1(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $date = $this->subtractDaysFromDate($followings[0]->getDate(), 1);
        $this->addNewFollowingToCase($followings[0], $currentCase, $date, Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase, $followings[0]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone adicionar un seguimiento con estado de salud Sospechoso con fecha anterior a la fecha (1)
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveD_1(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $date = $this->subtractDaysFromDate($followings[0]->getDate(), 1);
        $this->addNewFollowingToCase($followings[0], $currentCase, $date, Constants::HEALTH_STATUS_SUSPICIOUS);
        $this->saveCase($currentCase, $followings[0]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Actualizar fecha del estado(2) al día siguiente de su fecha actual.
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCR_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[1]->setDate($this->addDaysToDate($followings[0]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRCR_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }


    /**
     * Propuesta de solución: Actualizar fecha del estado(2) al día siguiente de su fecha actual.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSD_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[1]->setDate($this->addDaysToDate($followings[0]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }


    /**
     * Propuesta de solución: Actualizar fecha del estado(1) al día anterior de su fecha actual.
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCR_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12), Caso 2 (34) y Caso 3 (56)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDSD_123456(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * Propuesta de solución: Actualizar fecha del estado(1) al día anterior de su fecha actual.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCR_11234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), 1));

        $currentCase->setFollowings([]);

        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCR_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * Propuesta de solución: Actualizar fecha estado (1) a un dia anterior a la fecha actual
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSC_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (345)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSCR_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * Propuesta de solución: Crear un estado de confirmado anterior a la fecha actual.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveF_1(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $date =  $this->subtractDaysFromDate($followings[0]->getDate(), 1);
        $this->addNewFollowingToCase($followings[0], $currentCase, $date, Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase, $followings[0]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Tratar como un descartado. Es decir, Cambiar el estador Recuperado a Sopechoso.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveRD_12(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (3)
     *
     * @param CaseDTO $currentCase     
     * @return CaseDTO[]
     */
    public function solveSDS_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }
    /**
     * Propuesta de solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 2 (56)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDCR_123456(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }


    /**
     * Propuesta de solución: Eliminar el seguimiento(2) Recuperado
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveD_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }
    /**
     * Propuesta de solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 2 (5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDS_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * Propuesta de solución: Actualizar fecha del Recuperado al dia siguiente
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCR_122(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[2]->setDate($this->addDaysToDate($followings[1]->getDate(), 1));

        $currentCase->setfollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sopechoso(1) y Descartado (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSD_1123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setfollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 2 (567)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDSCR_1234567(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6, 7]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }


    /**
     * Propuesta de solución: Se propone mantener los estados Recuperado(1) y Confirmado(2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCR_1123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setfollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }
    /**
     * Propuesta de solución: Se propone eliminar el seguimiento con estado de salud recuperado(1)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveRCR_123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2]]);

        $this->deleteCase($followings[0]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**   
     * Propuesta de solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 3 (5)
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDC_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }
    /**   
     *Propuesta de solución: Se propone mantener los estados Confirmado(1) y Recuperado(3)
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRDR_1234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1) y Confirmado(2)
     * o los estados Sospechoso(2) y Descartado(3)
     *
     *  @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCSD_1223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[1]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**   
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1) y Confirmado(2) o los estados Sospechoso(2) y Descartado(3)
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSD_1223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Eliminar el seguimiento(2) Descartado
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCDR_123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**   
     * Propueta de solución: Eliminar el seguimiento(2) Recuperado
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSRD_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (123) y Caso 2 (45)
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSCRCR_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2, 3], [4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**   
     *Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRSD_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (1) y Caso 2 (23)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCSD_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1], [2, 3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSMDSD_12234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confirmado(1) y Recuperado(3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRDR_1223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar el estado(2) Confirmado
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCUHC_12234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[2],  $followings[3],  $followings[4]]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Actualizar fechas Sospechoso a fecha anterior y Confirmado a fecha posterior
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSHC_111(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), '1'));
        $followings[2]->setDate($this->addDaysToDate($followings[1]->getDate(), '1'));


        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);


        $this->saveCase($currentCase);

        return $currentCase;
    }


    /**
     * Propuesta de solución: Sospechoso(1) a fecha anterior a su fecha actual. Recuperado (3) a fecha posterior a su fecha actual
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCR_111(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), '1'));
        $followings[2]->setDate($this->addDaysToDate($followings[1]->getDate(), '1'));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Crear un estado anterior al descartado(1) como sospechoso. El sopechoso(2) actual llevarlo a un nuevo caso
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveDS_12(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $dateSuspicius = $this->subtractDaysFromDate($followings[0]->getDate(), '1');

        $this->addNewFollowingToCase($followings[0]->getId(), $currentCase, $dateSuspicius, Constants::HEALTH_STATUS_SUSPICIOUS);
        $this->saveCase($currentCase, $followings[0]->getId());

        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Prppuesta de solución: Eliminar la reinfección(2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSVD_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());;

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar hospitalización
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSHD_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }


    /**   
     * Propuesta de solución: Eliminar el Fallecido(2)
     * 
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSFD_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDC_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sopechoso(1) y Descartado (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSD_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRS_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solucion: Se propone eliminar el estado Confirmado uci(1)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveUCR_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propueta de solucion: Se propone mantener los estados Confirmado(1) y Recuperado (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (123) y Caso 2 (4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSCRS_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2, 3], [4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34) 
     * y eliminar los seguimientos con estado de salud Sospechoso(4) y Descartado(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDSD_123445(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2], $followings[3]]);

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (345) y eliminar los estados con fecha(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRCRDR_123455(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2], $followings[3], $followings[4]]);

        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        $this->deleteCase($followings[5]->getId());

        return $cases;
    }


    /**
     * Propuesta de solución: Se propone mantener los estados Confirmado(2) y Recuperado (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveXCXR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[3]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (1) y Caso 2 (234) y eliminar Descartardo(5) y Recuperado(6)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCSCRDR_123456(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[5]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confimado(2) y Recuperado(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveACACR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Se propone mantener los estados Confimado(2) y Recuperado(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveYCYCR_12345(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Se propone mantener los estados Confimado(2), Confirmado uci(3) y recuperado(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveUCUCR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta solución: Se propone mantener los estados Sospechoso(1), Confirmado(2) y Recuperado (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCRCR_12233(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[4]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 2 (56) y eliminar estado Sospechoso(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDSCR_1234556(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $this->deleteCase($followings[4]->getId());
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2], $followings[3], $followings[5], $followings[6]]);

        $arrIdentifiers = [[1, 2], [3, 4], [5, 6]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confirmado(1) y Recuperado (6)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRDR_123456(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);

        $currentCase->setFollowings([$followings[0], $followings[5]]);
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1) y Descartado(2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSD_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);

        $currentCase->setFollowings([$followings[1], $followings[2]]);
        $this->deleteCase($followings[0]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confimado(1) y Recuperado(3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCCR_12333(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);

        $currentCase->setFollowings([$followings[0], $followings[4]]);
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSC_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1), Confirmado(3) y Recuperado (4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDC_12344(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34) y eliminar el 
     * seguimiento con estado de salud Recuperado(4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRSDR_12344(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $this->deleteCase($followings[4]->getId());
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2], $followings[3]]);

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1) , Confirmado(2) y Recuperado (4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSCRCR_112234(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2], $followings[5]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confirmado(1) y Recuperado(2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRDRDR_123334(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confimado(1) y Recuperado(3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO;
     */
    public function solveCRCR_1223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (456) 
     *  y eliminar los seguimientos  Sospechoso(3) y Descartado(4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDSCR_1234456(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[4], $followings[5], $followings[6]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1), Confimado(3) y Recuperado(4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCCR_123444(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[5]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(3) y Recuperado(4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveDSD_12234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[3], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRC_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confirmado(1) y Recuperado(2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCR_1222(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 2 (5)
     *  y eliminar estado Descartado(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDSD_123455(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2], $followings[3], $followings[4]]);

        $this->deleteCase($followings[5]->getId());

        $arrIdentifiers = [[1, 2], [3, 4], [5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confirmado(1) y Recuperado (2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCR_1122(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confimado(4) y Recuperado(5)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveZCZCR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[3], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone eliminar un seguimiento con estado de salud Confirmado(3) y el estado Recuperado(Ultimo)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCRCR_12333(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2]]);

        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se propone eliminar el seguimiento con estado de salud Descartado(1)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCR_1123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2], $followings[3]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     *Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCHCH_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 2 (56)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRCRVR_123456(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     *  Propuesta de solución: Se propone mantener los estados: solo un Sospechoso(1), Confirmado(2) y Recuperado (3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCR_12334(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2]]);

        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución:  Cambio de estado de confirmado a sospecho
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCD_12(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar Sospecho(1) y descartado(6)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSD_123345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[5]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar Sospecho(1) y descartado(3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDRSD_12233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar de esta estado el siguimiento(1) a sospechoso y borrar seguimiento(2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRD_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->saveCase($currentCase);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar casos seguimiento(2) y seguimiento(3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSDS_1234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }
    /**
     * Propuesta de solucion: Asignar al seguimiento(2) la fecha del seguimiento(1) y eliminar el seguimiento(1);
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveRSD_122(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[1]->setDate($followings[0]->getDate());

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2]]);

        $this->deleteCase($followings[0]->getId());

        return $currentCase;
    }
    /**
     * Propuesta de solución: Cambiar el seguimiento(1) por sospechoso y el seguimiento(2) por descartado, 
     * al seguimiento 1 restarle 1 dia
     * 
     *@param CaseDTO $currentCase
     *@return CaseDTO
     * 
     */
    public function solveCS_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), '1'));

        $followings[1]->setStatus(Constants::HEALTH_STATUS_DISCARDED);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(8);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCRDRDR_11234567(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[7]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());
        $this->deleteCase($followings[6]->getId());


        return $currentCase;
    }

    /**
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(5);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCVR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solucion: Dejar seguimiento(1) y seguimiento(4);
     *
     * @return void
     * @return CaseDTO
     */
    public function solveCRDR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(8);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSCDCDCR_12344556(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[7]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());
        $this->deleteCase($followings[6]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(5);
     * 
     *@param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSC_11234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(5);
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCDR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(7);
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveC_1123456(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[7]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());
        $this->deleteCase($followings[6]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solucion: Dejar seguimiento(2) y seguimiento(4);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveDCDR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[3]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solucion: Dejar seguimiento(2) y seguimiento(3);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveDCR_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2]]);

        $this->deleteCase($followings[0]->getId());

        return $currentCase;
    }
    /**
     * Propuesta de solución: Dejar seguimiento(2) y seguimiento(5);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveFCFCR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento(2) y seguimiento(7);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveRCRCHCR_1234567(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[6]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento(2) y seguimiento(4);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCDR_1123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[3]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento(2) y seguimiento(4);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCDR_1234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[3]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Dejar seguimiento(2) y seguimiento(6);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCDRDR_123445(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[5]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento(2) y seguimiento(6);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCDR_112234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[5]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento(2), cambiar el ultimo seguimiento a recuperado 
     * y elmiminar los seguimientos 1 y 3
     * 
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCHC_1234(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $followings[3]->setStatus(Constants::HEALTH_STATUS_RECOVERED);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[1], $followings[3]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento(2) y seguimiento(6);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCRSCR_112334(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[5]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento(2) y seguimiento(5);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCRV_112345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[1], $followings[4]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[6]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Cambiar el seguimiento 4 por recuperado
     *  eliminar el siguimiento 1 y 2
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCS_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[3]->setStatus(Constants::HEALTH_STATUS_RECOVERED);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[2], $followings[3]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[1]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 3 y 6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCSCR_122345(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[2], $followings[5]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 7 y 10
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSNDSDSCHCR_1223456789(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[6], $followings[9]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());
        $this->deleteCase($followings[7]->getId());
        $this->deleteCase($followings[8]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 3 y 4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCR_1223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[2], $followings[3]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 2 y 3
     *
     * @param caseDTO $currentCase
     * @return caseDTO;    

     */
    public function solveUCR_123(caseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2]]);

        $this->deleteCase($followings[0]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 4 y 5
     *
     * @param caseDTO $currentCase
     * @return caseDTO
     */
    public function solveSDSCR_12334(caseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[3], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Posible acción. Crear un estado confirmado en medio de las fechas de los estados (1) y (2)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solve_12(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $date = $followings[0]->getDate();
        $followings[0]->setDate($this->subtractDaysFromDate($date, 1));
        $this->addNewFollowingToCase($followings[0]->getId(), $currentCase, $date, Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase, $followings[0]->getId());


        return $currentCase;
    }


    /**
     * Propuesta de solución:: Borrar seguimientos de el medio
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCCCR_1123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento confirmado(1), hospitalizado(3) y recuperado(5) 
     * CHR_135
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCCHCR_12345(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2],  $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Dejar seguimiento(1) y Recuperado(2)
     * CR_12
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCCR_112(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución:Dejar seguimiento confirmado(1) y recuperado(2)
     * CR_12
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCCR_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución:Dejar seguimiento confirmado(1) y recuperado(3)
     *CR_13
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCCR_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimiento confirmado(1) y recuperado(5)
     * CR_15
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCCR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar confirmado(1) y recuperado(3)
     * CR_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCDR_112(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución:Dejar confirmado(1), hospitalizado(3), y recuperado(5)
     * CHR_125
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCHCHR_12345(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());


        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar confirmado(1) y recuperado(3)
     * CR_13
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCCR_12333(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());


        return $currentCase;
    }

    /**
     * Propuesta de solución:Dejar confirmado(1) y recuperado(3)
     * CR_13
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRDDR_12333(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar Estado Recuperado por "Confirmado" y Restar una de las fechas para anexarlo al Confirmado
     * CR_-1  1
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy
     */
    public function solveRR_11(CaseDTO $currentCase)
    {
        $currentCase->followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);
        $currentCase->followings[0]->setDate($this->subtractDaysFromDate($currentCase->followings[1]->getDate(), 1));
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el primer Estado Recuperado por "Confirmado".
     * CR_12
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy
     */
    public function solveRR_12(CaseDTO $currentCase)
    {
        $currentCase->followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar los primeros dos followings.
     * CR_12
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy
     */
    public function solveSCCR_1112(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[0]->getId());
        $this->deleteCase($currentCase->followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar confirmado(1) y recuperado(3), al confirmado restarle en fecha -1.
     * CR_-1  1
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRR_111(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();
        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar confirmado(1) y recuperado(3)
     * CR_12     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRR_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución:Dejar seguimientos confirmado(1) y recuperado(3)
     * CR_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRR_123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Divider en dos casos, modificar el seguimiento 5 a sospechocho, restarle 1 dia con respecto al descartado, 
     * eliminar los seguimientos 2, 3 
     * CR_13 SD_-1 4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRRRDD_123344(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $followings[4]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $followings[4]->setDate($this->subtractDaysFromDate($followings[5]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3], $followings[4],  $followings[5]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }


    /**
     * Propuesta de solución: Dividir en dos casos, eliminar seguimientos 3,6
     * CR_12 SD_45
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRSSDR_123445(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2],  $followings[4]]);

        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[5]->getId());

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solucíón: ELiminar el siguiento 2
     * CR_13
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCVR_123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimientos 2 y 3
     * CR_14
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCVCR_1234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar el seguimiento 1
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDD_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimiento del medio.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSD_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimiento del medio.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDD_122(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar los seguimientos del medio.
     * SD_15
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDD_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar el seguimiento Confirmado(3)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCCR_1234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[3]]);

        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el estado sospecho por confirmado
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSR_12(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar los seguimientos 2,4 
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCCR_12334(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar los seguimientos 2,4 
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCCR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el estado del seguimiento 1 por Sospecho
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveDD_12(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar el seguimieto confirmado 3
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCCR_1223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[3]]);

        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar los seguimientos del medio
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDSD_12234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar los seguimientos 1, 5 y 7
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDCCR_1234567(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[4], $followings[6]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[5]->getId());

        return $currentCase;
    }

    /**
     *Propuesta de solución: Eliminar el seguimiento 2
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSD_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimiento 3 y 4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCRSR_11234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[4]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimiento 1
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSCR_1123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[2], $followings[3]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }




    /**
     * Propuesta de solución: Eliminar seguimiento 3
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCRR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[3]]);

        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Dividir en dos casos y eliminar seguimiento 5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSCCR_123445(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2], $followings[3], $followings[5]]);

        $this->deleteCase($followings[4]->getId());

        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Eliminar seguimientos del medio.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDSDD_1234567(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[6]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimientos del medio.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSSD_1112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimiento  3 y 4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCCCR_11234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[4]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar seguimiento 3
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCCR_1123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[3]]);

        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el seguimiento fecha al día anterior.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSR_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[1]->setDate($this->subtractDaysFromDate($followings[0]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setfollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar el primer y último following
     * CR_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    public function solveCSRSRSRDDR_1123456777(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
        $this->deleteCase($currentCase->followings[6]->getId());
        $this->deleteCase($currentCase->followings[7]->getId());
        $this->deleteCase($currentCase->followings[8]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3,4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDSD_11122(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3,4,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSSSSCR_1123456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3,4,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSSSSD_123456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSSCR_11123(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSCCR_11223(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3,4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSSSD_11112(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3,4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDDD_12345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3,4,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDDDD_122222(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar 2,3,4,5,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDDDDD_1222222(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Dejar el primer y último following
     * CR_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    public function solveCSSCRSCR_11122334(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
        $this->deleteCase($currentCase->followings[6]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el estado del primer following a confirmado
     * CR_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    public function solveDR_12(CaseDTO $currentCase)
    {
        $currentCase->followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución:Cambiar el estado del primer following por Sospechoso y Eliminar el segundo following
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    public function solveDSD_112(CaseDTO $currentCase)
    {
        $currentCase->followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $this->deleteCase($currentCase->followings[1]->getId());
        unset($currentCase->followings[1]);
        $this->saveCase($currentCase);
        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar el segundo following y tercer following
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    public function solveDSDS_1122(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el estado del primer following el último queda igual
     * Eliminar los followings restantes.
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    public function solveDSSCD_11233(CaseDTO $currentCase)
    {
        $currentCase->followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $this->deleteCase($currentCase->followings[1]->getId());
        unset($currentCase->followings[1]);
        $this->deleteCase($currentCase->followings[2]->getId());
        unset($currentCase->followings[2]);
        $this->deleteCase($currentCase->followings[3]->getId());
        unset($currentCase->followings[3]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /*
     * Propuesta de solución: Cambiar el estado del seguimiento 1 a una fecha anterior
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCHCR_11234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setfollowings($followings);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Eliminar los siguimientos del medio
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDD_1222(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[3]]);


        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Eliminar los siguimientos del medio
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSRRD_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Eliminar el siguimiento del medio
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSSD_122(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[2]]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar los siguimiento del medio
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSSSSD_112345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[5]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el estado del seguimiento 1 por sospechoso y y restar una fecha.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveDD_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $followings[0]->setDate($this->subtractDaysFromDate($followings[0]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setfollowings($followings);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución dejar sospechoso(2) y recuperado(3), al recuperado 3 cambiar a estado descartado;
     * SD_13
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSRSR_11123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[4]->setStatus(Constants::HEALTH_STATUS_DISCARDED);

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[1], $followings[4]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        return $currentCase;
    }


    /**
     *  Propuesta de solución: Eliminar casos del medio. 
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSRSRCSR_11111223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[7]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());
        $this->deleteCase($followings[6]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar casos confirmado(1), Uci(2), hospitalizado(3) y recuperado(8),
     * CUHR_1238
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCUHRRURRHR_1234556778(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1], $followings[2], $followings[9]]);

        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());
        $this->deleteCase($followings[6]->getId());
        $this->deleteCase($followings[7]->getId());
        $this->deleteCase($followings[8]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Eliminar los seguimiento 2 y 3
     * CUHR_1238
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCVC_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Dejar los estado recuperado(1) y descartado(2), cambiar el estado del recuperado a sospechoso
     * SD_12
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveRRDD_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Al seguimiento 1 cambiar el estado a confirmado y dejar el seguimiento 1 y 4
     *  CR_13
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveRRRR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Dejar seguimientos 2 y 4, cambiar de estado al seguimiento 1 por sospechoso
     *  SD_13
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveRSDDR_12333(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[1], $followings[3]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1 2 y 6
     * SCR_124
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCCCCR_122234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1],  $followings[5]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar el primer, segundo y último following
     * al segundo following anexarle la fecha del tercer following. eliminar los restantes.     
     * SCR_123
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSCRDDR_112344(CaseDTO $currentCase)
    {
        $currentCase->followings[1]->setDate($currentCase->followings[2]->getDate());
        $this->deleteCase($currentCase->followings[2]->getId());
        unset($currentCase->followings[2]);
        $this->deleteCase($currentCase->followings[3]->getId());
        unset($currentCase->followings[3]);
        $this->deleteCase($currentCase->followings[4]->getId());
        unset($currentCase->followings[4]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /** Propuesta de solución: Dejar sospechoso(1), confirmado(2) y recuperado(6)
     * SCR_126
     *
     *  @param CaseDTO $currentCase
     *  @return CaseDTO
     */
    public function solveSCCCCR_123456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 4,5,6
     *  SD_12 SCR_456
     * Propuesta de solución: Cambiar el estado del primer following por Confirmado.
     * Dejar el último caso, eliminar los restantes.
     * CR_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSCRR_1122(CaseDTO $currentCase)
    {
        $currentCase->followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);
        $this->deleteCase($currentCase->followings[1]->getId());
        unset($currentCase->followings[1]);
        $this->deleteCase($currentCase->followings[2]->getId());
        unset($currentCase->followings[2]);

        $this->saveCase($currentCase);
        return $currentCase;
    }

    /** Propuesta de solución: Dejar seguimientos 4,5,6
     * SD_12 SCR_456
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCRSRVR_1123456(CaseDTO $currentCase)
    {
        $currentCase->followings[2]->setStatus(Constants::HEALTH_STATUS_DISCARDED);
        $currentCase->followings[4]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $currentCase->followings[5]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);
        $currentCase->followings[6]->setStatus(Constants::HEALTH_STATUS_RECOVERED);

        $this->deleteCase($currentCase->followings[1]->getId());
        unset($currentCase->followings[1]);
        $this->deleteCase($currentCase->followings[3]->getId());
        unset($currentCase->followings[3]);
        
        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Eliminar el 3er following.
     * SCRR_1234
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSCRR_1234(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar el estado del primer following por Confirmado
     * Cambiar el estado del último following por Recuperado. Eliminar los restantes.
     * CR_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSCRRDD_112222(CaseDTO $currentCase)
    {
        $currentCase->followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);
        $currentCase->followings[5]->setStatus(Constants::HEALTH_STATUS_RECOVERED);
        $this->deleteCase($currentCase->followings[1]->getId());
        unset($currentCase->followings[1]);
        $this->deleteCase($currentCase->followings[2]->getId());
        unset($currentCase->followings[2]);
        $this->deleteCase($currentCase->followings[3]->getId());
        unset($currentCase->followings[3]);
        $this->deleteCase($currentCase->followings[4]->getId());
        unset($currentCase->followings[4]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Cambiar la fecha del segundo following por el 5to following
     * Dejar el primer, segundo y último followings, eliminar los restantes.
     * SCR_123
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSCSCDCCCCCR_11112234567(CaseDTO $currentCase)
    {
        $currentCase->followings[1]->setDate($currentCase->followings[4]->getDate());
        $this->deleteCase($currentCase->followings[2]->getId());
        unset($currentCase->followings[2]);
        $this->deleteCase($currentCase->followings[3]->getId());
        unset($currentCase->followings[3]);
        $this->deleteCase($currentCase->followings[4]->getId());
        unset($currentCase->followings[4]);
        $this->deleteCase($currentCase->followings[5]->getId());
        unset($currentCase->followings[5]);
        $this->deleteCase($currentCase->followings[6]->getId());
        unset($currentCase->followings[6]);
        $this->deleteCase($currentCase->followings[7]->getId());
        unset($currentCase->followings[7]);
        $this->deleteCase($currentCase->followings[8]->getId());
        unset($currentCase->followings[8]);
        $this->deleteCase($currentCase->followings[9]->getId());
        unset($currentCase->followings[9]);
        $this->deleteCase($currentCase->followings[10]->getId());
        unset($currentCase->followings[10]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar primer y último following, eliminar los restantes
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSDDDSD_123445(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->saveCase($currentCase);
        return $currentCase;
    }

    /** Propuesta de solución: Dejar seguimientos 1,2,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCRVR_123445(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Dejar seguimientos 1,4,9
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCSCCSRDR_111233345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
        $this->deleteCase($currentCase->followings[6]->getId());
        $this->deleteCase($currentCase->followings[7]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Dejar seguimientos 1,2,3,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCUHCR_122345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: Dejar seguimientos 1,3,5
     * Propuesta de solución: Dividir en dos casos y elminar los casos 2,3,5,7,8
     * SCR_124 SD_67
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSCCCRSRDR_123345677(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[6]->getId());
        $this->deleteCase($followings[7]->getId());

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1], $followings[3],  $followings[5], $followings[8]]);

        $arrIdentifiers = [[1, 2, 3], [4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Dejar sospechoso(1), confirmado(2) y recuperado(3)
     * SCR_123
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCRR_12344(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,3,6
     * */
    public function solveSCCDCR_112223(CaseDTO $currentCase)
    {
        //Las posiciones que no se eliminan son  0, 2 ,5
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar sospechoso 5 y descartado 6
     * SD_56
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCRSR_123456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar primer y último following este 'ultimo se le debe cambiar el estado por Descartado
     * Eliminar los restantes
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSDDS_1233(CaseDTO $currentCase)
    {
        $currentCase->followings[3]->setStatus(Constants::HEALTH_STATUS_DISCARDED);
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se Dejan el 1er y 5to following, los demás se eliminan.
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSDDSD_12345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /** Propuesta de solución: Dejar seguimientos 1,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDDSD_122234(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }
    /**
     * Propuesta de solución: Dejar seguimientos 1,4
     * 
     * */
    public function solveSCDRSDDR_11234566(CaseDTO $currentCase)
    {
        //Las posiciones que no se eliminan son  4 y 6
        $this->deleteCase($currentCase->followings[0]->getId());
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
        $this->deleteCase($currentCase->followings[7]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar sospechoso 1, confirmado 2 y recuperado 7 
     * SCR_127
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCRSSDRDR_123456667(CaseDTO $currentCase)
    {
        //Las posiciones que no se eliminan son  0 , 1, 8
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
        $this->deleteCase($currentCase->followings[6]->getId());
        $this->deleteCase($currentCase->followings[7]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar sospechoso 1, confirmado 2 y recuperado 6
     * SCR_126
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDS_1122(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se Dejan el 1er y último following, los demás se eliminan.
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSDDSDSD_1223456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }



    /** Propuesta de solución: Dejar seguimientos 1,5,6
     * */
    public function solveSDCCRR_123456(CaseDTO $currentCase)
    {
        //Las posiciones que no se eliminan son  0 , 1, 5
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar sospechoso 1, confirmado 3 y recuperado 5
     * SCR_135
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDSCR_122345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        unset($currentCase->followings[1]);
        $this->deleteCase($currentCase->followings[2]->getId());
        unset($currentCase->followings[2]);
        $currentCase->followings[4]->setDate($currentCase->followings[3]->getDate());
        $this->deleteCase($currentCase->followings[3]->getId());
        unset($currentCase->followings[3]);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se Dejan el 1er y último following, los demás se eliminan.
     * SD_12
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSDRRDSD_1233345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
    }

    /* * Propuesta de solución: Dejar seguimientos 1,5,6
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDSCR_123334(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Se Dejan el 1er, 4to y último following
     * SCR_123
     * @param CaseDTO $currentCase
     * @return CaseDTO
     * @author Regmy Nieves
     */
    function solveSDSCCR_123456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDDSD_12334(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    public function solveSDCSCR_123345(CaseDTO $currentCase)
    {
        //Las posiciones que no se eliminan son  0 , 2, 5
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     *  Propuesta de solución: Dividir en dos casos y elimianr el seguimiento 3
     * SD_12  SCR456
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDDDSCR_1223456(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1],  $followings[4], $followings[5], $followings[6]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Dejar sospechoso(1), confirmado (5) y recuperado (6)
     * SCR_156.
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDCCDR_12345566(CaseDTO $currentCase)
    {
        //Las posiciones que no se eliminan son  0 , 5, 7
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[6]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dividir en dos casos
     * SD_12 SCR_679
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSDSDSSCRR_123456789(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1],  $followings[5], $followings[6], $followings[8]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[7]->getId());

        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Dejar sospechoso(1), confirmado(5) y recuperado(6)
     * SCR_156
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSSCRRR_12334566(CaseDTO $currentCase)
    {
        //Las posiciones que no se eliminan son  0 , 4, 6
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución:Dividir en dos y eliminar el seguimiento 2
     * SD_12 SD_34
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveSRDCR_12234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[3]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $followings[4]->setStatus(Constants::HEALTH_STATUS_DISCARDED);

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[2],  $followings[3], $followings[4]]);

        $this->deleteCase($followings[1]->getId());

        $this->saveCase($currentCase);

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Eliminar los Seguimientos 1 y 2
     * CR_12
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSHCR_1112(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[0]->getId());
        $this->deleteCase($currentCase->followings[1]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: 
     * SCR_12 2+1
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSSCR_1122(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $followings[3]->setDate($this->addDaysToDate($followings[0]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[2],  $followings[3]]);

        $this->deleteCase($followings[1]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }


    /**
     * Propuesta de solución. Eliminar el caso de la mitad
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSRD_123(CaseDTO $currentCase)
    {

        $this->deleteCase($currentCase->followings[1]->getId());

        return $currentCase;
    }



    /**
     * Propuesta de solución: Eliminar el seguimiento 3 y 4, al seguimiento 1 restarle un dia
     * SCR_-1 1 2 
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCCCR_11112(CaseDTO $currentCase)

    {
        $followings = $currentCase->getFollowings();
        $followings[0]->setDate($this->subtractDaysFromDate($followings[1]->getDate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1],  $followings[4]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: 
     * SCR_123 SD_56
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO 
     */
    public function solveSCCRRSRR_12344566(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[7]->setStatus(Constants::HEALTH_STATUS_DISCARDED);
        $followings[3]->setDate($followings[2]->getDate());

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1],  $followings[3], $followings[5], $followings[7]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[6]->getId());

        $this->saveCase($currentCase);

        $arrIdentifiers = [[1, 2, 3], [4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: SD_12
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSRDD_1233(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());

        return $currentCase;
    }


    /**
     * Propuesta de solución: CR_12 CR_34
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCSRSCR_112334(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[2],  $followings[4], $followings[5]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: CR_12, VR_45
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRCCVR_123445(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1],  $followings[4], $followings[5]]);

        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: SD_14
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSRCSR_112334(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[5]->setStatus(Constants::HEALTH_STATUS_DISCARDED);

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[1], $followings[5]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());
        $this->deleteCase($followings[4]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: SD_-1 1 C_24
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveDDD_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[0], $followings[1]]);

        $this->deleteCase($followings[2]->getId());

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * Propuesta de solución: SD_24
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSRDR_12345(CaseDTO $currentCase)
    {        
        // Posiciones que no se eliminan 1 y 3
        $this->deleteCase($currentCase->followings[0]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: SCR_145
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSCRR_123455(CaseDTO $currentCase)
    {             
        // Posiciones que no se eliminan 0, 3 y 5
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: SD_-1 1 CR_24
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveDCCR_1234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[2]->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $followings[2]->setDate($this->subtractDaysFromDate( $followings[0]->getdate(), 1));

        $currentCase->setFollowings([]);
        $currentCase->setfollowings([$followings[2], $followings[0], $followings[1], $followings[3]]);

        $this->saveCase($currentCase);

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;

    }


    /**
     * Buscar seguimiento por estado
     *
     * @param array $followings
     * @param string $status
     * @return FollowingDTO $followingDTO
     */
    public function searchFollowingByStatus($followings, $status)
    {
        $followingDTO = null;
        foreach ($followings as $following) {
            if ($following->getStatus() == $status) {
                $followingDTO = $following;
            }
        }
        return $followingDTO;
    }

    /**
     * Buscar los seguimientos de un caso por estado
     *
     * @param array $followings
     * @param string $status
     * @return void
     */
    public function searchFollowingsByStatus($followings, $status)
    {
        $followings_array = [];
        foreach ($followings as $following) {
            if ($following->getStatus() == $status) {
                $followings_array[] = $following;
            }
        }
        return $followings_array;
    }

    /**
     * Restar n días de una fecha
     * 
     * @param date fecha a usar
     * @param number_of_days número de días a restar 
     * 
     * @return resultDate
     */
    public function subtractDaysFromDate($date, $numberOfDays)
    {
        $dateTime = new DateTime($date);
        $resultDate = $dateTime->sub(new DateInterval('P' . $numberOfDays . 'D'));
        $resultDate = $resultDate->format('Y-m-d H:m:s');

        return $resultDate;
    }

    /**
     * Agregar n días de una fecha
     * 
     * @param date fecha a usar
     * @param number_of_days número de días a sumar 
     * 
     * @return resultDate
     */
    public function addDaysToDate($date, $number_of_days)
    {
        $dateTime = new DateTime($date);
        $resultDate = $dateTime->add(new DateInterval('P' . $number_of_days . 'D'));
        $resultDate = $resultDate->format('Y-m-d H:m:s');

        return $resultDate;
    }


    /**
     * Crea un seguimiento nuevo asociado al caso existente
     * 
     * @param CaseDTO $caseDTO
     * @param FollowingDTO $originalFollowing
     * @return void
     */
    public function addNewFollowingToCase($originalFollowing, &$caseDTO, $date, $status)
    {
        $followingDTO = new FollowingDTO;
        $followingDTO->setDocument($originalFollowing->getDocument());
        $followingDTO->setFormat_id($originalFollowing->getFormat_id());
        $followingDTO->setSent_to_following_id($originalFollowing->getSent_to_following_id());
        $followingDTO->setCreate_user_document($originalFollowing->getCreate_user_document());

        $followingDTO->setDate($date);
        $followingDTO->setStatus($status);

        $followings = $caseDTO->getFollowings();
        array_unshift($followings, $followingDTO);
        $caseDTO->setFollowings($followings);
    }

    // Función nueva de borrado físico
    // Eliminación del seguimiento.

    /**
     * Divide el caso en seguimientos que respetan el flujo normal del sistema.
     * Los seguimentos en el primer grupo quedarán en el caso existente los demás se asignará a nuevos casos.
     * La fecha de apertura del caso existente permanecerá igual.
     * La fecha de creación del caso ('created_at') será igual a la fecha del primer seguimiento del grupo.
     * La fecha de actualización de cada grupo de casos será igual a la del último seguimiento del grupo.
     * El estado de salud del caso (status_id) será el mismo que el estado de salud del último seguimiento.
     * El estado del caso dependerá del si el último estado de salud del grupo es un estado de cierre ('Descartado', 'Recuperado', 'Fallecido'). 
     * Considerando que al final, sólo debe existir un único caso abierto si esta situación aplica.
     * @param CaseDTO $oldCaseDTO
     * @param int[] $arrIdentifiers ejm: [[1,2], [3,4],[5,6,7]]
     */
    public function caseDivider($oldCaseDTO, $arrIdentifiers)
    {

        $mainCaseDTO = $this->updateOldCase($oldCaseDTO, [$oldCaseDTO->getFollowings()[0], $oldCaseDTO->getFollowings()[1]]);

        $newCasesDTO = $this->createNewCases($oldCaseDTO, $arrIdentifiers);

        $cases = array_merge($mainCaseDTO, $newCasesDTO);

        $activities  = $oldCaseDTO->getActivities();

        $this->updateCasesActivities($cases, $activities);

        foreach ($cases as $case) {
            $this->saveCase($case);
        }

        return $newCasesDTO;
    }

    /**
     * 
     * @param CaseDTO $oldCaseDTO
     * @param int[] $arrIdentifiers
     * @return CaseDTO[]
     */
    public function createNewCases($oldCaseDTO, $arrIdentifiers)
    {
        $newCasesDTO = array();

        for ($i = 1; $i < count($arrIdentifiers); $i++) {
            $groupFollowings = $arrIdentifiers[$i];

            $caseDTO = new CaseDTO();

            $caseDTO->initNewByCase($oldCaseDTO);

            $caseDTO->setFollowings($this->getFollowingsByPositions($oldCaseDTO, $groupFollowings));

            $follogingEnd = end($caseDTO->getFollowings());

            $caseDTO->setHealthStatus($follogingEnd->getStatus());

            $caseDTO->setDate($follogingEnd->getDate());
            $caseDTO->setStatus(in_array($follogingEnd->getStatus(), [Constants::HEALTH_STATUS_CONFIRMED, Constants::HEALTH_STATUS_SUSPICIOUS]) ? "1" : "0");
            $caseDTO->setCreated_at($caseDTO->getFollowings()[0]->getCreateAt());

            $caseDTO->setId($this->caseDAO->save($caseDTO));

            $this->updateFollowingsByCaseId($caseDTO);

            $newCasesDTO[] = $caseDTO;
        }

        return $newCasesDTO;
    }


    /**
     * updateFollowingsByCaseId
     *
     * @param  CaseDTO $caseDTO
     * @return void
     */
    public function updateFollowingsByCaseId(&$caseDTO)
    {
        $arrFollowings = [];
        $followings = $caseDTO->getFollowings();
        foreach ($followings as $following) {
            $following->setCaseId($caseDTO->getId());
            $arrFollowings[] = $following;
        }
        $caseDTO->setFollowings($arrFollowings);
    }

    /**
     * 
     * @param CaseDTO $caseDTO
     * @param int[] $arrPositions
     */
    public function getFollowingsByPositions($caseDTO, $arrPositions)
    {
        $followingsDTO = array();

        foreach ($arrPositions as $position) {
            $followingsDTO[] = $caseDTO->getFollowings()[$position - 1];
        }

        return $followingsDTO;
    }

    /**
     * 
     * @param CaseDTO $oldCaseDTO
     * @param FollowingDTO[] $followingsDTO
     */
    public function updateOldCase($oldCaseDTO, $followingsDTO)
    {

        $caseDTO = new CaseDTO();
        $caseDTO->setId($oldCaseDTO->getId());

        $caseDTO->setDocument($oldCaseDTO->getDocument());
        $caseDTO->setStatus($oldCaseDTO->getStatus());
        $caseDTO->setCreated_at($followingsDTO[0]->getCreated_at());

        $this->changeCaseToLastFollowing($caseDTO, $followingsDTO);

        $this->caseDAO->update($caseDTO);
        $arr[] = $caseDTO;
        return $arr;
    }

    /**
     * 
     * @param CaseDTO $caseDTO
     * @param FollowingDTO[] $followingsDTO
     */
    public function changeCaseToLastFollowing(&$caseDTO, $followingsDTO)
    {
        $caseDTO->setDate($followingsDTO[count($followingsDTO) - 1]->getDate());

        $caseDTO->setHealthStatus($followingsDTO[count($followingsDTO) - 1]->getStatus());
        $caseDTO->setFollowings($followingsDTO);
    }

    /**
     * 
     * @param CaseDTO[] $cases
     * @param ActivityDTO[] $activities
     */
    public function updateCasesActivities($cases, $activities)
    {
        $caseevert = array_reverse($cases);
        foreach ($caseevert as $case) {
            $tmpActivities = [];
            foreach ($activities as $k => $activity) {
                if ($activity->getCreated_at() >= $case->getCreated_at()) {
                    $activity->setCaseId($case->getId());
                    $tmpActivities[] = $activity;
                    unset($activities[$k]);
                }
            }
            $case->setActivities($tmpActivities);
        }
    }
    /**
     * 
     * @param CaseDTO $caseDTO
     */
    public function saveCase($caseDTO, $originalFollowingId = null)
    {
        if (!$caseDTO->getId() || is_null($caseDTO->getId()) || $caseDTO->getId() == '' || $caseDTO->getId() == " ") {
            $this->caseDAO->save($caseDTO);
        } else {
            $this->caseDAO->update($caseDTO);
        }

        if (count($caseDTO->getActivities()) > 0) {
            foreach ($caseDTO->getActivities() as $activityDTO) {
                if (!is_null($activityDTO)) {
                    $this->activitiesDAO->update($activityDTO);
                }
            }
        }

        if (count($caseDTO->getFollowings()) > 0) {
            foreach ($caseDTO->getFollowings() as $followingDTO) {
                if (is_null($followingDTO->getId())) {
                    $newFollowingId =  $this->followingDAO->save($followingDTO, $caseDTO);
                    if ($originalFollowingId != null) {
                        $this->followingDAO->clonePhoto($originalFollowingId, $newFollowingId);
                    }
                } else {
                    $this->followingDAO->update($followingDTO, $caseDTO);
                }
            }
        }
    }

    public function deleteCase($IdFollowing)
    {
        $this->followingDAO->delete($IdFollowing);
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,4,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCVCHR_12345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());

        return $currentCase;
    }
    /**
     * Propuesta de solución: Dejar seguimientos 1,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDDD_123444(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,7
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDDDSD_12345678(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
        $this->deleteCase($currentCase->followings[6]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDDSD_1234556(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSDDSD_1234567(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5,6
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSSCR_123445(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSSD_12334(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSSD_12345(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSDDD_11234(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSDSDDSD_12234456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());
        $this->deleteCase($currentCase->followings[6]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSDSDSD_1223456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());
        $this->deleteCase($currentCase->followings[5]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,3
     * Cambiando estado de salud del primero
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveVCR_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $followings[0]->setStatus(Constants::HEALTH_STATUS_CONFIRMED);

        $this->deleteCase($followings[1]->getId());
        unset($currentCase->followings[1]);

        $this->saveCase($currentCase);
        
        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,4
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveUCRR_1122(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: Dejar seguimientos 1,5
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSRRRSD_122234(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        $this->deleteCase($currentCase->followings[2]->getId());
        $this->deleteCase($currentCase->followings[3]->getId());
        $this->deleteCase($currentCase->followings[4]->getId());

        return $currentCase;
    }

    /**
     * Propuesta de solución: 
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSSSDCCR_1223456(CaseDTO $currentCase)
    {
        $this->deleteCase($currentCase->followings[1]->getId());
        unset($currentCase->followings[1]);
        $this->deleteCase($currentCase->followings[2]->getId());
        unset($currentCase->followings[2]);
        $this->deleteCase($currentCase->followings[5]->getId());
        unset($currentCase->followings[5]);

        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución:
     *  SD_12 SD_12  SD_13 SD_45
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSDSD_12345(CaseDTO $currentCase)
    {

        return $cases;
    }

    /**
     * Propuesta de solución:
     *  SD_12 SD_3 +1  CHR_456
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDSCHCR_1233456(CaseDTO $currentCase)
    {
        $currentCase->followings[3]->setDate($this->addDaysToDate($currentCase->followings[3]->getDate(), 1));

        $currentCase->followings[3]->setStatus(Constants::HEALTH_STATUS_DISCARDED);

        $arrIdentifiers = [[1, 2], [3, 4],[5,6,7]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

}
