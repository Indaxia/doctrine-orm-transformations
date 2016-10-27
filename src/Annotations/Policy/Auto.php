<?php
namespace ScorpioT1000\OTR\Annotations\Policy;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Automatically decides what to store, it typically uses getter/setter of the field.
 * Global policy: the same behaviour when field isn't specified.
 * Local policy: overrides and ignores all the global policy parameters. 
 * @Annotation */
class Auto
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\AutoTo, Interfaces\AutoFrom {

    public $priority = 0.0001;
}