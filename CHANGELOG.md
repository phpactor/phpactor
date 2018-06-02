Changelog
=========

## develop

Features:

  - [CodeTransform] Transformer to fix namesapce / class name

Improvements:

  - [Application] Improved status (show current version) #481

Bug fixes:

  - [CodeTransform] Generate method does can use pseudo type for return type #486
  - [Vim Plugin] Goto reference in a modified file causes warning #477.
  - [Application] Overridden CWD not being passed to `Paths` (affected config
    file resolution).
  - [Application] Fixed find references regression (only the current class
    wasn't being checked for references..)

## master

  - [Docs] Added Development section

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
