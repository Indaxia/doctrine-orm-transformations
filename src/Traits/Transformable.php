<?php
namespace ScorpioT1000\Doctrine\ORM\Transformations;

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\ORM\EntityManagerInterface;
use \Doctrine\ORM\Mapping\ManyToOne;
use \Doctrine\ORM\Mapping\ManyToMany;
use \Doctrine\ORM\Mapping\OneToOne;
use \Doctrine\ORM\Mapping\OneToMany;
use \ScorpioT1000\Doctrine\ORM\Transformations\Exceptions\FromArrayException;
use \ScorpioT1000\Doctrine\ORM\Transformations\Policy;

/* Implements Entity Transformations methods
 * @see ITransformable */
trait Transformable {
    /** @see ITransformable::toArray() */
    public function toArray($policy = [], $nested = true, AnnotationReader $ar = null) {
        $refClass = new \ReflectionClass(get_class($this));
        $result = ['_meta' => ['class' => $refClass->getName()]];
        if(! is_array($policy)) { $policy = []; }
        $ps = $reflectionClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                              | \ReflectionProperty::IS_PROTECTED
                                              | \ReflectionProperty::IS_PRIVATE);
        if(!$ar) { $ar = new AnnotationReader(); }
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            $subPolicy = isset($policy[$pn]) ? $policy[$pn] : Policy::Auto;
            if($subPolicy == Policy::Skip) { continue; }
            $result[$pn] = $this->toArrayProperty($p, $pn, $subPolicy, $nested, $ar);
        }
        return $result;
    }
    
    protected function toArrayProperty($p, $pn, $policy, $nested, AnnotationReader $ar) {
        $getter = 'get'.ucfirst($pn);
        if($column = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Column')) { // scalar
            $v = $this->$getter();
            switch($column->type) {
                case 'date':
                case 'time':
                case 'datetime':
                case 'detetimez':
                    if($v !== null && $policy !== Policy::KeepDateTime) {
                        return $v->format('Y-m-d\TH:i:s').'.000Z';
                    }
                    break;
            }
            return $v;
        } else if($association = $this->getPropertyAssociation($p, $ar)) { // entity or collection
            if(!$nested || $policy === Policy::DontFetch) {
                return $this->$pn;
            }
            $result = null;
            if($association instanceof OneToMany) {
                $result = ['_meta' => ['class' => 'Collection', 'association' => 'OneToMany'], 'values' => []];
            } else if($association instanceof ManyToMany) {
                $result = ['_meta' => ['class' => 'Collection', 'association' => 'ManyToMany'], 'values' => []];
            } else { // single entity
                $result = $this->$getter();
                if($result) { $result = $result->toArray($policy, true, $ar); }
                return $result;
            }
            $collection = $this->$getter(); // entity collection
            foreach($collection as $el) {
                $result['values'][] = $el->toArray($policy, true, $ar);
            }
            return $result;
        }
        return $this->$getter();
    }
    
    /** @see ITransformable::fromArray() */
    public function fromArray(
        array $src,
        EntityManagerInterface $entityManager = null,
        array $policy = [],
        AnnotationReader $ar = null
    ) {
        $refClass = new \ReflectionClass(get_class($this));
        if(! is_array($policy)) { $policy = []; }
        $ps = $reflectionClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                              | \ReflectionProperty::IS_PROTECTED
                                              | \ReflectionProperty::IS_PRIVATE);
        if(!$ar) { $ar = new AnnotationReader(); }
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            if(!isset($src[$pn])) { continue; }
            $subPolicy = isset($policy[$pn]) ? $policy[$pn] : Policy::Auto;
            if($subPolicy == Policy::Skip) { continue; }
            $this->fromArrayProperty($src[$pn], $p, $pn, $subPolicy, $ar);
        }
    }
    
    /** Here we have 3 cases:
     * 1. ID field
     * 2. Scalar property
     * 3. Relation property */
    protected function fromArrayProperty($v, $p, $pn, $policy, AnnotationReader $ar) {
        $setter = 'set'.ucfirst($pn);
        if($id = $this->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Id')) {
            // Skip id, it will be processed in the next steps
        } else if($column = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Column')) { // scalar
            if($v === null && $column->nullable) {
                $this->$setter(null);
                return;
            }
            switch($column->type) {
                case 'string':
                case 'text':
                case 'simple_array':
                case 'json_array':
                case 'guid':
                    if(is_string($v)) { $this->$setter($v); } break;
                case 'blob':
                    if(is_resource($v) && get_resource_type($v) == 'stream') {
                        $this->$setter($v);
                    } else if(is_string($v)) {
                        $stream = fopen('php://memory','r+');
                        fwrite($stream, $v);
                        rewind($stream);
                        $this->$setter($stream);
                    }
                    break;
                case 'integer':
                case 'smallint':
                    if(is_integer($v)) { $this->$setter($v); } break;
                case 'bigint':
                    if(is_numeric($v)) { $this->$setter($v); } break;
                case 'boolean':
                    if(is_bool($v)) { $this->$setter($v); } break;
                case 'decimal':
                    if(is_numeric($v)) { $this->$setter($v); } break;
                case 'float':
                    if(is_integer($v) || is_double($v)) { $this->$setter($v); } break;
                case 'object':
                case 'array':
                    throw new FromArrayException('Column type "'.$column->type.'" is disabled due to CVE-2015-0231');
                case 'date':
                case 'time':
                case 'datetime':
                case 'detetimez':
                    if($v) {
                        if(is_string($v)) {
                            $dt = new \DateTime();
                            $v = DateTime::createFromFormat('Y-m-d\TH:i:s+', $v, new \DateTimeZone('UTC'));
                        }
                        if(! $v instanceof \DateTime) {
                            throw new FromArrayException('Field "'.$pn.'" must be an ISO8601 string'.($column->nullable ? ' or null' : ''));
                        }
                    } else if($column->nullable) {
                        $v = null;
                    } else {
                        throw new FromArrayException('Field "'.$pn.'" must be an ISO8601 string');
                    }
                    $this->$setter($v);
                    break;
            }
            throw new FromArrayException('Field "'.$pn.'" must be a type of "'.$column->type.'"');
        } else if($association = $this->getPropertyAssociation($p, $ar)) { // entity or collection
            $this->fromArrayRelation($v, $p, $pn, $policy, $ar);   
        }
    }
    
    /** Here we have 6 cases:
     * 1. Sub-entity with empty id (new)
     * 2. Sub-entity with non-empty id (existent)
     * 3. Sub-entity as id based on Policy::DontFetch
     * 4. Sub-entity as null value
     * 5. Sub-collection with some entities (some new, some existent)
     * 6. Sub-collection with no entities */
    protected static function fromArrayRelation($v, $p, $pn, $policy, AnnotationReader $ar) {
        
    }
    
    protected static function getPropertyAssociation(\ReflectionProperty $p, AnnotationReader $ar) {
        $ans = $ar->getPropertyAnnotations($p);
        foreach($ans as $an) {
            if($an instanceof ManyToOne
               || $an instanceof ManyToMany
               || $an instanceof OneToOne
               || $an instanceof OneToMany) { return $an; }
        }
        return null;
    }
    
    /** @see ITransformable::toArrays() */
    public static function toArrays(array $entities, array $policy = [], $nested = true) {
        $arrays = [];
        foreach($entities as $e) { $arrays[] = $e->toArray($policy, $nested); }
        return $arrays;
    }
}