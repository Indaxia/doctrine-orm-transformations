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
    
    /** @see ITransformable::toArray()
      * ============================== */
    public function toArray(
        Policy\Interfaces\Policy $policy = null,
        Reader $ar = null,
        PolicyResolver $pr = null
    ) {
        if(!$ar) { $ar = static::createCachedReader(); }
        if(!$pr) { $pr = new PolicyResolver(); }
        $refClass = new \ReflectionClass($this);
        if ($refClass->getName() !== static::getEntityFullName($refClass)) {
            // if this was a proxy, use the base class for reflection
            $refClass = new \ReflectionClass(static::getEntityFullName($refClass));
        }
        $result = ['__meta' => ['class' => static::getEntityFullName($refClass)]];
        $ps = $refClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                       | \ReflectionProperty::IS_PROTECTED
                                       | \ReflectionProperty::IS_PRIVATE);
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            if($pn[0] === '_' && $pn[1] === '_') { continue; }
            $pr->increaseDepth();
            $propertyPolicy = $pr->resolvePropertyPolicyTo($policy, $pn, $p, $ar);
            if($propertyPolicy instanceof Policy\Interfaces\SkipTo) {
                $pr->decreaseDepth();
                continue;
            }
            $result[$pn] = $this->toArrayProperty($p, $pn, $propertyPolicy, $ar, $pr, $refClass);
            $pr->decreaseDepth();
        }
        return $result;
    }
    
    
    
    /** ========== PROPERTY (TO) ========== */
    protected function toArrayProperty($p, $pn, $policy, Reader $ar, PolicyResolver $pr, \ReflectionClass $headRefClass) {
        $getter = $policy->getter ?: 'get'.ucfirst($pn);
        $result = null;
        
        // ========== SCALAR TYPES ==========
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
        
        // ========== RELATIONS ==========
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
            
            // ========== COLLECTION RELATION ==========
            if($isCollection) {
                $collection = $v;
                if($collection->count()) {
                    if($policy instanceof Policy\Interfaces\FetchPaginateTo) { // pagination policy
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
                
            // ========== SUB-ENTITY RELATION ==========
            } else { // single entity
                if($v) { $result = $v->toArray($policy, $ar, $pr); }
            }
            
            if(($policy instanceof Policy\Interfaces\CustomTo) && $policy->transform) {
                $result = call_user_func_array($policy->transform, [$v, $result]);
            }
            
        // ========== NON-DOCTRINE TYPE ==========
        } else {
            $result = $this->$getter();
            if(($policy instanceof Policy\Interfaces\CustomTo) && $policy->format) {
                return call_user_func_array($policy->format, [$result, null]);
            }
        }
        return $result;
    }
    
    
    
    
    /** @see ITransformable::fromArray()
      * ================================ */
    public function fromArray(
        array $src,
        EntityManagerInterface $entityManager,
        Policy\Interfaces\Policy $policy = null,
        Reader $ar = null,
        PolicyResolver $pr = null
    ) {
        if(!$ar) { $ar = static::createCachedReader(); }
        if(!$pr) { $pr = new PolicyResolver(); }
        $refClass = new \ReflectionClass($this);
        $ps = $refClass->getProperties(  \ReflectionProperty::IS_PUBLIC
                                       | \ReflectionProperty::IS_PROTECTED
                                       | \ReflectionProperty::IS_PRIVATE);
        foreach($ps as $p) {
            if($p->isStatic()) { continue; }
            $pn = $p->getName();
            if(!array_key_exists($pn, $src) || ($pn[0] === '_' && $pn[1] === '_')) { continue; }
            $pr->increaseDepth();
            $propertyPolicy = $pr->resolvePropertyPolicyFrom($policy, $pn, $p, $ar);
            if($propertyPolicy instanceof Policy\Interfaces\SkipFrom) {
                $pr->decreaseDepth();
                continue;
            }
            $this->fromArrayProperty($src[$pn], $p, $pn, $propertyPolicy, $ar, $pr, $entityManager, $refClass);
            $pr->decreaseDepth();
        }
        return $this;
    }
    
    
    
    /** ========== PROPERTY (FROM) ========== */
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
            
        // ========== SCALAR TYPES ==========
        if($column = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Column')) {
            $oldV = $this->$getter();

            if($policy instanceof Policy\Interfaces\DenyUnsetFrom) {
                if($v === null) { return; } // deny set to null
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
                case 'json_array':  if(is_array($v)) { $this->$setter($v); return; } break;
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
                case 'smallint':    if(is_integer($v)) { $this->$setter($v); return; } break;
                case 'bigint':      if(is_numeric($v)) { $this->$setter($v); return; } break;
                case 'boolean':     if(is_bool($v)) { $this->$setter($v); return; } break;
                case 'decimal':     if(is_numeric($v)) { $this->$setter($v); return; } break;
                case 'float':       if(is_integer($v) || is_double($v)) { $this->$setter((double)$v); return; } break;
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
                    } else if($column->nullable || $pr->hasOption(PolicyResolver::ALLOW_NON_NULLABLE)) {
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
        
        // ========== RELATIONS ==========
        } else if($association = static::getPropertyAssociation($p, $ar)) {
            $this->fromArrayRelation($v, $p, $pn, $getter, $setter, $association, $policy, $ar, $pr, $em, $refClass);
            
        // ========== NON-DOCTRINE TYPE ==========
        } else {
            $this->$setter($v);
        }
    }
    
    
    
    /** ========== RELATIONS ========== */
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
            
            // sub-Entity
            if($association instanceof OneToOne || $association instanceof ManyToOne)
            {
                $this->fromArrayRelationEntity($v, $p, $pn, $getter, $setter, $association, $policy,
                                   $ar, $pr, $em, $refClass, $idField, $class, $subPolicy);
            
            // Collection
            } else if(($association instanceof OneToMany || $association instanceof ManyToMany)
                      && isset($v['__meta'])
                      && is_array($v['__meta'])
                      && isset($v['collection'])
                      && is_array($v['collection'])) { // OneToMany, ManyToMany
                $this->fromArrayRelationCollection($v, $p, $pn, $getter, $setter, $association, $policy,
                                                   $ar, $pr, $em, $refClass, $idField, $class, $subPolicy);
            }
        } else {
            throw new Exceptions\FromArrayException('Field "'.$pn.'" must be an Entity representation or contain "collection" field');
        }
    }
    
    
    
    /** ========== SUB-ENTITY RELATION ========== */
    protected function fromArrayRelationEntity($v, $p, $pn, $getter, $setter,
                                                $association, $policy,
                                                Reader $ar,
                                                PolicyResolver $pr,
                                                EntityManagerInterface $em,
                                                \ReflectionClass $refClass,
                                                $idField, $class, $subPolicy) {
        $jc = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\JoinColumn'); // find target id field name
        if($jc) { $idField = $jc->referencedColumnName; }
        $idGetter = 'get'.ucfirst($idField);  
                        
        $subEntity = null;
        
         // non-existent
        if(empty($v[$idField])) {
            if($policy instanceof Policy\Interfaces\DenyNewFrom) { return; }
            $subEntity = new $class();
            $subEntity->fromArray($v, $em, $subPolicy, $ar, $pr);
            $this->$setter($subEntity);
            
        // existent
        } else {
            $current = $this->$getter();
            
            // existent internal
            if($current && ($current->$idGetter() === $v[$idField])) { 
                $subEntity = $current;
                
            // RETRIEVING existent external
            } else if(!($policy instanceof Policy\Interfaces\DenyUpdateFrom) || $policy->allowExternal) { 
                $subEntity = $em->getReference($class, $v[$idField]);
            }
            
            // UPDATING internal or external
            if($subEntity) {
                if(!($policy instanceof Policy\Interfaces\DenyUpdateFrom) || $policy->allowExistent) {
                    $subEntity->fromArray($v, $em, $subPolicy, $ar, $pr);
                }
                $this->$setter($subEntity);
            }
            
        }
    }
    
    
    
    /** ========== COLLECTION RELATION ========== */
    protected function fromArrayRelationCollection($v, $p, $pn, $getter, $setter,
                                                $association, $policy,
                                                Reader $ar,
                                                PolicyResolver $pr,
                                                EntityManagerInterface $em,
                                                \ReflectionClass $refClass,
                                                $idField, $class, $subPolicy) {
        // find target id field name
        $jt = $ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\JoinTable');
        if($jt) {
            if(count($jt->inverseJoinColumns) > 1) { throw new Exceptions\FromArrayException('Composite key relations not supported yet. Field "'.$pn.'".'); }
            $ijc = reset($jt->inverseJoinColumns); // TODO: composite keys support
            if($ijc) {
                $idField = $ijc->referencedColumnName;
            }
        }
        
        $idGetter = 'get'.ucfirst($idField);
        $collection = $this->$getter();
        $newCollection = new \Doctrine\Common\Collections\ArrayCollection();
        $existent = [];
        
        if(! $collection instanceof \Doctrine\Common\Collections\Collection) {
            throw new Exceptions\FromArrayException('Method "'.$getter.'" of the field "'.$pn.'" doesn\'t return Collection');
        }
        
        // collect internal existent and index by id
        foreach($collection as $e) { 
            $existent[$e->$idGetter()] = $e;
        }
        
        // handle input entities
        foreach($v['collection'] as $e) {
            // new entity
            if(empty($e[$idField])) { 
                if($policy instanceof Policy\Interfaces\DenyNewFrom) { continue; }
                $subEntity = new $class();
                $subEntity->fromArray($e, $em, $subPolicy, $ar, $pr);
                $newCollection->add($subEntity);
                
            } else { // existent entity
                $id = $e[$idField];
                
                // internal existent
                if(isset($existent[$id])) {
                    // UPDATING internal
                    if(!($policy instanceof Policy\Interfaces\DenyUpdateFrom) || $policy->allowExistent) {
                        $existent[$id]->fromArray($e, $em, $subPolicy, $ar, $pr);
                    }
                    $newCollection->add($existent[$id]);
                    unset($existent[$id]);
                    
                // RETRIEVING external
                } else if(!($policy instanceof Policy\Interfaces\DenyUpdateFrom) || $policy->allowExternal) { 
                    $external = $em->getReference($class, $id);
                    if($external) {
                        // UPDATING internal
                        if(!($policy instanceof Policy\Interfaces\DenyUpdateFrom) || $policy->allowExistent) {
                            $external->fromArray($e, $em, $subPolicy, $ar, $pr);
                        }
                        $newCollection->add($external);
                    }
                }
            }
        }
        
        // keep the rest for DenyUnset
        if($policy instanceof Policy\Interfaces\DenyUnsetFrom) {
            foreach($existent as $e) {
                $newCollection->add($e);
            }
        }
        $this->$setter($newCollection);
    }
    
    
    
    
    /** @see ITransformable::toArrays()
      * =============================== */
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
    
    
    
    
    /* ========== MISC. ========== */
    
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
    
    /** Doctrine replaces Entity class with Proxy class, so remove proxy namespace from results. */
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
    
    public static function createCachedReader() {
        return new CachedReader(new AnnotationReader(), new ArrayCache());
    }
}
