History
=======

I developed Phpactor because I am a VIM user, and every year that passed
(there were many of them) I noticed that the relative stupidity of my editor
grew more and more as IDEs such as Jetbrains PHPStorm and idea platforms
offered more and more great features.

VIM is a great text editor, but it is essentially dumb. Nevertheless, I still
_felt_ that I could program faster and more efficiently in VIM running in
[Tmux](https://github.com/tmux/tmux/wiki) sessions in a minimal tiling desktop
environment such as [I3](https://i3wm.org/). I became extremely good at
typing, and developed muscle memory for class constructors and properties.

Of course there were some ways to mitigate this and for some time I had been
using ctags completion (basically regexy dumb completion) and code snippet
generators, but a real contextually aware code completion tool for PHP was
simply not available.

I was excited to find out about projects like
[Padawan](https://github.com/padawan-php/padawan.php) and other similar
efforts, after a while I thought "how hard can it be"? So in 2014 I wrote the
first version of Phpactor. It was terrible. 1 year later I wrote another,
again, it sucked. The ongoing third effort is what we have here today.

One of the things that made Phpactor possible was the Microsoft (yep) PHP
parser, which offers a practical and fast AST. At this time I also discovered
the Microsoft Language Server protocol, and Felix Beckers [PHP Language
Server](https://github.com/felixfbecker/php-language-server). This was awkward
as the language server was a solid effort to meet the same requirements as
Phpactor.

Why not PHP Language Server?
----------------------------

No reason - PHP language server is great. Well, there are some differentiators:

- **Phpactor does not index anything**: This is both good and bad, good
  because it is very fast and doesn't crash. It uses short lived processes,
  which is what PHP is good at. We locate files quickly by depending on
  the [Composer](https://getcomposer.org) autoloader. Bad because
  it cannot discover classes within-classes or (at time of writing) provide
  completion for non-class functions.
- **Phpactor is also a refactoring tool**: Phpactor provides refactorings and
  procedures to help you code faster and more accurately (such as complete
  constructor, generate method, implement contracts etc).
- **Phpactor ships with a VIM plugin**: Phpactor ships with a VIM plugin.

... and competition for the eco-system right?

Why not Language Server Protocol?
---------------------------------

The [Language Server
Protocol](https://github.com/Microsoft/language-server-protocol) defines a
standard protocol for an editor to talk to a language server. Phpactor is
essentially a language server.

Phpactor does not currently support the Language Server protocol, but it does
implement its own [RPC Protocol](rpc.md).

In the future it may happen that Phpactor will implement LSP, or maybe it will
be made redundant by the PHP Language Server.
