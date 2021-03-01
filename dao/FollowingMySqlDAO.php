<?php

//include 'mysql/ConnectionMySQL.php';
include 'FollowingDAO.php';
//use solverCases\ConnectionMySQL;

class FollowingMySqlDAO extends ConnectionMySQL implements FollowingDAO {

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
     * @return boolean
     */
    public function delete($id)
    {

    }

    /**
     * Actualiza un seguimiento
     * 
     * @param array $data
     * 
     * @return boolean
     */
    public function update($data)
    {

    }

    /**
     * Actualiza el caso de un seguimiento
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

