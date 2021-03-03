<?php
//namespace solverCases\DAO;

include 'mysql/ConnectionMySQL.php';
include 'CaseDAO.php';
include '../dto/CaseDTO.php';
//use solverCases\ConnectionMySQL;
//use solverCases\DTO\CaseDTO;

class CaseMySqlDAO extends ConnectionMySQL implements CaseDAO
{

    /**
     * Retorna un caso dado un id
     * 
     * @param integer $id
     * 
     * @return stdClass
     */
    public function getById($id)
    {
        
        $case = parent::query("SELECT * FROM tigo_sent_to_followings WHERE id = " . $id);
        
        $caseDTO = new CaseDTO($case);
        
        $followings[] = parent::query("SELECT * FROM tigo_log_followings WHERE sent_to_following_id = " . $id);
        
        $caseDTO->setFollowings($followings);
        
        $activities[] = parent::query("SELECT * FROM tigo_followings WHERE sent_to_following_id = " . $id);
        
        $caseDTO->setActivities($activities);
        
        return $caseDTO;
    }

    /**
     * Retorna un caso dado un id
     * 
     * @param integer $id
     * 
     * @return stdClass
     */
    public static function getDocumentById($id)
    {
        $case = parent::query("SELECT * FROM tigo_sent_to_following WHERE id = " . $id);
        return $case;
    }

    /**
     * Elimina un caso
     * 
     * @param integer $id
     * 
     * @return boolean
     */
    public function delete($id)
    {
    }

    /**
     * Actualiza un caso
     * 
     * @param CaseDTO $case
     * 
     * @return boolean
     */
    public function update(CaseDTO $case)
    {
        $case = parent::query("UPDATE tigo_sent_to_followings SET"
            . " date = " . $case->getDate()
            . ", document = " . $case->getDocument()
            . ", status = " . $case->getStatus()
            . " WHERE id = " . $case->getId());

        return $case;
    }

    /**
     * Crea un caso
     * 
     * @param CaseDTO $caseDTO
     * 
     * @return stdClass
     */
    public function save($caseDTO)
    {
        $case = parent::query("SELECT * FROM tigo_sent_to_following WHERE id = " . $id);
        return $case;
    }
}
