<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;
use Indaxia\OTR\Annotations\PolicyResolver;
use Indaxia\OTR\Annotations\PolicyResolverProfiler;
use Indaxia\OTR\Annotations\Policy;

class ToPolicyScalarTest extends TestCase
{
    public function testValuesWithGlobalPolicy()
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', '2099-12-31 23:59:59');
        
        $e = (new Entity\Scalar())
            ->setId(123456)
            ->setDt1($dt)
            ->setDt2($dt)
            ->setDt3($dt)
            ->setDate($dt)
            ->setTime($dt)
            ->setStr('test string \'"')
            ->setStrSkip('test string')
            ->setJa(['a string', 1337, 13.37, true])
            ->setSa(['a string', 1337, 13.37, true])
            ->setSae([])
            ->setDeci(123.456789)
            ->setBI('1234567890987654321')
            ->setBln(true)
            ->setFlt(0.0000001)
            ->setStrNull(null);

        $pr = newPR(PolicyResolver::SIMPLE_ARRAY_FIX);
        $a = $e->toArray(null, null, $pr);
        printPR($pr);
        
        $this->assertArrayHasKey('__meta', $a);    
        $this->assertArrayHasKey('class', $a['__meta']);        
        $this->assertEquals(['class' => 'Indaxia\OTR\Tests\Entity\Scalar'], $a['__meta']);
        $this->assertArrayHasKey('id', $a);   
        $this->assertEquals(123456, $a['id']);
        $this->assertArrayHasKey('dt1', $a);   
        $this->assertEquals('Thu, 31 Dec 2099 23:59:59 +0000', $a['dt1']);
        $this->assertArrayHasKey('dt2', $a);   
        $this->assertEquals('2099_12_31_23_59_59', $a['dt2']);
        $this->assertArrayHasKey('dt3', $a);   
        $this->assertEquals($dt, $a['dt3']);
        $this->assertArrayHasKey('date', $a);   
        $this->assertEquals($dt->format('Y-m-d\TH:i:s').'.000Z', $a['date']);
        $this->assertArrayHasKey('time', $a);   
        $this->assertEquals($dt->format('Y-m-d\TH:i:s').'.000Z', $a['time']);
        $this->assertArrayHasKey('str', $a);   
        $this->assertEquals('test string \'"', $a['str']);
        $this->assertArrayNotHasKey('strSkip', $a);
        $this->assertArrayHasKey('ja', $a);   
        $this->assertEquals(['a string', 1337, 13.37, true], $a['ja']);
        $this->assertArrayHasKey('sa', $a);   
        $this->assertEquals(['a string', 1337, 13.37, true], $a['sa']);
        $this->assertArrayHasKey('sae', $a);   
        $this->assertEquals([], $a['sae']);
        $this->assertArrayHasKey('deci', $a);   
        $this->assertEquals(123.456789, $a['deci']);
        $this->assertArrayHasKey('BI', $a);   
        $this->assertEquals('1234567890987654321', $a['BI']);
        $this->assertArrayHasKey('bln', $a);   
        $this->assertEquals(true, $a['bln']);
        $this->assertArrayHasKey('flt', $a);   
        $this->assertEquals(0.0000001, $a['flt'], '', 0.00000001);
    }
    
    public function testValuesWithLocalPolicy()
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', '2099-12-31 23:59:59');
        
        $e = (new Entity\Scalar())
            ->setId(123456)
            ->setDt1($dt)
            ->setDt2($dt)
            ->setDt3($dt)
            ->setDate($dt)
            ->setTime($dt)
            ->setStr('test string \'"')
            ->setStrNull(null);
            
        $pr = newPR(PolicyResolver::SIMPLE_ARRAY_FIX);
        
        $policy = (new Policy\To\Auto)->inside([
            'dt1' => new Policy\To\Auto,
            'dt2' => new Policy\To\Skip,
            'dt3' => new Policy\Auto,
            'date' => new Policy\To\KeepDateTime,
            'time' => (new Policy\To\FormatDateTime)->format('Y_m_d_H_i_s'),
            'str' => (new Policy\To\Custom)->format(function($value, $propertyName) {
                return str_replace('test', 'demo', $value);
            }),
            'strSkip' => new Policy\To\Auto,
            'strNull' => new Policy\To\Skip
        ]);
        
        $a = $e->toArray($policy, null, $pr);
        printPR($pr);
        
        $this->assertArrayHasKey('dt1', $a);   
        $this->assertEquals('2099-12-31T23:59:59.000Z', $a['dt1']);
        $this->assertArrayNotHasKey('dt2', $a);
        $this->assertArrayHasKey('dt3', $a);   
        $this->assertEquals('2099-12-31T23:59:59.000Z', $a['dt3']);
        $this->assertArrayHasKey('date', $a);   
        $this->assertEquals($dt, $a['date']);
        $this->assertArrayHasKey('time', $a);   
        $this->assertEquals($dt->format('Y_m_d_H_i_s'), $a['time']);
        $this->assertArrayHasKey('str', $a);   
        $this->assertEquals('demo string \'"', $a['str']);
        $this->assertArrayHasKey('strSkip', $a);
        $this->assertArrayNotHasKey('strNull', $a);
    }
    
}
?>