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

    }
}