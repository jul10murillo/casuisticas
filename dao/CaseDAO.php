<?php

interface CaseDAO{
    
    /**
     * Retorna un caso dado un id
     * 
     * @param integer $id
     * 
     * @return stdClass
     */
    public function getById($id);

    /**
     * Elimina un caso
     * 
     * @param integer $id
     * 
     * @return boolean
     */
    public function delete($id);

    /**
     * Actualiza un caso
     * 
     * @param CaseDTO $case
     * 
     * @return boolean
     */
    public function update(CaseDTO $case);
}