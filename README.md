ScorpioT1000's ITransformable examples
======================================

First include namespaces:
~~~~
    use \ScorpioT1000\Doctrine\ORM\Transformations\ITransformable;
    use \ScorpioT1000\Doctrine\ORM\Transformations\Traits\Transformable;
    use \ScorpioT1000\Doctrine\ORM\Transformations\Policy;
~~~~

How to transform entities to arrays and vice versa
--------------------------------------------------

Let's say we have the following entities:

~~~~
    class Car implements ITransformable {
        /** @ORM\Id
         * @ORM\Column(type="integer") */
        protected $id;
        
        /** @ORM\Column(type="string") */
        protected $keys;
        
        /** @ORM\OneToMany(targetEntity="Wheel") ... */
        $protected $wheels;
        
        public function getId();
        public function getKeys() ...
        public function setKeys($v) ...
        ...
    }
    
    class Engine implements ITransformable {
        /** @ORM\Id
         * @ORM\Column(type="integer") */
        protected $id;
        
        /** @ORM\Column(type="string") */
        protected $serialNumber;
        
        public function getId();
        public function getSerialNumber() ...
        public function setSerialNumber($v) ...
        ...
    }
    
    class Wheel implements ITransformable {
        /** @ORM\Id
         * @ORM\Column(type="integer") */
        protected $id;
        
        /** @ORM\Column(type="string") */
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
    
    // here we have some $car. Let's transform it to array.
    $result = $car->toArray([
                'keys': Policy::Skip, // 'Car.keys' will be excluded
                'engine': [
                    'serialNumber': Policy::Skip // 'Car.engine.serialNumber' will be excluded
                ],
                'wheels': [
                    'brakes': Policy::Skip // The field 'brakes' will be excluded from each Entity in 'Car.engine.wheels' Collection
                ]
            ]);
            
    /* $result will be something like:
    *  [
    *      '_meta' => ['class' => 'Car'],
    *      'id' => 1,
    *      'engine' => [
    *          '_meta' => ['class' => 'Engine', 'association' => 'OneToOne'],
    *          'id' => 83
    *      ],
    *      'wheels' => [
    *          '_meta' => ['class' => 'Wheel', 'association' => 'OneToMany'],
    *          'collection' => [
    *              [
    *                  '_meta' => ['class' => 'Wheel'],
    *                  'id' => 1,
    *                  'model' => 'A'
    *              ],
    *              [
    *                  '_meta' => ['class' => 'Wheel'],
    *                  'id' => 2,
    *                  'model' => 'A'
    *              ],
    *              [
    *                  '_meta' => ['class' => 'Wheel'],
    *                  'id' => 3,
    *                  'model' => 'B'
    *              ],
    *              [
    *                  '_meta' => ['class' => 'Wheel'],
    *                  'id' => 4,
    *                  'model' => 'B'
    *              ]
    *          ]
    *      ]
    *  ]
    */
    
    
    // And we can transform it to Entity again.
    // It will retrieve sub-entities by id using EntityManager
    // Don't forget to use try-catch block to avoid uncaught exceptions
    $carB = new Car();
    $carB->fromArray($result, $entityManager, []);
~~~~


How to redeclare Transformable methods
--------------------------------------

~~~~
    class A implements ITransformable {
        use TransformableTrait {
            toArray as traitToArray;
            fromArray as traitFromArray;
        }
        
        public function toArray($policy = ['password' => Policy::Skip]) {
            return $this->traitToArray($policy, false);
        }
        
        public function fromArray(array $src, EntityManagerInterface $entityManager) {
            $this->traitFromArray($src, ['password' => Policy::Skip], $entityManager);
        }
    }
~~~~
