<?php

namespace App\Tests\Repository;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TeamRepositoryTest extends KernelTestCase
{
    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    /**
     * This test ensures that a Team and its manager are properly persisted
     * and their bidirectional link is correctly restored from the DB.
     */
    public function testPersistTeamWithManager(): void
    {
        $manager = (new User())
            ->setUsername('BossLady')
            ->setEmail('boss@example.com')
            ->setFirstName('Boss')
            ->setLastName('Lady')
            ->setPassword('hashy')
            ->setRoles(['ROLE_MANAGER']);

        $team = (new Team())
            ->setName('Integration Avengers')
            ->setDescription('Testing squad')
            ->setManager($manager);

        $this->em->persist($manager);
        $this->em->persist($team);
        $this->em->flush();
        $this->em->clear();

        $foundTeam = $this->em->getRepository(Team::class)->findOneBy(['name' => 'Integration Avengers']);
        $this->assertNotNull($foundTeam);
        $this->assertSame('BossLady', $foundTeam->getManager()->getUsername());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (null !== $this->em) {
            $this->em->close();
        }
    }
}
