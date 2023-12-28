<?php

declare(strict_types=1);

namespace App\Common\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'fd_customBigIntSequence')]
#[ORM\Entity()]
class CustomBigIntSequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'sequenceId', type: 'integer', nullable: false, options: ["unsigned" => true])]
    private int $sequenceId;

    #[ORM\Column(name: 'sequenceName', type: 'string', length: 255, nullable: false)]
    private string $sequenceName;

    #[ORM\Column(name: 'sequenceIncrement', type: 'text', nullable: false)]
    private string $sequenceIncrement = '1';

    #[ORM\Column(name: 'sequenceMinValue', type: 'text', nullable: false)]
    private string $sequenceMinValue = '1';

    #[ORM\Column(name: 'sequenceMaxValue', type: 'text', nullable: true)]
    private ?string $sequenceMaxValue = null;

    #[ORM\Column(name: 'sequenceCurrentValue', type: 'text', nullable: false)]
    private string $sequenceCurrentValue;

    #[ORM\Column(name: 'createdAt', type: 'datetime_immutable', updatable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updatedAt', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;
}
