.. _lsp_code_actions:

LSP code actions
================

See `Language Server Specification (Code Action Request)`_ for details.

List of currently available code actions:

+---------------------------------------------+---------------------------------------+
| Code Action                                 | Kind                                  |
+=============================================+=======================================+
| :ref:`refactoring_import_missing_class`     | ``quickfix.import_class``             |
+---------------------------------------------+---------------------------------------+
| :ref:`refactoring_complete_constructor`     | ``quickfix.complete_constructor``     |
+---------------------------------------------+---------------------------------------+
| :ref:`refactoring_add_missing_assignements` | ``quickfix.add_missing_properties``   |
+---------------------------------------------+---------------------------------------+
| :ref:`implement_contracts`                  | ``quickfix.implement_contracts``      |
+---------------------------------------------+---------------------------------------+
| :ref:`refactoring_fix_namespace_and_class`  | ``quickfix.fix_namespace_class_name`` |
+---------------------------------------------+---------------------------------------+
| :ref:`generation_class_new`                 | ``quickfix.create_class``             |
+---------------------------------------------+---------------------------------------+

.. _Language Server Specification (Code Action Request): https://microsoft.github.io/language-server-protocol/specification#textDocument_codeAction
