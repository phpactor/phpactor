.. _lsp_support:

LSP Support
===========

See the `Language Server Specification`_ for details.

+-------------------------+---+-------------------------------------+
| Feature                 |   |                                     |
+=========================+===+=====================================+
| Completion              | ✔ | See :ref:`completion`               |
+-------------------------+---+-------------------------------------+
| Hover                   | ✔ |                                     |
+-------------------------+---+-------------------------------------+
| Signature Help          | ✔ |                                     |
+-------------------------+---+-------------------------------------+
| Goto Declaration        | ✔ |                                     |
+-------------------------+---+-------------------------------------+
| Goto Type               | ✔ |                                     |
+-------------------------+---+-------------------------------------+
| Goto Implementation     | ✔ |                                     |
+-------------------------+---+-------------------------------------+
| Find References         | ✔ | [#references]_                      |
+-------------------------+---+-------------------------------------+
| Document Highlight      | ✔ | Symbol highlighting                 |
+-------------------------+---+-------------------------------------+
| Workspace Symbols       | ✔ | Classes, functions and constants    |
+-------------------------+---+-------------------------------------+
| Document Symbol         | ✔ | For structural elements             |
+-------------------------+---+-------------------------------------+
| Selection Range         | ✔ |                                     |
+-------------------------+---+-------------------------------------+
| Code Action             | ✔ | [#code]_                            |
+-------------------------+---+-------------------------------------+
| Code Lens               | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Document Link           | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Document Color          | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Color Presentation      | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Formatting              | ✔ | [#formatting]_                      |
+-------------------------+---+-------------------------------------+
| Range Formatting        | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Rename                  | ✔ | Variables and members [#rename]_    |
+-------------------------+---+-------------------------------------+
| Folding/Selection Range | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Diagnostics             | ✔ | [#diagnostics]_                     |
+-------------------------+---+-------------------------------------+

.. _Language Server Specification: https://microsoft.github.io/language-server-protocol/specification

.. [#rpc] Available through RPC (i.e. non-LSP client) LSP support should be added soon.
.. [#code] See :doc:`/lsp/code-actions`.
.. [#references] For class like references, functions and member accesses (static and object instances)
.. [#rename] Native LSP support for renaming variables and class members, with support planned for renaming classes and namespaces. RPC fills the gap: :ref:`refactoring_rename_class`
.. [#formatting] With php-cs-fixer :ref:`php-cs-fixer <param_language_server_php_cs_fixer.enabled>`.
.. [#diagnostics] Basic PHP linting and also support for integrating with :ref:`phpstan <param_language_server_phpstan.enabled>`, :ref:`Psalm <param_language_server_psalm.enabled>` and :ref:`php-cs-fixer <param_language_server_php_cs_fixer.enabled>`.
