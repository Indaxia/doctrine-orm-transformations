<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Handles the field in ITransformabe::fromArray.
 * Opposite to Skip.
 * @ORM\Annotation */
class Accept implements Interfaces\AcceptFrom {
}