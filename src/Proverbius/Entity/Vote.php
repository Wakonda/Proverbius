<?php

namespace Proverbius\Entity;

class Vote
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var integer
     */
    protected $vote;

    /**
     *
     * @var \Proverbius\Entity\Proverb
     */
    protected $proverb;

    /**
     *
     * @var \Proverbius\Entity\User
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getVote()
    {
        return $this->vote;
    }

    public function setVote($vote)
    {
        $this->vote = $vote;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getProverb()
    {
        return $this->proverb;
    }

    public function setProverb($proverb)
    {
        $this->proverb = $proverb;
    }
}
