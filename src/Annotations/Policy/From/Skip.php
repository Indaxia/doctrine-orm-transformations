<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Skips (doesn't handle) the field.
 * @Annotation */
class Skip
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\SkipFrom
{        
    public function inside(array $policy) {
        throw new \Indaxia\OTR\Exceptions\PolicyException("Policy\\From\\Skip cannot contain policies");
    }

    public $priority = 0.9;
}