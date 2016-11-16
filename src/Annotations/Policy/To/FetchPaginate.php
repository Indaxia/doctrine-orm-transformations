<?php
namespace Indaxia\OTR\Annotations\Policy\To;

use \Indaxia\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Sets the fetch offset and limit for ITransformabe::toArray result collection.
 * It works with OneToMany and ManyToMany associations ONLY!
 * It works effectively when "fetch" option is set to "EXTRA_LAZY" - sends offset and limit instruction directly to the database.
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/extra-lazy-associations.html
 * It's not inherited from parent's global policy.
 * It's not inherited from parent's policy (!). Specify inside() to change behaviour.
 * @Annotation */
class FetchPaginate
    extends \Indaxia\OTR\Annotations\Annotation
    implements Interfaces\FetchPaginateTo {
    public $offset = 0;
    public $limit = \PHP_INT_MAX;
    /** Count from the end. It doesn't reverse the result's order (!) */
    public $fromTail = false;

    public $priority = 0.2;
    public $propagating = false;
}