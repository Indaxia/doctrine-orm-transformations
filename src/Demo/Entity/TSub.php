<?php

namespace ScorpioT1000\OTR\Demo\Entity;

use Doctrine\ORM\Mapping as ORM;
use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;

/**
 * @ORM\Entity
 * @ORM\Table(name="TransformationsDemo_TSub")
 */
class TSub implements ITransformable
{
    use Transformable;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=32, nullable=false) */
    protected $eid;
    
    /** @ORM\Column(type="datetime") */
    protected $datetime;
    
    /**
     * @ORM\OneToMany(targetEntity="TSubCol", mappedBy="many2oneInversed", cascade={"persist", "remove"}) */
    protected $one2many;
    
    /** @ORM\OneToOne(targetEntity="THead", inversedBy="one2one")
     * @ORM\JoinColumn(name="one2one_inversed", referencedColumnName="id") */
    protected $one2oneInversed;
    
    public function __construct() {
        $this->eid = bin2hex(openssl_random_pseudo_bytes(16));
        $this->datetime = new \DateTime();
        $this->one2many = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function getEid() { return $this->eid; }
    public function getStr() { return $this->str; }
    public function setStr($v) { $this->str = $v; }
    public function getDate() { return $this->date; }
    public function setDate($v) { $this->date = $v; }
    public function getTime() { return $this->time; }
    public function setTime($v) { $this->time = $v; }
    public function getDatetime() { return $this->datetime; }
    public function setDatetime($v) { $this->datetime = $v; }
    public function getJa() { return $this->ja; }
    public function setJa($v) { $this->ja = $v; }
    public function getDeci() { return $this->deci; }
    public function setDeci($v) { $this->deci = $v; }
    public function getBI() { return $this->BI; }
    public function setBI($v) { $this->BI = $v; }
    public function getBln() { return $this->bln; }
    public function setBln($v) { $this->bln = $v; }
    public function getFlt() { return $this->flt; }
    public function setFlt($v) { $this->flt = $v; }
    
    public function getOne2oneInversed() { return $this->one2oneInversed; }
    public function setOne2oneInversed($v) { $this->one2oneInversed = $v; }
    public function getOne2many() { return $this->one2many; }
    public function setOne2many($v) { $this->one2many = $v; }
}