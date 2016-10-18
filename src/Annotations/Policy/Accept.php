<?php
namespace ScorpioT1000\OTR\Annotations\Policy;

use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Handles the field in both ITransformabe::fromArray and ITransformabe::toArray.
 * Opposite to Skip.
 * @ORM\Annotation */
class Accept implements Interfaces\AceptTo, Interfaces\AceptFrom {
}