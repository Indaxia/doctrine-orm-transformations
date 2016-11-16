<?php
namespace Indaxia\OTR\Annotations\Policy\To;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Uses closure to override the default formatting behaviour.
 * @Annotation */
class Custom
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\CustomTo
{
    public $format = null;
    public $transform = null;
    public $priority = 0.5;
    public $propagating = false;
    
    /** Sets closure to format the SCALAR field.
     * @param \Closure $handler function($value, $columnType)
     *      The $value is passed to closure BEFORE any transformation.
     *      $columnType can be one of Doctrine DBAL types or null for non-doctrine type.
     *      @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#mapping-matrix
     *      The closure function MUST return a new value that will be placed at the result array.
     * 
     * @return Custom */
    public function format(\Closure $handler) {
        $this->format = $handler;
        return $this;
    }
    
    /** Sets closure to transform the RELATION (Entity or Collection) field into array.
     * @param \Closure $handler function($original, $transformed)
     *      $original can be an Entity, Collection or null
     *      $transformed is a transformation result, it can be changed and returned.
     *      The closure function MUST return a new value that will be placed at the result array.
     * 
     * @return Custom */
    public function transform(\Closure $handler) {
        $this->transform = $handler;
        return $this;
    }    

}