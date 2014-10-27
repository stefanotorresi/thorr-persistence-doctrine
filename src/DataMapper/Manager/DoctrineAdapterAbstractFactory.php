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
    /**
     * {@inheritdoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $serviceManager = $serviceLocator instanceof AbstractPluginManager ?
            $serviceLocator->getServiceLocator() : $serviceLocator;

        $config = $serviceManager->get('config');

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

        $objectManager = $this->getObjectManagerService($serviceManager);

        $config = $serviceManager->get('config');
        $entityClass = array_search($requestedName, $config['thorr_persistence_doctrine']['data_mappers']);

        $instance = class_exists($entityClass) ?
            new $requestedName($objectManager, $entityClass)
            : new $requestedName($objectManager);

        if (! $instance instanceof DoctrineAdapter) {
            throw new Exception\InvalidServiceNameException(sprintf(
                '"%s" is not a sub-class of "%s"',
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
    protected function getObjectManagerService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        $objectManagerServiceName = isset($config['thorr_persistence_doctrine']['object_manager']) ?
            $config['thorr_persistence_doctrine']['object_manager']
            : null;

        $objectManager = $serviceLocator->has($objectManagerServiceName) ?
            $serviceLocator->get($objectManagerServiceName)
            : null;

        if (! $objectManager instanceof ObjectManager) {
            throw new Exception\InvalidServiceNameException(
                'Invalid service configured in "[\'thorr_persistence_doctrine\'][\'object_manager\']" key. '
                . sprintf(
                    "Expected a '%s' instance, got '%s'.",
                    ObjectManager::class,
                    is_object($objectManager) ? get_class($objectManager) : gettype($objectManager)
                )
            );
        }

        return $objectManager;
    }
}
