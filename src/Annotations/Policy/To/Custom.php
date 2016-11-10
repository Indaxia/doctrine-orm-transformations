<?php
namespace Indaxia\OTR\Annotations\Policy\To;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Uses closure to format field.
 * @Annotation */
class Custom
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\CustomTo
{
    public $priority = 0.5;
    
    public $closure = null;
    
    /** Sets closure to prove if the field should be changed or skipped.
     * @param \Closure $c function($value, $propertyName)
     * @return mixed formatted value */
    public function format(\Closure $c) {
        $this->closure = $c;
        return $this;
    }

}