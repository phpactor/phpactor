Legacy RPC Commands
===================


.. This document is generated via the `development:generate-documentation` command


.. contents::
   :depth: 2
   :backlinks: none
   :local:


.. _StatusHandler:


StatusHandler
-------------


.. _RpcCommand_status_type:


``type``
""""""""


**Default**: ``"formatted"``


.. _FileInfoHandler:


FileInfoHandler
---------------


.. _RpcCommand_file_info_path:


``path``
""""""""


**Default**: ``null``


.. _ReferencesHandler:


ReferencesHandler
-----------------


.. _RpcCommand_references_mode:


``mode``
""""""""


**Default**: ``"find"``


.. _RpcCommand_references_filesystem:


``filesystem``
""""""""""""""


**Default**: ``"git"``


.. _RpcCommand_references_replacement:


``replacement``
"""""""""""""""


**Default**: ``null``


.. _RpcCommand_references_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_references_offset:


``offset``
""""""""""


**Default**: ``null``


.. _RpcCommand_references_source:


``source``
""""""""""


**Default**: ``null``


.. _ClassCopyHandler:


ClassCopyHandler
----------------


.. _RpcCommand_copy_class_dest_path:


``dest_path``
"""""""""""""


**Default**: ``null``


.. _RpcCommand_copy_class_source_path:


``source_path``
"""""""""""""""


**Default**: ``null``


.. _ClassMoveHandler:


ClassMoveHandler
----------------


.. _RpcCommand_move_class_dest_path:


``dest_path``
"""""""""""""


**Default**: ``null``


.. _RpcCommand_move_class_confirmed:


``confirmed``
"""""""""""""


**Default**: ``null``


.. _RpcCommand_move_class_move_related:


``move_related``
""""""""""""""""


**Default**: ``null``


.. _RpcCommand_move_class_source_path:


``source_path``
"""""""""""""""


**Default**: ``null``


.. _ClassInflectHandler:


ClassInflectHandler
-------------------


.. _RpcCommand_class_inflect_new_path:


``new_path``
""""""""""""


**Default**: ``null``


.. _RpcCommand_class_inflect_variant:


``variant``
"""""""""""


**Default**: ``null``


.. _RpcCommand_class_inflect_overwrite_existing:


``overwrite_existing``
""""""""""""""""""""""


**Default**: ``null``


.. _RpcCommand_class_inflect_current_path:


``current_path``
""""""""""""""""


**Default**: ``null``


.. _ClassNewHandler:


ClassNewHandler
---------------


.. _RpcCommand_class_new_new_path:


``new_path``
""""""""""""


**Default**: ``null``


.. _RpcCommand_class_new_variant:


``variant``
"""""""""""


**Default**: ``null``


.. _RpcCommand_class_new_overwrite_existing:


``overwrite_existing``
""""""""""""""""""""""


**Default**: ``null``


.. _RpcCommand_class_new_current_path:


``current_path``
""""""""""""""""


**Default**: ``null``


.. _TransformHandler:


TransformHandler
----------------


.. _RpcCommand_transform_transform:


``transform``
"""""""""""""


**Default**: ``null``


.. _RpcCommand_transform_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_transform_source:


``source``
""""""""""


**Default**: ``null``


.. _ExtractConstantHandler:


ExtractConstantHandler
----------------------


.. _RpcCommand_extract_constant_constant_name:


``constant_name``
"""""""""""""""""


**Default**: ``null``


.. _RpcCommand_extract_constant_constant_name_suggestion:


``constant_name_suggestion``
""""""""""""""""""""""""""""


**Default**: ``null``


.. _RpcCommand_extract_constant_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_extract_constant_offset:


``offset``
""""""""""


**Default**: ``null``


.. _RpcCommand_extract_constant_source:


``source``
""""""""""


**Default**: ``null``


.. _ExtractMethodHandler:


ExtractMethodHandler
--------------------


.. _RpcCommand_extract_method_method_name:


``method_name``
"""""""""""""""


**Default**: ``null``


.. _RpcCommand_extract_method_offset_start:


``offset_start``
""""""""""""""""


**Default**: ``null``


.. _RpcCommand_extract_method_offset_end:


``offset_end``
""""""""""""""


**Default**: ``null``


.. _RpcCommand_extract_method_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_extract_method_path:


``path``
""""""""


**Default**: ``null``


.. _GenerateAccessorHandler:


GenerateAccessorHandler
-----------------------


.. _RpcCommand_generate_accessor_names:


``names``
"""""""""


**Default**: ``null``


.. _RpcCommand_generate_accessor_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_generate_accessor_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_generate_accessor_offset:


``offset``
""""""""""


**Default**: ``null``


.. _GenerateMethodHandler:


GenerateMethodHandler
---------------------


.. _RpcCommand_generate_method_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_generate_method_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_generate_method_offset:


``offset``
""""""""""


**Default**: ``null``


.. _ImportClassHandler:


ImportClassHandler
------------------


.. _RpcCommand_import_class_qualified_name:


``qualified_name``
""""""""""""""""""


**Default**: ``null``


.. _RpcCommand_import_class_alias:


``alias``
"""""""""


**Default**: ``null``


.. _RpcCommand_import_class_offset:


``offset``
""""""""""


**Default**: ``null``


.. _RpcCommand_import_class_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_import_class_path:


``path``
""""""""


**Default**: ``null``


.. _RenameVariableHandler:


RenameVariableHandler
---------------------


.. _RpcCommand_rename_variable_name:


``name``
""""""""


**Default**: ``null``


.. _RpcCommand_rename_variable_name_suggestion:


``name_suggestion``
"""""""""""""""""""


**Default**: ``null``


.. _RpcCommand_rename_variable_scope:


``scope``
"""""""""


**Default**: ``null``


.. _RpcCommand_rename_variable_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_rename_variable_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_rename_variable_offset:


``offset``
""""""""""


**Default**: ``null``


.. _ChangeVisiblityHandler:


ChangeVisiblityHandler
----------------------


.. _RpcCommand_change_visibility_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_change_visibility_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_change_visibility_offset:


``offset``
""""""""""


Type: integer


**Default**: ``null``


.. _OverrideMethodHandler:


OverrideMethodHandler
---------------------


.. _RpcCommand_override_method_method_name:


``method_name``
"""""""""""""""


**Default**: ``null``


.. _RpcCommand_override_method_class_name:


``class_name``
""""""""""""""


**Default**: ``null``


.. _RpcCommand_override_method_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_override_method_source:


``source``
""""""""""


**Default**: ``null``


.. _ExtractExpressionHandler:


ExtractExpressionHandler
------------------------


.. _RpcCommand_extract_expression_variable_name:


``variable_name``
"""""""""""""""""


**Default**: ``null``


.. _RpcCommand_extract_expression_offset_start:


``offset_start``
""""""""""""""""


**Default**: ``null``


.. _RpcCommand_extract_expression_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_extract_expression_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_extract_expression_offset_end:


``offset_end``
""""""""""""""


**Default**: ``null``


.. _ImportMissingClassesHandler:


ImportMissingClassesHandler
---------------------------


.. _RpcCommand_import_missing_classes_path:


``path``
""""""""


**Default**: ``null``


.. _RpcCommand_import_missing_classes_source:


``source``
""""""""""


**Default**: ``null``


.. _HoverHandler:


HoverHandler
------------


.. _RpcCommand_hover_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_hover_offset:


``offset``
""""""""""


**Default**: ``null``


.. _CompleteHandler:


CompleteHandler
---------------


.. _RpcCommand_complete_type:


``type``
""""""""


**Default**: ``"php"``


.. _RpcCommand_complete_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_complete_offset:


``offset``
""""""""""


**Default**: ``null``


.. _NavigateHandler:


NavigateHandler
---------------


.. _RpcCommand_navigate_source_path:


``source_path``
"""""""""""""""


**Default**: ``null``


.. _RpcCommand_navigate_destination:


``destination``
"""""""""""""""


**Default**: ``null``


.. _RpcCommand_navigate_confirm_create:


``confirm_create``
""""""""""""""""""


**Default**: ``null``


.. _ContextMenuHandler:


ContextMenuHandler
------------------


.. _RpcCommand_context_menu_action:


``action``
""""""""""


**Default**: ``null``


.. _RpcCommand_context_menu_current_path:


``current_path``
""""""""""""""""


**Default**: ``null``


.. _RpcCommand_context_menu_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_context_menu_offset:


``offset``
""""""""""


**Default**: ``null``


.. _EchoHandler:


EchoHandler
-----------


.. _RpcCommand_echo_message:


``message``
"""""""""""


**Default**: ``null``


.. _ClassSearchHandler:


ClassSearchHandler
------------------


.. _RpcCommand_class_search_short_name:


``short_name``
""""""""""""""


**Default**: ``null``


.. _OffsetInfoHandler:


OffsetInfoHandler
-----------------


.. _RpcCommand_offset_info_offset:


``offset``
""""""""""


**Default**: ``null``


.. _RpcCommand_offset_info_source:


``source``
""""""""""


**Default**: ``null``


.. _GotoDefinitionHandler:


GotoDefinitionHandler
---------------------


.. _RpcCommand_goto_definition_language:


``language``
""""""""""""


Type: string


**Default**: ``"php"``


.. _RpcCommand_goto_definition_target:


``target``
""""""""""


Type: string


**Default**: ``"focused_window"``


.. _RpcCommand_goto_definition_offset:


``offset``
""""""""""


**Default**: ``null``


.. _RpcCommand_goto_definition_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_goto_definition_path:


``path``
""""""""


**Default**: ``null``


.. _GotoTypeHandler:


GotoTypeHandler
---------------


.. _RpcCommand_goto_type_language:


``language``
""""""""""""


**Default**: ``"php"``


.. _RpcCommand_goto_type_target:


``target``
""""""""""


**Default**: ``"focused_window"``


.. _RpcCommand_goto_type_offset:


``offset``
""""""""""


**Default**: ``null``


.. _RpcCommand_goto_type_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_goto_type_path:


``path``
""""""""


**Default**: ``null``


.. _GotoImplementationHandler:


GotoImplementationHandler
-------------------------


.. _RpcCommand_goto_implementation_language:


``language``
""""""""""""


**Default**: ``"php"``


.. _RpcCommand_goto_implementation_target:


``target``
""""""""""


**Default**: ``"focused_window"``


.. _RpcCommand_goto_implementation_offset:


``offset``
""""""""""


**Default**: ``null``


.. _RpcCommand_goto_implementation_source:


``source``
""""""""""


**Default**: ``null``


.. _RpcCommand_goto_implementation_path:


``path``
""""""""


**Default**: ``null``


.. _IndexHandler:


IndexHandler
------------


.. _RpcCommand_index_watch:


``watch``
"""""""""


**Default**: ``false``


.. _RpcCommand_index_interval:


``interval``
""""""""""""


Type: integer


**Default**: ``5000``

