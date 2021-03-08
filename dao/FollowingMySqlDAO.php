<?php

//include 'mysql/ConnectionMySQL.php';
include 'FollowingDAO.php';
//use solverCases\ConnectionMySQL;

class FollowingMySqlDAO extends ConnectionMySQL implements FollowingDAO
{

    /**
     * Retorna un seguimiento dado un id
     * 
     * @param integer $id
     * 
     * @return stdClass
     */
    public function getById($id)
    {
        $following = parent::query("SELECT * FROM tigo_log_followings WHERE id = " . $id);
        return $following;
    }


    /**
     * Retorna los seguimientos que hay desde una fecha inicial
     * 
     * @param string $startDate
     * 
     * @return stdClass
     */
    public function getByStartDate($startDate)
    {
    }

    /**
     * Elimina un seguimiento
     * 
     * @param integer $id
     * 
     * @return void
     */
    public function delete($id)
    {
        parent::query("DELETE FROM tigo_log_followings WHERE id = " . $id);
    }

    /**
     * Actualiza el caso de un seguimiento
     * 
     * @param integer $id
     * @param integer $caseId
     * 
     * @return stdClass
     */
    public static function updateCaseById($id, $caseId)
    {
        $following = parent::query("UPDATE tigo_log_followings SET sent_to_following_id = " . $caseId . " WHERE id = " . $id);
        return $following;
    }

    /**
     * Crea un log following
     * 
     * @param FollowingDTO $followingDTO
     * @param CaseDTO $caseDTO
     * 
     * @return stdClass
     */
    public function save($followingDTO, $caseDTO)
    {
        $status = in_array($followingDTO->getStatus(), [Constants::HEALTH_STATUS_CONFIRMED, Constants::HEALTH_STATUS_SUSPICIOUS]) ? "1" : "0";

        $following = parent::queryUpdateOrInsert("INSERT INTO tigo_log_followings (" . implode(",", $followingDTO->getArrayNameAllProperties()) . ") VALUES (" . implode(",", $followingDTO->getArrayValueAllProperties()) . ")");

        return $following;
    }

    /**
     * Actualiza el caso de un seguimiento
     * 
     * @param FollowingDTO $followingDTO
     * @param CaseDTO $caseDTO
     * 
     * @return stdClass
     */
    public function update($followingDTO, $caseDTO)
    {
        $following = parent::queryUpdateOrInsert(
            "UPDATE tigo_log_followings SET " .
                //"document=".$caseDTO->getDocument().",".
                "status_id=" . $followingDTO->getStatus() . "," .
                "sent_to_following_id=" . $followingDTO->getCaseId() . "," .
                "created_at='" . $followingDTO->getDate() . "'," .
                "updated_at='" . $followingDTO->getUpdated_at() . "' WHERE id = " . $followingDTO->getId()
        );

        return $following;
    }


    public function clonePhoto($originalFollowing, $idNewFollowing)
    {

        $resultTigoField =  parent::queryUpdateOrInsert("INSERT INTO tigo_SVEPI.tigo_fields 
        (`id_table`, `table`, `field`, `value`, `created_at`, `updated_at`)
        SELECT '$idNewFollowing', `table`, `field`, `value`, `created_at`, `updated_at` 
        FROM tigo_SVEPI.tigo_fields AS tf WHERE tf.id_table = $originalFollowing");

        return $resultTigoField;
    }
}
