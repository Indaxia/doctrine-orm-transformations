<?php
namespace ScorpioT1000\OTR\Annotations\Policy\To;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Disables Policy\FetchPaginateTo for ITransformabe::toArray result collection.
 * It works with OneToMany and ManyToMany associations ONLY!
 * @ORM\Annotation */
class FetchNoPaginate implements Interfaces\Policy {
}