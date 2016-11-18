JSON-ready Doctrine ORM Entity-Array Transformations
====================================== 

Features
--------
- JSON-ready toArray and fromArray Trait (**no need to extend class**);
- Manipulating fields and **nested** sub-fields using [Policy](https://github.com/Indaxia/doctrine-orm-transformations/tree/master/src/Annotations/Policy) for each one;
- Supports all Doctrine ORM Column types;
- Supports JavaScript [ISO8601](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/parse) format for "date", "time" and "datetime" types;
- Supports nested **Entities** and **Collections** for all the [Association](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html) types (be careful with self-referencing);
- **fromArray** asks EntityManager to find by "referencedColumnName" or creates new sub-entities (depends on Identifier emptiness and Policy);
- Same for [Collection](https://github.com/doctrine/collections/blob/master/lib/Doctrine/Common/Collections/ArrayCollection.php) members (OneToMany, ManyToMany);
- Static **toArrays** method transforms multiple entities at once;
- Has workarounds for [CVE-2015-0231](http://cve.mitre.org/cgi-bin/cvename.cgi?name=2015-0231) and [Doctrine issue #4673](https://github.com/doctrine/doctrine2/issues/4673);

Step 1: Installation
--------------------

in **composer.json** add:
```json
"require": {

    "Indaxia/doctrine-orm-transformations": "^2.*"
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
use \Indaxia\OTR\ITransformable;
use \Indaxia\OTR\Traits\Transformable;
use \Indaxia\OTR\Annotations\Policy;
```

Documentation
-------------

[Full Documentation](https://github.com/Indaxia/doctrine-orm-transformations/wiki)

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
        protected $wheels;
        
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
$result = $car->toArray((new Policy\Auto)->inside([
    'wheels' => new Policy\To\FetchPaginate(['offset'=0, 'limit'=4, 'fromTail'=false])
]));

// Local policy overrides global policy
$result = $car->toArray((new Policy\Auto)->inside([
    'keys' => new Policy\Auto
]));
```
[Policy options](https://github.com/Indaxia/doctrine-orm-transformations/tree/master/src/Annotations/Policy)
            
$result will be something like:

```php
[
    '__meta' => ['class' => 'Car'],
    'id' => 1,
    'engine' => [
        '__meta' => ['class' => 'Engine', 'association' => 'OneToOne'],
        'id' => 83
    ],
    'wheels' => [
        '__meta' => ['class' => 'Wheel', 'association' => 'OneToMany'],
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
You can also use something like [array2XML](https://github.com/Jeckerson/array2xml) and more.
    

And we can transform it to Entity again.
It will retrieve sub-entities by id using EntityManager.
Don't forget to use try-catch block to avoid uncaught exceptions.

```php
$carB = new Car();
    
// Simple way
$carB->fromArray($result, $entityManager);

// With Policy
$carB->fromArray($result, $entityManager, (new Policy\Auto())->inside([
    'keys' => mew Policy\Skip,
    'engine' => (new Policy\Auto())->inside([
        'serialNumber' => new Policy\From\DenyNewUnset
    ]),
    'wheels' => (new Policy\Auto())->inside([
        'brakes' => new Policy\From\Auto
    ])
]);
```
[Policy options](https://github.com/Indaxia/doctrine-orm-transformations/tree/master/src/Annotations/Policy)


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

-------------------------------

[Indaxia](http://indaxia.com) / 2016