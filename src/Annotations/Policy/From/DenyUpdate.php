<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't update the existent sub-Entity when it needed, skip instead in ITransformabe::fromArray.
 * It's applicable to Collection too.
 * It's applicable to scalar fields: it denies to change new value if the value is already set.
 * It's applicable to numbers even when numbers are 0, 0.0, "0.0" etc.
 * It's not inherited from parent's global policy.
 * It's not inherited from parent's policy (!). Specify inside() to change behaviour.
 * @Annotation */
class DenyUpdate
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\DenyUpdateFrom {

    public $priority = 0.100;
}