<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't create a new sub-Entity when it needed, skip instead in ITransformabe::fromArray.
 * It's applicable to Collection too.
 * It's applicable to scalar fields: it denies to set the new value if the value is empty.
 * @ORM\Annotation */
class DenyNew implements Interfaces\DenyNewFrom {
}