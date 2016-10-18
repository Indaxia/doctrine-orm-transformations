JSON-ready Doctrine ORM Entity-Array Transformations
======================================

Features
--------
- JSON-ready toArray and fromArray Trait (**no need to extend class**);
- Manipulating fields and **nested** sub-fields using [Policy](https://github.com/ScorpioT1000/doctrine-orm-transformations/blob/master/src/Policy.php) for each one;
- Supports almost all Doctrine Column ORM types ("object" and "array" are excluded due to CVE-2015-0231);
- Supports JavaScript [ISO8601](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/parse) format for "date", "time" and "datetime" types;
- Supports nested **Entities** and **Collections** for all the [Association](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html) types (be careful with self-referencing);
- **fromArray** asks EntityManager to find by "referencedColumnName" or creates new sub-entities (depends on Identifier emptiness and Policy);
- Same for [Collection](https://github.com/doctrine/collections/blob/master/lib/Doctrine/Common/Collections/ArrayCollection.php) members (OneToMany, ManyToMany);
- Static **toArrays** method transforms multiple entities at once;

Step 1: Installation
--------------------

in **composer.json** add:
```json
"require": {

    "ScorpioT1000/doctrine-orm-transformations": "^0.1@dev"
}
```
then
```shell
> cd <your doc root>
> composer update
```

Step 2: Reference common classes
--------------------------------

```php
use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;
use \ScorpioT1000\OTR\Annotations\Policy;
```

How to transform entities to arrays and vice versa
--------------------------------------------------

Let's say we have the following entities:

```php
    class Car implements ITransformable {
        use Transformable;
    
        /** @ORM\Id
         * @ORM\Column(type="integer") */
        protected $id;
        
        /** @Policy\To\Skip
         * @ORM\Column(type="string") */
        protected $keys;
        
        /** @ORM\OneToMany(targetEntity="Wheel") ... */
        $protected $wheels;
        
        public function getId();
        public function getKeys() ...
        public function setKeys($v) ...
        ...
    }
    
    class Engine implements ITransformable {
        use Transformable;
        
        /** @ORM\Id
         * @ORM\Column(type="integer") */
        protected $id;
        
        /** @Policy\To\Skip
         * @ORM\Column(type="string") */
        protected $serialNumber;
        
        public function getId();
        public function getSerialNumber() ...
        public function setSerialNumber($v) ...
        ...
    }
    
    class Wheel implements ITransformable {
        use Transformable;
        
        /** @ORM\Id
         * @ORM\Column(type="integer") */
        protected $id;
        
        /** @Policy\Skip
         * @ORM\Column(type="string") */
        protected $brakes;
        
        /** @ORM\Column(type="string") */
        protected $model;
        
        public function getId();
        public function getBrakes() ...
        public function setBrakes($v) ...
        public function getModel() ...
        public function setModel($v) ...
        ...
    }
```

Here we have some $car. Let's transform it to array.

```php
// Using global policy
$result = $car->toArray();
    
// Using local policy
$result = $car->toArray([
    'wheels' => new Policy\To\Paginate(offset=0, limit=4)
]);

// Local policy overrides global policy
$result = $car->toArray([
    'keys' => new Policy\Auto
]);
```
[Policy options](https://github.com/ScorpioT1000/doctrine-orm-transformations/blob/master/src/Policy.php)
            
$result will be something like:

```php
[
    '_meta' => ['class' => 'Car'],
    'id' => 1,
    'engine' => [
        '_meta' => ['class' => 'Engine', 'association' => 'OneToOne'],
        'id' => 83
    ],
    'wheels' => [
        '_meta' => ['class' => 'Wheel', 'association' => 'OneToMany'],
        'collection' => [
            [
                '_meta' => ['class' => 'Wheel'],
                'id' => 1,
                'model' => 'A'
            ],
            [
                '_meta' => ['class' => 'Wheel'],
                'id' => 2,
                'model' => 'A'
            ],
            [
                '_meta' => ['class' => 'Wheel'],
                'id' => 3,
                'model' => 'B'
            ],
            [
                '_meta' => ['class' => 'Wheel'],
                'id' => 4,
                'model' => 'B'
            ]
        ]
    ]
]
```

**It's ready for JSON transformation!**
```php
    echo json_encode($result);
```    
    
And we can transform it to Entity again.
It will retrieve sub-entities by id using EntityManager.
Don't forget to use try-catch block to avoid uncaught exceptions.

```php
$carB = new Car();
    
// Simple way
$carB->fromArray($result, $entityManager, []);

// With Policy
$carB->fromArray($result, $entityManager, [
    'keys' => new Policy\Skip,
    'engine' => [
        'serialNumber' => new Policy\From\AllowNewOnly
    ],
    'wheels' => [
        'brakes' => new Policy\From\Accept
    ]
]);
```
[Policy options](https://github.com/ScorpioT1000/doctrine-orm-transformations/blob/master/src/Policy.php)

More Demos
----------
[Entities & Symfony 3 Controller](https://github.com/ScorpioT1000/doctrine-orm-transformations/tree/master/src/Demo) are included and accessible through the namespace.


How to redeclare Transformable methods
--------------------------------------

```php
    class A implements ITransformable {
        use Transformable {
            toArray as traitToArray;
            fromArray as traitFromArray;
        }
        
        public function toArray ...
        public function fromArray ...
    }
```
