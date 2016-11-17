<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't update the existent sub-Entity when it needed in ITransformabe::fromArray.
 * It's applicable to scalar fields: it denies to change new value if the value is already set.
 * It's applicable to numbers even when numbers are 0, 0.0, "0.0" etc.
 * It's not inherited from parent's policy (!). Specify inside() to change behaviour.
 * It's applicable to Collection too:
 *      Set $allowExistent to true to allow UPDATING of existent (external and internal) entities in Collection.
 *      Set $allowExternal to true to allow INSERTION of external entities into Collection.
 *      Use DenyNew to deny INSERTION of non-existent entities.
 * @Annotation */
class DenyUpdate
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUpdateFrom {

    public $priority = 0.6;
    public $propagating = false;
    public $allowExistent = false;
    public $allowExternal = false;
}