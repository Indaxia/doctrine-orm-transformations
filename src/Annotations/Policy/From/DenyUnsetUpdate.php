<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUnset
 * @see DenyUpdate
 * @Annotation */
class DenyUnsetUpdate
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUnsetFrom, Interfaces\DenyUpdateFrom {

    public $priority = 0.6;
    public $propagating = false;
}