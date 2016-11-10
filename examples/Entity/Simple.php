<?php

namespace Indaxia\OTR\Demo\Entity;

use Doctrine\ORM\Mapping as ORM;
use \Indaxia\OTR\ITransformable;
use \Indaxia\OTR\Traits\Transformable;
use \Indaxia\OTR\Annotations\Policy;

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