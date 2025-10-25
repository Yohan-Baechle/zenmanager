<?php

namespace App\Tests\Entity;

use App\Entity\Clock;
use App\Entity\ClockRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ClockRequestTest extends TestCase
{
    public function testConstructorInitializesValues(): void
    {
        $request = new ClockRequest();

        $this->assertNull($request->getId());
        $this->assertNull($request->getUser());
        $this->assertNull($request->getType());
        $this->assertNull($request->getRequestedTime());
        $this->assertNull($request->getRequestedStatus());
        $this->assertNull($request->getTargetClock());
        $this->assertNull($request->getReason());
        $this->assertNull($request->getReviewedBy());
        $this->assertNull($request->getReviewedAt());
        $this->assertNull($request->getCreatedAt());
        $this->assertNull($request->getUpdatedAt());
        $this->assertSame('PENDING', $request->getStatus());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $user = new User();
        $reviewer = new User();
        $clock = new Clock();
        $time = new \DateTimeImmutable('2025-01-01 10:00:00');
        $reviewedAt = new \DateTimeImmutable('2025-01-02 15:00:00');

        $request = (new ClockRequest())
            ->setUser($user)
            ->setType('CREATE')
            ->setRequestedTime($time)
            ->setRequestedStatus(true)
            ->setTargetClock($clock)
            ->setStatus('APPROVED')
            ->setReason('Correction of missing clock-in due to technical issue.')
            ->setReviewedBy($reviewer)
            ->setReviewedAt($reviewedAt);

        $this->assertSame($user, $request->getUser());
        $this->assertSame('CREATE', $request->getType());
        $this->assertSame($time, $request->getRequestedTime());
        $this->assertTrue($request->getRequestedStatus());
        $this->assertSame($clock, $request->getTargetClock());
        $this->assertSame('APPROVED', $request->getStatus());
        $this->assertSame('Correction of missing clock-in due to technical issue.', $request->getReason());
        $this->assertSame($reviewer, $request->getReviewedBy());
        $this->assertSame($reviewedAt, $request->getReviewedAt());
    }

    /**
     * Vérifie que les valeurs de status sont bien gérées.
     */
    public function testStatusCanBeSetAndRetrieved(): void
    {
        $request = new ClockRequest();

        $request->setStatus('APPROVED');
        $this->assertSame('APPROVED', $request->getStatus());

        $request->setStatus('REJECTED');
        $this->assertSame('REJECTED', $request->getStatus());

        $request->setStatus('PENDING');
        $this->assertSame('PENDING', $request->getStatus());

        $this->expectException(\InvalidArgumentException::class);
        $request->setStatus('BLAFEU_BLAFEU_BLAFEU');
    }

    /**
     * Vérifie que les types de requêtes peuvent être définis correctement.
     */
    public function testTypeCanBeSetAndRetrieved(): void
    {
        $request = new ClockRequest();

        foreach (['CREATE', 'UPDATE', 'DELETE'] as $type) {
            $request->setType($type);
            $this->assertSame($type, $request->getType());
        }
    }

    /**
     * Vérifie que la date de création et de mise à jour sont bien définies via les callbacks.
     */
    public function testLifecycleCallbacksSetTimestamps(): void
    {
        $request = new ClockRequest();

        $this->assertNull($request->getCreatedAt());
        $this->assertNull($request->getUpdatedAt());

        $request->setCreatedAtValue();

        $this->assertInstanceOf(\DateTimeImmutable::class, $request->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $request->getUpdatedAt());

        $initialUpdatedAt = $request->getUpdatedAt();
        sleep(1);
        $request->setUpdatedAtValue();

        $this->assertNotEquals($initialUpdatedAt, $request->getUpdatedAt());
    }

    /**
     * Vérifie que reviewedBy et reviewedAt peuvent être remis à null.
     */
    public function testReviewedFieldsCanBeUnset(): void
    {
        $request = new ClockRequest();
        $reviewer = new User();
        $time = new \DateTimeImmutable();

        $request->setReviewedBy($reviewer);
        $request->setReviewedAt($time);

        $this->assertSame($reviewer, $request->getReviewedBy());
        $this->assertSame($time, $request->getReviewedAt());

        $request->setReviewedBy(null);
        $request->setReviewedAt(null);

        $this->assertNull($request->getReviewedBy());
        $this->assertNull($request->getReviewedAt());
    }

    /**
     * Vérifie que requestedStatus peut être null, true, ou false.
     */
    public function testRequestedStatusCanBeBooleanOrNull(): void
    {
        $request = new ClockRequest();

        $request->setRequestedStatus(true);
        $this->assertTrue($request->getRequestedStatus());

        $request->setRequestedStatus(false);
        $this->assertFalse($request->getRequestedStatus());

        $request->setRequestedStatus(null);
        $this->assertNull($request->getRequestedStatus());
    }

    /**
     * Vérifie la validité du champ "reason" avec différentes longueurs.
     */
    public function testReasonValidationLogic(): void
    {
        $request = new ClockRequest();

        $valid = str_repeat('a', 10);
        $request->setReason($valid);
        $this->assertSame($valid, $request->getReason());

        $max = str_repeat('b', 1000);
        $request->setReason($max);
        $this->assertSame($max, $request->getReason());
    }

    /**
     * Vérifie que la relation Clock peut être nullifiée.
     */
    public function testTargetClockCanBeNull(): void
    {
        $clock = new Clock();
        $request = new ClockRequest();

        $request->setTargetClock($clock);
        $this->assertSame($clock, $request->getTargetClock());

        $request->setTargetClock(null);
        $this->assertNull($request->getTargetClock());
    }
}
