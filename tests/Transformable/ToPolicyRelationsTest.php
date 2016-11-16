<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;
use Indaxia\OTR\Annotations\PolicyResolver;
use Indaxia\OTR\Annotations\PolicyResolverProfiler;
use Indaxia\OTR\Annotations\Policy;
use \Doctrine\Common\Collections\ArrayCollection;

class ToPolicyRelationsTest extends TestCase
{
    public function testValuesWithGlobalPolicy()
    {
        $e = Entity\Relations::generate();
        $pr = newPR();
        $a = $e->toArray(null, null, $pr);
        printPR($pr);
        
        $this->assertArrayHasKey('__meta', $a);
        $this->assertEquals(['class' => 'Indaxia\OTR\Tests\Entity\Relations'], $a['__meta']);
        $this->assertArrayHasKey('id', $a);
        $this->assertEquals(1000, $a['id']);
        
        $this->assertArrayHasKey('oneA', $a);
        $this->assertNotEmpty($a['oneA']);
        $this->assertArrayHasKey('value', $a['oneA']);
        $this->assertEquals('one A sub-entity', $a['oneA']['value']);
        
        $this->assertArrayHasKey('oneB', $a);
        $this->assertNotEmpty($a['oneB']);
        $this->assertArrayHasKey('value', $a['oneB']);
        $this->assertEquals('one B sub-entity', $a['oneB']['value']);
        
        $this->assertArrayNotHasKey('oneC', $a);
        $this->assertArrayNotHasKey('oneD', $a);
        $this->assertArrayNotHasKey('oneE', $a);
        $this->assertArrayNotHasKey('oneF', $a);
        
        $this->assertArrayHasKey('manyA', $a);
        $this->assertNotEmpty($a['manyA']);
        $this->assertArrayHasKey('collection', $a['manyA']);
        $this->assertNotEmpty($a['manyA']['collection']);
        $this->assertEquals(3, count($a['manyA']['collection']));
        foreach($a['manyA']['collection'] as $i => $se) {
            $this->assertArrayHasKey('value', $a['manyA']['collection'][$i]);
            $this->assertEquals('many A sub-entity '.$i, $a['manyA']['collection'][$i]['value']);            
        }
        
        $this->assertArrayHasKey('manyB', $a);
        $this->assertNotEmpty($a['manyB']);
        $this->assertArrayHasKey('collection', $a['manyB']);
        $this->assertNotEmpty($a['manyB']['collection']);
        $this->assertEquals(1, count($a['manyB']['collection']));
        foreach($a['manyB']['collection'] as $i => $se) {
            $this->assertArrayHasKey('value', $a['manyB']['collection'][$i]);
            $this->assertEquals('many B sub-entity '.$i, $a['manyB']['collection'][$i]['value']);            
        }
        
        $this->assertArrayHasKey('manyC', $a);
        $this->assertNotEmpty($a['manyC']);
        $this->assertArrayHasKey('collection', $a['manyC']);
        $this->assertNotEmpty($a['manyC']['collection']);
        $this->assertEquals(2, count($a['manyC']['collection']));
        foreach($a['manyC']['collection'] as $i => $se) {
            $this->assertArrayHasKey('value', $a['manyC']['collection'][$i]);
            $this->assertEquals('many C sub-entity '.($i+1), $a['manyC']['collection'][$i]['value']);            
        }
        
        $this->assertArrayHasKey('deep', $a);
        $this->assertNotEmpty($a['deep']);
        $this->assertArrayHasKey('oneA', $a['deep']);
        $this->assertArrayHasKey('id', $a['deep']);
        $this->assertEquals(10000, $a['deep']['id']);
        $this->assertArrayHasKey('__meta', $a['deep']);
        $this->assertEquals(['class' => 'Indaxia\OTR\Tests\Entity\Relations'], $a['deep']['__meta']);
        $this->assertNotEmpty($a['deep']['oneA']);
        $this->assertArrayHasKey('value', $a['deep']['oneA']);
        $this->assertEquals('one A sub-sub-entity', $a['deep']['oneA']['value']);
        
        $this->assertArrayNotHasKey('manyD', $a);
        $this->assertArrayNotHasKey('manyE', $a);
        $this->assertArrayNotHasKey('manyF', $a);
    }
    
    public function testValuesWithLocalPolicy()
    {
        $e = Entity\Relations::generate();
        $pr = newPR();
        
        $policy = (new Policy\To\Auto())->inside([
            'oneA' => (new Policy\To\Custom())->transform(function($original, $transformed) {
                $transformed['value'] = 'one A sub-entity customized';
                return $transformed;
            }),
            'oneB' => new Policy\To\Skip,
            'oneC' => new Policy\To\Auto,
            'oneD' => new Policy\Auto,
            'manyA' => new Policy\Skip,
            'manyB' => new Policy\To\FetchPaginate(['limit' => 1, 'fromTail' => true]),
            'manyC' => new Policy\Auto,
            'deep' => (new Policy\To\Auto)->inside([
                'oneA' => (new Policy\To\Auto)->inside([
                    'value' => (new Policy\To\Custom)->format(function($value, $columnType) {
                        return $value;
                    })
                ]),
                'oneB' => new Policy\To\Skip,
                'oneC' => new Policy\To\Skip,
                'oneD' => new Policy\To\Skip,
                'oneE' => new Policy\To\Skip,
                'oneF' => new Policy\To\Skip,
                'manyA' => new Policy\To\Skip,
                'manyB' => new Policy\To\Skip,
                'manyC' => new Policy\To\Skip,
                'manyD' => new Policy\To\Skip,
                'manyE' => new Policy\To\Skip,
                'manyF' => new Policy\To\Skip,
                'deep' => new Policy\To\Skip
            ])
        ]);
        
        $a = $e->toArray($policy, null, $pr);
        printPR($pr);
        
        $this->assertEquals(['class' => 'Indaxia\OTR\Tests\Entity\Relations'], $a['__meta']);
        $this->assertArrayHasKey('id', $a);
        $this->assertEquals(1000, $a['id']);
        
        $this->assertArrayHasKey('oneA', $a);
        $this->assertNotEmpty($a['oneA']);
        $this->assertArrayHasKey('value', $a['oneA']);
        $this->assertEquals('one A sub-entity customized', $a['oneA']['value']);
        
        $this->assertArrayNotHasKey('oneB', $a);
        
        $this->assertArrayHasKey('oneC', $a);
        $this->assertNotEmpty($a['oneC']);
        $this->assertArrayHasKey('value', $a['oneC']);
        $this->assertEquals('one C sub-entity', $a['oneC']['value']);
        
        $this->assertArrayHasKey('oneD', $a);
        $this->assertNotEmpty($a['oneD']);
        $this->assertArrayHasKey('value', $a['oneD']);
        $this->assertEquals('one D sub-entity', $a['oneD']['value']);
        
        $this->assertArrayNotHasKey('oneE', $a);
        $this->assertArrayNotHasKey('oneF', $a);
        
        $this->assertArrayNotHasKey('manyA', $a);
        
        $this->assertArrayHasKey('manyB', $a);
        $this->assertNotEmpty($a['manyB']);
        $this->assertArrayHasKey('collection', $a['manyB']);
        $this->assertNotEmpty($a['manyB']['collection']);
        $this->assertEquals(1, count($a['manyB']['collection']));
        foreach($a['manyB']['collection'] as $i => $se) {
            $this->assertArrayHasKey('value', $a['manyB']['collection'][$i]);
            $this->assertEquals('many B sub-entity '.($i+2), $a['manyB']['collection'][$i]['value']);            
        }
        
        $this->assertArrayHasKey('manyC', $a);
        $this->assertNotEmpty($a['manyC']);
        $this->assertArrayHasKey('collection', $a['manyC']);
        $this->assertNotEmpty($a['manyC']['collection']);
        $this->assertEquals(3, count($a['manyC']['collection']));
        foreach($a['manyC']['collection'] as $i => $se) {
            $this->assertArrayHasKey('value', $a['manyC']['collection'][$i]);
            $this->assertEquals('many C sub-entity '.$i, $a['manyC']['collection'][$i]['value']);            
        }
        
        $this->assertArrayNotHasKey('manyD', $a);
        $this->assertArrayNotHasKey('manyE', $a);
        $this->assertArrayNotHasKey('manyF', $a);
    }
}
?>