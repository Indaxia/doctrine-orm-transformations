ScorpioT1000's ITransformable examples
======================================

First include namespaces:
~~~~
    use AppBundle\Entity\Transformations\ITransformable;
    use AppBundle\Entity\Transformations\TransformableTrait;
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
                'keys': ITransformabe::EXCLUDE, // 'Car.keys' will be excluded
                'engine': [
                    'serialNumber': ITransformabe::EXCLUDE // 'Car.engine.serialNumber' will be excluded
                ],
                'wheels': [
                    'brakes': ITransformabe::EXCLUDE // The field 'brakes' will be excluded from each Entity in 'Car.engine.wheels' Collection
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
    $carB = new Car();
    $carB->fromArray($result, $entityManager, []);
~~~~


How to redeclare TransformableTrait methods
-------------------------------------------

~~~~
    class A implements ITransformable {
        use TransformableTrait {
            toArray as traitToArray;
            fromArray as traitFromArray;
        }
        
        public function toArray($policy = [], $nested = true) {
            $result = $this->traitToArray($policy, $nested);
            ...
            return $result;
        }
        
        public function fromArray(array $src, $policy = [], EntityManagerInterface $entityManager = null) {
            ...
            $this->traitFromArray($src, $policy, $entityManager);
        }
    }
~~~~
