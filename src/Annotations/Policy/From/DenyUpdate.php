<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't update the existent scalar or sub-Entity when it needed in ITransformabe::fromArray.
 * It's applicable to scalar fields: it denies to change new value if the value is already set.
 * It's applicable to numbers even when numbers are 0, 0.0, "0.0" etc.
 * It's applicable to Collection and Entity relations.
 * It's not inherited from parent's policy (!). Specify inside() to change behaviour.
 * Set $allowExternal to true to allow RETRIEVING of external entities from the database.
 * Set $allowExistent to true to allow UPDATING of external or existent entity.
 * @Annotation */
class DenyUpdate
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUpdateFrom {

    public $priority = 0.6;
    public $propagating = false;
    
    /** Allows updating of any entity from the given source array, regardless of where it retrieved from. */
    public $allowExistent = false;
    
    /** If a new id is specified, it calls $entityManager->getReference()
      * to retrieve the required entity from the database. */
    public $allowExternal = false;
}