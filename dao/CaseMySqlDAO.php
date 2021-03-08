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

        $caseDTO = new CaseDTO();
        $caseDTO->initByCase($caseDTO);

        $followings[] = parent::query("SELECT * FROM tigo_log_followings WHERE sent_to_following_id = " . $id);

        $caseDTO->setFollowings($followings);

        $activities[] = parent::query("SELECT * FROM tigo_followings WHERE sent_to_following_id = " . $id);

        $caseDTO->setActivities($activities);

        return $caseDTO;
    }

    /**
     * 
     * @param int[] $ids
     * @return \CaseDTO
     */
    public function getByIds($ids)
    {
        $cases = parent::query("SELECT * FROM tigo_sent_to_followings WHERE id in (" . join(',', $ids) . ")");

        $followings = parent::query("SELECT * FROM tigo_log_followings WHERE sent_to_following_id in (" . join(',', $ids) . ")");
        foreach ($followings as $value) {
            $followingsData[$value[7]][] = $value;
        }

        $activities = parent::query("SELECT * FROM tigo_followings WHERE sent_to_following_id in (" . join(',', $ids) . ")");

        foreach ($activities as $key => $value) {
            $activitiesData[$value[1]][] = $value;
        }

        foreach ($cases as $value) {

            $caseDTO = new CaseDTO();
            $caseDTO->initByCase($value);
            $caseDTO->setFollowings($followingsData[$value[0]]);

            $caseDTO->setActivities($activitiesData[$value[0]]);

            $caseDTOS[] = $caseDTO;
        }

        return $caseDTOS;
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
        $query = "UPDATE tigo_sent_to_followings SET"
            . " updated_at = '" . $case->getUpdated_at()
            . "', document = " .  $case->getDocument()
            . ", status = "    .  $case->getStatus()
            . ", status_id = " .  $case->getHealthStatus()
            . " WHERE id = "   .  $case->getId();
        // print_r('-------------<br>');
        $case = parent::queryUpdateOrInsert($query);
        return $case;
    }

    /**
     * Crea un caso
     * 
     * @param CaseDTO $case
     * 
     * @return stdClass
     */
    public function save($case)
    {
        $query = "INSERT INTO tigo_sent_to_followings (document,creation_date,closing_justification,status_id,novelty,`status`, responsible_document, create_user_document, created_at, updated_at, deleted_at) VALUES ("
            . '"' . $case->getDocument() . '",'
            . '"' . $case->getCreation_date() . '",'
            . '"' . $case->getClosing_justification() . '",'
            . '"' . $case->getHealthStatus() . '",'
            . '"' . $case->getNovelty() . '",'
            . '"' . $case->getStatus() . '",'
            . '"' . $case->getResponsible_document() . '",'
            . '"' . $case->getCreate_user_document() . '",'
            . '"' . $case->getCreated_at() . '",'
            . '"' . $case->getUpdated_at() . '",'
            . '"' . $case->getDeleted_at() . '")';
        // print_r('-------------<br>');
        $case = parent::queryUpdateOrInsert($query);
        return $case;
    }
}
