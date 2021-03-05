<?php
class ActivityDTO
{

    private $id;
    private $caseId;
    private $following_type_id;
    private $date;
    private $time;
    private $length;
    private $responsable_document;
    private $format_id;
    private $description;
    private $observations;
    private $next_following_date;
    private $discard_justification;
    private $additional_variable;
    private $status_id;
    private $created_at;
    private $updated_at;
    private $deleted_at;
    private $close_contacts;
    private $workcenter_id;
    

    function initByActivity($activity)
    {
        $this->id                    = $activity[0];
        $this->caseId                = $activity[1];
        $this->following_type_id     = $activity[2];
        $this->date                  = $activity[3];
        $this->time                  = $activity[4];
        $this->length                = $activity[5];
        $this->responsable_document  = $activity[6];
        $this->format_id             = $activity[7];
        $this->description           = $activity[8];
        $this->observations          = $activity[9];
        $this->next_following_date   = $activity[10];
        $this->discard_justification = $activity[11];
        $this->additional_variable   = $activity[12];
        $this->status_id             = $activity[13];
        $this->created_at            = $activity[14];
        $this->updated_at            = $activity[15];
        $this->deleted_at            = $activity[16];
        $this->close_contacts        = $activity[17];
        $this->workcenter_id         = $activity[18];
    }
    
    /**
     * 
     * @param ActivityDTO $activity
     */
    function initByActivityDTO($activity)
    {
        $this->id                    = $activity->getId();
        $this->caseId                = $activity->getCaseId();
        $this->following_type_id     = $activity->getFollowing_type_id();
        $this->date                  = $activity->getDate();
        $this->time                  = $activity->getTime();
        $this->length                = $activity->getLength();
        $this->responsable_document  = $activity->getResponsable_document();
        $this->format_id             = $activity->getFormat_id();
        $this->description           = $activity->getDescription();
        $this->observations          = $activity->getObservations();
        $this->next_following_date   = $activity->getNext_following_date();
        $this->discard_justification = $activity->getDiscard_justification();
        $this->additional_variable   = $activity->getAdditional_variable();
        $this->status_id             = $activity->getStatus_id();
        $this->created_at            = $activity->getCreated_at();
        $this->updated_at            = $activity->getUpdated_at();
        $this->deleted_at            = $activity->getDeleted_at();
        $this->close_contacts        = $activity->getClose_contacts();
        $this->workcenter_id         = $activity->getWorkcenter_id();
    }

    function getId()
    {
        return $this->id;
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

    function setCaseId($caseId)
    {
        $this->caseId = $caseId;
    }

    function setDate($date)
    {
        $this->date = $date;
    }
    
    function getFollowing_type_id()
    {
        return $this->following_type_id;
    }

    function getTime()
    {
        return $this->time;
    }

    function getLength()
    {
        return $this->length;
    }

    function getResponsable_document()
    {
        return $this->responsable_document;
    }

    function getFormat_id()
    {
        return $this->format_id;
    }

    function getDescription()
    {
        return $this->description;
    }

    function getObservations()
    {
        return $this->observations;
    }

    function getNext_following_date()
    {
        return $this->next_following_date;
    }

    function getDiscard_justification()
    {
        return $this->discard_justification;
    }

    function getAdditional_variable()
    {
        return $this->additional_variable;
    }

    function getStatus_id()
    {
        return $this->status_id;
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

    function getClose_contacts()
    {
        return $this->close_contacts;
    }

    function getWorkcenter_id()
    {
        return $this->workcenter_id;
    }

    function setFollowing_type_id($following_type_id)
    {
        $this->following_type_id = $following_type_id;
    }

    function setTime($time)
    {
        $this->time = $time;
    }

    function setLength($length)
    {
        $this->length = $length;
    }

    function setResponsable_document($responsable_document)
    {
        $this->responsable_document = $responsable_document;
    }

    function setFormat_id($format_id)
    {
        $this->format_id = $format_id;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function setObservations($observations)
    {
        $this->observations = $observations;
    }

    function setNext_following_date($next_following_date)
    {
        $this->next_following_date = $next_following_date;
    }

    function setDiscard_justification($discard_justification)
    {
        $this->discard_justification = $discard_justification;
    }

    function setAdditional_variable($additional_variable)
    {
        $this->additional_variable = $additional_variable;
    }

    function setStatus_id($status_id)
    {
        $this->status_id = $status_id;
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

    function setClose_contacts($close_contacts)
    {
        $this->close_contacts = $close_contacts;
    }

    function setWorkcenter_id($workcenter_id)
    {
        $this->workcenter_id = $workcenter_id;
    }

    function getArrayValueAllProperties()
    {
        return [
            isset($this->id) ? $this->id : '""',
            isset($this->caseId) ? $this->caseId : '""',
            isset($this->following_type_id) ? $this->following_type_id : '""',
            isset($this->date) ? $this->date : '""',
            isset($this->time) ? $this->time : '""',
            isset($this->length) ? $this->length : '""',
            isset($this->responsable_document) ? $this->responsable_document : '""',
            isset($this->format_id) ? $this->format_id : '""',
            isset($this->description) ? $this->description : '""',
            isset($this->observations) ? $this->observations : '""',
            isset($this->next_following_date) ? $this->next_following_date : '""',
            isset($this->discard_justification) ? $this->discard_justification : '""',
            isset($this->additional_variable) ? $this->additional_variable : '""',
            isset($this->status_id) ? $this->status_id : '""',
            isset($this->created_at) ? $this->created_at : '""',
            isset($this->updated_at) ? $this->updated_at : '""',
            isset($this->deleted_at) ? $this->deleted_at : '""',
            isset($this->close_contacts) ? $this->close_contacts : '""',
            isset($this->workcenter_id) ? $this->workcenter_id : '""'
        ];
    }

    function getArrayNameAllProperties()
    {
        return [
            'id',
            'caseId',
            'following_type_id',
            'date',
            'time',
            'length',
            'responsable_document',
            'format_id',
            'description',
            'observations',
            'next_following_date',
            'discard_justification',
            'additional_variable',
            'status_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'close_contacts',
            'workcenter_id'
        ];
    }

}
