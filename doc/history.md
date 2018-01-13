History
=======

**Note**: The following is personal history and opinion.

---

I developed Phpactor because I am a VIM (and Linux console) user, and every year
that passed (there were many of them) I noticed that the relative stupidity of
my editor grew more and more as IDEs such as Jetbrains
[PhpStorm](https://www.jetbrains.com/phpstorm/) and Idea platforms offered more
and more great features.

VIM is a great text editor, but it is essentially dumb. Nevertheless, I still
_felt_ that I could program faster and more efficiently in VIM running in
[Tmux](https://github.com/tmux/tmux/wiki) sessions in a minimal tiling desktop
environment such as [I3](https://i3wm.org/). I became extremely good at
typing, and developed muscle memory for class constructors and properties.

Not having these refactoring tools however also meant that the lack of them would
have a direct influence on the quality of my code and architecture (if would
take an hour to move a bunch of classes, the chances are I probably wouldn't do
that).

Of course there were some ways to mitigate this and for some time I had been
using ctags completion (basically regexy dumb completion) and code snippet
generators, but a real contextually aware code completion and refactoring tool
for PHP was simply not available.

I was excited to find out about projects like
[Padawan](https://github.com/padawan-php/padawan.php) and other similar
efforts, but they all seemed either unstable or sub-optimal (at the time at
least) and after a while I thought "how hard can it be"? So in 2014 I wrote the
first version of Phpactor. It was terrible. 1 year later I wrote another,
again, it sucked. The ongoing third effort is what we have here today.

One of the things that made Phpactor possible was the Microsoft [Tolerant PHP
parser](https://github.com/Microsoft/tolerant-php-parser) (TPP), a great library
which offers a practical and fast AST. At this time I also discovered the
Microsoft Language Server protocol, and Felix Beckers [PHP Language
Server](https://github.com/felixfbecker/php-language-server). This was awkward
as the language server was a solid effort and the overlap between it and
Phpactor was apparent. But I decided to carry on - I had previously waited for
other projects and they didn't mature.

Initially I also used the [Nickic PHP
parser](https://github.com/nikic/PHP-Parser) - the defacto PHP parser in the
PHP world today, but I found the TPP much easier to work with as it
provides tree-traversal (and was matched to Phpactor's use case - I believe
essentially created to support the [VS
Code](https://en.wikipedia.org/wiki/Visual_Studio_Code) eco-system and
contribute to Microsoft's efforts to _take control of the market_).

It is also worth mentioning that the backbone of Phpactor is
[worse-reflection](https://github.com/phpactor/worse-reflection), which was
influenced by [BetterReflection](https://github.com/Roave/BetterReflection). BetterReflection
is an awesome project by the [Roave](https://roave.com/) team to provide a static
alternative to the SPL Reflection library. Initially Phpactor used this
library, but as Phpactor had different requirements, it would have been
painfully slow progress to incrementally contribute the experimental functionality. worse-reflection
has gone through many iterations and continues to be unstable, it is the core
domain of Phpactor and is subservient to its needs.


Why not PHP Language Server?
----------------------------

No reason - PHP language server is great. Well, there are some differentiators:

- **Phpactor does not index anything**: This is both good and bad, good
  because it is fast (depending on the operation), doesn't suffer from memory
  issues and has no start-up penalty. It uses short lived processes, which is
  what PHP is good at. We locate files quickly by depending on
  the [Composer](https://getcomposer.org) autoloader. Bad because
  it cannot discover non-autoloadable classes or (at time of writing) provide
  completion for functions in the wild.
- **Phpactor is also a refactoring tool**: Phpactor provides [refactorings](refactorings.md) and
  procedures to help you code faster and more accurately (such as complete
  constructor, generate method, implement contracts etc).
- **Phpactor ships with a VIM plugin**: Phpactor ships with a [VIM plugin](vim-plugin.md).

... and competition for the eco-system right? "Competing" products allow more
independent vectors of discovery.

Why not Language Server Protocol?
---------------------------------

The [Language Server
Protocol](https://github.com/Microsoft/language-server-protocol) defines a
standard protocol for an editor to talk to a language server. Phpactor is
essentially a language server.

Phpactor does not currently support the Language Server protocol, but it does
implement its own [RPC Protocol](rpc.md). It is not currently aligned with the LSP.

In the future it may happen that Phpactor will implement LSP, or maybe it will
be made redundant by the PHP Language Server `¯\_(ツ)_/¯`.
