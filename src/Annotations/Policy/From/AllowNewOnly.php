<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUnset
 * @see DenyUpdate
 * @ORM\Annotation */
class AllowNewOnly implements Interfaces\DenyUnsetFrom, Interfaces\DenyUpdateFrom {
}