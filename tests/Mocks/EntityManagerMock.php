<?php

namespace Indaxia\OTR\Tests\Mocks;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

/**
 * Special EntityManager mock used for testing purposes.
 */
class EntityManagerMock extends EntityManager
{
    private $intities;
    
    /**
     * @var \Doctrine\ORM\UnitOfWork|null
     */
    private $_uowMock;

    /**
     * @var \Doctrine\ORM\Proxy\ProxyFactory|null
     */
    private $_proxyFactoryMock;
    
    

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork()
    {
        return isset($this->_uowMock) ? $this->_uowMock : parent::getUnitOfWork();
    }

    /* Mock API */

    /**
     * Sets a (mock) UnitOfWork that will be returned when getUnitOfWork() is called.
     *
     * @param \Doctrine\ORM\UnitOfWork $uow
     *
     * @return void
     */
    public function setUnitOfWork($uow)
    {
        $this->_uowMock = $uow;
    }

    /**
     * @param \Doctrine\ORM\Proxy\ProxyFactory $proxyFactory
     *
     * @return void
     */
    public function setProxyFactory($proxyFactory)
    {
        $this->_proxyFactoryMock = $proxyFactory;
    }

    /**
     * @return \Doctrine\ORM\Proxy\ProxyFactory
     */
    public function getProxyFactory()
    {
        return isset($this->_proxyFactoryMock) ? $this->_proxyFactoryMock : parent::getProxyFactory();
    }

    /**
     * Mock factory method to create an EntityManager.
     *
     * {@inheritdoc}
     */
    public static function create($conn, Configuration $config = null, EventManager $eventManager = null)
    {
        if (null === $config) {
            $config = new Configuration();
            $config->setProxyDir(__DIR__ . '/../Proxies');
            $config->setProxyNamespace('Doctrine\Tests\Proxies');
            $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(array(), true));
        }
        if (null === $eventManager) {
            $eventManager = new EventManager();
        }

        return new EntityManagerMock($conn, $config, $eventManager);
    }
    
    public function persist($entity) {
        if(!$entity) { throw new \Exception('EntityManagerMock: persist: Not an entity'); }
        if(!method_exists($entity, 'getId')) { throw new \Exception('EntityManagerMock::persist: working with custom "id" name is not implemented'); }
        if(!$entity->getId()) { $entity->setId(mt_rand(10000, \PHP_INT_MAX)); }
        $this->entities[get_class($entity).'#'.$entity->getId()] = $entity;
    }
    
    public function getReference($entityName, $id) {
        if(isset($this->entities[$entityName.'#'.$id])) {
            return $this->entities[$entityName.'#'.$id];
        }
        return null;
    }
    
    public function clear($entity = null) {
        if($entity) {
            if(!method_exists($entity, 'getId')) { throw new \Exception('EntityManagerMock::clear: working with custom "id" name is not implemented'); }
            unset($this->entities[get_class($entity).'#'.$entity->getId()]);
        } else {
            $this->entities = [];
        }
    }
}
