<?php
namespace ScorpioT1000\Doctrine\ORM\Transformations;

use \Doctrine\ORM\EntityManagerInterface;
use \Doctrine\Common\Annotations\AnnotationReader;

/** Provides JSON-ready Doctrine ORM Entity-Array transfomtaions */
interface ITransformabe { 
    
    /** Converts Entity and it's references to nested array structure.
     *  @param array $policy Associative array of instructions how to operate with the fields.
     *      Where: the key equals to field name. The value can be one of Policy constants or
     *      a sub-array containing the same field-instruction key-value scheme for nested entity or collection and so on, recursive.
     *      @see Policy
     *  @param boolean $nested include nested elements.
     *      Set to false if you want to get ID's (for nested Entity) and empty arrays (for nested Collection) instead of nested arrays.
     *  @param AnnotationReader $ar for internal recursive purposes
     *  @return array ready for JSON serialization.
     *  @see readme.md
     *  It excludes any static values.
    */
    public function toArray(array $policy = [], $nested = true, AnnotationReader $ar = null);
    
    /** Converts fills Entity's fields (including nested Entity and Collection) to the values from the given array.
     *  @param array A special array ready for ITransformable, so it should include '_meta' array
     *  @param EntityManagerInterface $entityManager Doctrine instance to retrieve nested Entities by ID or create new ones.
     *  @param array $policy Associative array of instructions how to operate with the fields.
     *      Where: the key equals to field name. The value can be one of Policy constants or
     *      a sub-array containing the same field-instruction key-value scheme for nested entity or collection and so on, recursive.
     *      @see Policy
     *  @param AnnotationReader $ar for internal recursive purposes
     *  @see readme.md
     *  @throws Exception when input type isn't acceptable
     *  It doesn't process any static values.
     */
    public function fromArray(
        array $src,
        EntityManagerInterface $entityManager,
        array $policy = [],
        AnnotationReader $ar = null
    );
    
    /** Applies toArray to multiple entities.
     * @param array $entities array of entities
     * @param array $policy
     * @param boolean $nested
     * @return array
     * @see ITransformable::toArray */
    public static function toArrays(array $entities, array $policy = [], $nested = true);
}