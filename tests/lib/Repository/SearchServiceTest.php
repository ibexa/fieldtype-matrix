<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\FieldTypeMatrix\Repository;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\FieldTypeMatrix\FieldType\Value;
use Ibexa\FieldTypeMatrix\FieldType\Value\Row;
use Ibexa\Tests\Integration\Core\Repository\BaseTestCase;

final class SearchServiceTest extends BaseTestCase
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContentWithMatrixFieldType(): void
    {
        $content = $this->createAndPublishContentWithMatrixFieldType();
        $searchService = $this->getRepository()->getSearchService();

        $searchResults = $searchService->findContent(
            new Query([
                'query' => new Query\Criterion\FullText('Foo'),
            ])
        );

        self::assertEquals(1, $searchResults->totalCount);
        self::assertEquals($content->id, $searchResults->searchHits[0]->valueObject->id);
    }

    private function createAndPublishContentWithMatrixFieldType(): Content
    {
        $contentType = $this->createContentTypeWithMatrixFieldType();

        $contentService = $this->getRepository()->getContentService();
        $locationService = $this->getRepository()->getLocationService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreateStruct->setField('title', 'Content with table');
        $contentCreateStruct->setField('table', new Value([
            new Row([
                'foo' => 'Foo',
                'bar' => 'Bar',
                'baz' => 'Baz',
            ]),
        ]));

        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $content = $contentService->createContent(
            $contentCreateStruct,
            [
                $locationService->newLocationCreateStruct(2),
            ]
        );

        return $contentService->publishVersion($content->getVersionInfo());
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function createContentTypeWithMatrixFieldType(): ContentType
    {
        $identifier = 'content_with_table';
        $repository = $this->getRepository();

        $contentTypeService = $repository->getContentTypeService();
        $permissionResolver = $repository->getPermissionResolver();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct($identifier);
        $typeCreate->mainLanguageCode = 'eng-GB';
        $typeCreate->urlAliasSchema = 'url|scheme';
        $typeCreate->nameSchema = 'name|scheme';
        $typeCreate->names = [
            'eng-GB' => 'Table: ' . $identifier,
        ];

        $typeCreate->creatorId = $this->generateId('user', $permissionResolver->getCurrentUserReference()->getUserId());
        $typeCreate->creationDate = $this->createDateTime();

        $titleFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('title', 'ibexa_string');
        $titleFieldCreateStruct->names = [
            'eng-GB' => 'Title',
        ];
        $titleFieldCreateStruct->fieldGroup = 'default';
        $titleFieldCreateStruct->position = 1;
        $titleFieldCreateStruct->isTranslatable = false;
        $titleFieldCreateStruct->isRequired = true;
        $titleFieldCreateStruct->isInfoCollector = false;
        $titleFieldCreateStruct->fieldSettings = [];
        $titleFieldCreateStruct->isSearchable = true;
        $titleFieldCreateStruct->defaultValue = '';

        $tableFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('table', 'ibexa_matrix');
        $tableFieldCreateStruct->names = [
            'eng-GB' => 'Table',
        ];
        $tableFieldCreateStruct->fieldGroup = 'default';
        $tableFieldCreateStruct->position = 2;
        $tableFieldCreateStruct->isTranslatable = false;
        $tableFieldCreateStruct->isRequired = true;
        $tableFieldCreateStruct->isInfoCollector = false;
        $tableFieldCreateStruct->isSearchable = true;
        $tableFieldCreateStruct->defaultValue = null;
        $tableFieldCreateStruct->fieldSettings = [
            'minimum_rows' => 1,
            'columns' => [
                [
                    'name' => 'Foo',
                    'identifier' => 'foo',
                ],
                [
                    'name' => 'Bar',
                    'identifier' => 'bar',
                ],
                [
                    'name' => 'Baz',
                    'identifier' => 'baz',
                ],
            ],
        ];

        $typeCreate->addFieldDefinition($titleFieldCreateStruct);
        $typeCreate->addFieldDefinition($tableFieldCreateStruct);

        $contentTypeDraft = $contentTypeService->createContentType($typeCreate, [
            $contentTypeService->loadContentTypeGroupByIdentifier('Content'),
        ]);

        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentTypeByIdentifier($identifier);
    }
}
