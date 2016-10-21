<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Skips (doesn't handle) the field in ITransformabe::fromArray. Opposite to Accept.
 * @ORM\Annotation */
class Skip
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\SkipFrom
{        
    public function inside($policy = []) {
        throw new \ScorpioT1000\OTR\Exceptions\PolicyException("Policy\\From\\Skip cannot contain policies");
    }

    public $priority = 0.9;
}