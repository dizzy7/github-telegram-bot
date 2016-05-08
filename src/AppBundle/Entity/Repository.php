<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="repository")
 */
class Repository
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="username")
     */
    public $user;

    /**
     * @ORM\Column(type="string")
     */
    public $repository;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\TagsSubsription", mappedBy="repository")
     */
    private $tagsSubscribers;

    /**
     * @ORM\Column(type="json_array")
     */
    public $tags = [];

    public function __construct()
    {
        $this->tagsSubscribers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TagsSubsription[]|Collection
     */
    public function getTagsSubscribers()
    {
        return $this->tagsSubscribers;
    }
}