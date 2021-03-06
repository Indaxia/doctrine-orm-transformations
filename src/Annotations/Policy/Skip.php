<?php
namespace Indaxia\OTR\Annotations\Policy;

use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Skips the field in both ITransformabe::fromArray and ITransformabe::toArray.
 * @Annotation */
class Skip 
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\SkipTo, Interfaces\SkipFrom 
{        
    public function inside(array $policy) {
        throw new \Indaxia\OTR\Exceptions\PolicyException("Policy\\Skip cannot contain policies");
    }

    public $priority = 0.9;
}