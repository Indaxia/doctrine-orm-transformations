<?php
namespace Indaxia\OTR\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Indaxia\OTR\ITransformable;
use Indaxia\OTR\Traits\Transformable;
use Indaxia\OTR\Annotations\Policy;

/**
 * @ORM\Entity
 */
class Relations implements ITransformable {
    use Transformable;
    
    /** @ORM\Id
     * @ORM\Column(type="integer") */
    protected $id;
    
        
    /**
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="one_a", referencedColumnName="id") */
    protected $oneA;
    
    /**
     * @Policy\To\Auto
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="one_b", referencedColumnName="id") */
    protected $oneB;
    
    /**
     * @Policy\To\Skip
     * @Policy\From\DenyNew
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="one_c", referencedColumnName="id") */
    protected $oneC;
    
    /**
     * @Policy\From\DenyUpdate
     * @Policy\To\Skip
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="one_d", referencedColumnName="id") */
    protected $oneD;
    
    /**
     * @Policy\From\DenyUpdate(allowExternal=true)
     * @Policy\To\Skip
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="one_e", referencedColumnName="id") */
    protected $oneE;
    
    /**
     * @Policy\From\DenyUnset
     * @Policy\To\Skip
     * @ORM\ManyToOne(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="one_f", referencedColumnName="id") */
    protected $oneF;
    
    
    
    /**
     * 
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinTable(name="many_a",
     *      joinColumns={ @ORM\JoinColumn(name="relations_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="simple_id", referencedColumnName="id") }
     * )
     */
    protected $manyA;
    
    /**
     * @Policy\To\FetchPaginate(offset=0, limit=1)
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinTable(name="many_b",
     *      joinColumns={ @ORM\JoinColumn(name="relations_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="simple_id", referencedColumnName="id") }
     * )
     */
    protected $manyB;
    
    /**
     * @Policy\To\FetchPaginate(limit=2, fromTail=true)
     * @Policy\From\DenyNew
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinTable(name="many_c",
     *      joinColumns={ @ORM\JoinColumn(name="relations_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="simple_id", referencedColumnName="id") }
     * )
     */
    protected $manyC;
    
    /**
     * @Policy\From\DenyUpdate
     * @Policy\To\Skip
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinTable(name="many_d",
     *      joinColumns={ @ORM\JoinColumn(name="relations_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="simple_id", referencedColumnName="id") }
     * )
     */
    protected $manyD;
    
    /**
     * @Policy\From\DenyUpdate(allowExternal=true)
     * @Policy\To\Skip
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinTable(name="many_e",
     *      joinColumns={ @ORM\JoinColumn(name="relations_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="simple_id", referencedColumnName="id") }
     * )
     */
    protected $manyE;
    
    /**
     * @Policy\From\DenyUnset
     * @Policy\To\Skip
     * @ORM\ManyToMany(targetEntity="Simple", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="many_f",
     *      joinColumns={ @ORM\JoinColumn(name="relations_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="simple_id", referencedColumnName="id") }
     * )
     */
    protected $manyF;
    
    
    /**
     * @Policy\To\Auto
     * @ORM\ManyToOne(targetEntity="Relations", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="one_deep", referencedColumnName="id") */
    protected $deep;
        
    
    public function __construct() {
        $this->manyA = new ArrayCollection();
        $this->manyB = new ArrayCollection();
        $this->manyC = new ArrayCollection();
        $this->manyD = new ArrayCollection();
        $this->manyE = new ArrayCollection();
        $this->manyF = new ArrayCollection();
    }
    
    public function getId() { return $this->id; }
    public function setId($v) { $this->id = $v; return $this; }
    
    public function getOneA() { return $this->oneA; }
    public function setOneA($v) { $this->oneA = $v; return $this; }
    public function getOneB() { return $this->oneB; }
    public function setOneB($v) { $this->oneB = $v; return $this; }
    public function getOneC() { return $this->oneC; }
    public function setOneC($v) { $this->oneC = $v; return $this; }
    public function getOneD() { return $this->oneD; }
    public function setOneD($v) { $this->oneD = $v; return $this; }
    public function getOneE() { return $this->oneE; }
    public function setOneE($v) { $this->oneE = $v; return $this; }
    public function getOneF() { return $this->oneF; }
    public function setOneF($v) { $this->oneF = $v; return $this; }
    
    public function getManyA() { return $this->manyA; }
    public function setManyA($v) { $this->manyA = $v; return $this; }
    public function getManyB() { return $this->manyB; }
    public function setManyB($v) { $this->manyB = $v; return $this; }
    public function getManyC() { return $this->manyC; }
    public function setManyC($v) { $this->manyC = $v; return $this; }
    public function getManyD() { return $this->manyD; }
    public function setManyD($v) { $this->manyD = $v; return $this; }
    public function getManyE() { return $this->manyE; }
    public function setManyE($v) { $this->manyE = $v; return $this; }
    public function getManyF() { return $this->manyF; }
    public function setManyF($v) { $this->manyF = $v; return $this; }
    
    public function getDeep() { return $this->deep; }
    public function setDeep($v) { $this->deep = $v; return $this; }
    
    // to keep tests clean
    public static function generate() {
        $e = (new static())
             ->setId(1000)
             ->setOneA((new Simple())->setId(1)->setValue('one A sub-entity'))
             ->setOneB((new Simple())->setId(2)->setValue('one B sub-entity'))
             ->setOneC((new Simple())->setId(3)->setValue('one C sub-entity'))
             ->setOneD((new Simple())->setId(4)->setValue('one D sub-entity'))
             ->setOneE((new Simple())->setId(5)->setValue('one E sub-entity'))
             ->setOneF((new Simple())->setId(6)->setValue('one F sub-entity'));
             
        $c = $e->getManyA();
        $c->add((new Simple())->setId(10)->setValue('many A sub-entity 0'));
        $c->add((new Simple())->setId(11)->setValue('many A sub-entity 1'));
        $c->add((new Simple())->setId(12)->setValue('many A sub-entity 2'));
        
        $c = $e->getManyB();
        $c->add((new Simple())->setId(20)->setValue('many B sub-entity 0'));
        $c->add((new Simple())->setId(21)->setValue('many B sub-entity 1'));
        $c->add((new Simple())->setId(22)->setValue('many B sub-entity 2'));
        
        $c = $e->getManyC();
        $c->add((new Simple())->setId(30)->setValue('many C sub-entity 0'));
        $c->add((new Simple())->setId(31)->setValue('many C sub-entity 1'));
        $c->add((new Simple())->setId(32)->setValue('many C sub-entity 2'));
        
        $c = $e->getManyD();
        $c->add((new Simple())->setId(40)->setValue('many D sub-entity 0'));
        $c->add((new Simple())->setId(41)->setValue('many D sub-entity 1'));
        $c->add((new Simple())->setId(42)->setValue('many D sub-entity 2'));
        
        $c = $e->getManyE();
        $c->add((new Simple())->setId(50)->setValue('many E sub-entity 0'));
        $c->add((new Simple())->setId(51)->setValue('many E sub-entity 1'));
        $c->add((new Simple())->setId(52)->setValue('many E sub-entity 2'));
        
        $c = $e->getManyF();
        $c->add((new Simple())->setId(60)->setValue('many F sub-entity 0'));
        $c->add((new Simple())->setId(61)->setValue('many F sub-entity 1'));
        $c->add((new Simple())->setId(62)->setValue('many F sub-entity 2'));
        
        $e->setDeep(
            (new Relations())
            ->setId(10000)
            ->setOneA((new Simple())->setId(100)->setValue('one A sub-sub-entity'))
        );
        
        return $e;
    }
}