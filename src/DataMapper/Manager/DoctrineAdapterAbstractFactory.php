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

        if (! isset($config['thorr_persistence']['data_mappers'])) {
            return false;
        }

        return in_array($requestedName, $config['thorr_persistence']['data_mappers']) && class_exists($requestedName);
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
        $entityClass = array_search($requestedName, $config['thorr_persistence']['data_mappers']);

        /**
         * by default the associated entity class is a data_mappers array key
         * this makes possible to specify values in the config without a key,
         * but rather providing their own default entityClass via the getter
         *
         * note that this way, you can only retrieve an adapter by using its FQCN
         * not via the manager's getDataMapperForEntity() method
         *
         * @see DoctrineAdapter::getEntityClass()
         */
        $instance = is_string($entityClass) ?
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

        $objectManagerServiceName = isset($config['thorr_persistence']['doctrine']['object_manager']) ?
            $config['thorr_persistence']['doctrine']['object_manager']
            : null;

        $objectManager = $serviceLocator->has($objectManagerServiceName) ?
            $serviceLocator->get($objectManagerServiceName)
            : null;

        if (! $objectManager instanceof ObjectManager) {
            throw new Exception\InvalidServiceNameException(
                "Invalid service configured in '['thorr_persistence']['doctrine']['object_manager']' key. "
                .sprintf(
                    "Expected a '%s' instance, got '%s'.",
                    ObjectManager::class,
                    is_object($objectManager) ? get_class($objectManager) : gettype($objectManager)
                )
            );
        }

        return $objectManager;
    }
}
