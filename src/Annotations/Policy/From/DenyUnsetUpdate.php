<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUnset
 * @see DenyUpdate
 * Set $allowExternal to true to allow RETRIEVING of external entities from the database.
 * Set $allowExistent to true to allow UPDATING of external or existent entity.
 * @Annotation */
class DenyUnsetUpdate
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUnsetFrom, Interfaces\DenyUpdateFrom {

    public $priority = 0.6;
    public $propagating = false;
    
    /** Allows updating of any entity from the given source array, regardless of where it retrieved from. */
    public $allowExistent = false;
    
    /** If a new id is specified, it calls $entityManager->getReference()
      * to retrieve the required entity from the database. */
    public $allowExternal = false;
}