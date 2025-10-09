<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use DateTimeImmutable;

class UserTest extends TestCase
{
    
    public function testConstructorWithNullValues(): void
    {
        $user = new User();

        $this->assertNull($user->getEmail());
        $this->assertNull($user->getFirstName());
        $this->assertNull($user->getLastName());
        $this->assertNull($user->getPhoneNumber());
        $this->assertNull($user->getId());

        print_r($user->getCreatedAt());
        print_r($user->getUpdatedAt());

        $this->assertNotNull($user->getCreatedAt(), 'CreatedAt should not be null');
        $this->assertNotNull($user->getUpdatedAt(), 'UpdatedAt should not be null');

        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());

        // default role
        $this->assertContains('ROLE_USER', $user->getRoles());

        // test sets createdAt and updatedAt to (approximately) the same moment
        $this->assertEqualsWithDelta(
            $user->getCreatedAt()->getTimestamp(),
            $user->getUpdatedAt()->getTimestamp(),
            0.1 // allows 0.1 of latency 
        );
    }

    public function testSettersAndGetters(): void
    {
        $user = new User();

        $user->setEmail('test@example.com')
             ->setFirstName('John')
             ->setLastName('Doe')
             ->setPhoneNumber('0123456789')
             ->setRole('user')
             ->setPassword('plain-password');

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('John', $user->getFirstName());
        $this->assertSame('Doe', $user->getLastName());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertSame('0123456789', $user->getPhoneNumber());
        $this->assertSame('user', $user->getRole());
        $this->assertSame('plain-password', $user->getPassword());
    }

    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        // order doesn't matter; ensure both are present and unique
        $this->assertEqualsCanonicalizing(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();

        // when email is null, getUserIdentifier returns empty string (due to cast)
        $this->assertSame('', $user->getUserIdentifier());

        $user->setEmail('a@b.c');
        $this->assertSame('a@b.c', $user->getUserIdentifier());
    }

    #that test is currently a bit fragile, so it might break in the future 
    #(\0Class\0property is namespace implementation detail and
    #crc32c is just the hashing strategy, if any of these changes, this test will be broken)
    public function testSerializeHidesPassword(): void
    {
        $user = new User();
        $user->setPassword('secret');

        $data = $user->__serialize();

        $passwordKey = "\0" . User::class . "\0password";
        $this->assertArrayHasKey($passwordKey, $data);

        // the stored value should be the CRC32C hash, not the raw password
        $this->assertNotSame('secret', $data[$passwordKey]);
        $this->assertSame(hash('crc32c', 'secret'), $data[$passwordKey]);
    }

    public function testSetUpdatedAtValueUpdatesUpdatedAt(): void
    {
        $user = new User();

        // set updatedAt to a far past value using reflection so we don't depend on sleeping/waiting
        $ref = new ReflectionClass(User::class);
        $prop = $ref->getProperty('updatedAt');
        $prop->setAccessible(true);
        $old = new DateTimeImmutable('2000-01-01');
        $prop->setValue($user, $old);

        $user->setUpdatedAtValue();

        $this->assertGreaterThan($old->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
    }

    #uncomment that later when there will be actual checks number format
    /*public function testNotANumberInPhoneNumber():void
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User();
        $user->setPhoneNumber('not-a-number');
    }   */
}