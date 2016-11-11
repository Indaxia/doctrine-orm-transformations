<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;

class ToSimpleTest extends TestCase
{
    public function testSimple()
    {
        $e = (new Entity\Simple())
            ->setId(123456)
            ->setValue('abc!@#)([\'"do');
            
        $this->assertEquals([
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => 123456,
            'value' => 'abc!@#)([\'"do'
        ], $e->toArray());
    }
    
    public function testSimpleNull()
    {
        $e = (new Entity\Simple())
            ->setId(null)
            ->setValue(null);
            
        $this->assertEquals[
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => null,
            'value' => null
        ], $e->toArray());
    }
    
    public function testSimpleEmpty()
    {
        $e = (new Entity\Simple())
            ->setId(0)
            ->setValue('');
            
        $this->assertEquals([
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => 0,
            'value' => ''
        ], $e->toArray());
    }
}
?>