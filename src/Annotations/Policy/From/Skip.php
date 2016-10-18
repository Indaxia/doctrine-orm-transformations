<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Skips (doesn't handle) the field in ITransformabe::fromArray. Opposite to Accept.
 * @ORM\Annotation */
class Skip implements Interfaces\SkipFrom {
}