<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-extends \Symfony\Component\Form\AbstractType<array<int, array<string, mixed>>>
 */
class MatrixCollectionType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_matrix_collection';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['columns', 'minimum_rows']);
        $resolver->addAllowedTypes('columns', 'array');
        $resolver->addAllowedTypes('minimum_rows', 'integer');

        $resolver->setDefaults([
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => MatrixEntryType::class,
            'prototype' => true,
            'prototype_name' => '__index__',
        ]);

        parent::configureOptions($resolver);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['columns'] = $options['columns'];
        $view->vars['minimum_rows'] = $options['minimum_rows'];
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
