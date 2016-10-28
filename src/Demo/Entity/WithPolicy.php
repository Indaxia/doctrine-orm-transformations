<?php

namespace ScorpioT1000\OTR\Demo\Entity;

use Doctrine\ORM\Mapping as ORM;
use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;
use \ScorpioT1000\OTR\Annotations\Policy;

/**
 * @ORM\Entity
 * @ORM\Table(name="OTR_WithPolicy")
 */
class WithPolicy implements ITransformable
{
    use Transformable;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO") */
    protected $id;
    
    /**
     * @Policy\Auto
     * @ORM\Column(type="string")
     * */
    protected $alpha = 'auto';
    
    /**
     * @Policy\To\FormatDateTime(format="Y-m-d H:i:s")
     * @ORM\Column(type="datetime")
     * */
    protected $bravo;
    
    /**
     * @Policy\To\FormatDateTime
     * @ORM\Column(type="datetime")
     * */
    protected $charlie;
    
    /**
     * @Policy\Skip
     * @ORM\Column(type="string")
     * */
    protected $delta = 'skip';
    
    /**
     * @Policy\From\Skip
     * @Policy\To\Auto
     * @ORM\Column(type="string")
     * */
    protected $epsilon = 'from skip, to auto';
    
    /**
     * @Policy\From\Auto
     * @Policy\To\Skip
     * @ORM\Column(type="string")
     * */
    protected $foxtrot = 'from auto, to skip';
    
    /**
     * @Policy\From\DenyNew
     * @ORM\Column(type="string")
     * */
    protected $golf = 'deny new';
    
    /**
     * @Policy\From\DenyUpdate
     * @ORM\Column(type="string")
     * */
    protected $hotel = 'deny update';
    
    /**
     * @Policy\From\DenyUnset
     * @ORM\Column(type="string")
     * */
    protected $india = 'deny unset';
    
    /**
     * @Policy\From\DenyNew
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="juliet", referencedColumnName="customId") */
    protected $juliet;
    
    /**
     * @Policy\From\DenyUpdate
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="kilo", referencedColumnName="customId") */
    protected $kilo;
    
    /**
     * @Policy\From\DenyUnset
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="lima", referencedColumnName="customId") */
    protected $lima;
    
    /**
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="TransformationsDemo_WithPolicy_Simple",
     *      joinColumns={ @ORM\JoinColumn(name="p1_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="p2_id", referencedColumnName="customId") }
     * )
     */
    protected $mike;
    
    /**
     * @Policy\From\DenyNew
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="TransformationsDemo_WithPolicy_november_Simple",
     *      joinColumns={ @ORM\JoinColumn(name="p1_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="p2_id", referencedColumnName="customId") }
     * )
     */
    protected $november;
    
    /**
     * @Policy\From\DenyUpdate
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="TransformationsDemo_WithPolicy_oscar_Simple",
     *      joinColumns={ @ORM\JoinColumn(name="p1_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="p2_id", referencedColumnName="customId") }
     * )
     */
    protected $oscar;
    
    /**
     * @Policy\From\DenyUnset
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="TransformationsDemo_WithPolicy_papa_Simple",
     *      joinColumns={ @ORM\JoinColumn(name="p1_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="p2_id", referencedColumnName="customId") }
     * )
     */
    protected $papa;
    
    
    protected $quebec;
    
    /**
     * 
     * @ORM\Column(type="float")
     * */
    protected $romeo = 0.0001;
    
    /**
     * @Policy\From\DenyNew
     * @ORM\Column(type="float")
     * */
    protected $sierra = 0.0002;
    
    /**
     * @Policy\From\DenyUpdate
     * @ORM\Column(type="float")
     * */
    protected $tango = 0.0003;
    
    /**
     * @Policy\From\DenyUnset
     * @ORM\Column(type="float")
     * */
    protected $uniform = 0.0004;
    
    /**
     * @Policy\From\DenyNewUnset
     * @ORM\Column(type="float")
     * */
    protected $victor = 0.0005;
    
    /**
     * @Policy\From\DenyNewUpdate
     * @ORM\Column(type="float")
     * */
    protected $whiskey = 0.0006;
    
    /**
     * @Policy\From\DenyUnsetUpdate
     * @ORM\Column(type="float")
     * */
    protected $xray = 0.0007;
    
    /**
     * 
     * @ORM\Column(type="json_array")
     * */
    protected $yankee = ['hello,hello!','json','array',13.37,1337];
    
    /**
     * 
     * @ORM\Column(type="simple_array")
     * */
    protected $zulu = ['something',0xDEADF00D];
    
    public function __construct() {
        $this->id = bin2hex(openssl_random_pseudo_bytes(16));
        $this->bravo = new \DateTime();
        $this->charlie = new \DateTime();
        $this->juliet = new Simple();
        $this->kilo = new Simple();
        $this->lima = new Simple();
        $this->mike = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mike->add(new Simple());
        $this->mike->add(new Simple());
        $this->mike->add(new Simple());
        $this->november = new \Doctrine\Common\Collections\ArrayCollection();
        $this->november->add(new Simple());
        $this->november->add(new Simple());
        $this->november->add(new Simple());
        $this->oscar = new \Doctrine\Common\Collections\ArrayCollection();
        $this->oscar->add(new Simple());
        $this->oscar->add(new Simple());
        $this->oscar->add(new Simple());
        $this->papa = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function getId() { return $this->id; }
    public function getAlpha() { return $this->alpha; }
    public function setAlpha($v) { $this->alpha = $v; }
    public function getBravo() { return $this->bravo; }
    public function setBravo($v) { $this->bravo = $v; }
    public function getCharlie() { return $this->charlie; }
    public function setCharlie($v) { $this->charlie = $v; }
    public function getDelta() { return $this->delta; }
    public function setDelta($v) { $this->delta = $v; }
    public function getEpsilon() { return $this->epsilon; }
    public function setEpsilon($v) { $this->epsilon = $v; }
    public function getFoxtrot() { return $this->foxtrot; }
    public function setFoxtrot($v) { $this->foxtrot = $v; }
    public function getGolf() { return $this->golf; }
    public function setGolf($v) { $this->golf = $v; }
    public function getHotel() { return $this->hotel; }
    public function setHotel($v) { $this->hotel = $v; }
    public function getIndia() { return $this->india; }
    public function setIndia($v) { $this->india = $v; }
    public function getJuliet() { return $this->juliet; }
    public function setJuliet($v) { $this->juliet = $v; }
    public function getKilo() { return $this->kilo; }
    public function setKilo($v) { $this->kilo = $v; }
    public function getLima() { return $this->lima; }
    public function setLima($v) { $this->lima = $v; }
    public function getMike() { return $this->mike; }
    public function setMike($v) { $this->mike = $v; }
    public function getNovember() { return $this->november; }
    public function setNovember($v) { $this->november = $v; }
    public function getOscar() { return $this->oscar; }
    public function setOscar($v) { $this->oscar = $v; }
    public function getPapa() { return $this->papa; }
    public function setPapa($v) { $this->papa = $v; }
    public function getQuebec() { return $this->quebec; }
    public function setQuebec($v) { $this->quebec = $v; }
    public function getRomeo() { return $this->romeo; }
    public function setRomeo($v) { $this->romeo = $v; }
    public function getSierra() { return $this->sierra; }
    public function setSierra($v) { $this->sierra = $v; }
    public function getTango() { return $this->tango; }
    public function setTango($v) { $this->tango = $v; }
    public function getUniform() { return $this->uniform; }
    public function setUniform($v) { $this->uniform = $v; }
    public function getVictor() { return $this->victor; }
    public function setVictor($v) { $this->victor = $v; }
    public function getWhiskey() { return $this->whiskey; }
    public function setWhiskey($v) { $this->whiskey = $v; }
    public function getXray() { return $this->xray; }
    public function setXray($v) { $this->xray = $v; }
    public function getYankee() { return $this->yankee; }
    public function setYankee($v) { $this->yankee = $v; }
    public function getZulu() { return $this->zulu; }
    public function setZulu($v) { $this->zulu = $v; }
    
}