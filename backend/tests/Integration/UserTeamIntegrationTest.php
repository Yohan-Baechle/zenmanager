<?php

namespace App\Tests\Integration;

use App\Entity\User;
use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserTeamIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeStatement('SET session_replication_role = replica;');
        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
            $tableName = $meta->getTableName();
            $connection->executeStatement($platform->getTruncateTableSQL($tableName, true));
        }
        $connection->executeStatement('SET session_replication_role = DEFAULT;');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->em !== null) {
            $this->em->close();
        }
    }

    /**
     * This test ensures that a User can be assigned to a Team
     * and Doctrine correctly persists the relationship.
     */
    public function testPersistUserWithTeam(): void
    {
        $team = (new Team())
            ->setName('Dream Team')
            ->setDescription('The best of the best, la cream of la cream');

        $user = (new User())
            ->setUsername('Dreamer')
            ->setEmail('dreamer@example.com')
            ->setPassword('secret')
            ->setFirstName('Dream')
            ->setLastName('Er')
            ->setRoles(['ROLE_EMPLOYEE'])
            ->setTeam($team);

        $this->em->persist($team);
        $this->em->persist($user);
        $this->em->flush();
        $this->em->clear();

        $foundUser = $this->em->getRepository(User::class)->findOneBy(['username' => 'Dreamer']);
        $this->assertNotNull($foundUser);
        $this->assertSame('Dream Team', $foundUser->getTeam()->getName());
    }
}


