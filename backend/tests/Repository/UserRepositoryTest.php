<?php

namespace App\Tests\Repository;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private \Doctrine\ORM\EntityManagerInterface $em;
    private \App\Repository\UserRepository $repo;

    /**
     * Boot Symfony’s kernel and get access to Doctrine + repo
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->repo = $this->em->getRepository(User::class);
    }

    /**
     * This test ensures that saving and retrieving a User via Doctrine works as expected.
     * We persist, flush, clear, then fetch again to simulate a true DB roundtrip.
     */
    public function testPersistAndRetrieveUser(): void
    {
        $user = new User();
        $user->setUsername('IntegrationMan')
             ->setEmail('intman@example.com')
             ->setFirstName('Integration')
             ->setLastName('Tester')
             ->setPassword('hashed_pass')
             ->setRoles(['ROLE_EMPLOYEE']);

        $this->em->persist($user);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repo->findOneBy(['email' => 'intman@example.com']);

        $this->assertNotNull($found);
        $this->assertSame('IntegrationMan', $found->getUsername());
        $this->assertSame('intman@example.com', $found->getEmail());
    }

    /**
     * Clean up Doctrine between tests to avoid leaks or stale connections.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->em !== null) {
            $this->em->close();
        }
    }
}
