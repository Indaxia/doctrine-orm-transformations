<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUnset
 * @see DenyNew
 * @Annotation */
class DenyNewUnset
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\DenyUnsetFrom, Interfaces\DenyNewFrom {

    public $priority = 0.100;
}