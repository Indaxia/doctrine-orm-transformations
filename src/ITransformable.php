<?php
namespace Indaxia\OTR;

use \Doctrine\ORM\EntityManagerInterface;
use \Doctrine\Common\Annotations\Reader;
use \Indaxia\OTR\Exceptions\Exception;
use \Indaxia\OTR\Annotations\PolicyResolver;
use \Indaxia\OTR\Annotations\Policy;

/** Provides JSON-ready Doctrine ORM Entity-Array transfomtaions */
interface ITransformable { 
    
    /** Converts Entity and it's references to nested array structure.
     *  @param Policy\Interfaces\Policy|null transfromation policy, null equals to Policy\Auto
     *  @param Reader $ar for internal recursive purposes
     *  @param PolicyResolver $pr for internal recursive purposes
     *  @return array ready for JSON serialization.
     *  @see readme.md
     *  @throws Exception when input type or policy aren't acceptable
     *  It excludes any static values.
    */
    public function toArray(
        Policy\Interfaces\Policy $policy = null,
        Reader $ar = null,
        PolicyResolver $pr = null
    );
    
    /** Converts fills Entity's fields (including nested Entity and Collection) to the values from the given array.
     *  @param array A special array ready for ITransformable, so it should include '__meta' array
     *  @param EntityManagerInterface $entityManager Doctrine instance to retrieve nested Entities by ID or create new ones.
     *  @param Policy\Interfaces\Policy|null transfromation policy, null equals to Policy\Auto
     *  @param Reader $ar for internal recursive purposes
     *  @param PolicyResolver $pr for internal recursive purposes
     *  @see readme.md
     *  @throws Exception when input type or policy aren't acceptable
     *  @return ITransformable $this
     *  It doesn't process any static values.
     */
    public function fromArray(
        array $src,
        EntityManagerInterface $entityManager,
        Policy\Interfaces\Policy $policy = null,
        Reader $ar = null,
        PolicyResolver $pr = null
    );
    
    /** Applies toArray to multiple entities.
     * @param array $entities array of entities
     * @param array $policy
     * @param Reader $ar for internal recursive purposes
     * @param PolicyResolver $pr for internal recursive purposes
     * @return array
     * @see ITransformable::toArray */
    public static function toArrays(
        array $entities,
        Policy\Interfaces\Policy $policy = null,
        Reader $ar = null,
        PolicyResolver $pr = null
    );
}