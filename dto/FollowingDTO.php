<?php

class FollowingDTO
{

    private $id;

    private $status;

    private $caseId;

    private $date;

    /**
     * initByFollowingDTO
     *
     * @param  FollowingDTO $following
     * @return void
     */
    function initByFollowingDTO($following)
    {
        $this->id     = $following->getId();
        $this->status = $following->getStatus();
        $this->caseId = $following->getCaseId();
        $this->date   = $following->getDate();
    }

    function initByFollowing($following)
    {
        $this->id     = $following[0];
        $this->status = $following[1];
        $this->caseId = $following[2];
        $this->date   = $following[3];
    }

    public function loadFromJson($followingJson)
    {
        $tmp = json_decode($followingJson);
        $this->id     = $tmp->id;
        $this->status = $tmp->status;
        $this->date   = $tmp->date;
    }

    function getId()
    {
        return $this->id;
    }

    function getStatus()
    {
        return $this->status;
    }

    function getCaseId()
    {
        return $this->caseId;
    }

    function getDate()
    {
        return $this->date;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setStatus($status)
    {
        $this->status = $status;
    }

    function setCaseId($caseId)
    {
        $this->caseId = $caseId;
    }

    function setDate($date)
    {
        $this->date = $date;
    }
}
