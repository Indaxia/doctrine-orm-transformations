<?php
namespace Indaxia\OTR\Tests\Transformable;

use PHPUnit\Framework\TestCase;
use Indaxia\OTR\Tests\Entity;

class FromSimpleTest extends TestCase
{
    public function testSimple()
    {
        global $entityManager;
        $e = new Entity\Simple();
        $data = [
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => 1000,
            'value' => 'abc!@#)([\'"do'
        ];
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        $this->assertEquals(1000, $e->getId());
        $this->assertEquals('abc!@#)([\'"do', $e->getValue());
    }
    
    public function testSimpleNull()
    {
        global $entityManager;
        $e = (new Entity\Simple())->setId(1001)->setValue('test');
        $data = [
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => null,
            'value' => null
        ];
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        $this->assertEquals(1001, $e->getId()); // id is untouched
        $this->assertEquals(null, $e->getValue());
    }
    
    public function testSimpleEmpty()
    {
        global $entityManager;
        $e = (new Entity\Simple())->setId(1002)->setValue('test');
        $data = [
            '__meta' => [
                'class' => 'Indaxia\OTR\Tests\Entity\Simple'
            ],
            'id' => 0,
            'value' => ''
        ];
        $pr = newPR();
        $e->fromArray($data, $entityManager, null, null, $pr);
        printPR($pr);
        $this->assertEquals(1002, $e->getId()); // id is untouched
        $this->assertEquals('', $e->getValue());
    }
}