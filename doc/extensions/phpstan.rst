Phpstan (LSP)
=============

.. github-link:: phpactor/language-server-phpstan-extension

The `Phpstan <https://phpstan.org/>`_ extension provides :ref:`language_server` diagnostics.

Installing
----------


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

.. code-block::

    # phpstan.neon
    parameters:
        level: 7

Usage
-----

You should automatically recieve diagnostics when the extension is installed.
