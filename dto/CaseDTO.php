<?php

include 'dto/FollowingDTO.php';
include 'dto/ActivityDTO.php';


class CaseDTO
{
    private $id;
    private $document;
    private $creation_date;
    private $closing_justification;
    private $novelty;
    private $responsible_document;
    private $create_user_document;
    private $modifier_user_document;
    private $created_at;
    private $updated_at;
    private $deleted_at;
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

    /**
     *
     * @var FollowingDTO[] 
     */
    public $followings;

    /**
     *
     * @var ActivityDTO[] 
     */
    public $activities;

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

    function getCreation_date()
    {
        return $this->creation_date;
    }

    function getClosing_justification()
    {
        return $this->closing_justification;
    }

    function getNovelty()
    {
        return $this->novelty;
    }

    function getResponsible_document()
    {
        return $this->responsible_document;
    }

    function getCreate_user_document()
    {
        return $this->create_user_document;
    }

    function getModifier_user_document()
    {
        return $this->modifier_user_document;
    }

    function setCreation_date($creation_date)
    {
        $this->creation_date = $creation_date;
    }

    function setClosing_justification($closing_justification)
    {
        $this->closing_justification = $closing_justification;
    }

    function setNovelty($novelty)
    {
        $this->novelty = $novelty;
    }

    function setResponsible_document($responsible_document)
    {
        $this->responsible_document = $responsible_document;
    }

    function setCreate_user_document($create_user_document)
    {
        $this->create_user_document = $create_user_document;
    }

    function setModifier_user_document($modifier_user_document)
    {
        $this->modifier_user_document = $modifier_user_document;
    }

    function getCreated_at()
    {
        return $this->created_at;
    }

    function getUpdated_at()
    {
        return $this->updated_at;
    }

    function getDeleted_at()
    {
        return $this->deleted_at;
    }

    function setCreated_at($created_at)
    {
        $this->created_at = $created_at;
    }

    function setUpdated_at($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    function setDeleted_at($deleted_at)
    {
        $this->deleted_at = $deleted_at;
    }


    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setDate($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    public function getUpdateAt()
    {
        return $this->updated_at;
    }

    function getHealthStatus()
    {
        return $this->healthStatus;
    }

    function setHealthStatus($healthStatus)
    {
        $this->healthStatus = $healthStatus;
    }

    function initByCase($case)
    {
        $this->id                     = $case[0];
        $this->document               = $case[1];
        $this->creation_date          = $case[2];
        $this->closing_justification  = $case[3];
        $this->healthStatus           = $case[4];
        $this->novelty                = $case[5];
        $this->status                 = $case[6];
        $this->responsible_document   = $case[7];
        $this->create_user_document   = $case[8];
        $this->modifier_user_document = $case[9];
        $this->created_at             = $case[10];
        $this->updated_at             = $case[11];
        $this->deleted_at             = $case[12];
    }

    function initNewByCase($case)
    {
        $this->document               = $case->getDocument();
        $this->creation_date          = $case->getCreation_date();
        $this->closing_justification  = $case->getClosing_justification();
        $this->healthStatus           = $case->getHealthStatus();
        $this->novelty                = $case->getNovelty();
        $this->status                 = $case->getStatus();
        $this->responsible_document   = $case->getResponsible_document();
        $this->create_user_document   = $case->getCreate_user_document();
        $this->modifier_user_document = $case->getModifier_user_document();
        $this->created_at             = $case->getCreated_at();
        $this->updated_at             = $case->getUpdated_at();
        $this->deleted_at             = $case->getDeleted_at();
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
                $activityOBJ->initByActivityDTO($activity);
                $activitiesData[] = $activityOBJ;
            } else {
                $activityOBJ->initByActivity($activity);
                $activitiesData[] = $activityOBJ;
            }
        }
        $this->activities = $activitiesData;
    }

    function getArrayValueAllProperties()
    {
        return [
            isset($this->id) ? $this->id : '""',
            isset($this->document) ? $this->document : '""',
            isset($this->creation_date) ? $this->creation_date : '""',
            isset($this->closing_justification) ? $this->closing_justification : '""',
            isset($this->healthStatus) ? $this->healthStatus : '""',
            isset($this->novelty) ? $this->novelty : '""',
            isset($this->status) ? $this->status : '""',
            isset($this->responsible_document) ? $this->responsible_document : '""',
            isset($this->create_user_document) ? $this->create_user_document : '""',
            isset($this->modifier_user_document) ? $this->modifier_user_document : '""',
        ];
    }

    function getArrayNameAllProperties()
    {
        return [
            'id',
            'document',
            'creation_date',
            'closing_justification',
            'healthStatus',
            'novelty',
            'status',
            'responsible_document',
            'create_user_document',
            'modifier_user_document',
        ];
    }
}
