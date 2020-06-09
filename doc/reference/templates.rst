.. _template_variants:

Templates
=========

Phpactor allows you to provide your own templates for some class and
code generation.

When :ref:`generating a new class <generation_class_new>` you can specify a **variant**.

Variants are registered in ``.phpactor.yml`` with
:ref:`param_code_transform.class_new.variants`:

.. code:: yaml

   code_transform.class_new.variants:
       unit: phpunit_test

This will make the variant ``unit`` available.

Implement the templates by placing template files in
``.phpactor/templates/phpunit_test``:

.. code:: twig

   <?php

   {# /path/to/project/.phpactor/templates/phpunit_test/SourceCode.php.twig #}
   namespace {{ prototype.namespace }};

   use PHPUnit\Framework\TestCase;

   {% for class in prototype.classes %}
   class {{ class.name }} extends TestCase
   {
   }
   {% endfor %}

.. note::

    You can also create templates globally in the XDG directories, in a
    ``templates`` folder - e.g. ``$HOME/.config/phpactor/templates``:

