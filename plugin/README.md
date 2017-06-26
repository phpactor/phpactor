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
nnoremap <silent><leader>u :call PhactUseAdd()<CR>
nnoremap <silent><Leader>o :call PhactGotoDefinition()<CR>
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
