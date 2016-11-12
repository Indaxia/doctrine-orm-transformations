<?php
namespace Indaxia\OTR\Annotations\Policy\From;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUpdate
 * @see DenyNew
 * @Annotation */
class DenyNewUpdate
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\DenyUpdateFrom, Interfaces\DenyNewFrom {

    public $priority = 0.6;
}