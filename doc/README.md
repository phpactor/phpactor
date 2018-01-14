---
currentMenu: about
---
About Phpactor
==============

Phpactor is an intelligent code-completion and refactoring tool for PHP.

- **Code Completion**: Provides accurate context aware code completion for
  classes.
- **No indexing**: Phpactor uses composer to guess class locations.
- **Introspection**: Jump to class and method definitions, find references.
- **Refactoring**: Move classes, complete constructors, implement contracts,
  generate methods, etc.
- **VIM plugin**: Lightweight VIM plugin.
- **CLI interface**: Most/some functionality is exposed through CLI commands.

Phpactor can be used in a limited way as a **standalone application**, but its full
power can only be utilized through its RPC protocol. It ships with support for VIM, but it
would not involve a huge amount of effort to support other editors such as
[GNU Emacs](https://www.gnu.org/s/emacs/).

Phpactor is a bleeding-edge product, it is far from being perfect, it probably
never will be. It's a work in progress. But, I have been using it extensively
in my daily work and haven't felt especially compelled to add new features for
some months.

Depends On
----------

- [Tolerant PHP Parser](https://github.com/Microsoft/tolerant-php-parser): Large parts of Phpactor are integrated with the TPP.
- [Couscous](http://couscous.io/): This documentation is generated with Couscous.
- [Symfony](https://symfony.com): The command line application is built with the Symfony console component.
- [Jetbrains PhpStorm Stubs](https://github.com/JetBrains/phpstorm-stubs): Built in PHP classes are supported through this stub collection from PhpStorm.

Influenced By
-------------

- [BetterReflection](https://github.com/Roave/BetterReflection): Phpactors reflection API was heavily influenced by this library.
- [Nikic PHP Parser](https://github.com/nikic/PHP-Parser): The original Phpactor used this before switching to TPP.
- [Language Server Protocol](https://github.com/Microsoft/language-server-protocol): While not
  implementing the LSP, some terminology has been borrowed from it.

Similar Projects
----------------

- [PHP Language Server](https://github.com/felixfbecker/php-language-server): An LSP implementation for PHP.
