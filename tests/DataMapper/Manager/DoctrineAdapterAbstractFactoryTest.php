<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\Test\DataMapper\Manager;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Thorr\Persistence\Doctrine\DataMapper\Manager\DoctrineAdapterAbstractFactory;
use Thorr\Persistence\Entity\AbstractEntity;
use Zend\ServiceManager\Exception\InvalidServiceNameException;
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
     * @dataProvider canCreateServiceconfigProvider()
     */
    public function testCanCreateService($config, $expectedResult)
    {
        $this->serviceLocator->expects($this->atLeastOnce())
                             ->method('get')
                             ->with('config')
                             ->willReturn($config);

        $this->assertSame(
            $expectedResult,
            $this->abstractFactory->canCreateServiceWithName($this->serviceLocator, 'unrelevant', DoctrineAdapter::class)
        );
    }

    public function canCreateServiceconfigProvider()
    {
        return [
            [
                [],
                false
            ],
            [
                ['thorr_persistence_doctrine' => []],
                false
            ],
            [
                [
                    'thorr_persistence_doctrine' => [
                        'data_mappers' => []
                    ]
                ],
                false
            ],
            [
                [
                    'thorr_persistence_doctrine' => [
                        'data_mappers' => [
                            DoctrineAdapter::class
                        ]
                    ]
                ],
                true
            ],
            [
                [
                    'thorr_persistence_doctrine' => [
                        'data_mappers' => [
                            'some-entity-class-name' => DoctrineAdapter::class
                        ]
                    ]
                ],
                true
            ],
        ];
    }

    public function testCreateService()
    {
        $config = [
            'thorr_persistence_doctrine' => [
                'object_manager' => 'SomeObjectManagerService',
                'data_mappers' => [
                    AbstractEntity::class => DoctrineAdapter::class
                ]
            ]
        ];

        $objectManager   = $this->getMock(ObjectManager::class);
        $metadataFactory = $this->getMock(ClassMetadataFactory::class);

        $objectManager->expects($this->any())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $metadataFactory->expects($this->any())
            ->method('hasMetadataFor')
            ->with(AbstractEntity::class)
            ->willReturn(true);

        $this->serviceLocator->expects($this->atLeastOnce())
            ->method('has')
            ->with($config['thorr_persistence_doctrine']['object_manager'])
            ->willReturn(true);

        $this->serviceLocator->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($name) use ($config, $objectManager) {
                switch ($name) {
                    case 'config' :
                        return $config;
                    case $config['thorr_persistence_doctrine']['object_manager'] :
                        return $objectManager;
                }
            });

        /** @var DoctrineAdapter $instance */
        $instance = $this->abstractFactory->createServiceWithName($this->serviceLocator, 'unrelevant', DoctrineAdapter::class);

        $this->assertInstanceOf(DoctrineAdapter::class, $instance);
        $this->assertSame(AbstractEntity::class, $instance->getEntityClass());
    }

    public function testInvalidObjectManagerConfigValue()
    {
        $this->setExpectedException(InvalidServiceNameException::class, "Invalid service");

        $this->abstractFactory->createServiceWithName($this->serviceLocator, 'unrelevant', DoctrineAdapter::class);
    }

    public function testInvalidAdapterClass()
    {
        $config = [
            'thorr_persistence_doctrine' => [
                'object_manager' => 'SomeObjectManagerService',
                'data_mappers' => [
                    \stdClass::class
                ]
            ]
        ];

        $objectManager   = $this->getMock(ObjectManager::class);

        $this->serviceLocator->expects($this->atLeastOnce())
            ->method('has')
            ->with($config['thorr_persistence_doctrine']['object_manager'])
            ->willReturn(true);

        $this->serviceLocator->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($name) use ($config, $objectManager) {
                switch ($name) {
                    case 'config' :
                        return $config;
                    case $config['thorr_persistence_doctrine']['object_manager'] :
                        return $objectManager;
                }
            });

        $this->setExpectedException(InvalidServiceNameException::class, "is not a sub-class");

        $this->abstractFactory->createServiceWithName($this->serviceLocator, 'unrelevant', \stdClass::class);
    }

    public function createServiceConfigProvider()
    {
        return [
            [
                [
                    'thorr_persistence_doctrine' => [
                        'object_manager' => 'SomeObjectManagerService',
                        'data_mappers' => [
                            DoctrineAdapter::class
                        ]
                    ]
                ]
            ]
        ];
    }
}
