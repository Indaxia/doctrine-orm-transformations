<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;
use Indaxia\OTR\Annotations\PolicyResolver;

class ToPolicyScalarTest extends TestCase
{
    public function testValuesWithGlobalPolicy()
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', '2099-12-31 23:59:59');
        
        $e = (new Entity\ToPolicyScalar())
            ->setId(123456)
            ->setDt1($dt)
            ->setDt2($dt)
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
            ->setFlt(0.0000001);
            
        $a = $e->toArray(null, null, new PolicyResolver(PolicyResolver::SIMPLE_ARRAY_FIX));
        
        $this->assertEquals($a['__meta'], ['class' => 'Indaxia\OTR\Tests\Entity\ToPolicyScalar']);
        $this->assertEquals($a['id'], 123456);
        $this->assertEquals($a['dt1'], 'Thu, 31 Dec 2099 23:59:59 +0000');
        $this->assertEquals($a['dt2'], '2099_12_31_23_59_59');
        $this->assertEquals($a['dt3'], $dt);
        $this->assertEquals($a['date'], $dt->format('Y-m-d\TH:i:s').'.000Z');
        $this->assertEquals($a['time'], $dt->format('Y-m-d\TH:i:s').'.000Z');
        $this->assertEquals($a['str'], 'test string \'"');
        $this->assertEmpty($a['strSkip']);
        $this->assertEquals($a['ja'], ['a string', 1337, 13.37, true]);
        $this->assertEquals($a['sa'], ['a string', 1337, 13.37, true]);
        $this->assertEquals($a['sae'], []);
        $this->assertEquals($a['deci'], 123.456789);
        $this->assertEquals($a['BI'], '1234567890987654321');
        $this->assertEquals($a['bln'], true);
        $this->assertEquals($a['flt'], 0.0000001, '', 0.00000001);
    }
    
}
?>