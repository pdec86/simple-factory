<?php

declare(strict_types=1);

namespace App\Common\Domain\Model\ValueObject;

use App\Common\Domain\Model\Traits\EanValidatorTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class CodeEan
{
    use EanValidatorTrait;

    #[ORM\Column(name: 'codeEAN', type: 'string', length: 13, nullable: false)]
    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function changeCode(string $code): self
    {
        return new self($code);
    }

    public function validate(): void
    {
        $this->validateEanCode($this->code);
    }
}
