<?php
namespace ScorpioT1000\OTR\Annotations\Policy\To;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Includes the fiend in ITransformabe::toArray result.
 * Opposite to Skip.
 * @ORM\Annotation */
class Accept implements Interfaces\AcceptTo {
}