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
     * @param ActivityDTO $activityDTO
     * 
     * @return boolean
     */
    public function update($activityDTO)
    {
        if( is_null($activityDTO)){
            echo 'here';
        }
        $activity = parent::queryUpdateOrInsert("UPDATE tigo_followings SET ".
            "sent_to_following_id=".$activityDTO->getCaseId()." WHERE id = ".$activityDTO->getId()
        );
        return $activity;
    }

    /**
     * 
     * @param ActivityDTO $activityDTO
     */
    function save($activityDTO)
    {
        $following = parent::queryUpdateOrInsert("INSERT INTO tigo_followings (".implode(",", $activityDTO->getArrayNameAllProperties()).") VALUES (".implode(",", $activityDTO->getArrayValueAllProperties()).")");
        return $following;
    }
}

