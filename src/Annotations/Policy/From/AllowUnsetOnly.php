<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable aggregate policy.
 * @see DenyUpdate
 * @see DenyNew
 * @ORM\Annotation */
class AllowUnsetOnly implements Interfaces\DenyUpdateFrom, Interfaces\DenyNewFrom {
}