# maximaster/jaft

Universal JSON:API
[league/fractal](https://packagist.org/packages/league/fractal) transformer for
your objects.

```bash
composer require maximaster/jaft
```

## Why?

According to [league/fractal](https://packagist.org/packages/league/fractal)
manual page about [Transformers](https://fractal.thephpleague.com/transformers/)
you should create a transformer per object type. That's a lot of routine work.

Or just configure universal `Maximaster\Jaft\Jaft` transformer and you are ready
to go.

## Example

**services.yaml**

```yaml
services:
    Maximaster\Jaft\Contract\Mapper:
        alias: 'api_mapper'

    api_mapper:
        class: Maximaster\Jaft\Mapper\DefaultMapper
        arguments:
            - '@api_output_manager'
            - '@api_transformer'
            - '@api_naming_provider'

    api_output_manager:
        class: League\Fractal\Manager
        arguments:
            - '@api_scope_factory'
        calls:
            -   setSerializer: [ '@api_serializer' ]

    api_serializer:
        class: Maximaster\Jaft\Serializer\JsonApiSerializer
        arguments:
            - ''
            - null

    api_scope_factory:
        class: League\Fractal\ScopeFactory

    api_transformer:
        class: Maximaster\Jaft\Jaft
        arguments:
            - '@api_schema_repository'
            - '@api_data_extractor'
            - '@api_naming_provider'
            - '@api_formatter'
            - null

    api_schema_repository:
        class: Maximaster\Jaft\SchemaRepository\PropertyInfoSchemaRepository
        arguments:
            - '@api_property_info_extractor'
            - '@api_naming_provider'
            - '@api_identity_locator'
            - '@api_type_converter'
            -
                - 'Project\Process\UserManagement\Domain\ValueObject\UserTimestamp'
                - 'Project\Process\DatabaseManagement\Domain\Entry\Field\NativeValueMap'
            -
                - 'Bitrix\Main\Type\DateTime'

    api_property_info_extractor:
        class: Symfony\Component\PropertyInfo\PropertyInfoExtractor
        arguments:
            - [ '@api_static_property_lister', '@api_packed_embeddable_property_lister', '@api_default_property_lister' ]
            - [ '@api_packed_embeddable_type_extractor', '@Maximaster\Jaft\PropertyTypeExtractor\ReplacedPropertyTypeExtractor', '@api_property_phpdoc_extractor', '@api_property_reflection_extractor' ]
            - [ '@api_property_phpdoc_extractor', '@api_property_reflection_extractor' ]
            - [ '@api_property_phpdoc_extractor', '@api_property_reflection_extractor' ]
            - [ '@api_property_phpdoc_extractor', '@api_property_reflection_extractor' ]

    api_static_property_lister:
        class: Maximaster\Jaft\PropertyListExtractor\StaticPropertyListExtractor
        arguments:
            - Project\Process\System\Domain\ValueObject\EntityRef: ['id']
              Project\Process\System\Domain\ValueObject\LabeledEntityRef: ['id', 'label']
              Project\Process\System\Domain\Contract\Ref: ['id']

    Maximaster\Jaft\PropertyTypeExtractor\ReplacedPropertyTypeExtractor:
        arguments:
            $realExtractor: '@api_property_phpdoc_extractor'
            $map: '%orm_entity_map%'

    api_packed_embeddable_property_lister:
        class: Maximaster\Jaft\PropertyListExtractor\PackedEmbeddablePropertyListExtractor

    api_default_property_lister:
        class: Maximaster\Jaft\PropertyListExtractor\AccessorPropertyListExtractor

    api_packed_embeddable_type_extractor:
        class: Maximaster\Jaft\PropertyTypeExtractor\PackedEmbeddablePropertyTypeExtractor

    api_property_phpdoc_extractor:
        class: Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor
        arguments:
            - null
            - null
            - [ '' ]

    api_property_reflection_extractor:
        class: Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor
        arguments:
            - [ ]
            - [ '' ]

    api_naming_provider:
        class: Project\Process\System\IO\OpenApi\OpenApiNamingProvider
        arguments:
            - '@api_fcqn_naming_provider'
            - !tagged_iterator project.open_api.overlay_naming_provider

    api_fcqn_naming_provider:
        class: Maximaster\Jaft\NamingProvider\FqcnNamingProvider
        arguments:
            - '%%3$s-%%s'
            - '@api_type_case_converter'

    api_identity_locator:
        class: Project\Process\System\IO\OpenApi\IdentityLocator\EntityIdentityLocator

    api_type_converter:
        class: Maximaster\Jaft\TypeConverter\PropertyInfoTypeConverter

    api_type_case_converter:
        class: Maximaster\Jaft\CaseConverter\JawiraCaseConverter
        arguments:
            - 'pascal'
            - 'kebab'

    api_data_extractor:
        class: Maximaster\Jaft\DataExtractor\PropertyAccessorDataExtractor
        arguments:
            - '@api_schema_repository'
            - '@api_property_accessor'

    api_property_accessor:
        class: Project\Process\System\IO\OpenApi\PropertyAccessor\CacheablePropertyAccessor
        arguments:
            $realAccessor: '@api_property_accessor_worker'

    api_property_accessor_worker:
        class: Project\Process\System\IO\OpenApi\PropertyAccessor\GetterPropertyAccessor
        arguments:
            $fallback: '@api_property_accessor_fallback'

    api_property_accessor_fallback:
        class: Symfony\Component\PropertyAccess\PropertyAccessor
        arguments:
            - !php/const Symfony\Component\PropertyAccess\PropertyAccessor::MAGIC_GET
            - false
            - null
            - true
            - '@api_property_reflection_extractor'

    api_formatter:
        class: Maximaster\Jaft\ObjectFormatter\AtoaObjectFormatter
        arguments:
            - '@api_formatter_converter'

    api_formatter_converter:
        class: Maximaster\Atoa\Converter
        arguments:
            - !tagged_iterator project.open_api.formatter

    api_property_name_converter:
        class: Maximaster\Jaft\CaseConverter\JawiraCaseConverter
        arguments:
            - 'auto'
            - 'camel'
```
