Phpactor VIM Plugin
===================

- [Installation](#installation)
- [Updating](#updating)
- [Configuration](#configuration)
- [Completion](#completion)
- [Context Menu](#context-menu)

Installation
------------

Install Phpactor using your favorite VIM package manager, or Vundle. Add the
plugin to your `.vimrc`:

```
Plugin 'phpactor/phpactor'
```

Then in VIM:

```
:VundleInstall
```


Now you will need to install the Phpactor dependencies with composer:

```
$ cd ~/.vim/bundles/phpactor
$ composer install
```

or using nvim, do the above where it counts.

Make a [pull request](https://github.com/phpactor/phpactor) to impove the
installation procedure!

<div class="alert alert-info">
This is not a recommendation (or disincentive) to use Vundle, this is just the package manager that I use
at time of writing.
</div>

Updating
--------

Updating Phpactor from VIM is easy:

```vim
:call phpactor#Update()
```

<div class="alert alert-warning">
Note that if the update included changes to the VIM plugin you will currently
need to either resource the plugin or reload VIM (pull requests are open!).
</div>

Keyboard Mappings
-----------------

The Phpactor plugin will **not** automatically assume any shortcuts, copy
the following configuration into your `.vimrc`:

```
" Include use statement
map <Leader>u :call phpactor#UseAdd()<CR>

" Invoke the context menu
map <Leader>mm :call phpactor#ContextMenu()<CR>

" Goto definition of class or class member under the cursor
map <Leader>o :call phpactor#GotoDefinition()<CR>

" Transform the classes in the current file
map <Leader>tt :call phpactor#Transform()<CR>

" Generate a new class (replacing the current file)
map <Leader>cc :call phpactor#ClassNew()<CR>
```

See the [Refactorings](refactorings.md) chapter for more functions you can map
shortcuts to.

Phpactor is requires at least PHP 7.0. If you use a different version of PHP
locally, you may need to target PHP 7.0, you can change the PHP binary the
plugin will use as follows:

```
let g:phpactorPhpBin = "/usr/bin/local/php6"
```

Completion
----------

### Omni-completion

Omni-completion ([Screenshot](http://localhost:8000/screenshots.html#code-completion)) is VIM's built-in auto-completion mechanism. Tell
VIM to use Phpactor for omni-completion in PHP files with the following conifg in `.vimrc`:

```vimscript
autocmd FileType php setlocal omnifunc=phpactor#Complete
```

To invoke omni complete in insert mode `<C-x><C-o>` (`ctrl-x` then `ctrl-o`).
See `:help compl-omni`.

<div class="alert alert-info">
The Omni-Complete method provides <i>feedback</i> messages when it cannot complete something. This information
is <b>useful</b>. Other completion mehanisms may not provide this information.

<b>Always enable omni-completion</b>. If another mechanism is failing to complete, invoke omni-complete to find out why.
</div>

### Neovim Completion Manager

If you are using [Neovim](https://neovim.io/) with the [Neovim Completion
Manager](https://github.com/roxma/nvim-completion-manager) you should certainly
install [ncm-phpactor](https://github.com/roxma/ncm-phpactor) to benefit from
great asynchronous complete-as-you-type auto-completion:

Context Menu
------------

The context menu is the main point of contact with Phpactor. Invoke it on any
class, member, variable, method call, or anything really.

If you move over a method and invoke the context menu with `:call
phpactor#ContextMenu()` (or with `<Leader>mm` as per the configuration above) you
should see something like the following:

```
Method "execute":
[r]eplace_references, (f)ind_references, (g)enerate_method, g(o)to_definition: 
```
