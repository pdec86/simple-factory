<?php

declare(strict_types=1);

namespace App\Common\DBAL\Types;

use App\Common\Domain\Model\Interfaces\IdentifierInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class CustomBigIntId extends Type
{
    abstract protected function castToClass(string $id);

    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getStringTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?IdentifierInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $this->checkIsValidNumber($value);

        return $this->castToClass($value);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        $dbValue = (string) $value->getValue();
        $this->checkIsValidNumber($dbValue);

        return $dbValue;
    }

    private function checkIsValidNumber(?string $value): void
    {
        if (null !== $value && preg_match('/^\d+$/', $value) == 0) { // matches 0 or false
            throw new \LogicException('Value, if not null, then should be a valid numeric string.');
        }

        if (null !== $value && strlen($value) > 255) {
            throw new \LogicException('Too long numeric string.');
        }
    }
}