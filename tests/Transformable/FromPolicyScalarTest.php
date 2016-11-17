<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;
use Indaxia\OTR\Annotations\Policy;

class FromPolicyScalarTest extends TestCase
{
    public function testDatesWithGlobalPolicy()
    {
        global $entityManager;
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', '2099-12-31 23:59:59');        
        $e = new Entity\Scalar();        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Scalar'],
            'id' => 123456,
            'dt1' => $dt,
            'dt2' => $dt->format('Y-m-d\TH:i:s').'.000Z',
            'dt3' => null,
            'date' => $dt->format('Y-m-d\TH:i:s').'.000Z',
            'time' => $dt->format('Y-m-d\TH:i:s').'.000Z'
        ];
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        $this->assertEquals($dt, $e->getDt1());
        $this->assertEquals($dt, $e->getDt2());
        $this->assertEquals(null, $e->getDt3());
        $this->assertEquals($dt, $e->getDate());
        $this->assertEquals(null, $e->getTime());
    }
    
    public function testArrays()
    {
        global $entityManager;       
        $e = new Entity\Scalar();
        $a = ['string value', 1337, 13.37, true];
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Scalar'],
            'id' => 123456,
            'sa' => $a,
            'sae' => [],
            'ja' => $a
        ];
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        $this->assertEquals($a, $e->getSa());
        $this->assertEquals([], $e->getSae());
        $this->assertEquals($a, $e->getJa());
    }
    
    public function testPolicyDenyStrings()
    {
        global $entityManager;       
        $e = (new Entity\Scalar())
             ->setStr1('') // DenyNew
             ->setStr2('set') // DenyUnset
             ->setStr3('set') // DenyUpdate
             ->setStr4('') // DenyNewUnset
             ->setStr5('set') // DenyNewUpdate
             ->setStr6('set') // DenyUnsetUpdate
             
             ->setFlt1(0.0) // DenyNew
             ->setFlt2(null) // DenyNew
             ->setFlt3(0.0) // DenyUpdate
             ->setFlt4(1.0) // DenyUpdate
             ->setFlt5(1.0) // DenyUnset
             ->setFlt6(null); // DenyUnset
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Scalar'],
            'str1' => 'new', // try new
            'str2' => '', // try unset
            'str3' => 'update', // try update
            'str4' => 'new', // try new
            'str5' => '', // try unset
            'str6' => 'update', // try update
            
            'flt1' => 1.0, // try update
            'flt2' => 1.0, // try new
            'flt3' => 1.0, // try update
            'flt4' => null, // try unset
            'flt5' => null, // try unset
            'flt6' => 0.0 // try new
        ];
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        $this->assertEquals('', $e->getStr1());
        $this->assertEquals('set', $e->getStr2());
        $this->assertEquals('set', $e->getStr3());
        $this->assertEquals('', $e->getStr4());
        $this->assertEquals('', $e->getStr5());
        $this->assertEquals('set', $e->getStr6());
        
        $this->assertEquals(1.0, $e->getFlt1());
        $this->assertEquals(null, $e->getFlt2());
        $this->assertEquals(0.0, $e->getFlt3());
        $this->assertEquals(null, $e->getFlt4());
        $this->assertEquals(1.0, $e->getFlt5());
        $this->assertEquals(0.0, $e->getFlt6());
    }
    
    public function testLocalPolicyDenyStrings()
    {
        global $entityManager;       
        $e = (new Entity\Scalar())
             ->setStr1('') // DenyNew
             ->setStr2('set') // DenyUnset
             ->setStr3('set') // DenyUpdate
             ->setStr4('') // DenyNewUnset
             ->setStr5('set') // DenyNewUpdate
             ->setStr6('set') // DenyUnsetUpdate
             
             ->setFlt1(0.0) // DenyNew
             ->setFlt2(null) // DenyNew
             ->setFlt3(0.0) // DenyUpdate
             ->setFlt4(1.0) // DenyUpdate
             ->setFlt5(1.0) // DenyUnset
             ->setFlt6(null); // DenyUnset
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Scalar'],
            'str1' => 'new', // try new
            'str2' => '', // try unset
            'str3' => 'update', // try update
            'str4' => 'new', // try new
            'str5' => '', // try unset
            'str6' => 'update', // try update
            
            'flt1' => 1.0, // try update
            'flt2' => 1.0, // try new
            'flt3' => 1.0, // try update
            'flt4' => null, // try unset
            'flt5' => null, // try unset
            'flt6' => 0.0 // try new
        ];
        $policy = (new Policy\From\Auto())->inside([
            'str1' => new Policy\From\Auto,
            'str2' => new Policy\From\Auto,
            'str3' => new Policy\From\Auto,
            'str4' => new Policy\From\Auto,
            'str5' => new Policy\From\Auto,
            'str6' => new Policy\From\Auto,
            'flt1' => new Policy\From\Auto,
            'flt2' => new Policy\From\Auto,
            'flt3' => new Policy\From\Auto,
            'flt4' => new Policy\From\Auto,
            'flt5' => new Policy\From\Auto,
            'flt6' => new Policy\From\Auto
        ]);
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        $this->assertEquals('new', $e->getStr1());
        $this->assertEquals('', $e->getStr2());
        $this->assertEquals('update', $e->getStr3());
        $this->assertEquals('new', $e->getStr4());
        $this->assertEquals('', $e->getStr5());
        $this->assertEquals('update', $e->getStr6());
        
        $this->assertEquals(1.0, $e->getFlt1());
        $this->assertEquals(1.0, $e->getFlt2());
        $this->assertEquals(1.0, $e->getFlt3());
        $this->assertEquals(null, $e->getFlt4());
        $this->assertEquals(null, $e->getFlt5());
        $this->assertEquals(0.0, $e->getFlt6());
    }
}