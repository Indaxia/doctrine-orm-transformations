<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't delete the existent sub-Entity when it needed, skip instead in ITransformabe::fromArray.
 * It's applicable to Collection too, but it works really slow when "fetch" option is set to "LAZY".
 *      Use fetch options "EAGER" or "EXTRA_LAZY" instead.
 * It's applicable to scalar fields: it denies to clear the value if it is set.
 * @Annotation */
class DenyUnset
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\DenyUnsetFrom {

    public $priority = 0.100;
}