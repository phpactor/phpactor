Phpstan (LSP)
=============

The PHPStan extension

Enable
------


.. code:: bash

    $ phpactor extension:install phpactor/language-server-phpstan-extension

Prerequisites
-------------

- Phpstan bin should be available (expects ``./vendor/bin/phpstan`` by default).
- Phpactor ``>= 0.16.0``.
- ``phpstan.neon`` configuration file (see below).

Phpstan Config
--------------

Your project requires a `phpstan.neon` configuration file defining it's
`level`:

.. code-block:: yaml

    # phpstan.neon
    parameters:
        level: 7

Usage
-----

You should automatically receive diagnostics when the extension is installed.
