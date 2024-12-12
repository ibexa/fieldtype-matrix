<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatrixEntryType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezmatrix_entry';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['columns']);
        $resolver->addAllowedTypes('columns', 'array');

        $resolver->setDefaults([
            'label' => false,
        ]);

        parent::configureOptions($resolver);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['columns'] = $options['columns'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['columns'] as $column) {
            $builder->add($column['identifier'], TextType::class, [
                'label' => false,
                'block_name' => 'cell',
            ]);
        }

        parent::buildForm($builder, $options);
    }
}

class_alias(MatrixEntryType::class, 'EzSystems\EzPlatformMatrixFieldtype\Form\Type\FieldType\MatrixEntryType');
