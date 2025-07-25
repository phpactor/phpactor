Changelog
=========

## 2025-07-25.0

Improvements:

  - Extract document highlighter to own module and add config option to
    disable it @dantleech
  - Fix false diagnostic for missing `__destruct` return type @przepompownia #2900
  - Add depth info to `worse:dump-ast` command @mamazo #2897
  - Ensure that stub locator results are cached in-memory @dantleech #2911
  - Upload PHAR as artifact on builds @drzraf #2915

Features:

  - Search filtering (as applicable to autocomplete, name importing etc)
    @dantleech

Bug fixes:

  - Require `ext-tokenizer` (fixes nixos distribution) @drupol
  - Don't complete HEREDOC identifier @przepompownia #2909
  - Fix parameter type resolution priority @dantleech
  - Fix completion rendering when snippets are disabled @mamazu #2898

## 2025.04.17.0

Improvements:

  - Do not suggest code action for missing return type if type is accurately
    provided by docblock @dantleech
  - Do not generate `void` return type on PHP 7.0

Bug fixes:

  - Support loading code templates when Phpactor included as a dependency
    @zobo

## 2025.03.28.0

Improvements:

  - Reference finding: Ask for confirmation to continue after soft timeout @dantleech #2856
  - PHAR fixes for Windows @zobo
  - LSP - Support for inline values @zobo
  - Code action prioritization @mamazu

## 2025.02.21.0

Features:

  - String <=> Heredoc code action #2825 @mamazu
  - Support new expression without parenthesis #2811
  - Support vscode evaluatable expressions #2905 @zobo
  - Runtime support for PHP 8.4 #2829
  - Initial support for property hooks @dantleech #2833

Improvements:

  - Performance: Do not run Indexed reference finder if references handled by
    Variable reference finder @dantleech
  - Performance: Do needlessly re-index documents before searching for
    references @dantleech
  - Psalm: add `config` option to specify Psalm config @GDXbsv #2835
  - Completion for `@internal`  tag #2827 @mamazu
  - Add documentation for Nova Language Client #2830 @EmranMR 
  - Enable fill constructor code action on attributes #2810 @mamazu
  - Require `ext-mbstring` extension to avoid off-by-one issues #2838 @dantleech

Bug fix:

  - Handle zero modulo evaluation @dantleech
  - Do not use FQNs for imported classes in generated docblocks #2843 @dantleech

Documentation:

  - Add information for Zed editor @sethstha #2836

## 2024-11-28.1

Bug fixes:

- Do not include the file scheme in when including/excluding files #2794

## 2024-11-28

Features:

  - Show codes for all diagnostics and allow them to be ignored @dantleech
    #2781

Improvements:

  - Do not highlight entire class for fix class/namespace name diagnostic
    #2728 @dantleech
  - Tolerate code action provider failures #2761 @dantleech
  - Limit number of methods that are documented on classes to improve
    completion/resolve performance for large classes #2768 @dantleech

Bug fixes:

  - Navigator: Fix attempt to create existing directories #2776 @bart-jaskulsi
  - Fix goto constant within a trait #2784 @dantleech
  - Preserve PHAR scheme when indexing PHAR stubs @dantleech #2754
  - Fix duplicated types when updating methods @mamazu #2779

## 2024-11-05

Bug fixes:

  - Docblock: support parsing quoted string literals as array valuyes #2730
  - Tell WorseReflection about new definitions from the stdin for the
    diagnostics process #2723
  - Flush index on save (make latest changes available to diagnostic process) #2722
  - Fix bad contextual filtering #2715 @dantleech
  - Take optional parameters into account with conditional types #2700 @dantleech
  - Fix import position when `declare` is present #2698 @dantleech
  - Fix NULL error in Docblock parser #2693 @dantleech
  - Handle "source not found" when resolving template map #2716 @dantleech

Improvements:

  - Allow exclude patterns to be set for diagnostics (e.g. `vendor/**/*`) #2705 @dantleech
  - Improve formatting for override method #2702 @dantleech
  - Offer completions on attributes not associated with class member body
    #2695 @przepompownia
  - Show prose associated with `@throws` tag #2694 @mamazu
  - Support parsing generic variance e.g. `covariant` #2664 @dantleech
  - Support opt-out of using temporary files with phpstan #2764 @tsterker

Features:

  - Add support for 8.3 typed class constants
  - Basic support for `@{phpstan,psalm}-assert` #2720 @dantleech

## 2024-06-30

Features:

  - PHAR Indexing #2412 #2611 @dantleech
  - Override method refactor #2686 @dantleech

Improvements:

  - Do not use indexer when renaming private properties/methods #2672 @dantleech
  - Fix contextual completion in constructor agrument position #2504
  - Basic support for `array_reduce` stub #2576
  - Support variadics in contextual completion #2603
  - Allow use of `%project_root%` in index paths #2665 @mamazon
  - Fix another `PHP_BINARY` avoid writing to `dev/null` and other windows fixes @MatmaRex
  - Use `get_debug_type` @zonuexe
  - Show strikethrough for deprecated diagnostics #2623 @mamazu
  - Adding more type coverage #2606 #2614 @mamazu

Bug fixes:

  - Fix renaming attributed class members @przepompownia
  - Do not error when PHPStan returns no output @mamazu
  - Only filter new object expression names in contextual completion #2603
  - Fixing include and exclude patterns #2593 @mamazu
  - Fix missing @implements code action #2668 @dantleech
  - Initialized properties don't appear in LSP document symbols #2678 @mamazu

## 2024-03-09

Features:

  - Completion suggestions filtered by accepting type #2456
  - Basic support for local type aliases #2462

Improvements:

  - Show enums in LSP document symbol provider #2575 @gmli
  - PHPStan show tip if as a dignostic hint if available #2512
  - Docblock completion, suggest `@throws` @przepompownia
  - Suggest named parameters on attributes @mamazu
  - Remove redundant documentation #2500 @einenlum
  - Resolve inherited generic types #2474
  - Allow additional CLI arguments to be passed to php code sniffer #2465
  - Clear document diagnostic cache on save #2458
  - Skip parent parameters on complete constructor #2471 @mamazu
  - Support generics on `@mixin` #2463
  - Remove "on develop warning" service #2533
  - Disable the processing of includes/requires, it doesn't work very well but
    it has massive performance impact on certain projects #2580
  - Include project PHP and runtime version and LSP status
  - Add `iterable` "generic" `@param` in docblock #2585
  - Improved diagnostic engine #2584
  - Ongoing windows compatiblity effort #2567 #2572 #2570 @MatmaRex
  - Ignore unnecessary files in gitexport #2570 @zonuexe
  - Improve ANSI test compatiblity #2521 @gerardroche
  - More snippet support #2515 #2508 @przepompownia
  - Add completion for `@throws` #2509 @przepompownia

Bug fixes:

  - Fix completion of constants in enums #2541 @eviljeks
  - Fix `renderException` call in bin/phpactor #2548 @MatmaRex
  - Psalm: fix exception handling #2587 @przepompownia
  - Do not generalize generated return types (i.e. false instead of bool) #2588
  - Fix diagnostic process concurrency and do not lint outdated files #2538
  - Upgrade `amp/process` to fix #2516 thanks to @gerardroche
  - Fix division by zero edge case
  - Fix crash if referenced file no longer exists on class rename #2518
  - Fix detection of import used relatively in an annotation #2539
  - Fix PHAR crashing issue on PHP8.3 #2533
  - Fix UTF-16 conversion for LSP #2530 #2557
  - Fix support for Attributes on readonly classes #2493
  - Fix `$this` undefined var false positive in anon. class #2469 @mamazu
  - Fix `$argv` undefined var false positives #2468 @mamazu

Documentation:

  - Added Helix LSP instructions #2581 @lens0021
  - Fix typos in Behat #2534 @vuon9
  - Fix broken external links #2500 @einenlum

## 2023-12-03

Bug fixes:

  - Support LSP document symbols in traits #2446 @lizhening
  - Fix null variable name crash #2443
  - Fix frame merging of include/require #2391
  - Fix enum representation in method generation #2395
  - Fix enum cases() not existing false-positive #2423
  - Fix incorrect enum import #2400
  - Fix undefined var false positive for arra unpacking #2403
  - Fix autoloading class conflcits with test files #2535 @gerardroche
  - Fix enum renaming in legacy renamer #2445
  - Fix enum renaming on "new" renamer #2445
  - Fix crash on resolveItem() caused by race condition (?) #2434
  - Fix false positive for undefined var where vardoc not counting as variable definition #2437
  - Render variadics as variadics in help, not as arrays #2448
  - Fix representation of int-range min/max #2444
  - Render default value for enum when filling object #2441

Features:

  - Generate enum cases and class constants #2422
  - Generate enum match arms #2401

Improvements:

  - PHPStan: Support setting custom config path and memory limit @ungrim97
  - Exclude tests from archive #2433

Breaking changes:

  - Drop support for PHP 8.0. Minimum version is now 8.1

## 2023.09.24

Bug fixes:

  - Fix crash with `php-cs-fixer` when using strict types rule #2348
  - Fix `null` error (and improve type safety) in the docblock parser #2379
  - Fix undefined-var false positive for undeclared variables that have `@var` #2366
  - Fix undefined-var false positive for pass by ref (again) #2361
  - Do not crash lanugage server if LSP header cannot be parsed (log error
    instead) #2373

Improvements:

  - Correctly implementing LSP ranges #2352 @mamazu
  - Add mechanism to automatically trigger an index update when breaking changes
    are made
  - Method generation on emums @mamazu


Improvements:

  - Support single line comments #2350
  - Do not promote parameters that are used in parent constructor #2119 @mamazu
  - Improve detection of Xdebug @bart-jaskulsi #2347
  - Improve plain docblock parsing #2345
  - Generate `@param` tag for iterables #2343 @mamazu

## 2023.08.06-1

Bug fixes:

  - Limit number of threads Psalm uses to 1 by default #
  - Update file watching lib to handle "process already exited" errors

## 2023.08.06

Improvements:

  - Improve Diagnostics: Run linters in parallel #2327
  - Index documents on save #2326

Bug fixes:

  - Fix generic extends with templated argument #2295
  - Do not report statically declared variables as undefined #2311
  - Do not trigger function completion for incomplete opening PHP tag
  - Fix PHP linter #2318
  - Do not report undeclared variables that are passed by reference as undefined #2329 @mecha

## 2023.06.17-1

Bug fixes:

  - Do not report globals or super globals as undefined #2302

## 2023.06.17

Features:

  - Diagnostics and code action for fixing missing `@implements` and `@extends` #2112
  - Diagnostic for undefined variables #2209
  - Code action to suggest fixes for undefined variables (in case of typos) #2209
  - PHPUnit: code action for adding `setUp` / `tearDown` #2180 @mamazu
  - Making the completion label formatter configurable #2277 @mamazu
  - Auto-reindex: unconditionally reindex modified files every N seconds
    (default 5 minutes) - work around for missed file modification
    notifications.

Improvements:

  - Revised getting started documentation #2282
  - Support indexing PHP files that don't have a `.php` extension #2296
  - Allow language server auto-configuration to be disabled #2159
    (`language_server_configuration.auto_config`)
  - Symfony: show and consider non-public services by default (e.g. in tests it's
    possible to retrieve non-public services) #2263
  - Support traits in enums #2256

Bug fixes:

  - Fix enum case completion #2284
  - Fix error handling for responses from language client #2283
  - Do not show named parameters after string literal argument #2259
  - Fix "instanceof" behavior for statically reflected classes #2273
  - Fix behavior when user cancels type selection on goto type #2270
  - Fix docblock parsing of `array<'quoted'|'strings'>` #2264
  - Fix constant declaration indexing with `define` #2249 @mamazu
  - Fix use of class-string<Foo> variable as static scope resolution qualifier #2238
  - URL decode root URI - fixes issues with special chars in path #2228
  - Do not deduplicate suggestions of different types (e.g. prop/method with same name) #2214
  - Fix list assignment #2226
  - Support parsing interface clause on enums #2220
  - Do not make fully qualified name usage relative in class-mover #2208 @mamazu
  - Fix resolution of `self` type (esp. in relation to traits) #2116
  - Fix different virtual member types with the same name replacing eachother #2108
  - Specify maximum size (255 chars) for string literal types #2144
  - Fix docblock parser with `$this` when used as generic argument #2092

## 2023.04.10

Features:

  - Show references to new objects when finding references to `__construct` method #2194
  - Support for inlay hints #2138
  - Deprecation diagnostics #2120
  - Auto configuration - automatically suggest and apply configuration #2114
  - Transform to "promote" unassigned consturctor properties #2106
  - Hierarchical namespace segment completion #2070
  - Completion for promoted property visiblity #2087
  - Option `language_server.diagnostic_outsource` to outsource diagnostics in separate process #2105

Bug fixes:

  - Also use in-memory files when enanching indexed records #2187
  - Prophecy: Do not crash when used in trait #2129
  - Prophecy: fixing chaining of methods via. `getObjectProphecy` #2122
  - `new class-string<Foo>` now resolves to `new Foo` #2065
  - Fix extract method within trait #2076 @mamazu
  - Do not attempt to index classes whose names are reserved words #2098
  - Fix typo in LanguageServerExtension::PARAM_FILE_EVENTS resulting in typo in documentation
  - Fix parsing array types in `@param` Tags in doc blocks #2172

Improvements:

  - Only show completion suggestions for real attributes #2183, #2100 @mamazu @przepompownia
  - Code action and formatting handlers now send progress notifications #2192
  - Invalidate diagnostics cache only when document changes #2191
  - Optimize analysis for scopes with many many assignments #2188
  - Made some heavy blocking operations non-blocking (e.g. diagnostics, code
    actions).
  - ⚠ Removed frame sorting which increases radically in some cases, but may
    also cause regressions #2179
  - Psalm: Support for overriding the error level #2174
  - Generating constructor at the top of the file #2113 @mamazu
  - Include (complex) docblock params when generating method
  - Take into account named parameters when "guessing" parameter names #2090
  - Show full FQN for classes in hover #2081
  - Upgrade to 3.17 of the language server protocol #2082
  - Facilitate changing visiblity on promoted properties @mamazu
  - Allow generation of constructor for Attributes.

## 2023.01.21

Bug fixes:

  - Allow class completion within constant declaration in class #1985 @przepompownia
  - Do not suggest return type on `__destruct` #1992
  - Do not report Prophecy methods as "not found" #2006
  - Do not add NULL to type list (fixes search bug) #2009
  - Create a real package for the tolerant-parser fork and use it #2033
  - Also highlight use statements when hovering on class #2039 @mamazu
  - Fix priotity of "internal" stub locator - facilitating enum completion #2040
  - Require posix extension #2042 @dacianb
  - Fix evaluation of replacement assignments #1705
  - Fix crash on missing token in Indexer #2049 @vlada-dudr
  - Fix missing compact use name false positive #2052
  - Fix `class-template<T>` when not in 1st arg position #2054

Features:

  - `@param` docblock generation
  - Reintroduce the PHPUnit extension
  - Support integer range type e.g. `int<0, max>` #2024

Improvements:

  - Support the Psalm cache #2046 @gbprod
  - Support completion inside match expression #2051 @przepompownia
  - Fixed typos in documentation #2050 @d-danilov
  - Psalm Extension: allow `info` diagnostics to be hidden #2032 @gbprod
  - Better docblock parsing and formatting #2004
  - More liberal support for vendor tags #2011 @ging-dev
  - Fix nested template type arguments #2016
  - Fix importing of nested types #2009
  - Reverts #1974 - which made the situation worse rather than better.
  - Change default WR cache TTL from 5 seconds to 1 second to avoid race with
    diagnostics timeout.
  - Add return tags to existing docblocks #1995
  - Naive support for attribute completion #2001 @przepompownia
  - Support union type from class-string variadic generic

## 2022.12.12

Breaking changes:

  - Minimum version of PHP changed to 8.0. **Phpactor will no longer run on PHP 7.4**.

Features:

  - [lsp] Generate mutator @florian-merle

Improvements:

  - [wr] Fix inference of array subscript expressions #1961

Bug fixes:

  - [lsp] Prevent race condition that makes old changes get analyzed after new changes #1974
  - [cmp] Constant visibility not taken into account for completion #1979 @przepompownia
  - [rn] Fix crash on rename interface #1982 @nataneb32
  - [wr] Fix crash on enum with custom methods #1966
  - [ls] Log errors even if they are for a request @lumnn
  - [ls] Do not include `results` key in JSON response when error @lumnn
  - [lsp] Do not send workDoneProgress notifications to clients that do not
    support it #1951
  - [lsp] Fix highlighting on PHP 8.1 #1960
  - [wr] Do not crash when encountering an array union operator #1971 @wouterj
  - [wr] Fixing handling of HEREDOC in StringResolver #1977 @mamazu

## 2022.11.12

Features:

  - [ct] Replace qualfier with import LSP refactoring #1939 @mamazu
  - [sf] New Symfony extension #1915
  - [wr] Generic constructor parameters support #1920

Bug fixes:

  - [wr] Fix member template params when declared in interface #1914
  - [cb] Do not prompt to generate constructor when object is given no arguments #1911

Improvements:

  - [cb] Add properties _after_ constants #1917 @mamazu
  - [--] Remove dependency on webmozart/path-util @mamazu
  - [wr] "invokable" type refactoring
  - [--] Do not register services for disabled extensions

Documentation:

  - Added Emacs LSP client guides @zonuexe

## 2022.10.11

Bug fixes:

  - [lsp] Import all unresolved names command no longer dupliates names #1835
  - [tp] Update tolerant parser library fixing issue with parsing `match` keyword #1873
  - [rpc] Fix regression with :PhpactorClassNew opening in `Untitled` buffer #1881
  - [ctf] Fix token issue with simple class-to-file converter #920
  - [wr] Built-in enum members are reflected #1902
  - [wr] Fix iterable generic not being resolved properly #1875

Improvements:

  - [wr] Better modelling of enums
  - [wr] Add additional phpactor-specific stubs (e.g. for Enums)
  - [lsp] Enum hover improvement
  - [lsp] Improve formating signature help parameters #1894
  - [lsp] Highlighting more 10x faster #1891
  - [cmp/lsp] inline type information for completion items
  - [cmp] complete `__construct` on `parent::` #1272
  - [wr] Refactored generic handling

Features:

  - [wr] Support for `class-string<T>` generic
  - [ct] Decorate interface #1879 @mamazu
  - [lsp] Document formatting via. php-cs-fixer #1897
  - [gtd] For member declarations, goto parent member definition if it exists #1886

## 2022.09.11

Bug fixes:

  - [wr] Inconsistent type resolution - removed node level cache #1673
  - [in] Fix exception when indexed file has no path #1643
  - [wr] Do not complete constants on class instance #1614
  - [wr] Include virtual properties in class members #1623
  - [wr] Fix false positive for virutal method not existing #1603
  - [wr] Ignore exceptions (permission denied f.e.) when traversing files #1569
  - [wr] Fix resolutin of virtual method
  - [ct] Fix missing properties refactor does not import class #1534
  - [ct] Fix false diagnostic for missing method #1500
  - [dl] Fix docblock definition location at class level docblocks
  - [idx] Do not try and use non-tokens for property names #1317
  - [cb] Fix rendering of array values in generated code
  - [wr] Fix arrow function completion #1303
  - [dl] Fixed off-by-one error with plain text goto definition
  - [fw] Ensure inotify is stopped before shutting down
  - [wr] properly deconstruct array in foreach
  - [lsp] import unresolved classes refactoring: Ensure only unique names are shown when asking user to select an import candidate
  - [lsp] ensure fully qualified filename is used for generate method refactoring #1313
  - [wr] detect branch determination with throw expression
  - [cr] add missing properties: correctly infer type from call expressions
  - [filesystem] Fix "too many files open" issue #1376
  - [class-mover] Fix long standing bug with aliased imports being duplicated
    on class move and other strange issues.
  - [lsp] Fix call to properties() on non-class in generate accessors provider.
  - [lsp] Fix unresolvable classes not being listed in code actions
  - [cb] Do not apply HTML escaping when rendering code templates
  - [wr] Promoted property docblock types not picked up #1334
  - [completion] Limit results from the search index (improve search performance significantly)

Improvements:

  - [cmp] Show partial namespace to disambiguate class name suggestions
  - [wr] Markdown formatted member completion documentation
  - [ls] publish diagnostics on open and update
  - [ct] Add missing properties for array assignments #1640
  - [cmp] Provide variables from parent frame for anonymous use #1602
  - [ref] Increase reference finder timeout to 1 minute by default #1579
  - [cmp] Improved contextual completion
  - [rn] Fixed numerous issues
  - [rename] Fixed numerous issues
  - [compl] Allow named param completion on functions
  - [wr] Infer return type for generators
  - [lsp] Show Phpactor version info in initialize result @lalanikarim
  - [ls] Fixed class completion performance
  - [ct] Add option to disable importing global functions
  - [cmp] (Better) support for completing imported names #1490
  - [wr] reset,array_shift and array_pop stubs
  - [wr] improved ternary support
  - [log] Include a channel prefxi in log messages
  - [wr] require `ext-pnctl` (language server would crash otherwise)
  - [wr] handle static properly #967
  - [ls] include list of diagnostic providers in status report
  - [ct] add retutn type to generated method if it would immediately return
  - [wr] support array [] addition operator
  - [wr] support in_array type assertion
  - [wr] support for casts
  - [wr] if statement branches
  - [wr] inline type inference
  - [wr] infer param types from _function_ docblock
  - [wr] support for Closure as a type #1413
  - [wr] expressions are evaluated as types
  - [wr] literal types and internal refactorings
  - [ls] Show error message in client if service stops unexpectedly with an
    error
  - [code-transform] Faithfully reproduce documented types in generated code
  - [docblock] New docblock parser to facilitate parsing complex types
  - [hover] Improve "offset" hover (mostly related to showing variable info)
  - [templates] Include templates for creating new interfaces, traits and enums
  - [wr] Resolve type from array access
  - [cb] Preserve `?` operator as distinct from a union type
  - [wr] Support for class-string type (not for type inference however)
  - [completion + location] Better support for union types
  - [cs] Updated CS and converted property docblock types to actual types

Features:

  - [-] Better constant suppoer - indexing, goto def, find references, hover, etc.
  - [cmp] Support absolute name completions
  - [ls] Lazily resolve documentation for completion items
  - [ct] Generate constructor refactoring
  - [ct] Fill object refactoring
  - [ct] Remove unused imports diagnositcs and code transformation #1758
  - [wr] Added native WR single-pass diagnostics #1700
  - [cmd] Index clean command #1691 @mamazu
  - [cmp] (re?)support completion on parent:: #1643
  - [cb] Render types based on PHP version #1655
  - [wr] Support `@property-read`
  - [wr] Support for mixins #990
  - [rf] Support for constants, properties and promoted properties
  - [compl] Docblock completion
  - [wr] Support for intersection types
  - [rf] Union type support for goto definition
  - [ct] Add missing PHP return types
  - [wr] Support for inference for `array_map`, and arrow and anonymous functions
  - [ct] Add missing @return type docblocks code transformation
  - [cmp] Explicitly enable/disable completors and disable `keyword` completor by default.
  - [wr] Support `iterator_to_array`
  - [wr] Handle constant glob to union types (`@return Foo::BAR_*`).
  - [lsp] show class category in offset hover info
  - [lsp] jump to types in a union type
  - [wr] Type combination
  - [wr] Support for type assertions via. is_*, instanceof etc
  - [wr] Array shape type support (types and completion)
  - [wr] Support for variadics
  - [lsp] Send rename file request to client when renaming a PSR class
      @przepompownia
  - [wr] Initial support for generics #1382
  - [lsp] Added generate accessors code action
  - [lsp] Added extract constant code action
  - [extension] Removed the extension manager.
  - [extension] PHPStan and Psalm extensions are now included by default.
  - [lsp] Code action to complete constructor with _public_ properties
  - [php] Bump min. PHP version to 7.4
  - [php] Fix PHP 8.1 deprecations
  - [config] JSON schema support
  - [cli] `phpactor config:init` command to create or update config (to
    include JSON schema location)
  - [completion] Enum support (requires 8.1 PHP runtime)
  - [reference-finder] Enum support (requires 8.1 PHP runtime)
  - [php8.1] Disable deprecation warnings unless `PHPACTOR_DEPRECATIONS`
    provided.

## 2022-01-03 (0.18.0)

Features:

  - [language-server] Import all names refactoring - @dantleech
  - [language-server] Extract expression - @BladeMF
  - [language-server] Extract method generation - @BladeMF
  - [language-server] Initial support for method generation - @BladeMF
  - [langauge-server] Support for renaming files (LSP 3.16) - @dantleech
  - [language-server] Ability to use client file events where available - @dantleech
  - [completion] Experimental support snippets for built-in functions - @weeman1337
  - [completion] Experimental support snippets for class constructos - @weeman1337
  - [completion] Added `experimental` flag
  - [completion] Added flag to enable / disable snippets entirely
  - [language-server] Ensure workspace is indexed before finding references - @dantleech
  - [language-server] Support for renaming class names (short only) - @dantleech
  - [language-server] Rename class members and variables - @BladeMF, @dantleech
  - [language-server] Basic support for workspace symbols.
  - [language-server] Added basic PHP linting by default.

Improvements:

  - [completion] Improve diagnostic message for #1245 - @dantleech
  - [language-server] Allow hover template paths to be customized - @BladeMF
  - [language-server] Show warning in client if extra config keys present - @dantleech
  - [code-transform] Improved performance for unresolvable class name finder - @dantleech
  - [code-transform] Improved information in name-not-found exception -
    @weeman1337
  - [language-server] Do not show "class not found" diagnostics by default - @dantleech
  - [worse-reflection, etc] Update to latest tolerant parser lib to support PHP 8.1 syntax

Bug fixes:

  - [worse-reflection] Fix handling of non-decimal integers - @Slamdunk
  - [worse-reflection] Fix variable detection in closures - @BladeMF
  - [completion] Fix snippet method completion #1172 - @BladeMF
  - [worse-reflection] Fix PHP8.0 deprecation warnings - @gregoire
  - [completion] Tests fail due to jetbrain stubs changes - @weeman1337
  - [worse-reference-finder] Do not know how to create class from type "NULL" #1246 - @dantleech
  - [worse-reflection] Property context class not propagated

## 2021-03-21 (0.17.1)

Features:

  - [completion] Support Attribute Completion

Bug fixes:

  - [language-server] Diagnostics do not tolerate NULL document version on save #1220
  - [worse-reflection] Unhandled exception thrown when variable name is "NULL"
  - [language-server] Unhandled exception when function not found on hover

## 2021-02-06 (0.17.0)

Features:

  - [completion] Support for PHP named parameters - @dantleech
  - [completion] Basic Doctrine annotation completion support - @elythyr
  - [completion] References are sorted alphabetically - @elythyr
  - [completion] Show warning character if method or class is deprecated
  - [completion] Sort class names and fucntions according to proximity to current file by default - @dantleech

  - [composer] Class map only mode by default (do not register autoloader at all, do not include files)
  - [file-watcher] Experimental support for [watchman](https://facebook.github.io/watchman/)
  - [indexer] CLI command for index search (mainly for debugging)
  - [indexer] PhpStorm stubs are now indexed
  - [indexer] Show memory usage and limit in progress notification.
  - [language-server] Import class/function code action and diagnostics
  - [language-server] Transform code actions and diagnostics (complete constructor, implement contracts, fix class name and add missing properties)
  - [completion] Keyword completion - @BladeMF
  - [language-server] Create class code actions - @dantleech
  - [phpactor] Update extensions after install composer hook - @dantleech

Improvements:

  - [worse-reflection] Support for list foreach
  - [worse-reflection] Various issues around NULL and exception handling
  - [worse-reflection] Improved frame resolution performance by 99.5x - @dantleech
  - [worse-reflection] Fixed mixed up start/end positions in symbol resolver - @BladeMF
  - [language-server] Update classes on workspace update - @BladeMF
  - [language-server] New LSP protocol and general refactoring - @dantleech
  - [language-server] Support document symbols (f.e. showing code outline for document)
  - [language-server] Support symbol highlighting
  - [language-server] Support for indexing constants
  - [code-tranform] Generated accessors automatically `ucfirst` the property name when prefix is used. - @einenlum
  - [worse-reflection] Improved inference for property types - @elythyr
  - [worse-reflection] Include virtual members from traits - @scisssssssors
  - [code-tranform] fix invalid missing property diagnostic (#1126) - @elythyr
  - [code-transform] Improve performance for missing properries - @dantleech

Bug fixes:

  - [code-transform] Catch unhandled exceptions - @dantleech
  - [text-document] valid php class names not detected for word-at-offset
  - [code-tranform] Return types not considered for unresolved names - @dantleech
  - [completion] Avoid reflection on NULL
  - [scf] Fix support for moving and removing folders - @Lumnn
  - [indexer] Fix indexing of static properties - @BladeMF
  - [completion] Fix signature help in nested symbols - @BladeMF
  - [worse-reflection] Static properties not resolved - @BladeMF
  - [lanaguge-server] Correctly highlight use statements against qualified
    names - @dantleech
  - [language-server] Fix occasional class-not-found error on code transform (due to incorrectly formatted path)
  - [worse-reflection] Do not consider "iterable" as an FQN - @elythyr
  - [code-transform] Fix trailing line on class import - @elythyr
  - [code-transform] Fix importing class names in docblocks - @elythyr

## 2020-06-09 (0.16.1)

Improvements:

  - [worse-reflection] Support for virtual methods in interfaces - @dantleech
  - [code-transform] Fix regression with importing from root namespace

## 2020-06-09 (0.16.0)

Features:

  - [vim-plugin] Ability to set custom project root strategy (#1027) - @przepompownia
  - [indexer-extension] Workspace reference finder (classes,functions,members) - @dantleech
  - [worse-reflection] Support "final" keyword - @dantleech
  - [language-server-hover] Show "final" keyword on class hover - @dantleech
  - [language-server-hover] Show inherited method documentation - @dantleech
  - [language-server-code-transform] Add command to import class - @dantleech
  - [language-server-completion] Automatically import class on completion confirm - @dantleech
  - [code-transform] Consider current class as a potential conflict for imports - @dantleech
  - [completion] Indexed class name and function completion - @dantleech
  - [indexer-extension] Support "deep references" (search over all implementaions) - @dantleech
  - [composer] Enable disbaling of autoloader inclusion via. `composer.enable` - @dantleech
  - [lanaguage-server-completion] Auto-import functions - @dantleech

Improvements:

  - [code-builder] Removed functionality to "update" parameters: was very
    buggy. Now only new parameters will be added when updating methods via.
    generate method.
  - [language-server-bridge] Service to convert Phpactor Locations to LSP locations - @dantleech
  - [code-transform] Class import updates context name on alias - @dantleech
  - [documentation] Generate the configuration reference - @dantleech
  - [completion-worse] Allow completors to be disabled via `completion_worse.disabled_completors` - @dantleech
  - [indexer-extension] Validate search results (remove from search index if invalid).
  - [language-server] Exit session immediately if NULL given as CWD (instead of crashing).
  - [container] Adds command for introspecting the container (`container:dump`) - @dantleech
  - [indexer-extension] Increase priority of indexer source-locators (they should come before the composer locators) - @dantleech
  - [language-server] Show explicit meassage when indexer dies

Bug fixes;

  - [completion] Completion limit of 32 imposed in 0.15 removed.
  - [ampfs-watch] Inotify watcher not reporting error when out of available
    watchers
    (https://github.com/phpactor/amp-fswatch/commit/1e38faadc3fb73158de9a966ee12d17992dad4fe)
    - @dantleech
  - [ampfs-watch] Buffered watcher not allowing errors to bubble up
    (https://github.com/phpactor/amp-fswatch/commit/b5cb54b6d01a9ec3dcbfdcca804c2d63c0e84a19)
    - @dantleech
  - [language-server] Ensure that `result` key is missing when `NULL` (some
    clients require it) - @dantleech
  - [code-transform] Fixed occasional whitespace issues when importing classes
  - [language-server] Support for LSP commands
  - [indexer] Fixed crash with empty class name

## 2020-05-03 0.15.0

Features:

  - [reference-finder] Goto type: goto the type of the symbol under the cursor #892 - @dantleech
  - [worse-reflection] Enable cache lifetime (important for long running
    processes) (#929) - @dantleech
  - [language-server] Included in the core - @dantleech
  - [indexer] Indexer included in the core - @dantleech
  - [rpc] Add docblock prose to hover
  - [vim-plugin] Add support `:checkhealth` and provide `:PhpactorStatus` in
    terminal window (#974) - @elythyr
  - [ref-finder] Goto definition works for vars (https://github.com/phpactor/worse-reference-finder/pull/1) - @FatBoyXPC
  - [phpactor-ls] workspace/references support - @dantleech

Improvements:

  - [text-document] Include `<` and `>` when getting "class" name undercursor
    (allow implorting `Foobar` from an `@var array<Foobar>` doc
  - [completion] Option to deduplicate suggetions (#905) - @dantleech
  - [completion] Option to limit completion options - @dantleech
  - [completion] Allow completors to return `true` when they finish (allow
    final consumer to know if list is complete) - @elythyr
  - [vim-plugin] Improved command registration (#965) - @elythyr
  - [completion] Improved signature help (https://github.com/phpactor/completion/pull/31) - @elythyr
  - [completion] Completors can return if they are complete (https://github.com/phpactor/completion/pull/30) - @elythyr

Bug fixes:

  - [code-transform] Generate accessor doesn't work on selected property (regression)
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
