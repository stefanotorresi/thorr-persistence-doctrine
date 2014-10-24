<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DataMapper\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineAdapterAbstractFactory implements AbstractFactoryInterface
{
    const OBJECT_MANAGER_ORM   = 'Doctrine\ORM\EntityManager';
    const OBJECT_MANAGER_MONGO = 'Doctrine\ODM\Mongo\DocumentManager';

    protected $validObjectManagers = [
        self::OBJECT_MANAGER_ORM,
        self::OBJECT_MANAGER_MONGO
    ];

    /**
     * {@inheritdoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $serviceManager = $serviceLocator instanceof AbstractPluginManager ?
            $serviceLocator->getServiceLocator() : $serviceLocator;

        $config = $serviceManager->get('config');

        if (! $this->getObjectManager($serviceManager)) {
            return false;
        }

        if (! isset($config['thorr_persistence_doctrine']['data_mappers'])) {
            return false;
        }

        return in_array($requestedName, $config['thorr_persistence_doctrine']['data_mappers']);
    }

    /**
     * {@inheritdoc}
     * @throws Exception\InvalidServiceNameException
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $serviceManager = $serviceLocator instanceof AbstractPluginManager ?
            $serviceLocator->getServiceLocator() : $serviceLocator;

        $config = $serviceManager->get('config');

        $entityClass = array_search($requestedName, $config['thorr_persistence_doctrine']['data_mappers']);

        $objectManager = $this->getObjectManager($serviceManager);

        if (! $objectManager->getMetadataFactory()->hasMetadataFor($entityClass)) {
            throw new Exception\InvalidServiceNameException(sprintf(
                '"%s" is not a valid entity class for requested mapper "%s"',
                $entityClass,
                $requestedName
            ));
        }

        $instance = new $requestedName($entityClass, $objectManager);

        if (! $instance instanceof DoctrineAdapter) {
            throw new Exception\InvalidServiceNameException(sprintf(
                '"%s" must be a sub-class of "%s"',
                $requestedName,
                DoctrineAdapter::class
            ));
        }

        return $instance;
    }

    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ObjectManager
     */
    protected function getObjectManager(ServiceLocatorInterface $serviceLocator)
    {
        if (! isset($config['thorr_persistence_doctrine']['object_manager'])) {
            return static::OBJECT_MANAGER_ORM;
        }

        if (! in_array($config['thorr_persistence_doctrine']['object_manager'], $this->validObjectManagers)) {
            throw new Exception\InvalidArgumentException(
                'Invalid "[\'thorr_persistence_doctrine\'][\'object_manager\']" value in "config" service. '
                . sprintf("Check %s constants for valid values", __CLASS__)
            );
        }

        if (! $serviceLocator->has($config['thorr_persistence_doctrine']['object_manager'])) {
            return;
        }

        return $serviceLocator->get($config['thorr_persistence_doctrine']['object_manager']);
    }
}
