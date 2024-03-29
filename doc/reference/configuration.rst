.. _ref_configuration:

Configuration
=============


.. This document is generated via the `development:generate-documentation` command


.. contents::
   :depth: 2
   :backlinks: none
   :local:


.. _CoreExtension:


CoreExtension
-------------


.. _param_console_dumper_default:


``console_dumper_default``
""""""""""""""""""""""""""


Name of the "dumper" (renderer) to use for some CLI commands


**Default**: ``"indented"``


.. _param_xdebug_disable:


``xdebug_disable``
""""""""""""""""""


If XDebug should be automatically disabled


**Default**: ``true``


.. _param_command:


``command``
"""""""""""


Internal use only - name of the command which was executed


**Default**: ``null``


.. _param_core.min_memory_limit:


``core.min_memory_limit``
"""""""""""""""""""""""""


Ensure that PHP has a memory_limit of at least this amount in bytes


**Default**: ``1610612736``


.. _param_$schema:


``$schema``
"""""""""""


Path to JSON schema, which can be used for config autocompletion, use phpactor config:initialize to update


**Default**: ``""``


.. _ClassToFileExtension:


ClassToFileExtension
--------------------


.. _param_class_to_file.project_root:


``class_to_file.project_root``
""""""""""""""""""""""""""""""


Root path of the project (e.g. where composer.json is)


**Default**: ``"%project_root%"``


.. _param_class_to_file.brute_force_conversion:


``class_to_file.brute_force_conversion``
""""""""""""""""""""""""""""""""""""""""


If composer not found, fallback to scanning all files (very time consuming depending on project size)


**Default**: ``true``


.. _CodeTransformExtension:


CodeTransformExtension
----------------------


.. _param_code_transform.class_new.variants:


``code_transform.class_new.variants``
"""""""""""""""""""""""""""""""""""""


Variants which should be suggested when class-create is invoked


**Default**: ``[]``


.. _param_code_transform.template_paths:


``code_transform.template_paths``
"""""""""""""""""""""""""""""""""


Paths in which to look for code templates


**Default**: ``["%project_config%\/templates","%config%\/templates"]``


.. _param_code_transform.indentation:


``code_transform.indentation``
""""""""""""""""""""""""""""""


Indentation chars to use in code generation and transformation


**Default**: ``"    "``


.. _param_code_transform.refactor.generate_accessor.prefix:


``code_transform.refactor.generate_accessor.prefix``
""""""""""""""""""""""""""""""""""""""""""""""""""""


Prefix to use for generated accessors


**Default**: ``""``


.. _param_code_transform.refactor.generate_accessor.upper_case_first:


``code_transform.refactor.generate_accessor.upper_case_first``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


If the first letter of a generated accessor should be made uppercase


**Default**: ``false``


.. _param_code_transform.refactor.generate_mutator.prefix:


``code_transform.refactor.generate_mutator.prefix``
"""""""""""""""""""""""""""""""""""""""""""""""""""


Prefix to use for generated mutators


**Default**: ``"set"``


.. _param_code_transform.refactor.generate_mutator.upper_case_first:


``code_transform.refactor.generate_mutator.upper_case_first``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


If the first letter of a generated mutator should be made uppercase


**Default**: ``true``


.. _param_code_transform.refactor.generate_mutator.fluent:


``code_transform.refactor.generate_mutator.fluent``
"""""""""""""""""""""""""""""""""""""""""""""""""""


If the mutator should be fluent


**Default**: ``false``


.. _param_code_transform.import_globals:


``code_transform.import_globals``
"""""""""""""""""""""""""""""""""


Import functions even if they are in the global namespace


**Default**: ``false``


.. _param_code_transform.refactor.object_fill.hint:


``code_transform.refactor.object_fill.hint``
""""""""""""""""""""""""""""""""""""""""""""


Object fill refactoring: show hint as a comment


**Default**: ``true``


.. _param_code_transform.refactor.object_fill.named_parameters:


``code_transform.refactor.object_fill.named_parameters``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Object fill refactoring: use named parameters


**Default**: ``true``


.. _CompletionWorseExtension:


CompletionWorseExtension
------------------------


.. _param_completion_worse.completor.doctrine_annotation.enabled:


``completion_worse.completor.doctrine_annotation.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``doctrine_annotation`` completor.

Completion for annotations provided by the Doctrine annotation library.


**Default**: ``true``


.. _param_completion_worse.completor.imported_names.enabled:


``completion_worse.completor.imported_names.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``imported_names`` completor.

Completion for names imported into the current namespace.


**Default**: ``true``


.. _param_completion_worse.completor.worse_parameter.enabled:


``completion_worse.completor.worse_parameter.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``worse_parameter`` completor.

Completion for method or function parameters.


**Default**: ``true``


.. _param_completion_worse.completor.named_parameter.enabled:


``completion_worse.completor.named_parameter.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``named_parameter`` completor.

Completion for named parameters.


**Default**: ``true``


.. _param_completion_worse.completor.constructor.enabled:


``completion_worse.completor.constructor.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``constructor`` completor.

Completion for constructors.


**Default**: ``true``


.. _param_completion_worse.completor.class_member.enabled:


``completion_worse.completor.class_member.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``class_member`` completor.

Completion for class members.


**Default**: ``true``


.. _param_completion_worse.completor.scf_class.enabled:


``completion_worse.completor.scf_class.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``scf_class`` completor.

Brute force completion for class names (not recommended).


**Default**: ``true``


.. _param_completion_worse.completor.local_variable.enabled:


``completion_worse.completor.local_variable.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``local_variable`` completor.

Completion for local variables.


**Default**: ``true``


.. _param_completion_worse.completor.subscript.enabled:


``completion_worse.completor.subscript.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``subscript`` completor.

Completion for subscript (array access from array shapes).


**Default**: ``true``


.. _param_completion_worse.completor.declared_function.enabled:


``completion_worse.completor.declared_function.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``declared_function`` completor.

Completion for functions defined in the Phpactor runtime.


**Default**: ``true``


.. _param_completion_worse.completor.declared_constant.enabled:


``completion_worse.completor.declared_constant.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``declared_constant`` completor.

Completion for constants defined in the Phpactor runtime.


**Default**: ``true``


.. _param_completion_worse.completor.declared_class.enabled:


``completion_worse.completor.declared_class.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``declared_class`` completor.

Completion for classes defined in the Phpactor runtime.


**Default**: ``true``


.. _param_completion_worse.completor.expression_name_search.enabled:


``completion_worse.completor.expression_name_search.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``expression_name_search`` completor.

Completion for class names, constants and functions at expression positions that are located in the index.


**Default**: ``true``


.. _param_completion_worse.completor.use.enabled:


``completion_worse.completor.use.enabled``
""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``use`` completor.

Completion for use imports.


**Default**: ``true``


.. _param_completion_worse.completor.attribute.enabled:


``completion_worse.completor.attribute.enabled``
""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``attribute`` completor.

Completion for attribute class names.


**Default**: ``true``


.. _param_completion_worse.completor.class_like.enabled:


``completion_worse.completor.class_like.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``class_like`` completor.

Completion for class like contexts.


**Default**: ``true``


.. _param_completion_worse.completor.type.enabled:


``completion_worse.completor.type.enabled``
"""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``type`` completor.

Completion for scalar types.


**Default**: ``true``


.. _param_completion_worse.completor.keyword.enabled:


``completion_worse.completor.keyword.enabled``
""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``keyword`` completor.

Completion for keywords (not very accurate).


**Default**: ``true``


.. _param_completion_worse.completor.docblock.enabled:


``completion_worse.completor.docblock.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""


Enable or disable the ``docblock`` completor.

Docblock completion.


**Default**: ``true``


.. _param_completion_worse.completor.constant.enabled:


``completion_worse.completor.constant.enabled``
"""""""""""""""""""""""""""""""""""""""""""""""


**Default**: ``false``


.. _param_completion_worse.completor.class.limit:


``completion_worse.completor.class.limit``
""""""""""""""""""""""""""""""""""""""""""


Suggestion limit for the filesystem based SCF class_completor


**Default**: ``100``


.. _param_completion_worse.name_completion_priority:


``completion_worse.name_completion_priority``
"""""""""""""""""""""""""""""""""""""""""""""


Strategy to use when ordering completion results for classes and functions:

- `proximity`: Classes and functions will be ordered by their proximity to the text document being edited.
- `none`: No ordering will be applied.


**Default**: ``"proximity"``


.. _param_completion_worse.snippets:


``completion_worse.snippets``
"""""""""""""""""""""""""""""


Enable or disable completion snippets


**Default**: ``true``


.. _param_completion_worse.experimantal:


``completion_worse.experimantal``
"""""""""""""""""""""""""""""""""


Enable experimental functionality


**Default**: ``false``


.. _param_completion_worse.debug:


``completion_worse.debug``
""""""""""""""""""""""""""


Include debug info in completion results


**Default**: ``false``


.. _CompletionExtension:


CompletionExtension
-------------------


.. _param_completion.dedupe:


``completion.dedupe``
"""""""""""""""""""""


If results should be de-duplicated


**Default**: ``true``


.. _param_completion.dedupe_match_fqn:


``completion.dedupe_match_fqn``
"""""""""""""""""""""""""""""""


If ``completion.dedupe``, consider the class FQN in addition to the completion suggestion


**Default**: ``true``


.. _param_completion.limit:


``completion.limit``
""""""""""""""""""""


Sets a limit on the number of completion suggestions for any request


**Default**: ``null``


.. _param_completion.label_formatter:


``completion.label_formatter``
""""""""""""""""""""""""""""""


Definition of how to format entries in the completion list


**Default**: ``"helpful"``


**Allowed values**: "helpful", "fqn"


.. _NavigationExtension:


NavigationExtension
-------------------


.. _param_navigator.destinations:


``navigator.destinations``
""""""""""""""""""""""""""


**Default**: ``[]``


.. _param_navigator.autocreate:


``navigator.autocreate``
""""""""""""""""""""""""


**Default**: ``[]``


.. _RpcExtension:


RpcExtension
------------


.. _param_rpc.store_replay:


``rpc.store_replay``
""""""""""""""""""""


Should replays be stored?


**Default**: ``false``


.. _param_rpc.replay_path:


``rpc.replay_path``
"""""""""""""""""""


Path where the replays should be stored


**Default**: ``"%cache%\/replay.json"``


.. _SourceCodeFilesystemExtension:


SourceCodeFilesystemExtension
-----------------------------


.. _param_source_code_filesystem.project_root:


``source_code_filesystem.project_root``
"""""""""""""""""""""""""""""""""""""""


**Default**: ``"%project_root%"``


.. _WorseReflectionExtension:


WorseReflectionExtension
------------------------


.. _param_language_server_code_transform.import_globals:


``language_server_code_transform.import_globals``
"""""""""""""""""""""""""""""""""""""""""""""""""


Show hints for non-imported global classes and functions


**Default**: ``false``


.. _param_worse_reflection.enable_cache:


``worse_reflection.enable_cache``
"""""""""""""""""""""""""""""""""


If reflection caching should be enabled


**Default**: ``true``


.. _param_worse_reflection.cache_lifetime:


``worse_reflection.cache_lifetime``
"""""""""""""""""""""""""""""""""""


If caching is enabled, limit the amount of time a cache entry can stay alive


**Default**: ``1``


.. _param_worse_reflection.enable_context_location:


``worse_reflection.enable_context_location``
""""""""""""""""""""""""""""""""""""""""""""


If source code is passed to a ``Reflector`` then temporarily make it available as a
source location. Note this should NOT be enabled if the source code can be
located in another (e.g. when running a Language Server)


**Default**: ``true``


.. _param_worse_reflection.cache_dir:


``worse_reflection.cache_dir``
""""""""""""""""""""""""""""""


Cache directory for stubs


**Default**: ``"%cache%\/worse-reflection"``


.. _param_worse_reflection.stub_dir:


``worse_reflection.stub_dir``
"""""""""""""""""""""""""""""


Location of the core PHP stubs - these will be scanned and cached on the first request


**Default**: ``"%application_root%\/vendor\/jetbrains\/phpstorm-stubs"``


.. _param_worse_reflection.diagnostics.undefined_variable.suggestion_levenshtein_disatance:


``worse_reflection.diagnostics.undefined_variable.suggestion_levenshtein_disatance``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Type: integer


Levenshtein distance to use when suggesting corrections for variable names


**Default**: ``4``


.. _FilePathResolverExtension:


FilePathResolverExtension
-------------------------


.. _param_file_path_resolver.project_root:


``file_path_resolver.project_root``
"""""""""""""""""""""""""""""""""""


**Default**: ``"\/home\/mamazu\/packages\/phpactor\/phpactor"``


.. _param_file_path_resolver.app_name:


``file_path_resolver.app_name``
"""""""""""""""""""""""""""""""


**Default**: ``"phpactor"``


.. _param_file_path_resolver.application_root:


``file_path_resolver.application_root``
"""""""""""""""""""""""""""""""""""""""


**Default**: ``null``


.. _param_file_path_resolver.enable_cache:


``file_path_resolver.enable_cache``
"""""""""""""""""""""""""""""""""""


**Default**: ``true``


.. _param_file_path_resolver.enable_logging:


``file_path_resolver.enable_logging``
"""""""""""""""""""""""""""""""""""""


**Default**: ``true``


.. _LoggingExtension:


LoggingExtension
----------------


.. _param_logging.enabled:


``logging.enabled``
"""""""""""""""""""


Type: boolean


**Default**: ``false``


.. _param_logging.fingers_crossed:


``logging.fingers_crossed``
"""""""""""""""""""""""""""


Type: boolean


**Default**: ``false``


.. _param_logging.path:


``logging.path``
""""""""""""""""


Type: string


**Default**: ``"application.log"``


.. _param_logging.level:


``logging.level``
"""""""""""""""""


Type: string


**Default**: ``"warning"``


**Allowed values**: "emergency", "alert", "critical", "error", "warning", "notice", "info", "debug"


.. _param_logger.name:


``logger.name``
"""""""""""""""


Type: string


**Default**: ``"logger"``


.. _param_logging.formatter:


``logging.formatter``
"""""""""""""""""""""


**Default**: ``null``


.. _ComposerAutoloaderExtension:


ComposerAutoloaderExtension
---------------------------


.. _param_composer.enable:


``composer.enable``
"""""""""""""""""""


Include of the projects autoloader to facilitate class location. Note that when including an autoloader code _may_ be executed. This option may be disabled when using the indexer


**Default**: ``true``


.. _param_composer.autoloader_path:


``composer.autoloader_path``
""""""""""""""""""""""""""""


Path to project's autoloader, can be an array


**Default**: ``"%project_root%\/vendor\/autoload.php"``


.. _param_composer.autoload_deregister:


``composer.autoload_deregister``
""""""""""""""""""""""""""""""""


Immediately de-register the autoloader once it has been included (prevent conflicts with Phpactor's autoloader). Some platforms may require this to be disabled


**Default**: ``true``


.. _param_composer.class_maps_only:


``composer.class_maps_only``
""""""""""""""""""""""""""""


Register the composer class maps only, do not register the autoloader - RECOMMENDED


**Default**: ``true``


.. _ConsoleExtension:


ConsoleExtension
----------------


.. _param_console.verbosity:


``console.verbosity``
"""""""""""""""""""""


Verbosity level


**Default**: ``32``


**Allowed values**: 16, 32, 64, 128, 256


.. _param_console.decorated:


``console.decorated``
"""""""""""""""""""""


Whether to decorate messages (null for auto-guessing)


**Default**: ``null``


**Allowed values**: true, false, null


.. _PhpExtension:


PhpExtension
------------


.. _param_php.version:


``php.version``
"""""""""""""""


Consider this value to be the project\'s version of PHP (e.g. `7.4`). If omitted
it will check `composer.json` (by the configured platform then the PHP requirement) before
falling back to the PHP version of the current process.


**Default**: ``null``


.. _LanguageServerExtension:


LanguageServerExtension
-----------------------


.. _param_language_server.catch_errors:


``language_server.catch_errors``
""""""""""""""""""""""""""""""""


**Default**: ``true``


.. _param_language_server.enable_workspace:


``language_server.enable_workspace``
""""""""""""""""""""""""""""""""""""


If workspace management / text synchronization should be enabled (this isn't required for some language server implementations, e.g. static analyzers)


**Default**: ``true``


.. _param_language_server.session_parameters:


``language_server.session_parameters``
""""""""""""""""""""""""""""""""""""""


Phpactor parameters (config) that apply only to the language server session


**Default**: ``[]``


.. _param_language_server.method_alias_map:


``language_server.method_alias_map``
""""""""""""""""""""""""""""""""""""


Allow method names to be re-mapped. Useful for maintaining backwards compatibility


**Default**: ``[]``


.. _param_language_server.diagnostic_sleep_time:


``language_server.diagnostic_sleep_time``
"""""""""""""""""""""""""""""""""""""""""


Amount of time to wait before analyzing the code again for diagnostics


**Default**: ``1000``


.. _param_language_server.diagnostics_on_update:


``language_server.diagnostics_on_update``
"""""""""""""""""""""""""""""""""""""""""


Perform diagnostics when the text document is updated


**Default**: ``true``


.. _param_language_server.diagnostics_on_save:


``language_server.diagnostics_on_save``
"""""""""""""""""""""""""""""""""""""""


Perform diagnostics when the text document is saved


**Default**: ``true``


.. _param_language_server.diagnostics_on_open:


``language_server.diagnostics_on_open``
"""""""""""""""""""""""""""""""""""""""


Perform diagnostics when opening a text document


**Default**: ``true``


.. _param_language_server.diagnostic_providers:


``language_server.diagnostic_providers``
""""""""""""""""""""""""""""""""""""""""


Specify which diagnostic providers should be active (default to all)


**Default**: ``null``


.. _param_language_server.diagnostic_outsource:


``language_server.diagnostic_outsource``
""""""""""""""""""""""""""""""""""""""""


If applicable diagnostics should be "outsourced" to a different process


**Default**: ``true``


.. _param_language_server.file_events:


``language_server.file_events``
"""""""""""""""""""""""""""""""


Register to receive file events


**Default**: ``true``


.. _param_language_server.file_event_globs:


``language_server.file_event_globs``
""""""""""""""""""""""""""""""""""""


**Default**: ``["**\/*.php"]``


.. _param_language_server.profile:


``language_server.profile``
"""""""""""""""""""""""""""


Logs timing information for incoming LSP requests


**Default**: ``false``


.. _param_language_server.trace:


``language_server.trace``
"""""""""""""""""""""""""


Log incoming and outgoing messages (needs log formatter to be set to ``json``)


**Default**: ``false``


.. _param_language_server.shutdown_grace_period:


``language_server.shutdown_grace_period``
"""""""""""""""""""""""""""""""""""""""""


Amount of time (in milliseconds) to wait before responding to a shutdown notification


**Default**: ``200``


.. _param_language_server.phpactor_bin:


``language_server.phpactor_bin``
""""""""""""""""""""""""""""""""


Internal use only - name path to Phpactor binary


**Default**: ``"\/home\/mamazu\/packages\/phpactor\/phpactor\/lib\/Extension\/LanguageServer\/..\/..\/..\/bin\/phpactor"``


.. _param_language_server.self_destruct_timeout:


``language_server.self_destruct_timeout``
"""""""""""""""""""""""""""""""""""""""""


Wait this amount of time (in milliseconds) after a shutdown request before self-destructing


**Default**: ``2500``


.. _param_language_server.diagnostic_outsource_timeout:


``language_server.diagnostic_outsource_timeout``
""""""""""""""""""""""""""""""""""""""""""""""""


Kill the diagnostics process if it outlives this timeout


**Default**: ``5``


.. _LanguageServerCompletionExtension:


LanguageServerCompletionExtension
---------------------------------


.. _param_language_server_completion.trim_leading_dollar:


``language_server_completion.trim_leading_dollar``
""""""""""""""""""""""""""""""""""""""""""""""""""


If the leading dollar should be trimmed for variable completion suggestions


**Default**: ``false``


.. _LanguageServerReferenceFinderExtension:


LanguageServerReferenceFinderExtension
--------------------------------------


.. _param_language_server_reference_reference_finder.reference_timeout:


``language_server_reference_reference_finder.reference_timeout``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Stop searching for references after this time (in seconds) has expired


**Default**: ``60``


.. _LanguageServerWorseReflectionExtension:


LanguageServerWorseReflectionExtension
--------------------------------------


.. _param_language_server_worse_reflection.workspace_index.update_interval:


``language_server_worse_reflection.workspace_index.update_interval``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Minimum interval to update the workspace index as documents are updated (in milliseconds)


**Default**: ``100``


.. _param_language_server_worse_reflection.inlay_hints.enable:


``language_server_worse_reflection.inlay_hints.enable``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable inlay hints (experimental)


**Default**: ``false``


.. _param_language_server_worse_reflection.inlay_hints.types:


``language_server_worse_reflection.inlay_hints.types``
""""""""""""""""""""""""""""""""""""""""""""""""""""""


Show inlay type hints for variables


**Default**: ``false``


.. _param_language_server_worse_reflection.inlay_hints.params:


``language_server_worse_reflection.inlay_hints.params``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""


Show inlay hints for parameters


**Default**: ``true``


.. _param_language_server_worse_reflection.diagnostics.enable:


``language_server_worse_reflection.diagnostics.enable``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""


Enable diagnostics


**Default**: ``true``


.. _LanguageServerIndexerExtension:


LanguageServerIndexerExtension
------------------------------


.. _param_language_server_indexer.workspace_symbol_search_limit:


``language_server_indexer.workspace_symbol_search_limit``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""


**Default**: ``250``


.. _param_language_server_indexer.reindex_timeout:


``language_server_indexer.reindex_timeout``
"""""""""""""""""""""""""""""""""""""""""""


Unconditionally reindex modified files every N seconds


**Default**: ``300``


.. _LanguageServerCodeTransformExtension:


LanguageServerCodeTransformExtension
------------------------------------


.. _param_language_server_code_transform.import_name.report_non_existing_names:


``language_server_code_transform.import_name.report_non_existing_names``
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""


Show an error if a diagnostic name cannot be resolved - can produce false positives


**Default**: ``true``


.. _LanguageServerConfigurationExtension:


LanguageServerConfigurationExtension
------------------------------------


.. _param_language_server_configuration.auto_config:


``language_server_configuration.auto_config``
"""""""""""""""""""""""""""""""""""""""""""""


Type: boolean


Prompt to enable extensions which apply to your project on language server start


**Default**: ``true``


.. _IndexerExtension:


IndexerExtension
----------------


.. _param_indexer.enabled_watchers:


``indexer.enabled_watchers``
""""""""""""""""""""""""""""


Type: array


List of allowed watchers. The first watcher that supports the current system will be used


**Default**: ``["inotify","watchman","find","php"]``


.. _param_indexer.index_path:


``indexer.index_path``
""""""""""""""""""""""


Type: string


Path where the index should be saved


**Default**: ``"%cache%\/index\/%project_id%"``


.. _param_indexer.include_patterns:


``indexer.include_patterns``
""""""""""""""""""""""""""""


Type: array


Glob patterns to include while indexing


**Default**: ``["\/**\/*.php","\/**\/*.phar"]``


.. _param_indexer.exclude_patterns:


``indexer.exclude_patterns``
""""""""""""""""""""""""""""


Type: array


Glob patterns to exclude while indexing


**Default**: ``["\/vendor\/**\/Tests\/**\/*","\/vendor\/**\/tests\/**\/*","\/vendor\/composer\/**\/*"]``


.. _param_indexer.stub_paths:


``indexer.stub_paths``
""""""""""""""""""""""


Type: array


Paths to external folders to index. They will be indexed only once, if you want to take any changes into account you will have to reindex your project manually.


**Default**: ``[]``


.. _param_indexer.poll_time:


``indexer.poll_time``
"""""""""""""""""""""


Type: integer


For polling indexers only: the time, in milliseconds, between polls (e.g. filesystem scans)


**Default**: ``5000``


.. _param_indexer.buffer_time:


``indexer.buffer_time``
"""""""""""""""""""""""


Type: integer


For real-time indexers only: the time, in milliseconds, to buffer the results


**Default**: ``500``


.. _param_indexer.follow_symlinks:


``indexer.follow_symlinks``
"""""""""""""""""""""""""""


Type: boolean


To allow indexer to follow symlinks


**Default**: ``false``


.. _param_indexer.project_root:


``indexer.project_root``
""""""""""""""""""""""""


Type: string


The root path to use for scanning the index


**Default**: ``"%project_root%"``


.. _param_indexer.reference_finder.deep:


``indexer.reference_finder.deep``
"""""""""""""""""""""""""""""""""


Type: boolean


Recurse over class implementations to resolve all references


**Default**: ``true``


.. _param_indexer.implementation_finder.deep:


``indexer.implementation_finder.deep``
""""""""""""""""""""""""""""""""""""""


Type: boolean


Recurse over class implementations to resolve all class implementations (not just the classes directly implementing the subject)


**Default**: ``true``


.. _param_indexer.supported_extensions:


``indexer.supported_extensions``
""""""""""""""""""""""""""""""""


Type: array


File extensions (e.g. `php`) for files that should be indexed


**Default**: ``["php","phar"]``


.. _ObjectRendererExtension:


ObjectRendererExtension
-----------------------


.. _param_object_renderer.template_paths.markdown:


``object_renderer.template_paths.markdown``
"""""""""""""""""""""""""""""""""""""""""""


Paths in which to look for templates for hover information.


**Default**: ``["%project_config%\/templates\/markdown","%config%\/templates\/markdown"]``


.. _LanguageServerPhpstanExtension:


LanguageServerPhpstanExtension
------------------------------


.. _param_language_server_phpstan.enabled:


``language_server_phpstan.enabled``
"""""""""""""""""""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _param_language_server_phpstan.bin:


``language_server_phpstan.bin``
"""""""""""""""""""""""""""""""


Path to the PHPStan executable


**Default**: ``"%project_root%\/vendor\/bin\/phpstan"``


.. _param_language_server_phpstan.level:


``language_server_phpstan.level``
"""""""""""""""""""""""""""""""""


Override the PHPStan level


**Default**: ``null``


.. _param_language_server_phpstan.config:


``language_server_phpstan.config``
""""""""""""""""""""""""""""""""""


Override the PHPStan configuration file


**Default**: ``null``


.. _param_language_server_phpstan.mem_limit:


``language_server_phpstan.mem_limit``
"""""""""""""""""""""""""""""""""""""


Override the PHPStan memory limit


**Default**: ``null``


.. _LanguageServerPsalmExtension:


LanguageServerPsalmExtension
----------------------------


.. _param_language_server_psalm.enabled:


``language_server_psalm.enabled``
"""""""""""""""""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _param_language_server_psalm.bin:


``language_server_psalm.bin``
"""""""""""""""""""""""""""""


Type: string


Path to psalm if different from vendor/bin/psalm


**Default**: ``"%project_root%\/vendor\/bin\/psalm"``


.. _param_language_server_psalm.show_info:


``language_server_psalm.show_info``
"""""""""""""""""""""""""""""""""""


Type: boolean


If infos from psalm should be displayed


**Default**: ``true``


.. _param_language_server_psalm.use_cache:


``language_server_psalm.use_cache``
"""""""""""""""""""""""""""""""""""


Type: boolean


If the Psalm cache should be used (see the `--no-cache` option)


**Default**: ``true``


.. _param_language_server_psalm.error_level:


``language_server_psalm.error_level``
"""""""""""""""""""""""""""""""""""""


Override level at which Psalm should report errors (lower => more errors)


**Default**: ``null``


.. _param_language_server_psalm.threads:


``language_server_psalm.threads``
"""""""""""""""""""""""""""""""""


Type: integer


Set the number of threads Psalm should use. Warning: NULL will use as many as possible and may crash your computer


**Default**: ``1``


.. _param_language_server_psalm.timeout:


``language_server_psalm.timeout``
"""""""""""""""""""""""""""""""""


Type: integer


Kill the psalm process after this number of seconds


**Default**: ``15``


.. _LanguageServerPhpCsFixerExtension:


LanguageServerPhpCsFixerExtension
---------------------------------


.. _param_language_server_php_cs_fixer.enabled:


``language_server_php_cs_fixer.enabled``
""""""""""""""""""""""""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _param_language_server_php_cs_fixer.bin:


``language_server_php_cs_fixer.bin``
""""""""""""""""""""""""""""""""""""


Path to the php-cs-fixer executable


**Default**: ``"%project_root%\/vendor\/bin\/php-cs-fixer"``


.. _param_language_server_php_cs_fixer.env:


``language_server_php_cs_fixer.env``
""""""""""""""""""""""""""""""""""""


Environment for PHP CS Fixer (e.g. to set PHP_CS_FIXER_IGNORE_ENV)


**Default**: ``{"XDEBUG_MODE":"off","PHP_CS_FIXER_IGNORE_ENV":true}``


.. _param_language_server_php_cs_fixer.show_diagnostics:


``language_server_php_cs_fixer.show_diagnostics``
"""""""""""""""""""""""""""""""""""""""""""""""""


Whether PHP CS Fixer diagnostics are shown


**Default**: ``true``


.. _param_language_server_php_cs_fixer.config:


``language_server_php_cs_fixer.config``
"""""""""""""""""""""""""""""""""""""""


Set custom PHP CS config path. Ex., %project_root%/.php-cs-fixer.php


**Default**: ``null``


.. _PhpCodeSnifferExtension:


PhpCodeSnifferExtension
-----------------------


.. _param_php_code_sniffer.enabled:


``php_code_sniffer.enabled``
""""""""""""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _param_php_code_sniffer.bin:


``php_code_sniffer.bin``
""""""""""""""""""""""""


Path to the phpcs executable


**Default**: ``"%project_root%\/vendor\/bin\/phpcs"``


.. _param_php_code_sniffer.env:


``php_code_sniffer.env``
""""""""""""""""""""""""


Environment for PHP_CodeSniffer (e.g. to set XDEBUG_MODE)


**Default**: ``{"XDEBUG_MODE":"off"}``


.. _param_php_code_sniffer.show_diagnostics:


``php_code_sniffer.show_diagnostics``
"""""""""""""""""""""""""""""""""""""


Whether PHP_CodeSniffer diagnostics are shown


**Default**: ``true``


.. _param_php_code_sniffer.args:


``php_code_sniffer.args``
"""""""""""""""""""""""""


Additional arguments to pass to the PHPCS process


**Default**: ``[]``


.. _param_php_code_sniffer.cwd:


``php_code_sniffer.cwd``
""""""""""""""""""""""""


Working directory for PHPCS


**Default**: ``null``


.. _LanguageServerBlackfireExtension:


LanguageServerBlackfireExtension
--------------------------------


.. _param_blackfire.enabled:


``blackfire.enabled``
"""""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _ProphecyExtension:


ProphecyExtension
-----------------


.. _param_prophecy.enabled:


``prophecy.enabled``
""""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _BehatExtension:


BehatExtension
--------------


.. _param_behat.enabled:


``behat.enabled``
"""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _param_behat.config_path:


``behat.config_path``
"""""""""""""""""""""


Path to the main behat.yml (including the filename behat.yml)


**Default**: ``"%project_root%\/behat.yml"``


.. _param_behat.symfony.di_xml_path:


``behat.symfony.di_xml_path``
"""""""""""""""""""""""""""""


If using Symfony, set this path to the XML container dump to find contexts which are defined as services


**Default**: ``null``


.. _SymfonyExtension:


SymfonyExtension
----------------


.. _param_symfony.enabled:


``symfony.enabled``
"""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``


.. _param_symfony.xml_path:


``symfony.xml_path``
""""""""""""""""""""


Path to the Symfony container XML dump file


**Default**: ``"%project_root%\/var\/cache\/dev\/App_KernelDevDebugContainer.xml"``


.. _param_completion_worse.completor.symfony.enabled:


``completion_worse.completor.symfony.enabled``
""""""""""""""""""""""""""""""""""""""""""""""


Enable/disable the Symfony completor - depends on Symfony extension being enabled


**Default**: ``true``


.. _param_public_services_only:


``public_services_only``
""""""""""""""""""""""""


Only consider public services when providing analysis for the service locator


**Default**: ``false``


.. _PHPUnitExtension:


PHPUnitExtension
----------------


.. _param_phpunit.enabled:


``phpunit.enabled``
"""""""""""""""""""


Type: boolean


Enable or disable this extension


**Default**: ``false``

