<?php
namespace ScorpioT1000\OTR\Traits;

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\ORM\EntityManagerInterface;
use \Doctrine\ORM\Mapping\ManyToOne;
use \Doctrine\ORM\Mapping\ManyToMany;
use \Doctrine\ORM\Mapping\OneToOne;
use \Doctrine\ORM\Mapping\OneToMany;
use \ScorpioT1000\OTR\Exceptions;
use \ScorpioT1000\OTR\Annotations\Policy;
use \ScorpioT1000\OTR\Annotations\PolicyResolver;

/* Implements Entity Transformations methods
 * @see ITransformable */
trait Transformable {
    /** @see ITransformable::toArray() */
    public function toArray(
        Policy\Interfaces\Policy $policy = null,
        AnnotationReader $ar = null,
        PolicyResolver $pr = null
    ) {
        $result = ['_meta' => ['class' => static::getEntityFullName($refClass)]];
        if(!$ar) { $ar = new AnnotationReader(); }
        if(!$pr) { $pr = new PolicyResolver(); }
        $refClass = new \ReflectionClass(get_class($this));
        $ps = $refClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                       | \ReflectionProperty::IS_PROTECTED
                                       | \ReflectionProperty::IS_PRIVATE);
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            if($pn[0] === '_' && $pn[1] === '_') { continue; }
            $propertyPolicy = $pr->resolvePropertyPolicy($policy, $pn, $p, $ar);
            if($propertyPolicy instanceof Policy\Interfaces\SkipTo) { continue; }
            $result[$pn] = $this->toArrayProperty($p, $pn, $propertyPolicy, $ar, $refClass);
        }
        return $result;
    }
    
    protected function toArrayProperty($p, $pn, $policy, AnnotationReader $ar, \ReflectionClass $headRefClass) {
        $getter = 'get'.ucfirst($pn);
        if($column = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Column')) { // scalar
            $v = $this->$getter();
            switch($column->type) {
                case 'date':
                case 'time':
                case 'datetime':
                case 'detetimez':
                    if($v !== null) {
                        if($policy instanceof Policy\Interfaces\KeepDateTimeTo) {
                            return $v->format('Y-m-d\TH:i:s').'.000Z';
                        } else if($policy instanceof Policy\Interfaces\FormatDateTimeTo) {
                            $r = $v->format($policy->format);
                            if($r === false) { throw new Exceptions\PolicyException('Wrong DateTime format for field "'.$pn.'"'); }
                        }
                    break;
            }
            return $v;
        } else if($association = static::getPropertyAssociation($p, $ar)) { // entity or collection
            if(!$nested) {
                return $this->$pn;
            }
            $result = null;
            if($association instanceof OneToMany) {
                $result = ['_meta' => ['class' => static::getEntityFullName($headRefClass, $association->targetEntity),
                                       'association' => 'OneToMany'], 'collection' => []];
            } else if($association instanceof ManyToMany) {
                $result = ['_meta' => ['class' => static::getEntityFullName($headRefClass, $association->targetEntity),
                                       'association' => 'ManyToMany'], 'collection' => []];
            } else { // single entity
                $result = $this->$getter();
                if($result) { $result = $result->toArray($policy, $ar, $pr); }
                return $result;
            }
            $collection = $this->$getter(); // entity collection
            if($collection->count()) {
                if($policy instanceof Policy\Interfaces\FetchPaginateTo) {
                    if($policy->reverse) {
                        $offset = $collection->count() - $policy->limit - $policy->offset;
                        if($offset < 0) { $offset = 0; }
                        $limit = ($collection->count() > $policy->limit) ? $collection->count() : $policy->limit;
                        $collection = $collection->slice($offset, $limit);
                    } else {
                        $collection = $collection->slice($policy->offset, $policy->limit);
                    }
                }
                foreach($collection as $el) {
                    $result['collection'][] = $el->toArray($policy, $ar, $pr);
                }
            }
            return $result;
        }
        return $this->$getter();
    }
    
    /** @see ITransformable::fromArray() */
    public function fromArray(
        array $src,
        EntityManagerInterface $entityManager,
        Policy\Interfaces\Policy $policy = null,
        AnnotationReader $ar = null,
        PolicyResolver $pr = null
    ) {
        if(!$ar) { $ar = new AnnotationReader(); }
        if(!$pr) { $pr = new PolicyResolver(); }
        $refClass = new \ReflectionClass(get_class($this));
        $ps = $refClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                       | \ReflectionProperty::IS_PROTECTED
                                       | \ReflectionProperty::IS_PRIVATE);
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            if(!isset($src[$pn]) || ($pn[0] === '_' && $pn[1] === '_')) { continue; }
            $propertyPolicy = $pr->resolveTo($pr->getChildPolicy($policy, $pn), $p, $ar);
            if($propertyPolicy instanceof Policy\Interfaces\SkipFrom) { continue; }
            $this->fromArrayProperty($src[$pn], $p, $pn, $propertyPolicy, $ar, $entityManager, $refClass);
        }
    }
    
    /** Here we have 3 cases:
     * 1. ID field
     * 2. Scalar property
     * 3. Relation property */
    protected function fromArrayProperty($v, $p, $pn, $policy,
                                         AnnotationReader $ar,
                                         EntityManagerInterface $em,
                                         \ReflectionClass $refClass) {
        $setter = 'set'.ucfirst($pn);
        $getter = 'get'.ucfirst($pn);
        if($id = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Id')) {
            // Skip id, it will be processed in the next steps
        } else if($column = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Column')) { // scalar
            if($v === null && $column->nullable) {
                if($policy instanceof Policy\Interfaces\DenyUnsetFrom && $this->$pn)
                { // cannot unset the existing value with this policy
                    return;
                }
                $this->$setter(null);
                return;
            }
            if($policy instanceof Policy\Interfaces\DenyNewFrom) {
                if((in_array($column->type, ['integer', 'smallint', 'bigint', 'float', 'decimal'])
                    && ($v !== null) && ($this->$pn === null)) // numbers can never be "empty": applicable for nullable only
                   || ($v && !$this->$pn)) {
                    return;
                }
            }
            if($policy instanceof Policy\Interfaces\DenyUpdateFrom
                && (($v && $this->$pn)
                    || in_array($column->type, ['integer', 'smallint', 'bigint', 'float', 'decimal'])))
            {  // numbers can never be "empty" so they are always denied with this policy
                return;
            }
            switch($column->type) {
                case 'string':
                case 'text':
                case 'simple_array':
                case 'guid':
                    if(is_string($v)) { $this->$setter($v); return; } break;
                case 'json_array':
                    if(is_array($v)) { $this->$setter($v); return; } break;
                case 'blob':
                    if(is_resource($v) && get_resource_type($v) == 'stream') {
                        $this->$setter($v);
                        return;
                    } else if(is_string($v)) {
                        $stream = fopen('php://memory','r+');
                        fwrite($stream, $v);
                        rewind($stream);
                        $this->$setter($stream);
                        return;
                    }
                    break;
                case 'integer':
                case 'smallint':
                    if(is_integer($v)) { $this->$setter($v); return; } break;
                case 'bigint':
                    if(is_numeric($v)) { $this->$setter($v); return; } break;
                case 'boolean':
                    if(is_bool($v)) { $this->$setter($v); return; } break;
                case 'decimal':
                    if(is_numeric($v)) { $this->$setter($v); return; } break;
                case 'float':
                    if(is_integer($v) || is_double($v)) { $this->$setter($v); return; } break;
                case 'object':
                case 'array':
                    throw new Exceptions\FromArrayException('Column type "'.$column->type.'" is disabled due to CVE-2015-0231');
                case 'date':
                case 'time':
                case 'datetime':
                case 'detetimez':
                    if($v) {
                        if(is_string($v)) {
                            $dt = new \DateTime();
                            $v = \DateTime::createFromFormat('Y-m-d\TH:i:s+', $v, new \DateTimeZone('UTC'));
                        }
                        if(! $v instanceof \DateTime) {
                            throw new Exceptions\FromArrayException('Field "'.$pn.'" must be an ISO8601 string'.($column->nullable ? ' or null' : ''));
                        }
                    } else if($column->nullable) {
                        $v = null;
                    } else {
                        throw new Exceptions\FromArrayException('Field "'.$pn.'" must be an ISO8601 string');
                    }
                    $this->$setter($v);
                    return;
            }
            throw new Exceptions\FromArrayException('Field "'.$pn.'" must be a type of "'.$column->type.'"');
        } else if($association = static::getPropertyAssociation($p, $ar)) { // entity or collection
            $this->fromArrayRelation($v, $p, $pn, $getter, $setter, $association, $policy, $ar, $em, $refClass);   
        }
    }
    
    /** Here we have 6 cases:
     * 1. Sub-entity with empty id (new)
     * 2. Sub-entity with non-empty id (existent)
     * 3. Sub-entity as id based on Policy::DontFetch
     * 4. Sub-entity as null value
     * 5. Sub-collection with some entities (some new, some existent)
     * 6. Sub-collection with no entities */
    protected function fromArrayRelation($v, $p, $pn, $getter, $setter,
                                                $association, $policy,
                                                AnnotationReader $ar,
                                                EntityManagerInterface $em,
                                                \ReflectionClass $refClass) {
        if($v === null) { // Sub-entity as null value
            if($association instanceof OneToMany || $association instanceof ManyToMany) {
                throw new Exceptions\FromArrayException('Field "'.$pn.'" must be a Collection');
            } else if($policy instanceof Policy\Interfaces\DenyUnsetFrom
                      && ($this->$getter() !== null)) { // cannot unset Entity with this policy
                return;
            }
            $this->$setter(null);
        }
        if(is_array($v)) {
            $idField = 'id';
            $class = static::getEntityFullName($refClass, $association->targetEntity);
            if(!is_subclass_of($class, 'ScorpioT1000\OTR\ITransformable')) {
                throw new Exceptions\FromArrayException('Entity "'.$class.'" must implement ITransformable interface');
            }
            
            if($association instanceof OneToOne || $association instanceof ManyToOne)
            {
                $jc = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\JoinColumn'); // find target id field name
                if($jc) { $idField = $jc->referencedColumnName; }
                                
                $subEntity = null;
                if(empty($v[$idField])) { // Sub-entity with empty id (new)
                    if(is_int($policy) && ($policy & Policy::DontConstuct)) { return; }
                    $subEntity = new $class();
                } else { // Sub-entity with non-empty id (existent)
                    $subEntity = $em->getReference($class, $v[$idField]);
                }
                if($subEntity) {
                    $subEntity->fromArray($v, $em, $policy, $ar);
                }
                $this->$setter($subEntity);
                return;
            } else if(isset($v['_meta'])
                      && is_array($v['_meta'])
                      && isset($v['collection'])
                      && is_array($v['collection'])) { // OneToMany, ManyToMany
                $values = [];
                $jt = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\JoinTable'); // find target id field name
                if($jt) {
                    $ijc = reset($jt->inverseJoinColumns);
                    if($ijc) {
                        $idField = $ijc->referencedColumnName;
                    }
                }
                $dontConstruct = is_int($policy) && ($policy & Policy::DontConstuct);
                foreach($v['collection'] as $e) {
                    if(empty($e[$idField])) { // Sub-entity with empty id (new)
                        if($dontConstruct) { continue; }
                        $subEntity = new $class();
                    } else { // Sub-entity with non-empty id (existent)
                        $subEntity = $em->getReference($class, $e[$idField]);
                    }
                    if($subEntity) {
                        $subEntity->fromArray($v, $em, $policy, $ar);
                    }
                    $values[] = $subEntity;
                }
                $this->$setter(new \Doctrine\Common\Collections\ArrayCollection($values));
                return;
            }
        } else if($policy & Policy::DontFetch) { // don't process entity for this policy
            return;
        }
        throw new Exceptions\FromArrayException('Field "'.$pn.'" must be an Entity representation or contain "collection" field');
    }
    
    /** @return Annotation|null returns null if its inversed side of bidirectional relation */
    protected static function getPropertyAssociation(\ReflectionProperty $p, AnnotationReader $ar) {
        $ans = $ar->getPropertyAnnotations($p);
        foreach($ans as $an) {
            if(($an instanceof ManyToOne && !$an->inversedBy)
               || ($an instanceof ManyToMany && !$an->inversedBy)
               || ($an instanceof OneToOne && !$an->inversedBy)
               || $an instanceof OneToMany) { return $an; }
        }
        return null;
    }
    
    protected static function getEntityFullName(\ReflectionClass $headRefClass, $name = null) {
        if($name && $name[0] !== "\\") {
            $ns = $headRefClass->getNamespaceName();
            if($ns) { 
                $name = $ns."\\".$name; 
            }
        } else {
            $name = $headRefClass->getName();
        }
        if(substr($name, 0, 15) === "Proxies\\__CG__\\") {
            $name = substr($name, 15);
        }
        return $name;        
    }
    
    /** @see ITransformable::toArrays() */
    public static function toArrays(
        array $entities,
        Policy\Interfaces\Policy $policy = null,
        AnnotationReader $ar = null,
        PolicyResolver $pr = null
    ) {
        if(!$ar) { $ar = new AnnotationReader(); }
        if(!$pr) { $ar = new PolicyResolver(); }
        $arrays = [];
        foreach($entities as $e) { $arrays[] = $e->toArray($policy, null, $ar, $pr); }
        return $arrays;
    }
}