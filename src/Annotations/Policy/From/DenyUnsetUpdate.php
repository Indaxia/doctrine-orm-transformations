<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUnset
 * @see DenyUpdate
 * @ORM\Annotation */
class DenyUnsetUpdate
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\DenyUnsetFrom, Interfaces\DenyUpdateFrom {

    public $priority = 0.100;
}