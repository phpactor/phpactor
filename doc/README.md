---
currentMenu: about
---
About Phpactor
==============

Phpactor is a intelligent code-completion and refactoring tool for PHP.

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

Phpactor is a bleeding-edge product, it is not perfect, it probably never will
be. It has been driven forward by immediate need. Comprimises have been made
and it's a work in progress. But, I have been using it extensively in my daily
work and havn't felt especially compelled to add new features for some months.
Your mileage my vary, pull requests are open!
