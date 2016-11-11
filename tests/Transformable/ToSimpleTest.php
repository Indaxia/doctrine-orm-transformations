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
            
        $this->assertEquals($e->toArray(), [
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => 123456,
            'value' => 'abc!@#)([\'"do'
        ]);
    }
    
    public function testSimpleNull()
    {
        $e = (new Entity\Simple())
            ->setId(null)
            ->setValue(null);
            
        $this->assertEquals($e->toArray(), [
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => null,
            'value' => null
        ]);
    }
    
    public function testSimpleEmpty()
    {
        $e = (new Entity\Simple())
            ->setId(0)
            ->setValue('');
            
        $this->assertEquals($e->toArray(), [
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => 0,
            'value' => ''
        ]);
    }
}
?>