<?php
namespace Indaxia\OTR\Tests\Entity;

use \Indaxia\OTR\ITransformable;
use \Indaxia\OTR\Traits\Transformable;
use \Indaxia\OTR\Annotations\Policy;

class Simple implements ITransformable {
    use Transformable;
    
    /** @ORM\Id
     * @ORM\Column(type="integer") */
    protected $id;
    
    /* @ORM\Column(type="string") */
    protected $value;
    
    public function getId() { return $this->id; }
    public function setId($v) { $this->id = $v; return $this; }
    public function getValue() { return $this->value; }
    public function setValue($v) { $this->value = $v; return $this; }
}
