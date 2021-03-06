<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Sds\DoctrineExtensions\Stamp\DataModel\UpdatedOnTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\Document
 * @Sds\Rest
 * @Sds\Rest\Cache(noCache=true, public=false)
 * @Sds\Permission\Basic(roles="all", allow="all")
 */
class Game {

    use UpdatedOnTrait;

    /**
     * @ODM\Id(strategy="NONE")
     */
    protected $name;

    /**
     * @ODM\String
     * @Sds\Validator\Required
     */
    protected $type;

    /**
     * @ODM\EmbedOne(targetDocument="Publisher")
     */
    protected $publisher;

    /**
     * @ODM\EmbedMany(targetDocument="Component")
     */
    protected $components;

    /**
     * @ODM\ReferenceOne(targetDocument="Author", simple="true", inversedBy="games", cascade="all")
     */
    protected $author;

    /**
     * @ODM\ReferenceMany(targetDocument="Review", mappedBy="game", cascade="all")
     */
    protected $reviews;

    public function __construct() {
        $this->components = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getPublisher() {
        return $this->publisher;
    }

    public function setPublisher($publisher) {
        $this->publisher = $publisher;
    }

    public function getComponents() {
        return $this->components;
    }

    public function setComponents($components) {
        $this->components = $components;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function getReviews() {
        return $this->reviews;
    }

    public function setReviews($reviews) {
        $this->reviews = $reviews;
    }
}
