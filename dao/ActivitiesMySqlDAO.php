<?php
include 'ActivitiesDAO.php';

class ActivitiesMySqlDAO extends ConnectionMySQL implements ActivitiesDAO {

    /**
     * Retorna un seguimiento dado un id
     * 
     * @param integer $id
     * 
     * @return stdClass
     */
    public function getById($id)
    {
        $following = parent::query("SELECT * FROM tigo_following WHERE id = " . $id);
        return $following;
    }

    /**
     * Elimina un following
     * 
     * @param integer $id
     * 
     * @return boolean
     */
    public function delete($id)
    {

    }

    /**
     * Actualiza un following
     * 
     * @param array $data
     * 
     * @return boolean
     */
    public function update($data)
    {

    }

    /**
     * Actualiza el caso de una actividad de seguimiento
     * 
     * @param integer $id
     * @param integer $caseId
     * 
     * @return stdClass
     */
    public static function updateCaseById($id,$caseId)
    {
        $following = parent::query("UPDATE tigo_log_followings SET sent_to_following_id = ".$caseId." WHERE id = " . $id);
        return $following;
    }
}

