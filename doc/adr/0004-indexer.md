New Indexer
===========

Supporting "mutliple indexes" has been a feature request for some time, but
why?

- **Workspace Folders**: Supporting multiple workspace folders - particularly
  useful for editors such as VSCode and Sublime Text.
- **Index Size**: Being able to reuse f.e. the same `phpstorm-stubs` index on
  all projects, as currently they are reindexed each time.

Other things:

- Support **multiple files for the same definition**. For example the PHPUnit
  polyfill `TestCase` will often be located instead of the real PHPUnit
  `TestCase`, see also the numerous **Laravel doesn't work** issues.
- **Multiple workers**: Currently all indexing happens in the main Phpactor
  process. We should instead start a number of indexing **workers** and send
  files paths (or batches) to them. This would improve indexing
  performance dramatically.

LSP Workspace Folders
---------------------

https://microsoft.github.io/language-server-protocol/specifications/lsp/3.17/specification/#workspace_workspaceFolders

Support multiple **project roots** per workspace.

Requirements:

- Need to be able to _add_ or _remove_ workspaces at runtime.

Implementation:

- Workspace folders are ignorant of eachother.
- Commands such as move class need to apply only on the current "workspace".
- Workspace symbol search aggregates symbols from _all_ workspace folders.

Scala Metals
------------

[Worspace Folders Blog Post](https://scalameta.org/metals/blog/2023/07/17/workspace-folders/).

In Metals each endpoint first calls `getServiceFor` which returns an "service"
configured for the **workspace containing the text document**:

```
  override def completion(
      params: CompletionParams
  ): CompletableFuture[CompletionList] =
    getServiceFor(params.getTextDocument.getUri).completion(params)
```

Option: Phpactor Container Instance Switching
---------------------------------------------

The closest concept in Phpactor is where we copy the DI container with
incoming connection parameters (e.g. with a TCP server a new client connects
with a different root URI and gets an entirely new Phpactor "instance" to
itself).

Therefore one option may be to have a facade exposing the LSP API and
switch to (or create) a **new Phpactor container instance** for each operation
that accepts a file (as with Metals). The container to use will be determined
by the workspace folder in which the file sits.

Workspace-wide operations (such as workspace symbols) would need to
_aggregate_ the symbols from all Phpactor instances.

Non-Option: Different Indexes for Each Folder
---------------------------------------------

The (or my) knee-jerk reaction to the Workspace Folders feature was that we
need to support multiople indexes.

This would mean:

- For completion - select the correct index before searching for results.
- For source location - select the correct `composer.json` to use to locate
  classes.
- Different cache per workspace (you could have two different projects with
  different versions of classes).

In short - lots of **pain** and complexity.

Multi-Index
-----------

Having multople _indexes_ could be solving a _different_ problem. It allows us to have
(for example) a _system-wide_ index for `phpstorm-stubs` which also having
separate indexes for the `project` and `vendor` which may be useful for
prioritisation?!

- [project] ---------\
- [phpstorm stubs] ------> [
- [vendor]  ---------/

```
IndexerBuilder::create()
    ->addPath('vendor')
    ->addPath('src')
    ->addPath('/home/daniel/www/phpactor/phpactor/vendor/phpstorm/phpstorm-stubs/')
    ->addPath('stubs')
```

This approach would enable us to:

- Not reindex the PHPStorm stubs in each project.
- Separate the project and vendor indexes and apply different policies to
  them.

For example knowing that a file is in `vendor` can be useful as we could
disable diagnostics - this feature could be facilitated by multiple indexes,
but could also be implemented in different ways.

Parallel Indexing
-----------------

Perhaps a better investment in time would be allowing index _jobs_ which can
run in the background. For example 4 processes would index the project 4 times
faster than 1 process while also running _in the background_ and not
intefering with the master Phpactor process.

