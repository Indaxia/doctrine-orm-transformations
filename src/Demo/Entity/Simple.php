<?php

namespace ScorpioT1000\OTR\Demo\Entity;

use Doctrine\ORM\Mapping as ORM;
use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;
use \ScorpioT1000\OTR\Annotations\Policy;

/**
 * @ORM\Entity
 * @ORM\Table(name="OTR_Simple")
 */
class Simple implements ITransformable
{
    use Transformable;
    
    /**
     * @ORM\Id
     * @ORM\Column(name="customId", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO") */
    protected $customId;
    
    /** @ORM\Column(type="string") */
    protected $value = "";
    
    
        
    public function getCustomId() { return $this->customId; }
    public function getValue() { return $this->value; }
    public function setValue($v) { $this->value = $v; }
}