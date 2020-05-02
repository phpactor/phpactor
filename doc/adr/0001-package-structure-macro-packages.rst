Package Structure: Macro Packages
=================================

**REJECTED**

Context
-------

Currently Phpactor is organized as follows:

::

   Phpactor\\Extension\\<subject>Extension\\
   Phpactor\\Extension\\<subject>LanguageServerExtension\\
   Phpactor\\Extension\\<subject>RpcExtension\\
   Phpactor\\<subject>\\{Adapter|Bridge}\\
   Phpactor\\<subject>\\{Model|Core}\\

-  The ``Extension`` connects the ``<subject>`` ``Model`` to the
   Phpactor container. There may be an Extension which is “pure”
   (abstract) and provide an extension point. Others will use the
   extension point to integrate adapters (concrete implementations).
-  The ``LanguageServer`` and (Phpactor) ``Rpc`` extensions provide
   handlers for these two RPC methods.
-  The adapters are the concrete implementations
   (e.g. ``WorseReflection`` for fulfilling the completion APIs).
-  The ``Model`` is the pure domain code and APIs.

Each separate namespace has a special concern. Originally it was
intended that package stability principles be followed and each package
would live as a separate repository with a separate package (let’s call
them micro-packages).

This has led to a situation where there are far too many packages,
integrating them all has become a huge problem and for the past year or
more it has significantly slowed down development on Phpactor:

-  Adding new features would involve adding 3 packages (model, extension
   and adapter).
-  As packages are young and unstable, refactoring would often affect
   two or more packages.
-  Maintaining the metadata over so many packages (build processes,
   supported PHP versions, etc) becomes repetitive.

Some steps were made towards a mono-repo with a sub-tree split but:

-  We would lose (or at least aim towards) semantic versioning without
   new tooling.
-  We risk cross-package contamination without new tooling.

Decision
--------

Keep separate repositories but combine extensions into the subject
namespace.

So:

::

   Phpactor\\<subject>\\Extension\\
   Phpactor\\<subject>\\Adapter\\
   Phpactor\\<subject>\\Model\\

In a single package. All extensions will live in the ``Extension``
namespace, e.g.:

::

   Phpactor\\<subject>\\Extension\\<subject>Extension
   Phpactor\\<subject>\\Extension\\<subject>RpcExtension
   Phpactor\\<subject>\\Extension\\<subject>LanguageServerExtension

If it were the case that the ``Model`` never changed, and the abstract
extension points never changed, it would be fine to have separate
packages, but reality is not like this (even if the code never changes,
PHP versions do).

Consequences
------------

This should significantly reduce the maintenance overhead as all
packages that change together are packaged together.

Semantic versioning will not be as accurate as before - changes in the
``LanguageServer`` or ``Rpc`` APIs will cause a BC break for one or the
other but not both.

The namespace changes from ``Phpactor\\Extension\\`` to
``Phpactor\\<subject>\\Extension``, which means that all external
extensions will have a BC break.

Rejected
--------

First of all, changing the namespace caused more trouble than
anticipated. While we could have provided stubs in the old namespace in
the packages, many packages exposed multiple public classes, so it
wasn’t practical.

We solved this by mapping ``Phpactor\\`` to ``lib/`` and move everything
in to keep the same namespace structure as before.
(e.g. ``Phpactor\\LanguageServer``,
``Phpactor\\Extension\\LanguageServer``.

But finally the **whole idea is flawed**: The “extension” dependencies
were in ``require-dev``, which meant that packages depending on an
extension, would need to explicitly require the package, the extensions
and any other dependencies of the extension.

As extensions often depend on multiple other extensions, this is
completely unpractical.
