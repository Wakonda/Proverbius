<?php

namespace Proverbius\Entity;

use Proverbius\Service\GenericFunction;

class Proverb
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
     * @var string
     */
    protected $slug;

    /**
     *
     * @var \Proverbius\Entity\country
     */
    protected $country;

    /**
     *
     * @var \Proverbius\Entity\tag
     */
    protected $tags;

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

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }
	
    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug()
    {
		$this->slug = GenericFunction::slugify($this->text, 30);
    }
}