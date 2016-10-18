<?php
namespace ScorpioT1000\OTR\Annotations\Policy\To;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Sets the fetch offset and limit for ITransformabe::toArray result collection.
 * It works with OneToMany and ManyToMany associations ONLY!
 * It works effectively when "fetch" option is set to "EXTRA_LAZY" - sends offset and limit instruction directly to the database.
 * Be careful with ITransformabe::fromArray - it will remove non-existent collection elements on Policy\Auto.
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/extra-lazy-associations.html
 * @ORM\Annotation */
class FetchPaginate implements Interfaces\Policy {
    public $offset = 0;
    public $limit = 20;
    public $reverse = false;
}