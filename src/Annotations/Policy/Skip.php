<?php
namespace ScorpioT1000\OTR\Annotations\Policy;

use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Skips the field in both ITransformabe::fromArray and ITransformabe::toArray.
 * Opposite to Accept.
 * @ORM\Annotation */
class Skip implements Interfaces\SkipTo, Interfaces\SkipFrom {
}