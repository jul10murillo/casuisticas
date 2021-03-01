<?php

include 'dao/CaseMySqlDAO.php';
include 'dao/FollowingMySqlDAO.php';
include 'dao/ActivitiesMySqlDAO.php';
include 'dto/CaseDTO.php';

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
     * solveSDSD_1234
     *
     * @param  int $idOldCase
     * @return void
     */
    public function solveSDSD_1234($idOldCase)
    {
        
        $oldCase = $this->caseDAO->getById($idOldCase);

        $arrIdentifiers = [[1, 2], [3, 4]];

        $cases = $this->caseDivider($oldCase, $arrIdentifiers);

        return $cases;
    }

    /**
     * solveR_1
     * 
     * @param CaseDTO $oldCase
     * @return void
     */
    public function solveR_1(CaseDTO $oldCase)  {
        $date = $this->subtractDaysFromDate($oldCase->getDate(), 1);
        $this->addNewFollowingToCase($oldCase, $date, Constants::HEALTH_STATUS_CONFIRMED);
        $this->saveCase($oldCase);
    }

    /**
     * solveD_1
     * 
     * @param CaseDTO $oldCase
     * @return void
     */
    public function solveD_1(CaseDTO $oldCase)  {
        $date = $this->subtractDaysFromDate($oldCase->getDate(), 1);
        $this->addNewFollowingToCase($oldCase, $date, Constants::HEALTH_STATUS_SUSPICIOUS);
        $this->saveCase($oldCase);
    }

    /**
     * Restar n días de una fecha
     * 
     * @param date fecha a usar
     * @param number_of_days número de días a restar 
     * 
     * @return resultDate
     */
    public function subtractDaysFromDate($date, $number_of_days)
    {
        $dateTime = new DateTime($date);
        $resultDate = $dateTime->sub(new DateInterval('P'.$number_of_days.'D'));        
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

        for ($i = 1; $i <= count($arrIdentifiers); $i++) {
            $groupFollowings = $arrIdentifiers[$i];

            $caseDTO = new CaseDTO();

            $caseDTO->setDocument($oldCaseDTO->getDocument());

            $caseDTO->addFollowingsToCase($caseDTO, $this->getFollowingsByPositions($oldCaseDTO, $groupFollowings));

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

        $caseDTO->setId(end($oldCaseDTO->getId()));
        $this->addFollowingsToCase($caseDTO, $followingsDTO);

        $this->caseDAO->update($caseDTO);
    }

    /**
     * 
     * @param CaseDTO $caseDTO
     * @param FollowingDTO[] $followingsDTO
     */
    public function addFollowingsToCase(&$caseDTO, $followingsDTO)
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

        if(count($caseDTO->getFollowings())>0){
            foreach($caseDTO->getFollowings() as $followingDTO){
                if(is_null($followingDTO->getId())){
                    $this->followingDAO->save($followingDTO);    
                }else{
                    $this->followingDAO->update($followingDTO);
                }
            }
        }
    }
}
