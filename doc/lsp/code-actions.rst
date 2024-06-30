.. _lsp_code_actions:

LSP code actions
================

See `Language Server Specification (Code Action Request)`_ for details.

List of currently available code actions:

+---------------------------------------------+----------------------------------------+
| Code Action                                 | Kind                                   |
+=============================================+========================================+
| :ref:`refactoring_import_missing_class`     | ``quickfix.import_class``              |
+---------------------------------------------+----------------------------------------+
| :ref:`refactoring_complete_constructor`     | ``quickfix.complete_constructor``      |
+---------------------------------------------+----------------------------------------+
| :ref:`refactoring_add_missing_assignments`  | ``quickfix.add_missing_properties``    |
+---------------------------------------------+----------------------------------------+
| :ref:`implement_contracts`                  | ``quickfix.implement_contracts``       |
+---------------------------------------------+----------------------------------------+
| :ref:`refactoring_fix_namespace_and_class`  | ``quickfix.fix_namespace_class_name``  |
+---------------------------------------------+----------------------------------------+
| :ref:`generation_class_new`                 | ``quickfix.create_class``              |
+---------------------------------------------+----------------------------------------+
| :ref:`generation_class_new`                 | ``quickfix.create_unresolvable_class`` |
+---------------------------------------------+----------------------------------------+
| :ref:`generation_method`                    | ``quickfix.generate_method``           |
+---------------------------------------------+----------------------------------------+
| :ref:`generation_extract_method`            | ``quickfix.extract_method``            |
+---------------------------------------------+----------------------------------------+
| :ref:`generation_extract_expression`        | ``quickfix.extract_expression``        |
+---------------------------------------------+----------------------------------------+
| :ref:`generation_extract_constant`          | ``quickfix.extract_constant``          |
+---------------------------------------------+----------------------------------------+
| :ref:`generation_generate_accessors`        | ``quickfix.generate_accessors``        |
+---------------------------------------------+----------------------------------------+

.. _Language Server Specification (Code Action Request): https://microsoft.github.io/language-server-protocol/specification#textDocument_codeAction
