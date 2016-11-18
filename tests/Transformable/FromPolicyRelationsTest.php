<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;
use Indaxia\OTR\Annotations\Policy;
use \Doctrine\Common\Collections\ArrayCollection;

class FromPolicyRelationsTest extends TestCase
{
    public function testBrief()
    {
        global $entityManager;
        $e = new Entity\Relations();
        
        $entityManager->persist((new Entity\Simple())->setId(2)->setValue('old value 2'));
        $entityManager->persist((new Entity\Simple())->setId(20)->setValue('old value 20'));
        $entityManager->persist((new Entity\Simple())->setId(30)->setValue('old value 30'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneA' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'value' => 'new one'
            ],
            'oneB' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'id' => 2,
                'value' => 'existing one'
            ],
            'manyA' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
                'collection' => []
            ],
            'manyB' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'value' => 'new one'
                    ],
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'id' => 20,
                        'value' => 'existing one'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertNotEquals(null, $e->getOneA());
        $this->assertEquals('new one', $e->getOneA()->getValue());
        
        $this->assertNotEquals(null, $e->getOneB());
        $this->assertEquals('existing one', $e->getOneB()->getValue());
        
        $this->assertNotEquals(null, $e->getManyA());
        $this->assertEquals(0, $e->getManyA()->count());
        
        $this->assertNotEquals(null, $e->getManyB());
        $this->assertEquals(2, $e->getManyB()->count());
        $this->assertNotEquals(null, $e->getManyB()->get(0));
        $this->assertEquals('new one', $e->getManyB()->get(0)->getValue());
        $this->assertNotEquals(null, $e->getManyB()->get(1));
        $this->assertEquals(20, $e->getManyB()->get(1)->getId());
        $this->assertEquals('existing one', $e->getManyB()->get(1)->getValue());
    }
    
    public function testNonExistentEntityDenyNew()
    {
        global $entityManager;
        $e = new Entity\Relations();
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneC' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'value' => 'new one'
            ],
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(null, $e->getOneC());        
    }
    
    public function testExistentInternalEntityDenyNew()
    {
        global $entityManager;
        $e = (new Entity\Relations())->setOneC((new Entity\Simple())->setId(1)->setValue('old one'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneC' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'id' => 1,
                'value' => 'new one'
            ],
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertNotEquals(null, $e->getOneC());
        $this->assertEquals('new one', $e->getOneC()->getValue());        
    }
    
    public function testExistentExternalEntityDenyNew()
    {
        global $entityManager;
        $e = (new Entity\Relations())->setOneC((new Entity\Simple())->setId(1)->setValue('first one'));
        $entityManager->persist((new Entity\Simple())->setId(2)->setValue('second one'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneC' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'id' => 2
            ],
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertNotEquals(null, $e->getOneC());
        $this->assertEquals(2, $e->getOneC()->getId()); 
        $this->assertEquals('second one', $e->getOneC()->getValue());        
    }
    
    public function testExistentInternalEntityDenyUpdate()
    {
        global $entityManager;
        $e = (new Entity\Relations())->setOneD((new Entity\Simple())->setId(1)->setValue('first one'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneD' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'id' => 1,
                'value' => 'second one'
            ],
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertNotEquals(null, $e->getOneD());
        $this->assertEquals('first one', $e->getOneD()->getValue());
    }
    
    public function testExistentExternalEntityDenyUpdate()
    {
        global $entityManager;
        $e = (new Entity\Relations())->setOneD((new Entity\Simple())->setId(1)->setValue('internal'));
        $entityManager->persist((new Entity\Simple())->setId(2)->setValue('external'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneD' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'id' => 2,
                'value' => 'external updated'
            ],
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertNotEquals(null, $e->getOneD());
        $this->assertEquals('internal', $e->getOneD()->getValue());        
    }
    
    public function testExistentExternalEntityDenyUpdateAllowExternal()
    {
        global $entityManager;
        $e = (new Entity\Relations())->setOneE((new Entity\Simple())->setId(1)->setValue('internal'));
        $entityManager->persist((new Entity\Simple())->setId(2)->setValue('external'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneE' => [
                '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                'id' => 2,
                'value' => 'external updated'
            ],
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertNotEquals(null, $e->getOneE());
        $this->assertEquals('external', $e->getOneE()->getValue());        
    }  
    
    public function testEntityDenyUnset()
    {
        global $entityManager;
        $e = (new Entity\Relations())->setOneF((new Entity\Simple())->setId(1)->setValue('first one'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'oneF' => null
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertNotEquals(null, $e->getOneF());    
    }
}
