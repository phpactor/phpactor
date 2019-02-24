Templates
=========

Phpactor allows you to provide your own templates for some class and code generation.

<div class="alert alert-danger">
This section is incomplete
</div>

When generating a new class you can specify a variant:

```
$ phpactor class:new tests/Registry/GeneratorTest.php --variant=phpunit_test
```

Variants are registered in `.phpactor.yml`:

```yaml
code_transform.class_new.variants:
    "Phpunit test": phpunit_test
```

In order to create the above variant we need to create a template locally in
`.phpactor/templates` (note you can also create them globally in the XDG
directories, in a `templates` folder):

```twig
<?php

{# /path/to/project/.phpactor/templates/phpunit_test/SourceCode.php.twig #}
namespace {{ prototype.namespace }};

use PHPUnit\Framework\TestCase;

{% for class in prototype.classes %}
class {{ class.name }} extends TestCase
{
}
{% endfor %}
```
