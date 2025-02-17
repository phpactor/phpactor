<?php

namespace Phpactor\Extension\Symfony\Model;

use Generator;

final class FormTypeCompletionCache
{
    /**
    * @var array<string, FormTypeCompletion>
    */
    private static array $completions = [];

    private static function initializeCompletions()
    {
        if(self::$completions != []) {
            return;
        }

        self::$completions = [
            // Text fields
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\IntegerType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'grouping',
                'rounding_mode',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\MoneyType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'currency',
                'divisor',
                'grouping',
                'rounding_mode',
                'html5',
                'input',
                'scale',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\NumberType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'grouping',
                'html5',
                'input',
                'scale',
                'rounding_mode',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType', [
                'always_empty',
                'hash_property_path',
                'toggle',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\PercentType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'rounding_mode',
                'html5',
                'scale',
                'symbol',
                'type',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\SearchType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\UrlType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType', [
                'default_protocol',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\RangeType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\TelType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\ColorType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType', [
                'html5',
            ]),

            // Choice fields
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'choices',
                'choice_attr',
                'choice_filter',
                'choice_label',
                'choice_loader',
                'choice_lazy',
                'choice_name',
                'choice_translation_domain',
                'choice_translation_parameters',
                'choice_value',
                'duplicate_preferred_choices',
                'expanded',
                'group_by',
                'multiple',
                'placeholder',
                'placeholder_attr',
                'preferred_choices',
                'separator',
                'separator_html',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\EnumType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'class'
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\EntityType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'choice_label',
                'class',
                'em',
                'query_builder',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\CountryType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'alpha3',
                'choice_translation_locale',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\LanguageType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'alpha3',
                'choice_self_translation',
                'choice_translation_locale',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\LocaleType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'choice_translation_locale',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\TimezoneType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'input',
                'intl',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\CurrencyType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'choice_translation_locale',
            ]),

            // Date and Time fields
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'days',
                'placeholder',
                'format',
                'html5',
                'input',
                'input_format',
                'model_timezone',
                'months',
                'view_timezone',
                'calendar',
                'widget',
                'years',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\DateIntervalType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'days',
                'placeholder',
                'hours',
                'input',
                'labels',
                'minutes',
                'months',
                'seconds',
                'weeks',
                'widget',
                'with_days',
                'with_hours',
                'with_invert',
                'with_minutes',
                'with_months',
                'with_seconds',
                'with_weeks',
                'with_years',
                'years',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\DateTimeType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'date_format',
                'date_label',
                'date_widget',
                'days',
                'placeholder',
                'format',
                'hours',
                'html5',
                'input',
                'input_format',
                'minutes',
                'model_timezone',
                'months',
                'seconds',
                'time_label',
                'time_widget',
                'view_timezone',
                'widget',
                'with_minutes',
                'with_seconds',
                'years',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\TimeType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'choice_translation_domain',
                'placeholder',
                'hours',
                'html5',
                'input',
                'input_format',
                'minutes',
                'model_timezone',
                'reference_date',
                'seconds',
                'view_timezone',
                'widget',
                'with_minutes',
                'with_seconds',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\BirthdayType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\WeekType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'placeholder',
                'html5',
                'input',
                'widget',
                'years',
                'weeks',
            ]),

            // Other fields
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'false_values',
                'value',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'multiple',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\RadioType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType'),

            // Symfony UX
            'Symfony\\UX\\Cropperjs\\Form\\CropperType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'public_url',
                'cropper_options',
            ]),
            'Symfony\\UX\\Dropzone\\Form\\DropzoneType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType'),

            // UID Fields
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\UuidType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\UlidType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType'),

            // Field groups
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'allow_add',
                'allow_delete',
                'delete_empty',
                'entry_options',
                'prototype_options',
                'entry_type',
                'keep_as_list',
                'prototype',
                'prototype_data',
                'prototype_name',
            ]),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType', [
                'first_name',
                'first_options',
                'options',
                'second_name',
                'second_options',
                'type',
            ]),

            // Hidden types
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType'),

            // Buttons
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType' => new FormTypeCompletion(null),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\ResetType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType'),
            'Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType' => new FormTypeCompletion('Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType', [
                'validate'
            ]),

            'Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType' => new FormTypeCompletion(null, [
                'action',
                'allow_extra_fields',
                'by_reference',
                'compound',
                'constraints',
                'data',
                'data_class',
                'empty_data',
                'is_empty_callback',
                'error_bubbling',
                'error_mapping',
                'extra_fields_message',
                'form_attr',
                'getter',
                'help',
                'help_attr',
                'help_html',
                'help_translation_parameters',
                'inherit_data',
                'invalid_message',
                'invalid_message_parameters',
                'label_attr',
                'label_format',
                'mapped',
                'method',
                'post_max_size_message',
                'property_path',
                'required',
                'setter',
                'trim',
                'validation_groups',
                'attr',
                'auto_intialize',
                'block_name',
                'block_prefix',
                'disabled',
                'label',
                'label_html',
                'row_attr',
                'translation_domain',
                'label_translation_parameters',
                'attr_translation_parameters',
                'priority',
            ]),
        ];
    }

    public static function complete(string $className): Generator
    {
        self::initializeCompletions();
        $completions = self::$completions[$className] ?? null;

        if($completions == null) {
            return true;
        }

        do {
            yield from $completions->getCompletions();
        } while($completions = self::$completions[$completions->parentClass] ?? null);
    }
}
