<?php

interface FollowingDAO{
    
    /**
     * Retorna un seguimiento dado un id
     * 
     * @param integer $id
     * 
     * @return stdClass
     */
    public function getById($id);

    /**
     * Retorna los following que hay desde una fecha inicial
     * 
     * @param string $startDate
     * @param string $endDate
     * 
     * @return stdClass
     */
    public function getByStartDate($startDate);

    /**
     * Elimina un following
     * 
     * @param integer $id
     * 
     * @return boolean
     */
    public function delete($id);

    /**
     * Actualiza un following
     * 
     * @param array $data
     * 
     * @return boolean
     */
    public function update($data);

    /**
     * Retorna los following que hay desde una fecha inicial
     * 
     * @param integer $id
     * @param integer $caseId
     * 
     * @return stdClass
     */
    public static function updateCaseById($id,$caseId);

}