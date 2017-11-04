Phpactor VIM Plugin
===================

![phpactor2sm](https://user-images.githubusercontent.com/530801/27995098-82e72c4c-64c0-11e7-96d2-f549c711ca8b.png)

The Phpactor VIM plugin intends to close the gap between VIM and PHP IDEs such
as PHPStorm while remaining lightweight.

Features
--------

- [Omni-completion](#omni-completion): Intelligent auto-completion with *no indexing!*.
- [Context Menu](#context-menu): Show list of actions to take on the current
  cursor position.
- [Insert use statement](#include-use-statement): Automatically search for and include the use
  statement for class under cursor.
- [Expand class to FQN](#class-expand): Search for class under cursor and
  expand it to its FQN.
- [Tranformations](#transformations): Apply transformations (implement contracts, etc).
- [Move and copy classes](#move-class): Move/copy classes and update references to them.
- [Find references](#class-references): Populate the quick fix list with
  references to the symbol under the cursor (currently classes and methods).
- [Go-to definition](#goto-definition): Goto the definition of a class or a
  class member.
- [Create class](#create-class): New class in an empty file, or generate in a
  new tab for a class name under the cursor.
- [Inflect class](#inflect-class): Generate a new class based on an existing
  class (e.g. generate interface)
- [Navigate](#navigate): Navigate to related source files.

Installation
------------

Using Vundle, add the plugin to your `.vimrc`:

```
Plugin 'phpactor/phpactor'
```

Afterwards you can run

```vim
:call phpactor#Update()
```

To composer update and install the dependencies.

Example configuration
---------------------

```
" Omni-completion
autocmd FileType php setlocal omnifunc=phpactor#Complete

" Include use statement
map <Leader>u :call phpactor#UseAdd()<CR>
map <Leader>e :call phpactor#ClassExpand()<CR>
map <Leader>pp :call phpactor#ContextMenu()<CR>
map <Leader>o :call phpactor#GotoDefinition()<CR>
map <Leader>pd :call phpactor#OffsetTypeInfo()<CR>
map <Leader>pfm :call phpactor#MoveFile()<CR>
map <Leader>pfc :call phpactor#CopyFile()<CR>
map <Leader>tt :call phpactor#Transform()<CR>
map <Leader>cc :call phpactor#ClassNew()<CR>
map <Leader>fr :call phpactor#FindReferences()<CR>

" Show information about "type" under cursor including current frame
nnoremap <silent><Leader>d :call phpactor#OffsetTypeInfo()<CR>

" Specify a different PHP binary to use when calling Phpactor
" let g:phpactorPhpBin = "/usr/bin/local/php6"
```

**NOTE**: The above mappings are probably sub-optimal, feel free to find a
something that works for you.

Omni-completion
---------------

To enable omni-completion type `set omnifunc=phpactor#Complete` or use the
confuration above to automatically enable it for all PHP files.

![recording](https://user-images.githubusercontent.com/530801/29006615-94356fe2-7af3-11e7-9d73-775d6f9f487a.gif)

To invoke omni complete in insert mode `<C-x><C-o>` (`ctrl-x` then `ctrl-o`).
See `:help compl-omni`.

**NOTE**: Omni-completion works, but it's not great. It is better to use
Phpactor as a backend for an as-you-type completion manager:

- [ncm-phpactor](https://github.com/roxma/ncm-phpactor): Integration for
  Neovim Completion Manager.

Context Menu
------------

![recording](https://user-images.githubusercontent.com/530801/31052985-96b96e10-a692-11e7-8d43-681f85c636d0.gif)

Allow the selection of an action to take on the symbol at the current cursor
position.

```bash
:call phpactor#ContextMenu()
```

Include use statement
---------------------

Will attempt to include the use statement for the word (class name) under the
cursor:

```bash
:call phpactor#UseAdd()
```

Expand Class
------------

Expand class under the cursor

```bash
:call phpactor#ClassExpand()
```

Goto Definition
---------------

Will try and goto the definition of a class, or a class member (method,
property, constant).

```bash
:call phpactor#GotoDefinition()
```

Move class
----------

Move the (class-containing) file in the current buffer to a new location and
update the class name and all references to it in the current git-tree.

```bash
:call phpactor#MoveFile()
```

Find References
---------------

Find references to the symbol under the cursor and populate the quickfix list
with them:

```bash
:call phpactor#FindReferences()
```

Copy class
----------

As with move, but simply copy the current file and updat the class name in the
copied file to be consistent with the filename.

```bash
:call phpactor#CopyFile()
```

Transformations
---------------

![recording](https://user-images.githubusercontent.com/530801/27984415-92800230-63cd-11e7-8492-d5a7a93bb6f0.gif)

Apply transformations to the current buffer:

```bash
:call phpactor#Transform
1: complete_constructor
2: implement_contracts
Type number and <Enter> or click with mouse (empty cancels): 
```

Create Class
------------

![recording](https://user-images.githubusercontent.com/530801/28240939-2d17c42c-6982-11e7-9ddb-9ecddf55ac87.gif)

```vimscript
:call phpactor#ClassNew()
```

Prompt for the create of a new class. If the cursor is on
a class name, it will suggest to create that class.

```bash
Create class: lib/Container/Bar
1: default
2: exception
Type number and <Enter> or click with mouse (empty cancels): 1
```

Inflect Class
-------------

```vimscript
:call phpactor#ClassInflect()
```

Prompt for the creation of a new class based on the current class or if the
cursor is on a class name, based on that.

```bash
Inflect class: lib/Container/BarInterface.php
1: interface
Type number and <Enter> or click with mouse (empty cancels): 1
```

Navigate
--------

```vimscript
:call phpactor#Navigate()
```

Offer a choice of related file destinations, f.e.

```bash
Destination:
1) system_test
2) unit_test
3) source
```

Destinations are configured in `.phpactor.yml`:

```
navigator.destinations:
    source: lib/<kernel>.php
    unit_test: tests/Unit/<kernel>Test.php
    integration_test: tests/Integration/<kernel>Test.php
    system_test: tests/System/<kernel>Test.php
```

Classes can be automatically created by mapping detinations to [new class](#create-class)
variants:

```
navigator.autocreate:
  source:default
  unit_test:phpunit_test
```
