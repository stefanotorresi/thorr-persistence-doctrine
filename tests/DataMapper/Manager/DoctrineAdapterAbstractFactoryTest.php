<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\Test\DataMapper\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Thorr\Persistence\Doctrine\DataMapper\Manager\DoctrineAdapterAbstractFactory;
use Zend\ServiceManager\Exception as SMException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Thorr\Persistence\Doctrine\DataMapper\Manager\DoctrineAdapterAbstractFactory
 */
class DoctrineAdapterAbstractFactoryTest extends TestCase
{
    /**
     * @var DoctrineAdapterAbstractFactory
     */
    protected $abstractFactory;

    /**
     * @var ServiceLocatorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceLocator;

    public function setUp()
    {
        $this->abstractFactory = new DoctrineAdapterAbstractFactory();
        $this->serviceLocator = $this->getMock(ServiceLocatorInterface::class);
    }

    /**
     * @param $config
     * @param $expectedResult
     *
     * @dataProvider canCreateServiceConfigProvider()
     */
    public function testCanCreateService($config, $serviceName, $expectedResult)
    {
        $this->serviceLocator->expects($this->atLeastOnce())
                             ->method('get')
                             ->with('config')
                             ->willReturn($config);

        $this->assertSame(
            $expectedResult,
            $this->abstractFactory->canCreateServiceWithName($this->serviceLocator, 'unrelevant', $serviceName)
        );
    }

    public function canCreateServiceConfigProvider()
    {
        return [
            [
                [],
                'anything',
                false,
            ],
            [
                [ 'thorr_persistence_dmm' => [] ],
                'anything',
                false
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [],
                    ],
                ],
                'anything',
                false
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'data_mappers' => [],
                        ],
                    ],
                ],
                'anything',
                false
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => 123,
                            ],
                        ],
                    ],
                ],
                'SomeDataMapperServiceName',
                false
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => DoctrineAdapter::class,
                            ],
                        ],
                    ],
                ],
                'SomeDataMapperServiceName',
                true
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => 'not-a-class',
                            ],
                        ],
                    ],
                ],
                'SomeDataMapperServiceName',
                false
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => [
                                    'class' => DoctrineAdapter::class,
                                ],
                            ],
                        ],
                    ],
                ],
                'SomeDataMapperServiceName',
                true
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => [],
                            ],
                        ],
                    ],
                ],
                'SomeDataMapperServiceName',
                false
            ],
            [
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => [
                                    'class' => 'not-a-class',
                                ],
                            ],
                        ],
                    ],
                ],
                'SomeDataMapperServiceName',
                false
            ],
        ];
    }

    /**
     * @param $config
     * @dataProvider createServiceConfigProvider
     */
    public function testCreateService($config, $requestedName, $expectedException)
    {
        $objectManagers = [
            'SomeObjectManagerService' => $this->getMock(ObjectManager::class, [], [], 'SomeObjectManagerService'),
            'AnotherObjectManagerService' => $this->getMock(ObjectManager::class, [], [], 'AnotherObjectManagerService'),
        ];

        $this->serviceLocator->expects($this->any())
            ->method('has')
            ->willReturnCallback(function ($name) use ($objectManagers) {
                return array_key_exists($name, $objectManagers);
            });

        $this->serviceLocator->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($name) use ($config, $objectManagers) {
                switch ($name) {
                    case 'config' :
                        return $config;
                    case 'SomeObjectManagerService' :
                    case 'AnotherObjectManagerService' :
                        return $objectManagers[$name];
                }
            });

        if ($expectedException) {
            $this->setExpectedException($expectedException[0], $expectedException[1]);
        }

        /** @var DoctrineAdapter $instance */
        $instance = $this->abstractFactory->createServiceWithName($this->serviceLocator, 'unrelevant', $requestedName);

        $this->assertInstanceOf(DoctrineAdapter::class, $instance);
        $this->assertSame(
            $requestedName,
            $config['thorr_persistence_dmm']['entity_data_mapper_map'][$instance->getEntityClass()]
        );

        if (isset($config['thorr_persistence_dmm']['doctrine']['data_mappers'][$requestedName]['object_manager'])) {
            $this->assertSame(
                $objectManagers[$config['thorr_persistence_dmm']['doctrine']['data_mappers'][$requestedName]['object_manager']],
                $instance->getObjectManager()
            );
        }
    }

    public function createServiceConfigProvider()
    {
        return [
            [
                // $config
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'object_manager' => 'SomeObjectManagerService',
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => DoctrineAdapter::class,
                            ],
                        ],
                        'entity_data_mapper_map' => [
                            'SomeEntityClass' => 'SomeDataMapperServiceName',
                        ],
                    ],
                ],
                // $requestedName
                'SomeDataMapperServiceName',
                // $expectedException
                null,
            ],
            [
                // $config
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'object_manager' => 'SomeObjectManagerService',
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => \stdClass::class,
                            ],
                        ],
                        'entity_data_mapper_map' => [
                            'SomeEntityClass' => 'SomeDataMapperServiceName',
                        ],
                    ],
                ],
                // $requestedName
                'SomeDataMapperServiceName',
                // $expectedException
                [ SMException\ServiceNotCreatedException::class, "Invalid data mapper type" ]
            ],
            [
                // $config
                [
                    'thorr_persistence_dmm' => [
                        'doctrine' => [
                            'object_manager' => 'SomeObjectManagerService',
                            'data_mappers' => [
                                'SomeDataMapperServiceName' => [
                                    'class' => DoctrineAdapter::class,
                                    'object_manager' => 'AnotherObjectManagerService',
                                ],
                            ],
                        ],
                        'entity_data_mapper_map' => [
                            'SomeEntityClass' => 'SomeDataMapperServiceName',
                        ],
                    ],
                ],
                // $requestedName
                'SomeDataMapperServiceName',
                // $expectedException
                null,
            ],
        ];
    }

    public function testInvalidObjectManagerClass()
    {
        $config = [
            'thorr_persistence_dmm' => [
                'doctrine' => [
                    'object_manager' => 'SomeObjectManagerService',
                    'data_mappers' => [
                        'SomeDataMapperServiceName' => DoctrineAdapter::class,
                    ],
                ],
                'entity_data_mapper_map' => [],
            ],
        ];

        $objectManager = $this->getMock(\stdClass::class);

        $this->serviceLocator->expects($this->atLeastOnce())
            ->method('has')
            ->with($config['thorr_persistence_dmm']['doctrine']['object_manager'])
            ->willReturn(true);

        $this->serviceLocator->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($name) use ($config, $objectManager) {
                switch ($name) {
                    case 'config' :
                        return $config;
                    case $config['thorr_persistence_dmm']['doctrine']['object_manager'] :
                        return $objectManager;
                }
            });

        $this->setExpectedException(SMException\InvalidServiceNameException::class, "Invalid object manager type");

        $this->abstractFactory->createServiceWithName($this->serviceLocator, 'unrelevant', 'SomeDataMapperServiceName');
    }
}
