<?php

namespace ScorpioT1000\OTR\Demo\Entity;

use Doctrine\ORM\Mapping as ORM;
use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;
use \ScorpioT1000\OTR\Annotations\Policy;

/**
 * @ORM\Entity
 * @ORM\Table(name="TransformationsDemo_Simple")
 */
class Simple implements ITransformable
{
    use Transformable;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue */
    protected $customId;
    
    
        
    public function getCustomId() { return $this->customId; }
}