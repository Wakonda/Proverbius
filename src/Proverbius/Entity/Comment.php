<?php

namespace Proverbius\Entity;

class Comment
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $text;

    /**
     *
     * @var datetime
     */
    protected $created_at;

    /**
     *
     * @var \Pinturus\Entity\User
     */
    protected $user;

    /**
     *
     * @var \Pinturus\Entity\Proverb
     */
    protected $proverb;

	public function __construct()
	{
		$this->created_at = date("Y-m-d H:i:s");
	}
	
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
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
