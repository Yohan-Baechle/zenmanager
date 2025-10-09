<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserConstructor(): void
    {
        // Create a new User instance with default values
        $user = new User();

        // Assert that the email, first name, last name, and role are null
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getFirstName());
        $this->assertNull($user->getLastName());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUserSettersAndGetters(): void
    {
        // Create a new User instance
        $user = new User();

        // Set values for email, first name, last name, and role
        $user->setEmail('test@example.com')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setRole('user');

        // Assert that the values are set correctly
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }
}