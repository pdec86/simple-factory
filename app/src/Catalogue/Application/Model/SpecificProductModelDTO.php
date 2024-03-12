<?php

declare(strict_types=1);

namespace App\Catalogue\Application\Model;

use App\Catalogue\Application\Model\Validators\EanCodeConstraint;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Symfony\Component\Validator\Constraints as Assert;

class SpecificProductModelDTO
{
    public readonly ?string $id;

    #[Assert\NotNull()]
    public readonly ?int $productId;

    #[Assert\NotBlank()]
    #[EanCodeConstraint()]
    public readonly CodeEan $codeEan;

    #[Assert\NotBlank()]
    public readonly string $name;

    #[Assert\NotBlank()]
    public readonly string $length;

    #[Assert\NotBlank()]
    public readonly string $width;

    #[Assert\NotBlank()]
    public readonly string $height;
    
    public function __construct(
        ?string $id,
        int $productId,
        CodeEan $codeEan,
        string $name,
        string $length,
        string $width,
        string $height,
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->codeEan = $codeEan;
        $this->name = $name;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
    }
}