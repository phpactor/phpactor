PHPStan
=======

`PHPStan <https://github.com/phpstan/phpstan>`_ is a static analysis tool that can be used to expose bugs in your PHP code.

Phpactor can integrate with PHPStan and provide diagnostics in your IDE.

To do so you set :ref:`param_language_server_phpstan.enabled`:

.. code-block:: bash

   $ phpactor config:set language_server_phpstan.enabled true

- Override the PHPStan level with :ref:`param_language_server_phpstan.level`
- Specify the path to PHPStan if different to ``/vendor/bin/phpstan`` via. :ref:`param_language_server_phpstan.bin`.

Keep in mind that Phpactor analyzes files with a temporary name, so defining a PHPStan baseline won't work as expected. All detected errors will be reported despite the baseline.
