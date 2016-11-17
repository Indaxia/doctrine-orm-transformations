<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;
use Indaxia\OTR\Annotations\Policy;
use \Doctrine\Common\Collections\ArrayCollection;

class FromPolicyRelationsTest extends TestCase
{
    public function testValuesWithGlobalPolicy()
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
        $this->assertEquals('existing one', $e->getManyB()->get(1)->getValue());
    }