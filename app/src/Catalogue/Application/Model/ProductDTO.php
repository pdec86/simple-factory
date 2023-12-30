<?php

declare(strict_types=1);

namespace App\Catalogue\Application\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    public readonly ?int $id;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 255)]
    public readonly string $name;

    public readonly ?string $description;
    
    public function __construct(
        ?int $id,
        string $name,
        ?string $description = null,
    ) {
        $this->id = $id;
        $this->name = $name;

        if (null !== $description) {
            $description = trim($description);
            
            if (0 === mb_strlen($description, 'UTF-8')) {
                $description = null;
            }
        }

        $this->description = $description;
    }
}