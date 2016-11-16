<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Uses closure to manually parse the field into Entity.
 * @Annotation */
class Custom
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\CustomFrom
{    
    public $priority = 0.5;
    
    public $closure = null;
    
    /** Sets closure to prove if the field should be changed or skipped.
     * @param \Closure $c function($value,
     *                             $propertyName,
     *                             \Indaxia\OTR\ITransformable $entity,
     *                             \Doctrine\ORM\EntityManagerInterface $em)
     *      The closure function MUST return TRUE if the field has been processed
     *      or FALSE to let OTR process it using Auto policy.
     *      
     * @return Custom */
    public function parse(\Closure $c) {
        $this->closure = $c;
        return $this;
    }

}