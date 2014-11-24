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

        if (! isset($config['thorr_persistence_dmm']['doctrine']['data_mappers'][$requestedName])) {
            return false;
        }

        $dataMapperSpec = $config['thorr_persistence_dmm']['doctrine']['data_mappers'][$requestedName];

        if (! is_array($dataMapperSpec) && ! is_string($dataMapperSpec)) {
            return false;
        }

        if (is_string($dataMapperSpec) && ! class_exists($dataMapperSpec)) {
            return false;
        }

        if (is_array($dataMapperSpec) && (! isset($dataMapperSpec['class']) || ! class_exists($dataMapperSpec['class']))) {
            return false;
        }

        return true;
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

        $dataMapperSpec = $config['thorr_persistence_dmm']['doctrine']['data_mappers'][$requestedName];
        $dataMapperClassName = is_string($dataMapperSpec) ? $dataMapperSpec : $dataMapperSpec['class'];

        $entityClass = array_search($requestedName, $config['thorr_persistence_dmm']['entity_data_mapper_map']);

        $objectManagerServiceName = isset($dataMapperSpec['object_manager']) ? $dataMapperSpec['object_manager'] : null;

        $objectManager = $this->getObjectManagerService($serviceManager, $objectManagerServiceName);

        $instance = new $dataMapperClassName($entityClass, $objectManager);

        if (! $instance instanceof DoctrineAdapter) {
            throw new Exception\ServiceNotCreatedException(sprintf(
                'Invalid data mapper type: "%s" is not a subtype of "%s"',
                $dataMapperClassName,
                DoctrineAdapter::class
            ));
        }

        return $instance;
    }

    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string                  $objectManagerServiceName
     * @return ObjectManager
     */
    protected function getObjectManagerService(ServiceLocatorInterface $serviceLocator, $objectManagerServiceName)
    {
        $config = $serviceLocator->get('config');

        if (! $objectManagerServiceName && isset($config['thorr_persistence_dmm']['doctrine']['object_manager'])) {
            $objectManagerServiceName = $config['thorr_persistence_dmm']['doctrine']['object_manager'];
        }

        $objectManager = $objectManagerServiceName && $serviceLocator->has($objectManagerServiceName) ?
            $serviceLocator->get($objectManagerServiceName)
            : null;

        if (! $objectManager instanceof ObjectManager) {
            throw new Exception\InvalidServiceNameException(sprintf(
                'Invalid object manager type: "%s" is not a subtype of "%s"',
                is_object($objectManager) ? get_class($objectManager) : gettype($objectManager),
                ObjectManager::class
            ));
        }

        return $objectManager;
    }
}
