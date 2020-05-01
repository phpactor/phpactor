About Phpactor
==============

Phpactor is an intelligent code-completion and refactoring tool for PHP.

-  **Code Completion**: Provides broad and accurate context aware code
   completion.
-  **No indexing**: Phpactor works in real-time and is optimized with
   `composer <https://getcomposer.org>`__. It is fast.
-  **Introspection**: Jump to class and method definitions, find
   references.
-  **Refactoring**: Move classes, complete constructors, implement
   contracts, generate methods, etc.
-  **Native VIM plugin**: Lightweight VIM plugin.
-  **CLI interface**: Most/some functionality is exposed through CLI
   commands.

Phpactor can be used in a limited way as a **standalone application**,
but its full power can only be utilized through its RPC protocol. It
ships with support for VIM and there is also a plugin for
`Emacs <https://github.com/emacs-php/phpactor.el>`__.

Phpactor is pronounced “factor”, as in “factorisation” and
“re-factoring”. It is a large project and some parts are more fully
realised than others but it is used as an essential tool in day-to-day
PHP work.

Packages
--------

Phpactor is made up of a few different packages:

.. figure:: images/components.png
   :alt: Components

   Components

-  `phpactor/class-mover <https://github.com/phpactor/class-mover>`__:
   Find and update class references.
-  `phpactor/class-to-file <https://github.com/phpactor/class-to-file>`__:
   Convert files to class names and vice-versa.
-  `phpactor/code-builder <https://github.com/phpactor/code-builder>`__:
   Library for creating and idempotently updating source code.
-  `phpactor/code-transform <https://github.com/phpactor/code-transform>`__:
   Transform code.
-  `phpactor/completion <https://github.com/phpactor/completion>`__:
   Completion library.
-  `phpactor/path-finder <https://github.com/phpactor/path-finder>`__:
   Simple for file relationships when they share common path segments.
-  `phpactor/source-code-filesystem <https://github.com/phpactor/source-code-filesystem>`__:
   Manage source code trees.
-  `phpactor/test-utils <https://github.com/phpactor/test-utils>`__:
   Shared test utilities for Phpactor
-  `phpactor/worse-reflection <https://github.com/phpactor/worse-reflection>`__:
   Static source reflection library.

Depends On
----------

Phpactor stands on the shoulders of some other packages:

-  `Couscous <http://couscous.io/>`__: This documentation is generated
   with Couscous.
-  `Jetbrains PhpStorm
   Stubs <https://github.com/JetBrains/phpstorm-stubs>`__: Built in PHP
   classes are supported through this stub collection from PhpStorm.
-  `PHPBench <https://github.com/phpbench/phpbench>`__: For tracking
   performance.
-  `PHPUnit <https://github.com/sebastianbergmann/phpunit>`__: The de
   facto standard PHP test framework
-  `Symfony <https://symfony.com>`__: The command line application is
   built with the Symfony console component.
-  `Tolerant PHP
   Parser <https://github.com/Microsoft/tolerant-php-parser>`__: Large
   parts of Phpactor are integrated with the TPP.

Influenced By
-------------

-  `BetterReflection <https://github.com/Roave/BetterReflection>`__:
   Phpactors reflection API was heavily influenced by this library.
-  `Language Server
   Protocol <https://github.com/Microsoft/language-server-protocol>`__:
   While not implementing the LSP, some terminology has been borrowed
   from it.
-  `Nikic PHP Parser <https://github.com/nikic/PHP-Parser>`__: The
   original Phpactor used this before switching to TPP.

Similar Projects
----------------

-  `PHP Language
   Server <https://github.com/felixfbecker/php-language-server>`__: An
   LSP implementation for PHP.
