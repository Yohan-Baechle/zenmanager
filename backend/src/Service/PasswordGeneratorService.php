<?php

namespace App\Service;

/**
 * Service for generating passwords compliant with ANSSI recommendations
 * Length: 16 characters
 * Mix of uppercase, lowercase, digits, and special characters
 */
class PasswordGeneratorService
{
    private const PASSWORD_LENGTH = 16;
    private const LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    private const UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const DIGITS = '0123456789';
    private const SPECIAL_CHARS = '!@#$%&*-_+=?';

    public function generate(): string
    {
        $password = [
            $this->getRandomChar(self::LOWERCASE),
            $this->getRandomChar(self::UPPERCASE),
            $this->getRandomChar(self::DIGITS),
            $this->getRandomChar(self::SPECIAL_CHARS),
        ];

        $allChars = self::LOWERCASE . self::UPPERCASE . self::DIGITS . self::SPECIAL_CHARS;
        $remainingLength = self::PASSWORD_LENGTH - count($password);

        for ($i = 0; $i < $remainingLength; $i++) {
            $password[] = $this->getRandomChar($allChars);
        }

        shuffle($password);

        return implode('', $password);
    }

    private function getRandomChar(string $chars): string
    {
        $max = strlen($chars) - 1;
        $index = random_int(0, $max);

        return $chars[$index];
    }
}
