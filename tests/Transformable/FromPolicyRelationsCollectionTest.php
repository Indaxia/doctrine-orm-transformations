<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;
use Indaxia\OTR\Annotations\Policy;
use \Doctrine\Common\Collections\ArrayCollection;

class FromPolicyRelationsCollectionTest extends TestCase
{
    public function testDenyNew_NonExistentToEmpty()
    {
        global $entityManager;
        $e = new Entity\Relations();
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyC' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'value' => 'new one'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(0, $e->getManyC()->count());
    }
    
    public function testDenyNew_ExistentExternalToEmpty()
    {
        global $entityManager;
        $e = new Entity\Relations();
        $entityManager->persist((new Entity\Simple())->setId(1)->setValue('existent'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyC' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'id' => 1,
                        'value' => 'updated'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(1, $e->getManyC()->count());
        $this->assertEquals('updated', $e->getManyC()->get(0)->getValue());
    }
    
    
    
        
    public function testDenyUpdate_NonExistentToEmpty()
    {
        global $entityManager;
        $e = new Entity\Relations();
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyD' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'value' => 'new one'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(1, $e->getManyD()->count());
        $this->assertEquals('new one', $e->getManyD()->get(0)->getValue());
    }
    
    public function testDenyUpdate_NonExistentToExistent()
    {
        global $entityManager;
        $e = new Entity\Relations();
        $e->getManyD()->add((new Entity\Simple())->setId(1)->setValue('existent'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyD' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'id' => 1,
                        'value' => 'updated'
                    ],
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'value' => 'new one'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(2, $e->getManyD()->count());
        $this->assertEquals('existent', $e->getManyD()->get(0)->getValue());
        $this->assertEquals('new one', $e->getManyD()->get(1)->getValue());
    }
    
    public function testDenyUpdate_NonExistentToExistent_Replacement()
    {
        global $entityManager;
        $e = new Entity\Relations();
        $e->getManyD()->add((new Entity\Simple())->setId(1)->setValue('existent'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyD' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'value' => 'new one'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(1, $e->getManyD()->count());
        $this->assertEquals('new one', $e->getManyD()->get(0)->getValue());
    }
    
    public function testDenyUpdate_ExistentExternalToEmpty()
    {
        global $entityManager;
        $e = new Entity\Relations();
        $entityManager->persist((new Entity\Simple())->setId(1)->setValue('existent'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyD' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'id' => 1,
                        'value' => 'updated'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(0, $e->getManyD()->count());
    }
    
    public function testDenyUpdateAllowExternal_ExistentExternalToEmpty()
    {
        global $entityManager;
        $e = new Entity\Relations();
        $entityManager->persist((new Entity\Simple())->setId(1)->setValue('existent'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyE' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'id' => 1,
                        'value' => 'updated'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(1, $e->getManyE()->count());
        $this->assertEquals('existent', $e->getManyE()->get(0)->getValue());
    }
    
    public function testDenyUpdateAllowExternal_ExistentExternalToExistent()
    {
        global $entityManager;
        $e = new Entity\Relations();
        $e->getManyE()->add((new Entity\Simple())->setId(1)->setValue('existent'));
        $entityManager->persist((new Entity\Simple())->setId(2)->setValue('external'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyE' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'id' => 1,
                        'value' => 'updated'
                    ],
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'id' => 2,
                        'value' => 'updated'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(2, $e->getManyE()->count());
        $this->assertEquals('existent', $e->getManyE()->get(0)->getValue());
        $this->assertEquals('external', $e->getManyE()->get(1)->getValue());
    }
    
    public function testDenyUnset_NonExistentToExistent_Replacement()
    {
        global $entityManager;
        $e = new Entity\Relations();
        $e->getManyF()->add((new Entity\Simple())->setId(1)->setValue('existent'));
        
        $data = [
            '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Relations'],
            'manyF' => [
               '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple', 'association' => 'ManyToMany'],
               'collection' => [
                    [
                        '__meta' => ['class' => 'Indaxia\OTR\Tests\Entity\Simple'],
                        'value' => 'new one'
                    ]
               ]
            ]
        ];
        
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        
        $this->assertEquals(2, $e->getManyD()->count());
        $this->assertEquals('existent', $e->getManyF()->get(0)->getValue());
        $this->assertEquals('new one', $e->getManyF()->get(1)->getValue());
    }
}