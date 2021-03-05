<?php

class FollowingDTO
{

    private $id;
    private $document;
    private $format_id;
    private $status_id;
    private $create_user_document;
    private $created_at;
    private $update_at;
    private $sent_to_following_id;

    /**
     * initByFollowingDTO
     *
     * @param  FollowingDTO $following
     * @return void
     */
    function initByFollowingDTO($following)
    {
        $this->id                   = $following->getId();
        $this->document             = $following->getDocument();
        $this->format_id            = $following->getFormatId();
        $this->status_id            = $following->getStatus();
        $this->create_user_document = $following->getCreateUserDocument();
        $this->created_at           = $following->getCreateAt();
        $this->update_at            = $following->getDate();
        $this->sent_to_following_id = $following->getCaseId();
    }

    function initByFollowing($following)
    {
        $this->id                   = $following[0];
        $this->document             = $following[1];
        $this->format_id            = $following[2];
        $this->status_id            = $following[3];
        $this->create_user_document = $following[4];
        $this->created_at           = $following[5];
        $this->update_at            = $following[6];
        $this->sent_to_following_id = $following[7];
    }

    public function loadFromJson($followingJson)
    {
        $tmp = json_decode($followingJson);
        $this->id     = $tmp->id;
        $this->status = $tmp->status;
        $this->date   = $tmp->date;
    }

    function getCreateAt()
    {
        return $this->created_at;
    }

    function setCreateAt($created_at)
    {
        $this->created_at = $created_at;
    }

    function getCreateUserDocument()
    {
        return $this->create_user_document;
    }

    function setCreateUserDocument($create_user_document)
    {
        $this->create_user_document = $create_user_document;
    }

    function getFormatId()
    {
        return $this->format_id;
    }

    function setFormatId($format_id)
    {
        $this->format_id = $format_id;
    }

    function getDocument()
    {
        return $this->document;
    }

    function setDocument($document)
    {
        $this->document = $document;
    }

    function getId()
    {
        return $this->id;
    }

    function getStatus()
    {
        return $this->status_id;
    }

    function getCaseId()
    {
        return $this->sent_to_following_id;
    }

    function getDate()
    {
        return $this->update_at;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setStatus($status_id)
    {
        $this->status_id = $status_id;
    }

    function setCaseId($sent_to_following_id)
    {
        $this->sent_to_following_id = $sent_to_following_id;
    }

    function setDate($update_at)
    {
        $this->update_at = $update_at;
    }

    function getArrayValueAllProperties()
    {
        return [
            $this->id,
            $this->document,
            $this->format_id,
            $this->status_id,
            $this->create_user_document,
            $this->created_at,
            $this->update_at,
            $this->sent_to_following_id,
        ];
    }

    function getArrayNameAllProperties()
    {
        return [
            'id',
            'document',
            'format_id',
            'status_id',
            'create_user_document',
            'created_at',
            'update_at',
            'sent_to_following_id',
        ];
    }
}
