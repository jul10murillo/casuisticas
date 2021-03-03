<?php

include 'dto/FollowingDTO.php';
include 'dto/ActivityDTO.php';


class CaseDTO
{
    private $id;
    private $document;
    /**
     * Estado del caso (1,0) = (Abierto, Cerrado)
     * @var type 
     */
    private $status;
    
    /**
     * Estado de salud (Sospechoso, DEscartado, Confirmado)
     * @var type 
     */
    private $healthStatus;
    private $date;
    
    /**
     *
     * @var FollowingDTO[] 
     */
    private $followings;
    
    
    /**
     *
     * @var ActivityDTO[] 
     */
    private $activities;


    function setCase($case) {
        $this->id           = $case[0];
        $this->document     = $case[1];
        $this->status       = $case[6];
        $this->healthStatus = $case[4];
        $this->date         = $case[2];
    }


    function getFollowings() {
        return $this->followings;
    }

    function getActivities() {
        return $this->activities;
    }
    
    /**
     * 
     * @param FollowingDTO[] $followings
     */
    function setFollowings($followings) {
        
        foreach ($followings as $following) {
            $followingRow = new FollowingDTO();
            $followingRow->setId($following->getID());
            $followingRow->setStatus($following->getStatus());
            $followingRow->setCaseId($following->getCaseId());
            $followingRow->setDate($following->getDate());
            $followingData[] = $followingRow;
        }

        $this->followings = $followingData;

    }

    function setOldFollowings($followings)
    {
        foreach ($followings as $following) {
            $followingRow = new FollowingDTO();
            $followingRow->setId($following[0]);
            $followingRow->setStatus($following[3]);
            $followingRow->setCaseId($following[7]);
            $followingRow->setDate($following[6]);
            $followingData[] = $followingRow;
        }

        $this->followings = $followingData;
    }

    function setActivities($activities) {
        foreach ($activities as $activity) {
            $activitiesData[] = new ActivityDTO($activity);
        }
        $this->activities = $activitiesData;
    }
    

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setDocument($document)
    {
        $this->document = $document;
    }
    
    public function getDocument()
    {
        return $this->document;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setDate($date)
    {
        $this->date = $date;
    }
    
    public function getDate()
    {
        return $this->date;
    }
    
    
    public function getHealthStatus()
    {
        return $this->healthStatus;
    }
    
    public function setHealthStatus($healthStatus)
    {
        $this->healthStatus = $healthStatus;
    }
    
    /**
     * addFollowingsToCase
     *
     * @param  FollowingDTO[] $followings
     * @return void
     */
    public function addFollowingsToCase($followings)
    {
        
    }


}
