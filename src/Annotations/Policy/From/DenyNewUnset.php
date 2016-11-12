<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUnset
 * @see DenyNew
 * @Annotation */
class DenyNewUnset
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUnsetFrom, Interfaces\DenyNewFrom {

    public $priority = 0.6;
}