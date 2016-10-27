<?php
namespace ScorpioT1000\OTR\Annotations\Policy\To;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Donesn't convert \DateTime to ISO8601 string in ITransformabe::toArray
 * @see http://www.iso.org/iso/catalogue_detail?csnumber=40874
 * Note: ITransformable always works in UTC timezone.
 * @ORM\Annotation */
class KeepDateTime
    extends \ScorpioT1000\OTR\Annotations\Policy\To\Auto
    implements Interfaces\KeepDateTimeTo {

    public $priority = 0.010;
}