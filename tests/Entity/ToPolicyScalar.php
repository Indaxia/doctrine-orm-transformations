<?php
namespace Indaxia\OTR\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Indaxia\OTR\ITransformable;
use Indaxia\OTR\Traits\Transformable;
use Indaxia\OTR\Annotations\Policy;

class ToPolicyScalar implements ITransformable {
    use Transformable;
    
    /** @ORM\Id
     * @ORM\Column(type="integer") */
    protected $id;
    
    /* @Policy\To\FormatDateTime
     * @ORM\Column(type="datetime") */
    protected $dt1;
    
    /* @Policy\To\FormatDateTime(format="Y_m_d_H_i_s")
     * @ORM\Column(type="datetime") */
    protected $dt2;
    
    /* @Policy\To\KeepDateTime
     * @ORM\Column(type="datetime") */
    protected $dt3;
    
    /** @ORM\Column(type="date") */
    protected $date;
    
    /** @ORM\Column(type="time") */
    protected $time;
    
    /** @ORM\Column(length=255) */
    protected $str;
    
    /** @Policy\Skip
     * @ORM\Column(length=255) */
    protected $strSkip;
    
    /** @ORM\Column(type="simple_array") */
    protected $sa;
    
    /** @ORM\Column(type="simple_array") */
    protected $sae;
    
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
    
    public function getId() { return $this->id; }
    public function setId($v) { $this->id = $v; return $this; }
    public function getDt1() { return $this->dt1; }
    public function setDt1($v) { $this->dt1 = $v; return $this; }
    public function getDt2() { return $this->dt2; }
    public function setDt2($v) { $this->dt2 = $v; return $this; }
    public function getStr() { return $this->str; }
    public function setStr($v) { $this->str = $v; return $this; }
    public function getStrSkip() { return $this->strSkip; }
    public function setStrSkip($v) { $this->strSkip = $v; return $this; }
    public function getDate() { return $this->date; }
    public function setDate($v) { $this->date = $v; return $this; }
    public function getTime() { return $this->time; }
    public function setTime($v) { $this->time = $v; return $this; }
    public function getJa() { return $this->ja; }
    public function setJa($v) { $this->ja = $v; return $this; }
    public function getSa() { return $this->sa; }
    public function setSa($v) { $this->sa = $v; return $this; }
    public function getSae() { return $this->sae; }
    public function setSae($v) { $this->sae = $v; return $this; }
    public function getDeci() { return $this->deci; }
    public function setDeci($v) { $this->deci = $v; return $this; }
    public function getBI() { return $this->BI; }
    public function setBI($v) { $this->BI = $v; return $this; }
    public function getBln() { return $this->bln; }
    public function setBln($v) { $this->bln = $v; return $this; }
    public function getFlt() { return $this->flt; }
    public function setFlt($v) { $this->flt = $v; return $this; }
}
