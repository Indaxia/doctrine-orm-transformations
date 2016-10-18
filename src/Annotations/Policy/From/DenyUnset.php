<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't delete the existent sub-Entity when it needed, skip instead in ITransformabe::fromArray.
 * It's applicable to Collection too.
 * It's applicable to scalar fields: it denies to clear the value if it's is set.
 * @ORM\Annotation */
class DenyUnset implements Interfaces\DenyUnsetFrom {
}