Changelog
=========

## develop

Features:

  - [reference-finder] Goto type: goto the type of the symbol under the cursor #892 - @dantleech
  - [worse-reflection] Enable cache lifetime (important for long running
    processes) (#929) - @dantleech
  - [vim-plugin] Detect the current workspace directory (#923) - @przepompownia
  - [language-server] Included in the core - @dantleech
  - [indexer] Indexer included in the core - @dantleech
  - [rpc] Add docblock prose to hover 
  - [vim-plugin] Add support `:checkhealth` and provide `:PhpactorStatus` in
    terminal window (#974) - @elythyr

Improvements:

  - [text-document] Include `<` and `>` when getting "class" name undercursor
    (allow implorting `Foobar` from an `@var array<Foobar>` doc
  - [completion] Option to deduplicate suggetions (#905) - @dantleech
  - [completion] Option to limit completion options - @dantleech
  - [completion] Allow completors to return `true` when they finish (allow
    final consumer to know if list is complete) - @elythyr

Bug fixes:

  - [vim-plugin] Configuration was not global (#964) - @elythyr
  - [class-mover] `$` was removed when renaming static variables (#925) -
    @dantleech
  - [class-to-file] Remove duplicate candidates (fixes issue with class
    completion duplicate suggestions)

Documentation:

  - [doc] Fix examples in refactoring documentation - @Great-Antique
  - [doc] Fix example mappings and add missing commands - @yeagassy

## 2020-03-04 0.14.1

Bug fixes:

  - [vim-plugin] Fix `force_reload` behavior with `g:useOpenWindow`

## 2020-03-01 0.14.0

Features:

  - [vim-plugin] Introduces Commands for user actions (instead of having to
    call the functions)
  - [vim-plugin] Generate the VIM help from the plugin's code documentation
  - [code-builder] Support for nullable types - @elythyr / @dantleech
  - [code-builder] Generates typed property for PHP 7.4 - @elythyr
  - [worse-reflection] Support for PHP 7.4 property types - @dantleech / @elythyr
  - [phpactor|code-builder] Allow to override the templates by PHP version - @elythyr
  - [phpactor] Auto-detection of project PHP version - @dantleech
  - [code-transform|rpc] Import missing classes - @dantleech
  - [context-menu] Invoke menu for the nearest actionable node (i.e. you can
    invoke the context menu on whitespace now) - @elythyr
  - [vim-plugin] Extract functions handles motions @elythyr
  - [vim-plugin] Jumping to another file preserves the jumplist @elythyr
  - [class-mover] Jump to implementation - @dantleech

Bug fix:

  - [code-transform] Cannot rename variable from anonymous function variable
    (#829) - @dantleech
  - [code-transform] Complete constructor does not take into account aliased
    imports (#886) - @dantleech
  - [code-builder] New aliased class imports alias not added (#860) - @dantleech
  - [worse-reflection] instanceof returns negative if class implements
    interface but extends another class - @dantleech
  - [worse-reflection] foreach key variable resolves as symbol type "unknown"
    - @dantleech
  - [text-document] Word splitting includes commas, and other non-word chars
    (#851) - @einenlum
  - [worse-reflection] Functions wrongly memonized as classes - @dantleech
  - [class-new-cli] response shows source code instead of path (#792)
  - [class-new] Wrong file path when destination shares the same namespace as source (#795).

Improvements:

  - [vim-plugin] Better handling of `json_decode` errors
  - [vim-plugin] Add option to switch to open windows
    `g:phpactorUseOpenWindows` - @przepompownia
  - [vim-plugin] Stable context menu shortcuts - @dantleech (#896)

## 2019-10-23 0.13.5

Bug fix:

  - [text-document] `?` included with word-at-offset #833

## 2019-09-13 0.13.4

Bug fixes:

  - [text-document] Word-at-offset offset off by one #816

## 2019-08-25 0.13.3

Bug fixes:

  - [context-menu] Import class from context menu not working #816

## 2019-08-25 0.13.0

Features:

  - [vim-plugin] Add new `GotoDefinition[Vsplit|Hsplit|Tab]` functions.
  - [code-builder] Initial support for nullable types - @einenlum
  - [vim-plugin] FZF integration for list inputs (#769) - @elythyr
  - [vim-plugin] FZF multiple selection (#773). @elythyr
  - [vim-plugin] Maintain correct cursor position after certain text diffs (#770) - @elythyr
  - [code-transform|rpc] Generate multiple accessors for a class - @elythyr
  - [code-tranform] Generate static methods if the call was static (#25) - @einenlum
  - [completion] Use declared classes as completion source
  - [import-class] Import declared classes (as long as they can be statically
    resolved).
  - [rpc] Class import uses offset to determine type to import
  - [class-mover] Possiblity to move related any files whose relations are
    defined in `navigator.destinations` (for both command and rpc)
  - [worse-reflection] Support virtual class properties (in addition to
    methods).

Bug fixes:

  - [completion] Signature helper does not work on interfaces (#752) - @taluu
  - [code-builder] Class import doesn't work with single element namespace
    #760
  - [code-builder] Variant is not passed to class generator (#766)
  - [phpactor|cli] response shows source code instead of path (#792)
  - [class-mover|rpc] Fix order of open/close operations, prevent VIM crashing

BC Break:

  - [rpc] Import class no longer requires name parameter. RPC version changed
    to version 2.
  - [code-transform] Generate accessors is now a class action and allows
    generation of multiple accessors.

## 2019-03-03 0.12.0

BC Break:

  - [completion] Comletion API changed to accept the new
    [TextDocument](https://github.com/phpactor/text-document).

Features:

  - [goto-definition] Goto definition extracted from core into separate
    packages including [extension
    point](https://github.com/phpactor/reference-finder-extension).
  - [goto-definition] Support for "plain text" goto class definition, works
    for docblocks, and non-PHP files.
  - [completion] Do not suggest non-static method on static calls.
  - [completion] Suggest ::class constant, fixes #673
  - [completion] Docblock type injection allow name to be omitted #618
  - [application] Log errors in command error handler (for logging async
    completion errors using the complete command)
  - [worse-reflection] Support variadic arguments #621
  - [worse-reflection] Support for virtual methods #682
  - [worse-reflection] Support for evaluating `clone()`
  - [worse-reflection] Support for registering custom virtual class member
    providers.
  - [vim-plugin] Find references shows context line #706
  - [code-builder] Trait support, thanks @dshoreman

Improvements:

  - [code-transform] Support extracting expressions to methods #666
  - [code-transform] Extract method adds return statement to calling code if
    extracted code contained a return #704
  - [worse-reflection] Support union catch #711

Bug fixes:

  - [completion] Fix type resolution immediately following docblock #678
  - [completion] Include `$` on static properties #677
  - [extension-manager] Do not install dev dependencies for extensions #674
  - [class-to-file] sort candidates by path length #712 thanks @greg0ire
  - [code-transform] Rename variable includes anonumous function use #713
  - [worse-reflection] Do not downcast union types in named docblocks #711
  - [code-transform] Extract method sometimes creates method in new class in
    same file #730
  - [code-transform] Add Missing Properties added trait props in new class #726

## 2018-12-21 0.11.1

  - [application] Resolve the vendor directory correctly when Phpactor
    included as a dependency, thanks @kermorgant

## 2018-12-02 0.11.0

BC Break:

  - [rpc] All handlers must now be registered with the "name" attribute.

Features:

  - [worse-reflection-extension] Allows new framewalkers to be registered
    (i.e. new ways to infer types).
  - [config] Support loading config from JSON files

Improvements:

  - [rpc] Handlers are lazy loaded, improving the RPC baseline latency

## 2018-11-26 0.10.0

BC Break:

  - [php] Bumped minimum PHP version to 7.1
  - [config] Renamed `reflection.enable_cache` => `worse_reflection.enable_cache`
  - [config] Renamed `reflection.stub_directory` => `worse_reflection.stub_directory`
  - [config] Renamed `autoload` => `composer.autoloader_path`
  - [config] Renamed `autoload.deregister` => `composer.autoload_deregister`

Features:

  - [ExtensionManager] Facility to dynamically add extensions to Phpactor
  - [RPC] `extension_list`, `extension_remove` and `extension_install`
    handlers.
  - [Completion] Class alias completor, #592
  - [CodeTransform] Cycle class member visiblity #521
  - [RPC] Adds `hover` handler which shows the synopsis of the symbol
    underneath the cursor.
  - [Completion] Introduction of a type-specific completion registry, to allow
    registration of completors for different sources, e.g. cucumber.

Improvements:

  - [Application] Do not eagerly load commands (~20% baseline improvement)
  - [Transform] Complete constructor will work work on ! interfaces #597
  - [Transform] Import missing types on generate method
  - [Transform] Adds return types on generate method
  - [CodeBuilder] Do not add additional spaces when importing classes
  - [Completion] Completion qualifiers to allow reusable way to determine
    candidate completors.
  - [Vim Plugin] The "label" for omni complete suggestions is now truncated to
    a specified length.

Other:

  - [Console] Config dump now only shows JSON format
  - [Completion] Completors now `yield` suggestions and problems are no longer
    returned. The `issues` key returned from suggestions is now deprecated.
  - [Vim Plugin] The "omni error" feature has been removed (as completion no
    longer returns them).

## 2018-08-04 0.9.0

BC Breaks:

  - [RPC Plugins] a new `update_file_source` method is now returned by most
    code-transforming RPC handlers (e.g. import class, complete constructors).
    this is used in place of `replace_file_source`.
    See [https://github.com/phpactor/phpactor/issues/550](#550) for details

Deprecations:

  - [Completion|Completion Plugins] Serialized key `info` is deprecated in favour of
    `short_description` and could be removed, at least, in 0.10.0.

Features:

  - [RPC] `open_file` command now has a `force_reload` flag
  - [Completion|Vim Plugin] Auto-import class names (thanks @kermorgant for
    improvements)
  - [Completion] Suggestion types now have more explicit types (e.g. `method`,
    `constant`, `class`, rather than the VIM-centric kind characters).
  - [WorseReflection] Fallback to inferring property types from constructor assignments.
  - [RPC|Vim Plugin] RPC handler for file class info (e.g. namespace, class
    FQN) and VIM functions new `phpactor#getNamespace()` and
    `phpactir#getClassFullName()`. Thanks @voronkovich
  - [WorseReflection] Reflect any user-defined functions parsed when including
    the Composer autoloader. #562
  - [WorseReflection] Support trait alias maps. #540
  - [RPC] Return semantic RPC protocol version in response (starting at `1.0.0`).
  - [Completion] Complete constructor parameters.

Improvements:

  - [Rpc|VIM Plugin] Source code is now updated (by way of a diff algorithm)
    not replaced. The cursor position and undo history are maintained.
  - [VIM Plugin] Regression test for Transform RPC call.
  - [Application] Make class completion candidate limit configurable.
  - [WorseReflection] Foreach Frame walker: inject keys in foreach loop, #578
  - [RPC] find references: Do not return files with no concrete references,
    #581
  - [CodeBuilder] Tracks which nodes have been modified after factory
    creation.
  - [ClassToFile] Composer class-to-file strategy no longer discards inferior
    prefix lengths from consideration, fixes #576

Bug fixes:

  - [Completion] Fixed multi-byte issue with class completor.
  - [VIM Plugin] Allow duplicate name suggestions (e.g. same class short-name
    different FQNs) in omni-complete results.
  - [CodeBuilder] Builder attempts to act on a string (when return type is f.e.
    self). #529
  - [WorseReflection] Fix fatal error when `Parameter#getName()` returns NULL in
    SymbolContextResolver. #533
  - [CodeBuilder] Fix for unrelated methods being updated, #583

## 2018-08-03 0.8.0

Improvements:

  - [WorseReflectoin] Smoke test for find parsing errors.
  - [WorseReflection] Improved efficiency for frame building.
    non-variable.
  - [Completion] Improved multi-byte performance, fixes #537 thanks
    @weirdan

Bug fixes:

  - [WorseReflection] Handle fatal error on incomplete extends.
  - [WorseReflection] Handle fatal error on instanceof coercion on
  - [Completion] Fixed class member container resolution accuracy
  - [SourceCodeFilesystem] Quote regular expressions in file list filter, fixes #543

Misc

  - [RPC] Refactored handlers to define input requirements more explicitly.

## 2018-07-02 0.7.0

Features:

  - [CodeTransforn] Extract expression
  - [Application] Changed behavior of Transform command: accepts globs, shows
    diffs and writes to files (rather than just dumping them to stdout if they
    changed).
  - [Completion] Support constant completion
  - [Application] Use version from composer instead of hard-coded version.
    Thanks @weirdan

Improvements:

  - [Completion] Support namespaced functions, fixes #473.
  - [Completion] Sort completion results alphabetically.
  - [Docs] Added section on completion.
  - [WorseReflection] Explicitly do not support anonymous classes when
    resolving nodes, fixes #505.

Bug fixes:

  - [WorseReflection] Do not parse non-PHP files when building stub cache.
  - [Completion] Fixed last non-whitespace char detection, fixes #504

Misc

  - Downgraded composer to 1.x as 2.x-dev now requires PHP 7.2

## 2018-06-16 0.6.0

Features:

  - [CodeTransform] Transformer to fix namesapce / class name #474

Improvements:

  - [WorseReflection] Resolve UseNameVariables (e.g. context menu `use ($f<>oo)`). #466
  - [Application] Improved status (show current version) #481
  - [CodeTransform] Better handling of new file generation
  - [Docs] Added Development section

Bug fixes:

  - [WorseReflection] access property on null error when resolving incomplete
    function variable use.
  - [CodeTransform] Generate method does can use pseudo type for return type #486
  - [Vim Plugin] Goto reference in a modified file causes warning #477.
  - [Application] Overridden CWD not being passed to `Paths` (affected config
    file resolution).
  - [Application] Fixed find references regression (only the current class
    wasn't being checked for references..)

## 2018-05-20 0.5.0

Features:

  - [Completion] Parameter completion, suggests variables that are valid for
    the parameter position.

Refactoring:

  - [SourceCodeFilesystem] Public API accepts scalar paths in addition to
    value objects.

Improvements:

  - [Documentation] Updated VIM completion plugin docs including
    `phpactor/ncm-phpactor` fork (mainline is not maintained currently).

## 2018-05-01 0.4.0

Features:

  - [Navigation] Reflection navigation: navigate to related classes (currently
    supports parent class and interfaces).
  - [Completion] Built-in function completion, #371
  - [Completion] _Experimental_ class completion: complete use, new and
    extends. Class names inferred from file names.
  - [GotoDefinition] Goto function definitions (currently limited to functions
    defined by the PHPStorm stubs).

Improvements:

  - [ClassMover] Find/replace references will only traverse possible classes
    when givn a known class member #349 (also it will no longer ask the scope,
    instead defaulting to either composer or full-filesystem search depending
    on env).
  - [ClassMover] (RPC) Will update current (unsaved) source.
  - [vim-plugin] Correctly handle expanding class when at beginning of word, #438 thanks @greg0ire 
  - [vim-plugin] Reload file before replacing contents, fixes #445
  - [vim-plugin] File references, do not show quick fix list if all references
    are in current file.
  - [vim-plugin] Completion - trigger on any word-like, fixes #443
  - [WorseReflection] Support for `@property` type override (but doesn't
    create a "pretend" property).
  - [Application] Pass the Phpactor vendor directory as an argument to the
    Application and include vendor files (e.g. stubs) relative to that, fixes
    #460
  - [Application] Use XDG data directory for cache.
  - [Documentation] Typo fix, thanks @pierreboissinot

Bug fixes:

  - [RPC] Import class from context menu, uses context class path
    instead of current #448
  - [CodeBuilder] Regression where already-existing names are imported fixes
    #452
  - [Application] Fixed location of cache directory.
  - [Application] Fixed binary path, thanks @talbergs
  - [RPC] Specify completion type for text input, fixes #455

Refactoring:

  - [WorseReflection] Full support for reflecting functions.
  - [WorseReflection] All member collections extend common interface,
    class-likes have a `members(): ReflectionMemberCollection` method.
  - [Completion] Refactored to make interface more efficient, decoupled
    formatting from completion.
  - [Completion] Made existing completors a subset of tolerant-parser
    completors (means there is one "chain" tolerant completor which delegates
    to the other completors and we only have to parse once).

## 0.3.0

Features:

  - [Application] Disable XDebug by default, very much improve performance.
      Fixes #317

Improvements:

  - [Completion] Do not evaluate left operand when completing expression,
    #380
  - [RPC] Request validation (no more undefined index errors).
  - [WorseReflection] Classes inherit constants from interfaces.
  - [CodeBuilder] Use statements added after the first lexigraphically
    inferior existing use-statement, fixes #176. Thanks @greg0ire.

Bug fixes:

  - [WorseReflection] Associated class for trait methods is the trait
    itself, not the class it's used in, #412
  - [WorseReflection] Do not evaluate assignments with missing tokens.
  - [SourceCodeFilesystem] Non-existing paths not ignored.
  - [CodeTransform] Indentation not being taken into account for code
    updates (fixes #423).
  - [WorseReflection] Tolerate incomplete if statements, fixes #424
  - [WorseReflection] Tolerate missing token in expression evaluator #430

## 0.2.0

Features:

   - [VIM Plugin] `g:phpactorBranch` can be used to set the update branch.
   - [WorseReflection] Support parenthesised expressions (i.e. complete for `(new Foobar())->`), #279

Improvements:

   - [Application] Large restructuring of code, almost everything is now in an extension.
   - [WorseReflection] [problem with name import](https://github.com/phpactor/worse-reflection/pull/37) (thanks @adeslade)
   - [WorseReflection] All class members implement common interface, fixes
     #283
   - [VIM Plugin] Disable the omni-complete errors by default, as this breaks
     the assumptions of some auto-complete managers (set
     `g:phpactorOmniError` to `v:true` to enable again), fixes #370.
   - [VIM Plugin] Only define settings if not already set.
   - [WorseReflection] `Type#__toString` represents arrays and collections
   - [WorseReflection] Improved `Type` class.
   - [Completion] Use partial match to filter class members, fixes #321
   - [phpactor.vim] Correctly return start position for omni-complete
   - [Docblock] Be tolerant of invalid tags, fixes #382
   - [WorseReflection] Refactored FrameBuilder: Extracted walkers
   - [WorseReflection] [Expression evaluator](https://github.com/phpactor/worse-reflection/blob/master/lib/Core/Inference/ExpressionEvaluator.php).

Bugfixes:

   - [SourceCodeFilesystem] Support symlinks in vendor dir #396
   - [WorseReflection] trait lists were not being correctly interpreted #320
   - [WorseReflection] could not find class "NULL"...
   - [SourceCodeFilesystem] Support symlinks in vendor dir #396
   - [Dockblock] Tolerate extra spaces, fixes #365
   - [Completion] Was using the type of the first declared variable, instead
     of the last before the offset.
   - [Completion] Used `Type#__toString` to reflect class.
   - [CodeBuilder] Extract method rewrites arguments #361
   - [VimPlugin] Fixed goto definition, #398
   - [WorseReflection] [problem with name import](https://github.com/phpactor/worse-reflection/pull/37) (thanks @adeslade)

## 0.1.0 

**2018-04-03**

First tagged version, changes from 30th March.

- **CodeTransform**
  - New implementation of class import
      - Offer to alias existing classes,
      - Error message if class in same namespace,
- **Completion**
    - New [Completion library](https://github.com/phpactor/completion).
    - Improved formatting.
    - Local variable completion.
- **Documentation**
    - Configuration [documentation](http://phpactor.github.io/phpactor/configuration.html).
    - Better Drupal integration (thanks @fenetikm).
    - VIM Plugin documentation (`:help phpactor`) (thanks @joereynolds)
- **RPC**
    - Request Replay: replay requests made from the IDE.
- **WorseReflection**
    - Docblocks for Arrays and simple `Collection<Type>` supported.
    - Foreach supported.
    - Method `@param` supported.
- **Infrastructure**
    - All packages are on packagist.
    - [Infrastructure] Do not store PHPBench results on Travis if PR is a fork.
- Various bug fixes everywhere.
