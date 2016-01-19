<?php
/**
 * @license See the file LICENSE for copying permission.
 */

namespace Thorr\Persistence\Doctrine\Test\Integration;

use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Thorr\Persistence\Doctrine;
use Thorr\Persistence\Doctrine\DataMapper;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

class ModuleIntegrationTest extends TestCase
{
    /**
     * @var array
     */
    protected $appConfig;

    protected function setUp()
    {
        $this->serviceManager = new ServiceManager();
        $this->appConfig      = [
            'modules' => [
                'Thorr\Persistence\Doctrine',
            ],
            'module_listener_options' => [],
        ];
    }

    public function testCanLoadModule()
    {
        $app           = Application::init($this->appConfig);
        $loadedModules = $app->getServiceManager()->get('ModuleManager')->getLoadedModules();
        $this->assertArrayHasKey('Thorr\Persistence\Doctrine', $loadedModules);
        $this->assertInstanceOf(Doctrine\Module::class, $loadedModules['Thorr\Persistence\Doctrine']);
    }

    public function testDoctrineORMEntityManagerDefaultMappingsAreConfigured()
    {
        $this->appConfig['modules'][] = 'DoctrineModule';
        $this->appConfig['modules'][] = 'DoctrineORMModule';

        $this->addModuleConfig([
            'doctrine' => [
                'connection' => [
                    'orm_default' => [
                        'driverClass' => \Doctrine\DBAL\Driver\PDOSqlite\Driver::class,
                        'params' => [
                            'memory' => true,
                        ]
                    ],
                ],
                'driver' => [
                    'test_driver' => [
                        'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                        'paths' => __DIR__ . '/Asset',
                    ],
                    'orm_default' => [
                        'drivers' => [
                            Asset\Entity::class => 'test_driver',
                        ],
                    ],
                ],
            ]
        ]);
        $app            = Application::init($this->appConfig);
        $serviceManager = $app->getServiceManager();
        $entityManager  = $serviceManager->get('Doctrine\ORM\EntityManager');

        $this->assertInstanceOf(ClassMetadata::class, $entityManager->getClassMetadata(Asset\Entity::class));
    }

    public function testDataMapperManagerConfiguration()
    {
        array_unshift($this->appConfig['modules'], 'Thorr\Persistence');
        $this->appConfig['modules'][] = 'DoctrineModule';
        $this->appConfig['modules'][] = 'DoctrineORMModule';

        $this->addModuleConfig([
            'doctrine' => [
                'connection' => [
                    'orm_default' => [
                        'driverClass' => \Doctrine\DBAL\Driver\PDOSqlite\Driver::class,
                        'params' => [
                            'memory' => true,
                        ]
                    ],
                ],
                'driver' => [
                    'test_driver' => [
                        'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                        'paths' => __DIR__ . '/Asset',
                    ],
                    'orm_default' => [
                        'drivers' => [
                            Asset\Entity::class => 'test_driver',
                        ],
                    ],
                ],
            ],
            'thorr_persistence_dmm' => [
                'entity_data_mapper_map' => [
                    Asset\Entity::class => 'TestMapper'
                ],
                'doctrine' => [
                    'adapters' => [
                        'TestMapper' => DoctrineAdapter::class,
                    ],
                ],
            ],
        ]);

        $app            = Application::init($this->appConfig);
        $serviceManager = $app->getServiceManager();
        $dataMapperManager = $serviceManager->get(DataMapperManager::class);
        $this->assertInstanceOf(
            DoctrineAdapter::class,
            $dataMapperManager->getDataMapperForEntity(Asset\Entity::class)
        );
    }

    /**
     * @param array $config
     */
    protected function addModuleConfig(array $config)
    {
        $this->appConfig['service_manager'] = [
            'services'   => [
                'config-delegator' => function ($sl, $name, $rName, $callback) use ($config) {
                    $oldConfig = $callback();
                    $newConfig = ArrayUtils::merge($oldConfig, $config);

                    return $newConfig;
                },
            ],
            'delegators' => [ 'config' => [ 'config-delegator' ] ],
        ];
    }
}
