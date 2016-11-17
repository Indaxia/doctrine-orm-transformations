<?php
namespace Indaxia\OTR\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Indaxia\OTR\ITransformable;
use Indaxia\OTR\Traits\Transformable;
use Indaxia\OTR\Annotations\Policy;

/**
 * @ORM\Entity
 */
class Scalar implements ITransformable {
    use Transformable;
    
    /** @ORM\Id
     * @ORM\Column(type="integer") */
    protected $id;
    
    /** @Policy\To\FormatDateTime
     * @ORM\Column(type="datetime") */
    protected $dt1;
    
    /** @Policy\To\FormatDateTime(format="Y_m_d_H_i_s")
     * @ORM\Column(type="datetime") */
    protected $dt2;
    
    /** @Policy\To\KeepDateTime
     * @ORM\Column(type="datetime", nullable=true) */
    protected $dt3;
    
    /** @Policy\From\Auto
     * @ORM\Column(type="date") */
    protected $date;
    
    /** @Policy\From\Skip
     * @ORM\Column(type="time") */
    protected $time;
    
    /** @ORM\Column(length=255) */
    protected $str;
    
    /** @Policy\To\Skip
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
    
    /** @ORM\Column(type="string", nullable=true) */
    protected $strNull;
    
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyNew
     * @ORM\Column(type="string", nullable=true) */
    protected $str1;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyUnset
     * @ORM\Column(type="string", nullable=true) */
    protected $str2;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyUpdate
     * @ORM\Column(type="string", nullable=true) */
    protected $str3;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyNewUnset
     * @ORM\Column(type="string", nullable=true) */
    protected $str4;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyNewUpdate
     * @ORM\Column(type="string", nullable=true) */
    protected $str5;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyUnsetUpdate
     * @ORM\Column(type="string", nullable=true) */
    protected $str6;
    
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyNew
     * @ORM\Column(type="float", nullable=true) */
    protected $flt1;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyNew
     * @ORM\Column(type="float", nullable=true) */
    protected $flt2;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyUpdate
     * @ORM\Column(type="float", nullable=true) */
    protected $flt3;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyUpdate
     * @ORM\Column(type="float", nullable=true) */
    protected $flt4;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyUnset
     * @ORM\Column(type="float", nullable=true) */
    protected $flt5;
    
    /** @Policy\To\Skip
     *  @Policy\From\DenyUnset
     * @ORM\Column(type="float", nullable=true) */
    protected $flt6;
    
    public function getId() { return $this->id; }
    public function setId($v) { $this->id = $v; return $this; }
    public function getDt1() { return $this->dt1; }
    public function setDt1($v) { $this->dt1 = $v; return $this; }
    public function getDt2() { return $this->dt2; }
    public function setDt2($v) { $this->dt2 = $v; return $this; }
    public function getDt3() { return $this->dt3; }
    public function setDt3($v) { $this->dt3 = $v; return $this; }
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
    public function getStrNull() { return $this->strNull; }
    public function setStrNull($v) { $this->strNull = $v; return $this; }
    
    public function getStr1() { return $this->str1; }
    public function setStr1($v) { $this->str1 = $v; return $this; }
    public function getStr2() { return $this->str2; }
    public function setStr2($v) { $this->str2 = $v; return $this; }
    public function getStr3() { return $this->str3; }
    public function setStr3($v) { $this->str3 = $v; return $this; }
    public function getStr4() { return $this->str4; }
    public function setStr4($v) { $this->str4 = $v; return $this; }
    public function getStr5() { return $this->str5; }
    public function setStr5($v) { $this->str5 = $v; return $this; }
    public function getStr6() { return $this->str6; }
    public function setStr6($v) { $this->str6 = $v; return $this; }
    
    public function getFlt1() { return $this->flt1; }
    public function setFlt1($v) { $this->flt1 = $v; return $this; }
    public function getFlt2() { return $this->flt2; }
    public function setFlt2($v) { $this->flt2 = $v; return $this; }
    public function getFlt3() { return $this->flt3; }
    public function setFlt3($v) { $this->flt3 = $v; return $this; }
    public function getFlt4() { return $this->flt4; }
    public function setFlt4($v) { $this->flt4 = $v; return $this; }
    public function getFlt5() { return $this->flt5; }
    public function setFlt5($v) { $this->flt5 = $v; return $this; }
    public function getFlt6() { return $this->flt6; }
    public function setFlt6($v) { $this->flt6 = $v; return $this; }
}
