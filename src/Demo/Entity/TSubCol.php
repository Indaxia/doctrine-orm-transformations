<?php

namespace ScorpioT1000\OTR\Demo\Entity;

use Doctrine\ORM\Mapping as ORM;
use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;

/**
 * @ORM\Entity
 * @ORM\Table(name="OTR_TSubCol")
 */
class TSubCol implements ITransformable
{
    use Transformable;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16, nullable=false) */
    protected $cid;
    
    /** @ORM\Column(type="datetime") */
    protected $datetime;
    
    /**
     * @ORM\ManyToOne(targetEntity="TSub", inversedBy="one2many")
     * @ORM\JoinColumn(name="many2one_inversed", referencedColumnName="eid")
     */
    protected $many2oneInversed;
    
    public function __construct() {
        $this->cid = bin2hex(openssl_random_pseudo_bytes(8));
        $this->datetime = new \DateTime();
    }
    public function getCid() { return $this->cid; }
    
    public function getDatetime() { return $this->datetime; }
    public function setDatetime($v) { $this->datetime = $v; }
    
    public function getMany2oneInversed() { return $this->many2oneInversed; }
    public function setMany2oneInversed($v) { $this->many2oneInversed = $v; }
}