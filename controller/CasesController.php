<?php

include 'dao/CaseMySqlDAO.php';
include 'dao/FollowingMySqlDAO.php';
include 'dao/ActivitiesMySqlDAO.php';
include 'dto/CaseDTO.php';
include 'TestController.php';

class CasesController
{

    private $caseDAO;
    private $followingDAO;
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
        $function = 'solve' . $name;

        if (!TestController::codeCase($name, $arguments)) {
            $response = [
                'status'  => false,
                'caseIn'  => $arguments,
                'message' => 'El caso:' . $arguments . ' no corresponde a la solución seleccionada:' . $function,
            ];
            return json_encode($response);
        }
        try {
            $caseOut  = $this->$function($arguments);
            $response = [
                'status'  => true,
                'message' => 'Caso Resuelto',
                'caseIn'  => $arguments,
                'caseOut' => $caseOut
            ];
        } catch (\Throwable $th) {
            $response = [
                'status'  => false,
                'caseIn'  => $arguments,
                'message' => 'Error a ejecutar la función:' . $function,
                'error'   => $th
            ];
        }
        return json_encode($response);
    }

    /**
     * solveSDSD_1234
     *
     * @param  CaseDTO $oldCase
     * @return void
     */
    public function solveSDSD_1234($oldCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($oldCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * solveR_1
     * 
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveR_1(CaseDTO $currentCase)
    {
        $date = $this->subtractDaysFromDate($currentCase->getDate(), 1);
        $this->addNewFollowingToCase($currentCase, $date, Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveD_1
     * 
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveD_1(CaseDTO $currentCase)
    {
        $date = $this->subtractDaysFromDate($currentCase->getDate(), 1);
        $this->addNewFollowingToCase($currentCase, $date, Constants::HEALTH_STATUS_SUSPICIOUS);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveCR_11
     * 
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCR_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $confirmed_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_CONFIRMED);
        $recovered_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_RECOVERED);

        $recovered_following->setDate($this->addDaysToDate($confirmed_following->getDate(), '1'));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$confirmed_following, $recovered_following]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveCRCR_1234
     * 
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCRCR_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }


    /**
     * solveSD_11
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSD_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $suspicius_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_SUSPICIOUS);
        $discarted_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_DISCARDED);

        $discarted_following->setDate($this->addDaysToDate($suspicius_following->getDate(), '1'));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$suspicius_following, $discarted_following]);

        $this->saveCase($currentCase);

        return $currentCase;
    }


    /**
     * solveSCR_112
     * 
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCR_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $suspicius_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_SUSPICIOUS);
        $confirmed_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_CONFIRMED);
        $recovered_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_RECOVERED);

        $suspicius_following->setDate($this->subtractDaysFromDate($confirmed_following->getDate(), '1'));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$suspicius_following, $confirmed_following, $recovered_following]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveSDSDSD_123456
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDSDSD_123456(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * solveSCRSR_11234
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCRSR_11234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $suspicius_following = $this->searchFollowingsByStatus($followings, Constants::HEALTH_STATUS_SUSPICIOUS);
        $confirmed_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_CONFIRMED);
        $recovered_following = $this->searchFollowingsByStatus($followings, Constants::HEALTH_STATUS_RECOVERED);

        $suspicius_following[0]->setDate($this->subtractDaysFromDate($confirmed_following->getDate(), '1'));

        $currentCase->setFollowings([]);

        $currentCase->setFollowings([
            $suspicius_following[0],
            $confirmed_following,
            $recovered_following[0],
            $suspicius_following[1],
            $recovered_following[1]
        ]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveSDCR_1234
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDCR_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * solveSC_11
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSC_11(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $suspicius_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_SUSPICIOUS);
        $confirmed_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_CONFIRMED);

        $suspicius_following->setDate($this->subtractDaysFromDate($confirmed_following->getDate(), '1'));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$suspicius_following, $confirmed_following]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveSDSCR_12345
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDSCR_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }

    /**
     * solveF_1 
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveF_1(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $deceased_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_DESEASED);

        $date =  $this->subtractDaysFromDate($deceased_following->getDate(), '1');

        $this->addNewFollowingToCase($currentCase, $date, Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveRD_12
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveRD_12(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $recovered_following = $this->searchFollowingsByStatus($followings, Constants::HEALTH_STATUS_RECOVERED);
        $discarded_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_DISCARDED);

        $recovered_following->setStatus(Constants::HEALTH_STATUS_SUSPICIOUS);
        $suspicius_following = $recovered_following;

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$suspicius_following, $discarded_following]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveSDS_123
     *
     * @param CaseDTO $currentCase     
     * @return void
     */
    public function solveSDS_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $cases;
    }
    /**
     * solveSDSDCR_123456
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDSDCR_123456(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }


    /**
     * solveSRD_123
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSRD_123(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $suspicius_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_SUSPICIOUS);
        $recovered_following = $this->searchFollowingsByStatus($followings, Constants::HEALTH_STATUS_RECOVERED);
        $discarted_following = $this->searchFollowingByStatus($followings, Constants::HEALTH_STATUS_DISCARDED);

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$suspicius_following, $discarted_following]);

        $this->saveCase($currentCase);

        return $currentCase;
    }
    /**
     * solveSDSDS_12345
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDSDS_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }

    /**
     * solveSCR_122
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCR_122(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $suspicius_following = $this->searchFollowingByStatus($followings, constants::HEALTH_STATUS_SUSPICIOUS);
        $confirmed_following = $this->searchFollowingByStatus($followings, constants::HEALTH_STATUS_CONFIRMED);
        $recovered_following = $this->searchFollowingByStatus($followings, constants::HeALTH_STATUS_RECOVERED);

        $dateNew = $this->addDaysToDate($recovered_following->getDate(), '1');

        $recovered_following->setDate($date);

        $currentCase->setfollowings([]);
        $currentCase->setFollowings([$suspicius_following, $confirmed_following, $recovered_following]);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solveSDSD_1123
     *
     * @param CaseDTO $currentCase
     * @return void
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
     * solveSDSDSCR_1234567
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDSDSCR_1234567(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5, 6, 7]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }


    /**
     * solveCRCR_1123
     *
     * @param CaseDTO $currentCase
     * @return void
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
     * solveRCR_123
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveRCR_123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2]]);

        $this->deleteCase($followings[0]->getId());

        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[2]]);
        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**
     * solvesolveCRCR_1123
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solvesolveCRCR_1123(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $suspicius_followings = $this->searchFollowingsByStatus($followings, constants::HEALTH_STATUS_SUSPICIOUS);
        $confirmed_followings = $this->searchFollowingsByStatus($followings, constants::HEALTH_STATUS_CONFIRMED);
        $recovered_followings = $this->searchFollowingsByStatus($followings, constants::HeALTH_STATUS_RECOVERED);

        $suspicius_following[0]->setDate($this->subtractDaysFromDate($confirmed_followings[0]->getDate(), '1'));
        $suspicius_following[1]->setDate($this->subtractDaysFromDate($confirmed_followings[1]->getDate(), '1'));

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$suspicius_followings[0], $confirmed_followings[0], $recovered_followings[0], $suspicius_followings[1], $confirmed_followings[1], $recovered_followings[1]]);
        $this->saveCase($currentCase);

        return $currentCase;
    }


    /**     
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSR_12(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $dayConfirmed = $followings[0]->getDate();
        $daySuspicius = $this->subtractDaysFromDate($dayConfirmed, '1');

        $followings[0] . setdate($daySuspicius);
        $this->addNewFollowingToCase($currentCase, $dayConfirmed, Constants::HEALTH_STATUS_CONFIRMED);

        $this->saveCase($currentCase);

        return $currentCase;
    }

    /**   
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDSDC_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4], [5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }
    /**   
     *
     * @param CaseDTO $currentCase
     * @return void
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
     *
     * @param CaseDTO $currentCase
     * @return void
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
     *
     * @param CaseDTO $currentCase
     * @return void
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
    
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCRCR_12345(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2, 3], [4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }

    /**   
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCRSD_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [4, 5]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }

    public function solveCSD_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1], [2, 3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }

    public function solveSMDSD_12234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3, 4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);
        return $case;
    }

    public function solveCRDR_1223(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[3]]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    public function solveSCUHC_12234(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],  $followings[2],  $followings[3],  $followings[4]]);

        $this->saveCase($currentCase);

        return $currentCase;
    }

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

    public function solveSVD_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[2]);

        $this->deleteCase($followings[1]->getId());;

        return $currentCase;
    }

    public function solveSHD_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[2]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }


    /**   
     *Eliminación de fallecido
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSFD_122(CaseDTO $currentCase)
    {

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[2]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    public function solveSDC_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    public function solveSDSD_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[3]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    public function solveCRS_123(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2], [3]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    public function solveUCR_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[2]);

        $this->deleteCase($followings[1]->getId());

        return $currentCase;
    }

    public function solveCRCR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings($followings[0], $followings[3]);

        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }

    public function solveSCRS_1234(CaseDTO $currentCase)
    {
        $arrIdentifiers = [[1, 2, 3], [4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

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

    public function solveXCXR_1233(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1], $followings[3]]);

        $this->deleteCase($followings[0]->getId());
        $this->deleteCase($followings[2]->getId());

        return $currentCase;
    }


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

   public function solveUCUCR_12345(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1],$followings[2],$followings[4] ]);

        $this->deleteCase($followings[0]->getId()); 
        $this->deleteCase($followings[3]->getId()); 
        
        return $currentCase;
    }  
 
    /**
     * Propuesta solución: Se propone mantener los estados Sospechoso(1), Confirmado(2) y Recuperado (3)
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSCRCR_12233(CaseDTO $currentCase){

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[0],$followings[1],$followings[4] ]);

        $this->deleteCase($followings[2]->getId()); 
        $this->deleteCase($followings[3]->getId()); 
        
        return $currentCase;
    }    

    /**
     * Propuesta solución: Se propone dividir en tres casos: Caso 1 (12), Caso 2 (34) y Caso 2 (56) y eliminar estado Sospechoso(5)
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveSDSDSCR_1234556(CaseDTO $currentCase){

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $this->deleteCase($followings[4]->getId()); 
        $currentCase->setFollowings([$followings[0],$followings[1],$followings[2],$followings[3], $followings[5], $followings[6]]);

        $arrIdentifiers = [[1,2], [3,4], [5,6] ];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confirmado(1) y Recuperado (6)
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCRSRDR_123456(CaseDTO $currentCase){
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
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1) y Descartado(2)
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCSD_112(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]); 

        $currentCase->setFollowings([$followings[1],$followings[2]]);
        $this->deleteCase($followings[0]->getId());

        return $currentCase; 
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Confimado(1) y Recuperado(3)
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCSRCR_12333(CaseDTO $currentCase){

        $followings = $currentCase->getFollowings();
        $currentCase->setFollowings([]); 

        $currentCase->setFollowings([$followings[0],$followings[4]]);
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[2]->getId());
        $this->deleteCase($followings[3]->getId());      

        return $currentCase;         
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34)
     *
     * @param CaseDTO $currentCase
     * @return void
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
     * @return void
     */
    public function solveSDCSR_12344(CaseDTO $currentCase){

        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]); 
        $currentCase->setFollowings([$followings[0],$followings[2], $followings[4]]);
        
        $this->deleteCase($followings[1]->getId());
        $this->deleteCase($followings[3]->getId());      

        return $currentCase;  
    }

    /**
     * Propuesta de solución: Se propone dividir en dos casos: Caso 1 (12) y Caso 2 (34) y eliminar el 
     * seguimiento con estado de salud Recuperado(4)
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCRSDR_12344(CaseDTO $currentCase)
    {
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $this->deleteCase($followings[4]->getId()); 
        $currentCase->setFollowings([$followings[0],$followings[1],$followings[2],$followings[3]]);

        $arrIdentifiers = [[1,2], [3,4]];
        $cases = $this->caseDivider($currentCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * Propuesta de solución: Se propone mantener los estados Sospechoso(1) , Confirmado(2) y Recuperado (4)
     *
     * @param CaseDTO $currentCase
     * @return void
     */
    public function solveCSCRCR_112234(CaseDTO $currentCase){
        
        $followings = $currentCase->getFollowings();

        $currentCase->setFollowings([]);
        $currentCase->setFollowings([$followings[1],$followings[2],$followings[5]]);

        $this->deleteCase($followings[0]->getId());
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

        $mainCaseDTO = $this->updateOldCase($oldCaseDTO[0], [$oldCaseDTO[0]->getFollowings()[0], $oldCaseDTO[0]->getFollowings()[1]]);

        $newCasesDTO = $this->createNewCases($oldCaseDTO[0], $arrIdentifiers);

        $cases = array_merge($mainCaseDTO, $newCasesDTO);

        $activities  = $oldCaseDTO[0]->getActivities();

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

        for ($i = 1; $i <= count($arrIdentifiers); $i++) {
            $groupFollowings = $arrIdentifiers[$i];

            $caseDTO = new CaseDTO();

            $caseDTO->setDocument($oldCaseDTO->getDocument());

            $caseDTO->setFollowings($this->getFollowingsByPositions($oldCaseDTO, $groupFollowings));

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
                    $this->followingDAO->save($followingDTO);
                } else {
                    $this->followingDAO->update($followingDTO);
                }
            }
        }
    }
    public function deleteCase($IdFollowing)
    {
        $this->followingDAO->delete($IdFollowing);
    }
}
