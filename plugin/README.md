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

Example key mappings
--------------------

```
" Include use statement
map <Leader>u :call PhactUseAdd()<CR>
map <Leader>o :call PhactGotoDefinition()<CR>
map <Leader>pd :call PhactOffsetTypeInfo()<CR>
map <Leader>i :call PhactReflectAtOffset()<CR>
map <Leader>pfm :call PhactMoveFile()<CR>
map <Leader>pfc :call PhactCopyFile()<CR>

" Show information about "type" under cursor including current frame
nnoremap <silent><Leader>i :call PhactOffsetTypeInfo()<CR>
```

Include use statement
---------------------

Will attempt to include the use statement for the word (class name) under the
cursor:

```
: call PhactUseAdd()
```

Goto Definition
---------------

Will attempt to go to the definition of the word (class name) under the
cursor:

```
: call PhactGotoDefinition()
```

Move file
---------

Move the (class-containing) file in the current buffer to a new location and
update the class name and all references to it in the current git-tree.

```
: call PhactMoveFile()
```

Copy file
---------

As with move, but simply copy the current file and updat the class name in the
copied file to be consistent with the filename.

```
: call PhactCopyFile()
```

Reflect at offset
-----------------

Provide a synopsis of the class for the word under the cursor (if a class can
be inferred from it).

```
:call PhactReflectAtOffset()
```
