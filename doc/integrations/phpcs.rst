PHP_CodeSniffer
===============

`PHP_CodeSniffer <https://github.com/squizlabs/PHP_CodeSniffer>` is a set of
two PHP scripts; the main phpcs script that tokenizes PHP, JavaScript and CSS
files to detect violations of a defined coding standard, and a second phpcbf
script to automatically correct coding standard violations. PHP_CodeSniffer is
an essential development tool that ensures your code remains clean and
consistent.

Phpactor can use PHP_CodeSniffer to:

- format your code via the LSP ``textDocument/formatting`` action,
- provide diagnostics for potential fixes,

To do so you set :ref:`_param_php_code_sniffer.enabled`:

.. code-block:: bash

   $ phpactor config:set php_code_sniffer.enabled true

- Specify the path to ``phpcs`` if different to ``/vendor/bin/phpcs`` via. :ref:`param_php_code_sniffer.bin`.
