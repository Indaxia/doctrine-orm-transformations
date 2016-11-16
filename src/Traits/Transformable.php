<?php
namespace Indaxia\OTR\Traits;

use \Doctrine\Common\Annotations\Reader;
use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\Annotations\CachedReader;
use \Doctrine\Common\Cache\ArrayCache;
use \Doctrine\ORM\EntityManagerInterface;
use \Doctrine\ORM\Mapping\ManyToOne;
use \Doctrine\ORM\Mapping\ManyToMany;
use \Doctrine\ORM\Mapping\OneToOne;
use \Doctrine\ORM\Mapping\OneToMany;
use \Indaxia\OTR\Exceptions;
use \Indaxia\OTR\Annotations\Policy;
use \Indaxia\OTR\Annotations\PolicyResolver;

/* Implements Entity Transformations methods
 * @see ITransformable */
trait Transformable {
    /** @see ITransformable::toArray() */
    public function toArray(
        Policy\Interfaces\Policy $policy = null,
        Reader $ar = null,
        PolicyResolver $pr = null
    ) {
        if(!$ar) { $ar = static::createCachedReader(); }
        if(!$pr) { $pr = new PolicyResolver(); }
        $refClass = new \ReflectionClass(get_class($this));
        $result = ['__meta' => ['class' => static::getEntityFullName($refClass)]];
        $ps = $refClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                       | \ReflectionProperty::IS_PROTECTED
                                       | \ReflectionProperty::IS_PRIVATE);
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            if($pn[0] === '_' && $pn[1] === '_') { continue; }
            $propertyPolicy = $pr->resolvePropertyPolicyTo($policy, $pn, $p, $ar);
            if($propertyPolicy instanceof Policy\Interfaces\SkipTo) { continue; }
            $result[$pn] = $this->toArrayProperty($p, $pn, $propertyPolicy, $ar, $pr, $refClass);
        }
        return $result;
    }
    
    protected function toArrayProperty($p, $pn, $policy, Reader $ar, PolicyResolver $pr, \ReflectionClass $headRefClass) {
        $getter = $policy->getter ?: 'get'.ucfirst($pn);
        $result = null;
        
        if($column = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Column')) { // scalar
            $result = $this->$getter();
            if(($policy instanceof Policy\Interfaces\CustomTo) && $policy->format) {
                return call_user_func_array($policy->format, [$result, $column->type]);
            }
            switch($column->type) {
                case 'simple_array':
                    // @see https://github.com/doctrine/doctrine2/issues/4673
                    if($pr->hasOption(PolicyResolver::SIMPLE_ARRAY_FIX)
                       && is_array($result)
                       && (count($result) === 1)
                       && ($result[0] === null)) {
                        return [];
                    } break;
                case 'date':
                case 'time':
                case 'datetime':
                case 'detetimez':
                    if($result !== null) {
                        if($policy instanceof Policy\Interfaces\FormatDateTimeTo) {
                            $result = $result->format($policy->format);
                            if($result === false) { throw new Exceptions\PolicyException('Wrong DateTime format for field "'.$pn.'"'); }
                        } else if(!$policy instanceof Policy\Interfaces\KeepDateTimeTo) {
                            $result = $result->format('Y-m-d\TH:i:s').'.000Z';
                        }
                    }
                    break;
            }
        
        } else if($association = static::getPropertyAssociation($p, $ar)) { // entity or collection
            $isCollection = false;
            
            if($association instanceof OneToMany) {
                $result = ['__meta' => ['class' => static::getEntityFullName($headRefClass, $association->targetEntity),
                                       'association' => 'OneToMany'], 'collection' => []];
                $isCollection = true;
            } else if($association instanceof ManyToMany) {
                $result = ['__meta' => ['class' => static::getEntityFullName($headRefClass, $association->targetEntity),
                                       'association' => 'ManyToMany'], 'collection' => []];
                $isCollection = true;
            }
            
            $v = $this->$getter();
            
            if($isCollection) {
                $collection = $v; // entity collection
                if($collection->count()) {
                    if($policy instanceof Policy\Interfaces\FetchPaginateTo) {
                        if($policy->fromTail) {
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
            } else { // single entity
                if($v) { $result = $v->toArray($policy, $ar, $pr); }
            }
            
            if(($policy instanceof Policy\Interfaces\CustomTo) && $policy->transform) {
                call_user_func_array($policy->transform, [$v, $result]);
            }
        } else { // not a doctrine type
            $result = $this->$getter();
            if(($policy instanceof Policy\Interfaces\CustomTo) && $policy->format) {
                return call_user_func_array($policy->format, [$result, null]);
            }
        }
        return $result;
    }
    
    /** @see ITransformable::fromArray() */
    public function fromArray(
        array $src,
        EntityManagerInterface $entityManager,
        Policy\Interfaces\Policy $policy = null,
        Reader $ar = null,
        PolicyResolver $pr = null
    ) {
        if(!$ar) { $ar = static::createCachedReader(); }
        if(!$pr) { $pr = new PolicyResolver(); }
        $refClass = new \ReflectionClass(get_class($this));
        $ps = $refClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                       | \ReflectionProperty::IS_PROTECTED
                                       | \ReflectionProperty::IS_PRIVATE);
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            if(!array_key_exists($pn, $src) || ($pn[0] === '_' && $pn[1] === '_')) { continue; }
            $propertyPolicy = $pr->resolvePropertyPolicyFrom($policy, $pn, $p, $ar);
            if($propertyPolicy instanceof Policy\Interfaces\SkipFrom) { continue; }
            $this->fromArrayProperty($src[$pn], $p, $pn, $propertyPolicy, $ar, $pr, $entityManager, $refClass);
        }
        return $this;
    }
    
    /** Here we have 3 cases:
     * 1. ID field
     * 2. Scalar property
     * 3. Relation property */
    protected function fromArrayProperty($v, $p, $pn, $policy,
                                         Reader $ar,
                                         PolicyResolver $pr,
                                         EntityManagerInterface $em,
                                         \ReflectionClass $refClass) {
        $setter = $policy->setter ?: 'set'.ucfirst($pn);
        $getter = $policy->getter ?: 'get'.ucfirst($pn);
            
        if($policy instanceof Policy\Interfaces\CustomFrom) {
            if(call_user_func_array($policy->closure, [$v, $pn, $this, $em])) { return; }
        }
            
        if($id = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Id')) {
            // Skip id, it will be processed in the next steps
        } else if($column = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Column')) { // scalar
            $oldV = $this->$getter();

            if($policy instanceof Policy\Interfaces\DenyUnsetFrom) {
                if($v === null && !$column->nullable) { return; } // deny set to null
                if(!$pr->isNumberType($column->type) && !$v) { 
                    // numbers can never be "empty": deny unset for non-numbers only
                    return; 
                }
            }
            
            if($policy instanceof Policy\Interfaces\DenyNewFrom) {
                if($pr->isNumberType($column->type)) {
                    // numbers can never be "empty": deny new for nullable only
                    if($oldV === null) { return; }
                } else if($v && !$oldV) { 
                    return; // deny new for other types
                }
            }
            
            if($policy instanceof Policy\Interfaces\DenyUpdateFrom) {
                if($pr->isNumberType($column->type)) { 
                    // numbers can never be "empty": deny update for non-nullable only
                    if($v !== null && $oldV !== null) { return; }
                } else if($v && $oldV) { 
                    return; // deny update for other types
                }
            }
            
            switch($column->type) {
                case 'string':
                case 'text':
                case 'guid':
                    if(is_string($v)) { $this->$setter($v); return; } break;
                case 'object':
                case 'array':
                    if(! $pr->hasOption(PolicyResolver::OPT_IGNORE_CVE_2015_0231)) {
                        throw new Exceptions\FromArrayException('Column type "'.$column->type.'" is disabled due to CVE-2015-0231.'
                                                                .'Use PolicyResolver::IGNORE_CVE_2015_0231 to ignore.');
                    }
                case 'simple_array':
                    if(is_array($v)) {
                        // @see https://github.com/doctrine/doctrine2/issues/4673
                        if((count($v) === 0) && !$column->nullable && $pr->hasOption(PolicyResolver::SIMPLE_ARRAY_FIX)) {
                            $this->$setter([null]);
                            return;
                        }
                        $this->$setter($v); return;
                    } break;
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
                case 'date':
                case 'time':
                case 'datetime':
                case 'detetimez':
                    $dt = $v;
                    if($dt) {
                        if(is_string($dt)) {
                            $dt = \DateTime::createFromFormat('Y-m-d\TH:i:s+', substr($dt, 0, 19), new \DateTimeZone('UTC'));
                        }
                        if(! $dt instanceof \DateTime) {
                            throw new Exceptions\FromArrayException('Field "'.$pn.'" must be an ISO8601 string'.($column->nullable ? ' or null' : ''));
                        }
                    } else if($column->nullable) {
                        $dt = null;
                    } else {
                        throw new Exceptions\FromArrayException('Field "'.$pn.'" must be an ISO8601 string');
                    }
                    $this->$setter($dt);
                    return;
            }
            if($v === null) {
                if($column->nullable || $pr->hasOption(PolicyResolver::ALLOW_NON_NULLABLE)) {
                    $this->$setter(null);
                    return;
                } else {
                    throw new Exceptions\FromArrayException('Field "'.$pn.'" cannot be null. Use PolicyResolver::ALLOW_NON_NULLABLE to ignore.');
                }
            }
            throw new Exceptions\FromArrayException('Field "'.$pn.'" must be a type of "'.$column->type.'"');
        } else if($association = static::getPropertyAssociation($p, $ar)) { // entity or collection
            $this->fromArrayRelation($v, $p, $pn, $getter, $setter, $association, $policy, $ar, $pr, $em, $refClass);   
        }
    }
    
    
    /** Here we have 5 cases:
     * 1. Sub-entity with empty id (new)
     * 2. Sub-entity with non-empty id (existent)
     * 3. Sub-entity as null value
     * 4. Sub-collection with some entities (some new, some existent)
     * 5. Sub-collection with no entities */
    protected function fromArrayRelation($v, $p, $pn, $getter, $setter,
                                                $association, $policy,
                                                Reader $ar,
                                                PolicyResolver $pr,
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
            return;
        }
        if(is_array($v)) {
            $idField = 'id';
            $class = static::getEntityFullName($refClass, $association->targetEntity);
            if(!is_subclass_of($class, 'Indaxia\OTR\ITransformable')) {
                throw new Exceptions\FromArrayException('Entity "'.$class.'" must implement ITransformable interface');
            }
            $subPolicy = $policy;
            if($policy instanceof Policy\Interfaces\DenyFrom) {
                $subPolicy = (new Policy\From\Auto())->insideOf($policy);
            }
            
            if($association instanceof OneToOne || $association instanceof ManyToOne)
            {
                $jc = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\JoinColumn'); // find target id field name
                if($jc) { $idField = $jc->referencedColumnName; }
                                
                $subEntity = null;
                if(empty($v[$idField])) { // Sub-entity with empty id (new)
                    if($policy instanceof Policy\Interfaces\DenyNewFrom) { return; }
                    $subEntity = new $class();
                } else { // Sub-entity with non-empty id (existent)
                    if($policy instanceof Policy\Interfaces\DenyUpdateFrom) { return; }
                    $subEntity = $em->getReference($class, $v[$idField]);
                }
                if($subEntity) {
                    $subEntity->fromArray($v, $em, $subPolicy, $ar, $pr);
                }
                $this->$setter($subEntity);
                return;
            } else if(isset($v['__meta'])
                      && is_array($v['__meta'])
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
                
                $newEntities = [];
                $existentRaw = [];
                foreach($v['collection'] as $e) { // find new entities
                    if(empty($e[$idField])) { // new
                        if($policy instanceof Policy\Interfaces\DenyNewFrom) { continue; }
                        $subEntity = new $class();
                        $subEntity->fromArray($e, $em, $subPolicy, $ar, $pr);
                        $newEntities[] = $subEntity;
                    } else {
                        $existentRaw[$e[$idField]] = $e;
                    }
                }
                
                
                $collection = $this->$getter();
                if(! $collection instanceof \Doctrine\Common\Collections\Collection) {
                    throw new Exceptions\FromArrayException('Method "'.$getter.'" of the field "'.$pn.'" doesn\'t return Collection');
                }
                $idGetter = 'get'.ucfirst($idField);                
                foreach($collection as $index => $e) {
                    $id = $e->$idGetter();
                    $existent = isset($existentRaw[$id]) ? $existentRaw[$id] : null;
                    if($existent) { // update
                        if(!$policy instanceof Policy\Interfaces\DenyUpdateFrom) {
                            $e->fromArray($existent, $em, $subPolicy, $ar, $pr); // PROBLEM: sub-fields not updated!!!
                        }
                    } else { // doesn't exist in source, unset
                        if(!$policy instanceof Policy\Interfaces\DenyUnsetFrom) {
                            $collection->remove($index);
                        }
                    }
                }
                
                foreach($newEntities as $e) { // insert all new
                    $collection->add($e);
                }
                return;
            }
        }
        throw new Exceptions\FromArrayException('Field "'.$pn.'" must be an Entity representation or contain "collection" field');
    }
    
    /** @return Annotation|null returns null if its inversed side of bidirectional relation */
    protected static function getPropertyAssociation(\ReflectionProperty $p, Reader $ar) {
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
        Reader $ar = null,
        PolicyResolver $pr = null
    ) {
        if(!$ar) { $ar = static::createCachedReader(); }
        if(!$pr) { $ar = new PolicyResolver(); }
        $arrays = [];
        foreach($entities as $e) { $arrays[] = $e->toArray($policy, null, $ar, $pr); }
        return $arrays;
    }
    
    public static function createCachedReader() {
        return new CachedReader(new AnnotationReader(), new ArrayCache());
    }
}