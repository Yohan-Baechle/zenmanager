<?php

namespace App\Tests\Integration;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ManagerTeamIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (null !== $this->em) {
            $this->em->close();
        }
    }

    /**
     * This test ensures bidirectional relation between Manager and Team works.
     */
    public function testManagerOwnsManagedTeam(): void
    {
        $manager = (new User())
            ->setUsername('Bossman')
            ->setEmail('boss@example.com')
            ->setPassword('pass')
            ->setFirstName('Big')
            ->setLastName('Boss')
            ->setRoles(['ROLE_MANAGER']);

        $team = (new Team())
            ->setName('The A-Team')
            ->setDescription('We love it when a plan comes together')
            ->setManager($manager);

        $manager->addManagedTeam($team);

        $this->em->persist($manager);
        $this->em->persist($team);
        $this->em->flush();
        $this->em->clear();

        $foundManager = $this->em->getRepository(User::class)->findOneBy(['email' => 'boss@example.com']);
        $this->assertCount(1, $foundManager->getManagedTeams());
        $this->assertSame('The A-Team', $foundManager->getManagedTeams()->first()->getName());
    }
}
