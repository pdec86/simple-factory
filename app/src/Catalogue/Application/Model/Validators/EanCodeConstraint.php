<?php

declare(strict_types=1);

namespace App\Catalogue\Application\Model\Validators;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class EanCodeConstraint extends Constraint
{
    public string $message = 'Provided "{{ string }}" is not a valid EAN code.';
    public string $mode = 'strict';

    public function __construct(string $mode = null, string $message = null, array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return EanCodeValidator::class;
    }
}
