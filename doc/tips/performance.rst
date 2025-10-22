Performance
===========

Large Files
-----------

Phpactor is not currently very performant as a **language server** when used on
large and complex files. This is due to the (blocking) static analysis overhead
from diagnostics.

You can improve performance by :

-  :ref:`disabling diagnostics<param_language_server.diagnostics_on_update>` when documents are _updated_
-  :ref:`disabling document highlighting<param_language_server_highlight.enabled>` when documents are _updated_

You can disable both settings with:

.. code-block:: bash

   $ phpactor config:set language_server.diagnostics_on_update false
   $ phpactor config:set language_server_highlight.enabled false

Indexing
--------

The Phpactor indexer will include all files that satisfy the :ref:`include globs<param_indexer.include_patterns>` and exclude any files in the :ref:`exclude globs<param_indexer.exclude_patterns>`.

Depending on your project you may want to customize this, for example, in a **Symfony** project you can avoid indexing the `var/cache` directory by excluding `/var/cache/**/*`.

The following command (run in the project root) will update ``.phpactor.json`` to exclude cache and other common directories:

.. code-block:: bash

   $ phpactor config:set indexer.exclude_patterns '["/vendor/**/Tests/**/*","/vendor/**/tests/**/*","/var/cache/**/*","/vendor/composer/**/*"]'

