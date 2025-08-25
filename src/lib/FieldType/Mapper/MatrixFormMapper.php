<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\FieldTypeMatrix\Form\Type\ColumnType;
use Ibexa\FieldTypeMatrix\Form\Type\FieldType\MatrixFieldType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;

final readonly class MatrixFormMapper implements FieldDefinitionFormMapperInterface, FieldValueFormMapperInterface
{
    /**
     * "Maps" FieldDefinition form to current FieldType.
     * Gives the opportunity to enrich $fieldDefinitionForm with custom fields for:sM,
     * - validator configuration,
     * - field settings
     * - default value.
     */
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('minimum_rows', IntegerType::class, [
                'required' => false,
                'property_path' => 'fieldSettings[minimum_rows]',
                'label' => /** @Desc("Minimum number of rows") */ 'field_definition.ibexa_matrix.minimum_rows',
                'translation_domain' => 'ibexa_matrix_fieldtype',
                'disabled' => $isTranslation,
            ])
            ->add('columns', CollectionType::class, [
                'entry_type' => ColumnType::class,
                'entry_options' => ['required' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => false,
                'prototype' => true,
                'prototype_name' => '__number__',
                'required' => false,
                'property_path' => 'fieldSettings[columns]',
                'label' => false,
                'translation_domain' => 'ibexa_matrix_fieldtype',
                'disabled' => $isTranslation,
            ]);
    }

    /**
     * Maps Field form to current FieldType.
     * Allows to add form fields for content edition.
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $fieldSettings = $fieldDefinition->getFieldSettings();
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        MatrixFieldType::class,
                        [
                            'label' => $fieldDefinition->getName(),
                            'required' => $fieldDefinition->isRequired,
                            'columns' => $fieldSettings['columns'],
                            'minimum_rows' => $fieldSettings['minimum_rows'],
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
