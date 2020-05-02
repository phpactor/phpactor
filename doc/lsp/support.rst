LSP Support
===========

See the `Language Server Specification`_ for details.

+-------------------------+---+-------------------------------------+
| Feature                 |   |                                     |
+=========================+===+=====================================+
| Completion              | ✔ | See :ref:`completion`               |
+-------------------------+---+-------------------------------------+
| Completion Resolve      | ✘ |                                     |
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
| Find References         | ✘ | Supported by RPC  [#rpc]_ [#nav]_   |
+-------------------------+---+-------------------------------------+
| Document Highlight      | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Document Symbol         | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Code Action             | ✘ | Supported by RPC [#rpc]_ [#code]_   |
+-------------------------+---+-------------------------------------+
| Code Lens               | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Document Link           | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Document Color          | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Color Presentation      | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Formatting              | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Range Formatting        | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Rename                  | ✘ | Supported by RPC [#rpc]_ [#rename]_ |
+-------------------------+---+-------------------------------------+
| Folding/Selection Range | ✘ |                                     |
+-------------------------+---+-------------------------------------+
| Diagnostics             | ✘ | Planned [#static]_                  |
+-------------------------+---+-------------------------------------+

.. _Language Server Specification: https://microsoft.github.io/language-server-protocol/specification

.. [#rpc] Available through RPC (i.e. non-LSP client) LSP support should be added soon.
.. [#code] Code actions translate to :ref:`refactoring` actions in Phpactor, and should be available in the next release.
          added soon.
.. [#nav] RPC supports :ref:`navigation_class_references` and :ref:`navigation_class_member_references`
.. [#rename] RPC supports :ref:`refactoring_rename_variable`, :ref:`refactoring_rename_class`, :ref:`refactoring_rename_member`,
.. [#static] Extension(s) will be provided to integrate static analynsis tools such as PHPStan https://github.com/phpactor/phpactor/issues/980
