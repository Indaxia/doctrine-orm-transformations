<?php
namespace Indaxia\OTR\Annotations\Policy\To;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Uses closure to format field and return a new value.
 * @Annotation */
class Custom
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\CustomTo
{
    public $priority = 0.5;
    
    public $closure = null;
    
    /** Sets closure to prove if the field should be changed or skipped.
     * @param \Closure $c function($value,
     *                             $propertyName)
     *      The closure function MUST return a new value even if the $value is an Entity.
     * 
     * @return Custom */
    public function format(\Closure $c) {
        $this->closure = $c;
        return $this;
    }

}