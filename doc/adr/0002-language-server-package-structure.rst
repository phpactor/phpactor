Language Server Package Structure
=================================

**ACCEPTED**

Context
-------

For reasons described in the rejected 0001 ADR we have an issue where
adding language server features involves, if done properly, would
involve the creation of many more separate packages, increasing the
maintenance overhead significantly.

The Language Server is made up of:

-  ``phpactor/language-server``: A generic language server package, not
   coupled to Phpactor.
-  ``phpactor/language-server-extension``: Command to launch the
   language server, hooks to register RPC handlers.
-  ``phpactor/language-server-completion-extension``: Handlers for
   completion (using the existing Phpactor implementations from other
   packages).
-  ``phpactor/language-server-reference-finder-extension``: As with the
   completion extension.
-  ``phpactor/language-server-hover-extension``: This package *did* add
   support for hover, but was recently merged into completion.

In addition, there is the prospect of adding:

-  ``phpactor/language-server-indexer-extension``: Add the indexing
   service to the server.
-  ``phpactor/language-server-code-action-extension``: Integrate all the
   code-transform actions.
-  ``phpactor/language-server-worse-reflection-extension``: Add the
   workspace source-locator for worse reflection.
-  …

Decision
--------

Keep the generic language server package, but combine all the Phpactor
extensions into a single package:

-  ``phpactor/language-server``: A generic language server package, not
   coupled to Phpactor.
-  ``phpactor/language-server-extension``: Macro package containing all
   the Phpactor Language Server extensions.

The macro package will be organised with all extensions living in their
own namespaces, i.e. \ ``lib/<extension name>``:

::

   lib/LanguageServer/
   lib/LanguageServerCompletion/
   lib/LanguageServerReferenceFinder/
   lib/...

The namespace will be mapped as ``'Phpactor\\Extension\\' => 'lib/'`` -
so the extensions namespaces remain unchanged.

Tests will also be namespaces as before, but will require additional
autoload mapping.

Consequences
------------

Having all the language server functionality in one place makes it
easier to refactor, and much easier to add new features.

In the future it should still be possible to break micro-packages out of
the macro-package.

There is a risk that it is easier for packages to contaminate each
other.
