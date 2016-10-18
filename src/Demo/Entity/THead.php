<?php

namespace ScorpioT1000\OTR\Demo\Entity;

use Doctrine\ORM\Mapping as ORM;
use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;

/**
 * @ORM\Entity
 * @ORM\Table(name="TransformationsDemo_THead")
 */
class THead implements ITransformable
{
    use Transformable;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=32, nullable=false) */
    protected $id;
    
    /** @ORM\Column(length=255) */
    protected $str;
    
    /** @ORM\Column(type="date") */
    protected $date;
    
    /** @ORM\Column(type="time") */
    protected $time;
    
    /** @ORM\Column(type="datetime") */
    protected $datetime;
    
    /** @ORM\Column(type="json_array") */
    protected $ja;
    
    /** @ORM\Column(type="float") */
    protected $flt;
    
    /** @ORM\Column(type="decimal") */
    protected $deci;
    
    /** @ORM\Column(type="bigint") */
    protected $BI;
    
    /** @ORM\Column(type="boolean") */
    protected $bln;
    
    /** @ORM\ManyToOne(targetEntity="TSub", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="many2one", referencedColumnName="eid") */
    protected $many2one;
    
    /** @ORM\OneToOne(targetEntity="TSub", mappedBy="one2oneInversed", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="one2one", referencedColumnName="eid") */
    protected $one2one;
    
    /**
     * @ORM\ManyToMany(targetEntity="TSubCol", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="TransformationsDemo_THead_TSubCol",
     *      joinColumns={ @ORM\JoinColumn(name="thead_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="tsubcol_id", referencedColumnName="cid") }
     * )
     */
    protected $many2many;
    
    public function __construct() {
        $this->id = bin2hex(openssl_random_pseudo_bytes(16));
        $a = ['alpha','beta','charlie','delta','echo'];
        $this->str = $a[rand(0,4)];
        $this->date = new \DateTime();
        $this->date->setDate(rand(2030,2060), rand(1,12), rand(1,28));
        $this->time = new \DateTime();
        $this->time->setTime(rand(0,23),rand(0,59),rand(0,59));
        $this->datetime = new \DateTime();
        $this->ja = [$a[rand(0,4)],$a[rand(0,4)],$a[rand(0,4)]];
        $this->flt = (double)rand(0,1024) / 3.0;
        $this->deci = (string)($this->flt + 1.0);
        $this->BI = rand(0x1000000,0xFFFFFFF).rand(0x1000000,0xFFFFFFF);
        $this->bln = (rand(0,1) === 0);
        
        $this->many2many = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function getId() { return $this->id; }
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
    
    public function getMany2one() { return $this->many2one; }
    public function setMany2one($v) { $this->many2one = $v; }
    public function getOne2one() { return $this->one2one; }
    public function setOne2one($v) { $this->one2one = $v; }
    public function getMany2many() { return $this->many2many; }
    public function setMany2many($v) { $this->many2many = $v; }
    
}