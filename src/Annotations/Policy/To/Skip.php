<?php
namespace ScorpioT1000\OTR\Annotations\Policy\To;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Excludes the field from ITransformabe::toArray result. Opposite to Accept.
 * @ORM\Annotation */
class Skip implements Interfaces\SkipTo {
}