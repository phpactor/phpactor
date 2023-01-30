PHP-CS-Fixer
============

`PHP-Cs-Fixer <https://github.com/FriendsOfPHP/PHP-CS-Fixer>`_  is a tool
fixes your code to follow standards; whether you want to follow PHP coding
standards as defined in the PSR-1, PSR-2, etc., or other community driven ones
like the Symfony one. You can also define your (team's) style through
configuration.

Phpactor can use PHP-CS-Fixer to:

- format your code via the LSP `textDocument/formatting` action,
- provide diagnostics for potential fixes,
- provide `source.fixAll.phpactor.phpCsFixer` code action to allow auto-fixing on save.

To do so you set :ref:`param_language_server_php_cs_fixer.enabled`:

.. code-block:: bash

   $ phpactor config:set language_server_php_cs_fixer.enabled true

- Specify the path to PHP-CS-Fixer if different to ``/vendor/bin/php-cs-fixer`` via. :ref:`param_language_server_php_cs_fixer.bin`.
