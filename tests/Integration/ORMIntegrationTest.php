<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\Test\Integration;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit_Framework_TestCase as TestCase;
use Ramsey\Uuid\Uuid;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Thorr\Persistence\Entity\AbstractEntity;

class ORMIntegrationTest extends TestCase
{
    /**
     * @var DoctrineAdapter
     */
    protected $adapter;

    public function setUp()
    {
        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $driver = new MappingDriverChain();
        $driver->addDriver(new XmlDriver('config'), AbstractEntity::class);
        $driver->addDriver(new XmlDriver(__DIR__ . '/Asset'), Asset\Entity::class);

        $config = Setup::createConfiguration(true);
        $config->setMetadataDriverImpl($driver);

        $entityManager = EntityManager::create($conn, $config);

        $classes    = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);

        $this->adapter = new DoctrineAdapter(Asset\Entity::class, $entityManager);
    }

    public function testUuidIntegration()
    {
        $entity = new Asset\Entity();
        $uuid   = $entity->getUuid();

        $this->adapter->save($entity);

        $this->assertSame($uuid, $entity->getUuid());

        $this->assertSame(
            $entity,
            $this->adapter->findByUuid($uuid),
            sprintf('Failed to find an entity with uuid %s', $uuid)
        );

        $this->assertSame(
            $entity,
            $this->adapter->findByUuid(Uuid::fromString($uuid)),
            'Failed to find an entity with uuid object'
        );

        $this->adapter->removeByUuid($uuid);

        $this->assertNull($this->adapter->findByUuid($uuid));
    }

    public function testSaveDeferred()
    {
        $entity = new Asset\Entity();

        $this->adapter->saveDeferred($entity);
        $this->assertNull($this->adapter->findByUuid($entity->getUuid()));

        $this->adapter->commit();
        $this->assertSame($entity, $this->adapter->findByUuid($entity->getUuid()));
    }

    public function testRemoveDeferred()
    {
        $entity = new Asset\Entity();
        $this->adapter->save($entity);

        $this->adapter->removeDeferred($entity);
        $this->assertEquals($entity, $this->adapter->findByUuid($entity->getUuid()));

        $this->adapter->commit();
        $this->assertNull($this->adapter->findByUuid($entity->getUuid()));
    }
}
