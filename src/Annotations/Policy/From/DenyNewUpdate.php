<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUpdate
 * @see DenyNew
 * @Annotation */
class DenyNewUpdate
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\DenyUpdateFrom, Interfaces\DenyNewFrom {

    public $priority = 0.100;
}