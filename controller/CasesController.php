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
        $arguments = $arguments[0];

        $function = 'solve' . $name;

        if (!TestController::codeCase($name, $arguments)) {
            $response = [
                'status'  => false,
                'caseIn'  => $arguments->getId(),
                'message' => 'El caso: ' . $arguments->getId() . ' no corresponde a la solución seleccionada: ' . $function,
            ];
            return json_encode($response);
        }
        try {
            if (method_exists($this, $function)) {
                $caseOut  = $this->$function($arguments);
                $response = [
                    'status'  => true,
                    'message' => 'Caso Resuelto',
                    'caseIn'  => $arguments->getId(),
                    'caseOut' => $caseOut
                ];
            } else {
                $response = [
                    'status'  => false,
                    'caseIn'  => $arguments->getId(),
                    'message' => 'La función: ' . $function . ' no existe',
                ];
            }
        } catch (\Throwable $th) {
            $response = [
                'status'  => false,
                'caseIn'  => $arguments->getId(),
                'message' => 'Error a ejecutar la función: ' . $function,
                'error'   => $th
            ];
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
        $this->addNewFollowingToCase($currentCase, $date, Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase);

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
        $this->addNewFollowingToCase($currentCase, $date, Constants::HEALTH_STATUS_SUSPICIOUS);
        $this->saveCase($currentCase);

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
        $currentCase->setFollowings([$suspicius_following, $confirmed_following, $recovered_following]);

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
    public function solveSCRSR_11234(CaseDTO $currentCase)
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
        $currentCase->setFollowings([$suspicius_following, $confirmed_following]);

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

        $this->addNewFollowingToCase($currentCase, $date, Constants::HEALTH_STATUS_CONFIRMED);

        $this->saveCase($currentCase);

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
        return $case;
    }


    /**
     * Propuesta de solución: Eliminar el seguimiento(2) Recuperado
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSRD_123(CaseDTO $currentCase)
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
        return $case;
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
        $currentCase->setFollowings($followings[0], $followings[4]);

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
        return $case;
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
        return $case;
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
        $currentCase->setFollowings([$followings[0],  $followings[4]]);

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
        $this->deleteCase($followings[4]->getId());

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
        $currentCase->setFollowings([$followings[0],  $followings[4]]);

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
        $currentCase->setFollowings([$followings[0],  $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

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
        return $case;
    }

    /**   
     *Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO[]
     */
    public function solveCRSD_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
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
        return $case;
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
        return $case;
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

        $this->addNewFollowingToCase($currentCase, $dateSuspicius, Constants::HEALTH_STATUS_SUSPICIOUS);

        $this->saveCase($currentCase);

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
        $currentCase->setFollowings($followings[0], $followings[2]);

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
        $currentCase->setFollowings($followings[0], $followings[2]);

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
        $currentCase->setFollowings($followings[0], $followings[2]);

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
        $currentCase->setFollowings($followings[0], $followings[3]);

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
        $currentCase->setFollowings($followings[0], $followings[2]);

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
        $currentCase->setFollowings($followings[0], $followings[3]);

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
        $currentCase->setFollowings([$followings[0], $followings[1], $followings[2], $followings[3]]);

        $arrIdentifiers = [[1], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());

        return $cases;
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
    public function solveCRSRDR_123456(CaseDTO $currentCase)
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
    public function solveCSRCR_12333(CaseDTO $currentCase)
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
        $cases = $this->caseDivider($oldCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1), Confirmado(3) y Recuperado (4)
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSDCSR_12344(CaseDTO $currentCase)
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
    public function solveSCSRCR_123444(CaseDTO $currentCase)
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
    public function solveSRDSD_12234(CaseDTO $currentCase)
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
        $cases = $this->caseDivider($oldCase, $arrIdentifiers);

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

        $this->deleteCase($followings[4]->getId());
        $this->deleteCase($followings[5]->getId());

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
    public function solveSCRSR_12334(CaseDTO $currentCase)
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
    public function solveSRSDSD_123345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings(); 

        $currentCase->setFollowings([]); 
        $currentCase->setFollowings([$followings[0],$followings[5]]);

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
        $currentCase->setFollowings([$followings[0],$followings[4]]);

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

        $followings[1]->setStatus(Constants::HeALTH_STATUS_DISCARDED);

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
    public function solveCRCRDRDR_11234567(CaseDTO $currentCase){
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
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(5);
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCRCVR_12345(CaseDTO $currentCase){
        $followings = $currentCase->getFollowings();
        
        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[4]);

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
        $currentCase->setFollowings($followings[0], $followings[3]);

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
     * Propuesta de solucion: Dejar seguimiento(1) y seguimiento(5);
     * 
     *@param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveCSCSR_11234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        
        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[4]);

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
    public function solveCSRDR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        
        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[4]);

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
    public function solveCSRSRSR_1123456(CaseDTO $currentCase)
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
        $currentCase->setFollowings($followings[1], $followings[3]);

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
        $currentCase->setFollowings($followings[1], $followings[2]);

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
        $currentCase->setFollowings($followings[1], $followings[4]);

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
        $currentCase->setFollowings($followings[1], $followings[6]);

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
        $currentCase->setFollowings($followings[1], $followings[3]);

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
        $currentCase->setFollowings($followings[1], $followings[3]);

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
        $currentCase->setFollowings($followings[1], $followings[5]);

        $this->deleteCase($followings[0]->getId());  
        $this->deleteCase($followings[2]->getId()); 
        $this->deleteCase($followings[3]->getId()); 
        $this->deleteCase($followings[4]->getId());        

        return $currentCase; 
    }

    /**
     * Undocumented function
     *
     * @param CaseDTO $currentCase
     * @return CaseDTO
     */
    public function solveSCDRSR_112234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[1], $followings[5]);

        $this->deleteCase($followings[0]->getId());  
        $this->deleteCase($followings[2]->getId()); 
        $this->deleteCase($followings[3]->getId()); 
        $this->deleteCase($followings[4]->getId());        

        return $currentCase;

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
        $resultDate = $resultDate->format('Y-m-d');

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
        $resultDate = $resultDate->format('Y-m-d');

        return $resultDate;
    }


    /**
     * Crea un seguimiento nuevo asociado al caso existente
     * 
     * @param CaseDTO $caseDTO
     * @return void
     */
    public function addNewFollowingToCase(&$caseDTO, $date, $status)
    {
        $followingDTO = new FollowingDTO();
        $followingDTO->setCaseId($caseDTO->getId());
        $followingDTO->setDate($date);
        $followingDTO->setStatus($status);

        $followings = $caseDTO->getFollowings();
        array_unshift($followings, $followingDTO);
        $caseDTO->setFollowings($followings);

        $this->saveCase($caseDTO);
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
            // if ($i = 2) {
            //     echo "test";
            // }
            $groupFollowings = $arrIdentifiers[$i];

            $caseDTO = new CaseDTO();

            $caseDTO->initNewByCase($oldCaseDTO);
            
            $caseDTO->setFollowings($this->getFollowingsByPositions($oldCaseDTO, $groupFollowings));

            $follogingEnd = end($caseDTO->getFollowings());

            $caseDTO->setHealthStatus($follogingEnd->getStatus());

            $caseDTO->setDate($follogingEnd->getDate());
            $caseDTO->setStatus(in_array($follogingEnd->getStatus(), [Constants::HEALTH_STATUS_CONFIRMED, Constants::HEALTH_STATUS_SUSPICIOUS]) ? "1" : "0");

            $newCase = $this->caseDAO->save($caseDTO);

            $newCasesDTO[] = $newCase;
        }

        return $newCasesDTO;
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
     * @param FollowingDTO $followingsDTO
     */
    public function updateOldCase($oldCaseDTO, $followingsDTO)
    {

        $caseDTO = new CaseDTO();
        $caseDTO->setId($oldCaseDTO->getId());

        $caseDTO->setDocument($oldCaseDTO->getDocument());
        $caseDTO->setStatus($oldCaseDTO->getStatus());

        $this->changeCaseToLastFollowing($caseDTO, $followingsDTO);

        $this->caseDAO->update($caseDTO);
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
        $casesRevert = array_reverse($cases);
        foreach ($casesRevert as $case) {
            $tmpActivities = [];
            foreach ($activities as $k => $activity) {
                if ($activity->getDate() >= $case->getDate()) {
                    $activity->setCaseId($case->getId());
                    $tmpActivities[] = $activity;
                    unset($activities[$k]);
                }
            }
            $case->setActivities($tmpActivities);

            $this->saveCase($case);
        }
    }

    /**
     * 
     * @param CaseDTO $caseDTO
     */
    public function saveCase($caseDTO)
    {
        if (count($caseDTO->getActivities()) > 0) {
            foreach ($caseDTO->getActivities() as $activityDTO) {
                $this->activitiesDAO->update($activityDTO);
            }
        }

        if (count($caseDTO->getFollowings()) > 0) {
            foreach ($caseDTO->getFollowings() as $followingDTO) {
                if (is_null($followingDTO->getId())) {
                    $this->followingDAO->save($followingDTO, $caseDTO);
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
}
