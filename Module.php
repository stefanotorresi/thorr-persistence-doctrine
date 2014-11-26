<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 *
 * This file is placed here for compatibility with Zendframework 2's ModuleManager.
 * It allows usage of this module even without composer.
 * The original Module.php is in 'src/{ModuleNamespace}' in order to respect PSR-0
 */

namespace Thorr\Persistence\Doctrine;

use Rhumsaa\Uuid\Doctrine\UuidType;
use Thorr\Persistence\DataMapper\Manager\DataMapperManagerConfigProviderInterface;
use Zend\ModuleManager\Feature;

class Module implements
    Feature\ConfigProviderInterface,
    DataMapperManagerConfigProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'thorr_persistence_dmm' => [
                'doctrine' => [
                    'object_manager' => 'doctrine.objectmanager.orm_default', // an ObjectManager service name
                    'adapters' => [], // DataMapperServiceName => Spec couples
                ],
            ],

            /**
             * Doctrine mappings for Thorr\Persistence\Entity\AbstractEntity
             */
            'doctrine' => [
                'driver' => [
                    'thorr_persistence_doctrine_orm' => [
                        'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                        'paths' => __DIR__ . '/config',
                    ],
                    'thorr_persistence_doctrine_mongo_odm' => [
                        'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver',
                        'paths' => __DIR__ . '/config',
                    ],
                    'orm_default' => [
                        'drivers' => [
                            'Thorr\Persistence\Entity' => 'thorr_persistence_doctrine_orm'
                        ]
                    ],
                    'odm_default' => [
                        'drivers' => [
                            'Thorr\Persistence\Entity' => 'thorr_persistence_doctrine_mongo_odm',
                        ]
                    ]
                ],
                'configuration' => [
                    'orm_default' => [
                        'types' => [
                            'uuid' => UuidType::class,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getDataMapperManagerConfig()
    {
        return [
            'abstract_factories' => [
                DataMapper\Manager\DoctrineAdapterAbstractFactory::class
            ]
        ];
    }
}
