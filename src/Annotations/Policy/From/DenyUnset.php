<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't null the existent sub-Entity or scalars when it needed, skip instead in ITransformabe::fromArray.
 * It's applicable to Collection too, but it works really slow when "fetch" option is set to "LAZY".
 *      Use fetch options "EAGER" or "EXTRA_LAZY" instead.
 * It's applicable to scalar fields: it denies to clear the value if it is set.
 * Note: it won't work with non-nullable numbers. Numbers cannot be "empty".
 * It's not inherited from parent's policy (!). Specify inside() to change behaviour.
 * Warning: it allows to assign new entities instead of existent. Use Skip to deny any changes.
 * @Annotation */
class DenyUnset
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUnsetFrom {

    public $priority = 0.6;
    public $propagating = false;
}