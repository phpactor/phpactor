Phpactor VIM Plugin
===================

Installation
------------

Using Vundle:

```
Plugin 'dantleech/phpactor'
```

and then you will need to composer install:

```bash
$ cd ~/.vim/bundles/phpactor
$ composer install
```

Example configuration
---------------------

```
" Omni-completion
autocmd FileType php setlocal omnifunc=PhpactComplete

" Include use statement
map <Leader>u :call phpactor#UseAdd()<CR>
map <Leader>o :call phpactor#GotoDefinition()<CR>
map <Leader>pd :call phpactor#OffsetTypeInfo()<CR>
map <Leader>i :call phpactor#ReflectAtOffset()<CR>
map <Leader>pfm :call phpactor#MoveFile()<CR>
map <Leader>pfc :call phpactor#CopyFile()<CR>

" Show information about "type" under cursor including current frame
nnoremap <silent><Leader>i :call phpactor#OffsetTypeInfo()<CR>
```

Omni-completion
---------------

To enable omni-completion type `set omnifunc=phpactor#Complete` or use the
confuration above to automatically enable it for all PHP files.

The omni-completion uses phpactor's reflection offset type inference and
reflection API.

![recording](https://user-images.githubusercontent.com/530801/27839804-2b309e8e-60ec-11e7-8df4-f5467cf56c8d.gif)

To invoke omni complete in insert mode `<ctrl>-n <ctrl-o>`.

Include use statement
---------------------

Will attempt to include the use statement for the word (class name) under the
cursor:

```
: call phpactor#UseAdd()
```

Goto Definition
---------------

Will attempt to go to the definition of the word (class name) under the
cursor:

```
: call phpactor#GotoDefinition()
```

Move file
---------

Move the (class-containing) file in the current buffer to a new location and
update the class name and all references to it in the current git-tree.

```
: call phpactor#MoveFile()
```

Copy file
---------

As with move, but simply copy the current file and updat the class name in the
copied file to be consistent with the filename.

```
: call phpactor#CopyFile()
```

Reflect at offset
-----------------

Provide a synopsis of the class for the word under the cursor (if a class can
be inferred from it).

```
:call phpactor#ReflectAtOffset()
```
