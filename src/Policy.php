<?php
namespace ScorpioT1000\Doctrine\ORM\Transformations;

/** Provides Entity-Array transfomtaion $policy values  */
interface Policy {    
    /** Automatically decide what to store, it typically uses getter/setter of the field.
     * The same behaviour when policy for field isn't specified. */
    const Auto            = 0x0001;
    
    /** Don't process the field (for ITransformabe::fromArray),
     * exclude it from result (for ITransformabe::toArray). */
    const Skip            = 0x0002;
    
    /** Don't fetch the sub-Entity, store it's ID instead (for ITransformabe::toArray) 
     * By default, the field is a sub-Entity or Collection, it requests it from database
     * when "fetch" option equals to "LAZY" or "EXTRA_LAZY"*/
    const DontFetch       = 0x0004;
    
    /** Don't create a new Entity, skip instead (for ITransformabe::fromArray) 
     * By default, if source array is an entity without ID or ID is empty,
     * it creates a new Entity by it's class */
    const DontConstuct    = 0x0008;
    
    /** Don't convert \DateTime to ISO8601 string (for ITransformabe::toArray)
     * @see http://www.iso.org/iso/catalogue_detail?csnumber=40874
     * Note: It works with UTC timezone only. */
    const KeepDateTime    = 0x0010;
}