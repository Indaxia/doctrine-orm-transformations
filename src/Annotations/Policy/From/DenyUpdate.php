<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't update the existent sub-Entity when it needed in ITransformabe::fromArray.
 * It's applicable to Collection too.
 * It's applicable to scalar fields: it denies to change new value if the value is already set.
 * It's applicable to numbers even when numbers are 0, 0.0, "0.0" etc.
 * It's not inherited from parent's global policy.
 * It's not inherited from parent's policy (!). Specify inside() to change behaviour.
 * Warning: it allows to assign new entities instead of existent. Use Skip to deny any changes.
 * @Annotation */
class DenyUpdate
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUpdateFrom {

    public $priority = 0.6;
}