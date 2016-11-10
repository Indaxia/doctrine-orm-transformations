<?php
namespace Indaxia\OTR\Annotations\Policy\To;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Formats datetime according to \DateTime::format schema
 * @see http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
 * Note: ITransformable always works in UTC timezone.
 * @Annotation */
class FormatDateTime
    extends \Indaxia\OTR\Annotations\Policy\To\Auto
    implements Interfaces\FormatDateTimeTo {
    public $format = 'r';

    public $priority = 0.010;
}