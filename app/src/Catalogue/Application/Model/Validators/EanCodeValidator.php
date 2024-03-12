<?php

declare(strict_types=1);

namespace App\Catalogue\Application\Model\Validators;

use App\Common\Domain\Model\Exceptions\InvalidEanException;
use App\Common\Domain\Model\Traits\EanValidatorTrait;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

#[\Attribute]
class EanCodeValidator extends ConstraintValidator
{
    use EanValidatorTrait;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EanCodeConstraint) {
            throw new UnexpectedTypeException($constraint, EanCodeConstraint::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof CodeEan) {
            throw new UnexpectedValueException($value, CodeEan::class);
        }

        try {
            $this->validateEanCode($value->getCode());
        } catch (InvalidEanException $ex) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value->getCode())
            ->addViolation();
        }
    }
}
