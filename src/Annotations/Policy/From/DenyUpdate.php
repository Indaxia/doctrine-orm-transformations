<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't update the existent sub-Entity when it needed, skip instead in ITransformabe::fromArray.
 * It's applicable to Collection too.
 * It's applicable to scalar fields: it denies to change the  new value if the value is already set.
 * @ORM\Annotation */
class DenyUpdate
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\DenyUpdateFrom {
}