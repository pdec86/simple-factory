<?php

declare(strict_types=1);

namespace App\Common\Domain\Model\Traits;

use App\Common\Domain\Model\Exceptions\InvalidEanException;

trait EanValidatorTrait
{
    private function validateEanCode(string $code): void
    {
        // Must be a string
        if (!is_string($code)) {
            throw new InvalidEanException("Provided code is not a string.");
        }
        
        // Must consist of 13 (EAN) digits
        if (!preg_match('/^(\d{8}|\d{13})$/', $code)) {
            throw new InvalidEanException("Provided code must have 8 or 13 digits.");
        }

        $sum = 0;
        $multiplyByThree = true;
        $lastDigitIndex = strlen($code) - 1;
        $checkDigit = (int) ($code[$lastDigitIndex]);

        // reverse the actual digits (excluding the check digit)
        $reversedCode = strrev(substr($code, 0, $lastDigitIndex));

        for ($i = 0; $i < strlen($reversedCode); $i++) {
            if ($multiplyByThree) {
                $sum += ((int) $reversedCode[$i]) * 3;
            }
            else
            {
                $sum += (int) $reversedCode[$i];
            }

            $multiplyByThree = !$multiplyByThree;
        }

        if (($sum + $checkDigit) % 10 !== 0) {
            throw new InvalidEanException("Invalid EAN code. Checksum does not match.");
        }
    }
}
