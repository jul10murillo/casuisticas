<?php
class ActivityDTO{
	
    private $id;
    private $caseId;
    private $date;

    function __construct($activity) {
        $this->id     = $activity[0];
        $this->caseId = $activity[1];
        $this->date   = $activity[2];
    }

    function getId() {
        return $this->id;
    }

    function getCaseId() {
        return $this->caseId;
    }

    function getDate() {
        return $this->date;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCaseId($caseId) {
        $this->caseId = $caseId;
    }

    function setDate($date) {
        $this->date = $date;
    }

}