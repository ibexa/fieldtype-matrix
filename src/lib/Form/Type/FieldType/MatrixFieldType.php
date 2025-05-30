<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Form\Type\FieldType;

use Ibexa\FieldTypeMatrix\FieldType\Value\Row;
use Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection;
use Ibexa\FieldTypeMatrix\Form\Transformer\FieldTypeModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-extends \Symfony\Component\Form\AbstractType<array{entries: array<int, array<string, mixed>>}>
 */
class MatrixFieldType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_matrix';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['columns', 'minimum_rows']);
        $resolver->addAllowedTypes('columns', 'array');
        $resolver->addAllowedTypes('minimum_rows', 'integer');
        $resolver->setDefault('translation_domain', 'ibexa_matrix_fieldtype');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['columns'] = $options['columns'];
        $view->vars['minimum_rows'] = $options['minimum_rows'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entries', MatrixCollectionType::class, [
                'columns' => $options['columns'],
                'minimum_rows' => $options['minimum_rows'],
                'entry_options' => [
                    'columns' => $options['columns'],
                ],
            ]);

        $columnsByIdentifier = array_flip(array_column($options['columns'], 'identifier'));

        // Filter out unnecessary/obsolete columns data
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($columnsByIdentifier): void {
            $value = $event->getData();

            /** @var \Ibexa\FieldTypeMatrix\FieldType\Value\Row $originalRow */
            foreach ($value->getRows() as $originalRow) {
                $cells = $originalRow->getCells();
                $rows[] = new Row(array_intersect_key($cells, $columnsByIdentifier));
            }

            $value->setRows(new RowsCollection($rows ?? []));
        });

        $builder->addModelTransformer(new FieldTypeModelTransformer());
    }
}
