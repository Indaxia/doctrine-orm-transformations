<?php
namespace ScorpioT1000\OTR\Annotations\Policy;

use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Skips the field in both ITransformabe::fromArray and ITransformabe::toArray.
 * Opposite to Accept.
 * @ORM\Annotation */
class Skip 
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\SkipTo, Interfaces\SkipFrom 
{        
    public function inside($policy = []) {
        throw new \ScorpioT1000\OTR\Exceptions\PolicyException("Policy\\Skip cannot contain policies");
    }

    public static $priority = 0.9;
}