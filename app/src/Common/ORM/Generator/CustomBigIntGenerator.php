<?php

declare(strict_types=1);

namespace App\Common\ORM\Generator;

use App\Common\Domain\Model\CustomBigIntSequence;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

class CustomBigIntGenerator extends AbstractIdGenerator
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function generateId(EntityManagerInterface $em, $entity): string
    {
        $defaultEntityManager = $this->registry->getManager($this->registry->getDefaultManagerName());
        /** @var ClassMetadata $customSequenceMetadata */
        $customSequenceMetadata = $defaultEntityManager->getClassMetadata(CustomBigIntSequence::class);
        $sequenceTable = new Identifier($customSequenceMetadata->getTableName());
        $sequenceColIncrement = new Identifier($customSequenceMetadata->getColumnName('sequenceIncrement'));
        $sequenceColName = new Identifier($customSequenceMetadata->getColumnName('sequenceName'));
        $sequenceColMinValue = new Identifier($customSequenceMetadata->getColumnName('sequenceMinValue'));
        // $sequenceColMaxValue = new Identifier($customSequenceMetadata->getColumnName('sequenceMaxValue'));
        $sequenceColCurrentValue = new Identifier($customSequenceMetadata->getColumnName('sequenceCurrentValue'));
        $sequenceColCreatedAt = new Identifier($customSequenceMetadata->getColumnName('createdAt'));
        $sequenceColUpdatedAt = new Identifier($customSequenceMetadata->getColumnName('updatedAt'));

        $entityMetadata = $em->getClassMetadata(get_class($entity));
        $sequenceName = new Identifier($entityMetadata->getTableName());

        /** @var Connection $sequenceConn */
        $sequenceConn = $defaultEntityManager->getConnection();
        $sequencePlatform = $sequenceConn->getDatabasePlatform();

        $value = null;
        $sequenceConn->beginTransaction();

        try {
            $sql = 'SELECT ' . $sequenceColCurrentValue->getQuotedName($sequencePlatform)
                . ', ' . $sequenceColIncrement->getQuotedName($sequencePlatform)
                . ' FROM ' . $sequencePlatform->appendLockHint($sequenceTable->getQuotedName($sequencePlatform), LockMode::PESSIMISTIC_WRITE)
                . ' WHERE ' . $sequenceColName->getQuotedName($sequencePlatform) . ' = ? ' . $sequencePlatform->getWriteLockSQL();
            
            $row = $sequenceConn->fetchAssociative($sql, [$sequenceName->getQuotedName($sequencePlatform)]);

            if (false == $row) {
                $value = '1';

                $affectedRows = $sequenceConn->insert(
                    $sequenceTable->getQuotedName($sequencePlatform), [
                        $sequenceColName->getQuotedName($sequencePlatform) => $sequenceName->getQuotedName($sequencePlatform),
                        $sequenceColMinValue->getQuotedName($sequencePlatform) => '1',
                        $sequenceColCurrentValue->getQuotedName($sequencePlatform) => $value,
                        $sequenceColIncrement->getQuotedName($sequencePlatform) => '1',
                        $sequenceColCreatedAt->getQuotedName($sequencePlatform) => (new DateTimeImmutable())->format(DATE_ATOM),
                    ],
                );

                if ($affectedRows != 1) {
                    throw new \Exception('Race-condition detected while updating sequence. Aborting generation.');
                }
            } else {
                $value = $row[$sequenceColCurrentValue->getQuotedName($sequencePlatform)];
                $this->checkIsValidNumber($value);
                $incrementBy = $row[$sequenceColIncrement->getQuotedName($sequencePlatform)];
                $this->checkIsValidNumber($incrementBy);

                $value = bcadd($value, $incrementBy);
                $this->checkIsValidNumber($value);

                $sql  = 'UPDATE ' . $sequenceTable->getQuotedName($sequencePlatform) . ' ' .
                       'SET '
                       . $sequenceColCurrentValue->getQuotedName($sequencePlatform) . ' = ?, '
                       . $sequenceColUpdatedAt->getQuotedName($sequencePlatform) . ' = ? '
                       . 'WHERE ' . $sequenceColName->getQuotedName($sequencePlatform) . ' = ? '
                       . ' AND ' . $sequenceColCurrentValue->getQuotedName($sequencePlatform) .  ' = ?';

                $affectedRows = $sequenceConn->executeStatement($sql, [
                    $value,
                    (new DateTimeImmutable())->format(DATE_ATOM),
                    $sequenceName->getQuotedName($sequencePlatform),
                    $row[$sequenceColCurrentValue->getQuotedName($sequencePlatform)],
                ]);

                if ($affectedRows !== 1) {
                    throw new \Exception('Race-condition detected while updating sequence. Aborting generation.');
                }
            }

            $sequenceConn->commit();
        } catch (\Throwable $ex) {
            $sequenceConn->rollBack();
            throw new \Exception('Aborting ID generation.');
        }

        return $value;
    }

    private function checkIsValidNumber(?string $value): void
    {
        if (null !== $value && preg_match('/\d+/', $value) == 0) { // matches 0 or false
            throw new \LogicException('Value, if not null, then should be a valid numeric string.');
        }

        if (null !== $value && strlen($value) > 255) {
            throw new \LogicException('Too long numeric string.');
        }
    }
}
