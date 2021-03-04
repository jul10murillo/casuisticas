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

    function initByCase($case)
    {
        $this->id           = $case[0];
        $this->document     = $case[1];
        $this->status       = $case[2];
        $this->healthStatus = $case[3];
        $this->date         = $case[4];
    }

    function getFollowings()
    {
        return $this->followings;
    }

    function getActivities()
    {
        return $this->activities;
    }

    function setFollowings($followings)
    {
        foreach ($followings as $following) {
            $followingOBJ = new FollowingDTO();
            if (!is_array($following)) {
                $followingOBJ->initByFollowingDTO($following);
                $followingData[] = $followingOBJ;
            } else {
                $followingOBJ->initByFollowing($following);
                $followingData[] = $followingOBJ;
            }
        }
        $this->followings = $followingData;
    }

    function setActivities($activities)
    {
        foreach ($activities as $activity) {
            $activityOBJ = new ActivityDTO();
            if (!is_array($activity)) {
                $activitiesData[] = $activityOBJ->initByActivityDTO($activity);
                $activitiesData[] = $activityOBJ;
            } else {
                $activityOBJ->initByActivity($activity);
                $activitiesData[] = $activityOBJ;
            }
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


    function getHealthStatus()
    {
        return $this->healthStatus;
    }

    function setHealthStatus($healthStatus)
    {
        $this->healthStatus = $healthStatus;
    }
}
