<?php
namespace Indaxia\OTR\Annotations\Policy\To;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Excludes the field from result.
 * @Annotation */
class Skip
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\SkipTo
{        
    public function inside(array $policy) {
        throw new \Indaxia\OTR\Exceptions\PolicyException("Policy\\To\\Skip cannot contain policies");
    }

    public $priority = 0.9;
}