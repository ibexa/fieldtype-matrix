<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\FieldTypeMatrix\Command;

use Doctrine\DBAL\Connection;
use Exception;
use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\Gateway;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;
use Ibexa\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;
use Ibexa\FieldTypeMatrix\FieldType\Converter\MatrixConverter;
use SimpleXMLElement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ibexa:migrate:legacy_matrix')]
class MigrateLegacyMatrixCommand extends Command
{
    private const DEFAULT_ITERATION_COUNT = 1000;
    private const EZMATRIX_IDENTIFIER = 'ezmatrix';
    private const CONFIRMATION_ANSWER = 'yes';

    private Connection $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'iteration-count',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Number of matrix FieldType values fetched into memory and processed at once',
                self::DEFAULT_ITERATION_COUNT
            )->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Prevents confirmation dialog. Please use it carefully.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('force') !== true) {
            $io->caution('Read carefully. This operation is irreversible. Make sure you are using correct database and have backup.');
            $answer = $io->ask('Are you sure you want to start migration? (type "' . self::CONFIRMATION_ANSWER . '" to confirm)');

            if ($answer !== self::CONFIRMATION_ANSWER) {
                $io->comment('Canceled.');

                return Command::FAILURE;
            }
        }

        $io->comment('Migrating legacy ezmatrix fieldtype');

        $iterationCount = (int)$input->getOption('iteration-count');
        $converter = new MatrixConverter();

        $contentClassAttributes = $this->getContentClassAttributes();

        libxml_use_internal_errors(true);

        foreach ($contentClassAttributes as $contentClassAttribute) {
            $io->comment(sprintf('Migrate %s:%s attribute.', $contentClassAttribute['contenttype_identifier'], $contentClassAttribute['identifier']));

            try {
                $xml = new SimpleXMLElement((string)$contentClassAttribute['columns']);

                $isValidXml = true;
            } catch (Exception $e) {
                $isValidXml = false;
            }

            if ($isValidXml) {
                $columnList = $xml->xpath('//column-name');

                $columns = [];

                foreach ($columnList as $column) {
                    $columns[(int)$column['idx']] = [
                        'identifier' => (string)$column['id'],
                        'name' => (string)$column,
                    ];
                }

                $fieldDefinition = new FieldDefinition();
                $storageFieldDefinition = new StorageFieldDefinition();

                $fieldDefinition->fieldTypeConstraints->fieldSettings = [
                    'minimum_rows' => $contentClassAttribute['minimum_rows'],
                    'columns' => array_values($columns),
                ];

                $converter->toStorageFieldDefinition($fieldDefinition, $storageFieldDefinition);

                $this->updateContentClassAttribute(
                    (int)$contentClassAttribute['id'],
                    (int)$storageFieldDefinition->dataInt1,
                    (string)$storageFieldDefinition->dataText5
                );

                $columnsJson = $storageFieldDefinition->dataText5;
            } else {
                $columnsJson = $contentClassAttribute['columns'];
            }

            $contentAttributesCount = $this->getContentObjectAttributesCount(
                (int)$contentClassAttribute['id']
            );

            if ($contentAttributesCount === 0) {
                $io->comment(sprintf('Zero instances of %s:%s attribute to migrate.', $contentClassAttribute['contenttype_identifier'], $contentClassAttribute['identifier']));
                continue;
            }

            $columns = json_decode($columnsJson);

            $progressBar = $this->getProgressBar($contentAttributesCount, $output);
            $progressBar->start();

            for ($offset = 0; $offset <= $contentAttributesCount; $offset += $iterationCount) {
                gc_disable();

                $contentObjectAttributes = $this->getContentObjectAttributes(
                    (int)$contentClassAttribute['id'],
                    $offset,
                    $iterationCount
                );

                foreach ($contentObjectAttributes as $contentObjectAttribute) {
                    try {
                        $xml = new SimpleXMLElement(
                            (string)$contentObjectAttribute['data_text']
                        );
                    } catch (Exception $e) {
                        $progressBar->advance();

                        continue;
                    }

                    $storageFieldValue = new StorageFieldValue();
                    $fieldValue = new FieldValue([
                        'data' => [
                            'entries' => [],
                        ],
                    ]);

                    $rows = $this->convertCellsToRows($xml->xpath('c'), $columns);

                    $fieldValue->data['entries'] = $rows;

                    $converter->toStorageValue($fieldValue, $storageFieldValue);

                    $this->updateContentObjectAttribute(
                        (int)$contentObjectAttribute['id'],
                        (string)$storageFieldValue->dataText
                    );

                    $progressBar->advance();
                }

                gc_enable();
            }

            $progressBar->finish();

            $output->writeln(['', '']);
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }

    /**
     * @param array $cells
     * @param array $columns
     *
     * @return array
     */
    private function convertCellsToRows(array $cells, array $columns): array
    {
        $row = [];
        $rows = [];
        $columnsCount = \count($columns);

        foreach ($cells as $index => $cell) {
            $columnIndex = $index % $columnsCount;
            $columnIdentifier = $columns[$columnIndex]->identifier;

            $row[$columnIdentifier] = (string)$cell;

            if ($columnIndex === $columnsCount - 1) {
                $rows[] = $row;
                $row = [];
            }
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function getContentClassAttributes(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select([
                'attr.id',
                'attr.identifier',
                'attr.data_int1 as minimum_rows',
                'attr.data_text5 as columns',
                'class.identifier as contenttype_identifier',
            ])
            ->from(ContentTypeGateway::FIELD_DEFINITION_TABLE, 'attr')
            ->join('attr', ContentTypeGateway::CONTENT_TYPE_TABLE, 'class', 'class.id = attr.content_type_id')
            ->where('attr.data_type_string = :identifier')
            ->setParameter('identifier', self::EZMATRIX_IDENTIFIER);

        return $query->executeQuery()->fetchAllAssociative();
    }

    private function updateContentClassAttribute(int $id, int $minimumRows, string $columns): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(ContentTypeGateway::FIELD_DEFINITION_TABLE, 'attr')
            ->set('attr.data_int1', ':minimum_rows')
            ->set('attr.data_text5', ':columns')
            ->where('attr.id = :id')
            ->setParameter('id', $id)
            ->setParameter('minimum_rows', $minimumRows)
            ->setParameter('columns', $columns);

        $query->executeStatement();
    }

    private function getContentObjectAttributesCount(int $id): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('count(1)')
            ->from(Gateway::CONTENT_FIELD_TABLE, 'attr')
            ->where('attr.content_type_field_definition_id = :ct_field_def_id')
            ->setParameter('ct_field_def_id', $id);

        return (int)$query->executeQuery()->fetchOne();
    }

    /**
     * @return array
     */
    private function getContentObjectAttributes(int $id, int $offset, int $iterationCount): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(['id', 'data_text'])
            ->from(Gateway::CONTENT_FIELD_TABLE, 'attr')
            ->where('attr.content_type_field_definition_id = :ct_field_def_id')
            ->setParameter('ct_field_def_id', $id)
            ->setFirstResult($offset)
            ->setMaxResults($iterationCount);

        return $query->executeQuery()->fetchAllAssociative();
    }

    private function updateContentObjectAttribute(int $id, string $rows): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(Gateway::CONTENT_FIELD_TABLE, 'attr')
            ->set('attr.data_text', ':rows')
            ->where('attr.id = :id')
            ->setParameter('id', $id)
            ->setParameter('rows', $rows);

        $query->executeStatement();
    }

    protected function getProgressBar(int $maxSteps, OutputInterface $output): ProgressBar
    {
        $progressBar = new ProgressBar($output, $maxSteps);
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );

        return $progressBar;
    }
}
