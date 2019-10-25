---
currentMenu: vim-plugin
---
Phpactor VIM Plugin
===================

- [Installation](#installation)
- [Updating](#updating)
- [Configuration](#configuration)
- [Completion](#completion)
- [Completion plugins](#completion-plugins)
- [Context Menu](#context-menu)
- [Quickfix List](#quickfix-list)
- [Extras](#extras)

Installation
------------

**Prerequisites**:

- [Composer](https://getcomposer.org/download)
- PHP 7.0
- [VIM 8](https://github.com/vim/vim) or
  [Neovim](https://github.com/neovim/neovim)

It is recommended (but not necessary) for you to use a VIM plugin manager. In
this document we will use the [vim-plug](https://github.com/junegunn/vim-plug)
plugin manager, but other plugin managers are quite similar.

Require Phpactor in your VIM configuration file (e.g. `~/.vimrc` or
`~/.config/nvim/init.vim` when using Neovim):

```
Plug 'phpactor/phpactor', {'for': 'php', 'do': 'composer install'}
```

Then update your plugins:

```
:PlugInstall
```

If you need to install the dependencies manually, then:

```
$ cd ~/.vim/plugged/phpactor
$ composer install
```

<div class="alert alert-info">
Make a <b><i class="fa fa-github"></i> <a href="https://github.com/phpactor/phpactor">Pull Request</a></b> to improve this
installation procedure!
</div>

Now open a PHP file and issue the following command `:call phpactor#Status()`:

```
Support
-------
[✔] Composer detected - faster class location and more features!
[✔] Git detected - enables faster refactorings in your repository scope!
[✔] XDebug is disabled. XDebug has a negative effect on performance.

Config files
------------
[✔] /home/daniel/www/phpactor/phpactor/.phpactor.yml
[✔] /home/daniel/.config/phpactor/phpactor.yml
[✘] /etc/xdg/phpactor/phpactor.yml
```

Phpactor works best with Composer - but much functionality including
auto-completion can still work (sometimes slowly depending on project size).

### Troubleshooting

**E117: Unknown function: phpactor#Status**:

Vim-plug and most other package managers for Vim will lazy-load Phpactor when it's
needed, i.e. when opening a PHP file. If you get this error, open a PHP file and
run the command again.

**Composer not found** or **Git not detected**:

The Git and Composer checks are referring to the current "workspace" (i.e. where you
started Vim from). If you've already setup Git and Composer for your project, ensure
you are starting Vim from the project directory to enable detection.

Updating
--------

Updating Phpactor from VIM is easy:

```vim
:call phpactor#Update()
```

<div class="alert alert-warning">
Note that if the update included changes to the VIM plugin you will currently
need to either re-source (`:source ~/path/to/phpactor/plugin/phpactor.vim`) the plugin or reload VIM (pull requests are open!).
</div>

If you are feeling dangerous, you may choose to track the `develop` branch,
by specifying a branch name in your VIM configuration file:

```
let g:phpactorBranch = "develop"
```

Keyboard Mappings
-----------------

The Phpactor plugin will **not** automatically assume any shortcuts, copy
the following configuration into your `.vimrc`:

```vimscript
" Include use statement
nmap <Leader>u :call phpactor#UseAdd()<CR>

" Invoke the context menu
nmap <Leader>mm :call phpactor#ContextMenu()<CR>

" Invoke the navigation menu
nmap <Leader>nn :call phpactor#Navigate()<CR>

" Goto definition of class or class member under the cursor
nmap <Leader>oo :call phpactor#GotoDefinition()<CR>
nmap <Leader>oh :call phpactor#GotoDefinitionHsplit()<CR>
nmap <Leader>ov :call phpactor#GotoDefinitionVsplit()<CR>
nmap <Leader>ot :call phpactor#GotoDefinitionTab()<CR>

" Show brief information about the symbol under the cursor
nmap <Leader>K :call phpactor#Hover()<CR>

" Transform the classes in the current file
nmap <Leader>tt :call phpactor#Transform()<CR>

" Generate a new class (replacing the current file)
nmap <Leader>cc :call phpactor#ClassNew()<CR>

" Extract expression (normal mode)
nmap <silent><Leader>ee :call phpactor#ExtractExpression(v:false)<CR>

" Extract expression from selection
vmap <silent><Leader>ee :<C-U>call phpactor#ExtractExpression(v:true)<CR>

" Extract method from selection
vmap <silent><Leader>em :<C-U>call phpactor#ExtractMethod()<CR>
```

See the [Refactorings](refactorings.md) chapter for more functions you can map
shortcuts to.

<div class="alert alert-info">
If you prefer not to define the mappings yourself then the <a
href="https://github.com/elythyr/phpactor-mappings">Phpactor Mappings</a>
plugin provides you with sensible defaults.
</div>

Phpactor requires at least PHP 7.0. If you use a different version of PHP
locally, you may need to target a new version of PHP - add the following to
your `.vimrc` to change the PHP binary:

```
let g:phpactorPhpBin = "/usr/bin/local/php7.0"
```


Configuration
-------------

The plugin has some configuration options:

```vim
let g:phpactorPhpBin = 'php'
let g:phpactorBranch = 'master'
let g:phpactorOmniAutoClassImport = v:true
let g:phpactorInputListStrategy = 'inputlist|fzf'

" Example of implementation with vim's inputlist() function
function! InputListCustomStrategy(label, choices, ResultHandler)
    echo a:label
    let choice = inputlist(s:add_number_to_choices(a:choices))

    if (choice == 0)
        throw "cancelled"
    endif

    call a:ResultHandler(a:choices[choice - 1])
endfunction

let g:phpactorCustomInputListStrategy = 'InputListCustomStrategy'
```

- `g:phpactorPhpBin`: PHP executable to use.
- `g:phpactorBranch`: Phpactor branch (default is `master`, use `develop` for
  bleeding edge).
- `g:phpactorOmniAutoClassImport`: Automatically import classes when
  completing class names with OmniComplete.
- `g:phpactorInputListStrategy`: Select a strategy for the [Input
  List](#input-list).
- `g:phpactorCustomInputListStrategy`: Specify your own strategy.
- `g:phpactorUseFzfForQuickfix`: Defines if fzf shoudl be used to build the quickfix, default: `v:true`.

Extensions
----------

You can manage your [Phpactor extensions](/extensions.html) from with VIM:

```
call phpactor#ExtensionList()
call phpactor#ExtensionInstall()
call phpactor#ExtensionRemove()
```

Note that these commands are not very verbose. For increased verbosity
execute the commands from the command line (as detailed
[here](/extensions.html).

Completion
----------

### Omni-completion

Omni-completion
([Screenshot](./screenshots.html#code-completion)) is
VIM's built-in auto-completion mechanism.

Add the following to your `.vimrc` in order to use Phpactor for omni-completion (for PHP files):

```vimscript
autocmd FileType php setlocal omnifunc=phpactor#Complete
```

To invoke omni complete in insert mode `<C-x><C-o>` (`ctrl-x` then `ctrl-o`).
See `:help compl-omni`.

For case sensitive searching, set
```vimscript
let g:phpactorCompletionIgnoreCase = 0
```

Omni complete can also provide feedback when something fails to complete, this
can be useful, enable it with:

```
let g:phpactorOmniError = v:true
```


Completion plugins
------------------

Completion plugins provide a significantly better completion experience.

### Neovim Completion Manager (NCM)

*deprecated, use NCM2 below*

The [Neovim Completion
Manager](https://github.com/roxma/nvim-completion-manager) add this to your
(e.g. `~/.config/nvim/init.vim`) (NCM) is a very fast completion manager for
[Neovim](https://neovim.io/), install using the Plug plugin manager:

```vim
Plug 'roxma/nvim-completion-manager'
Plug 'phpactor/ncm-phpactor'
```

### NCM2

The [NCM2](https://github.com/ncm2/ncm2) is the successor to NCM.

See the
[README](https://github.com/phpactor/ncm2-phpactor) file
for instructions on installing NCM2 and the Phpactor plugin.

### Deoplete

[deoplete.nvim](https://github.com/Shougo/deoplete.nvim) is a completion
plugin for both standard VIM Neovim, install it and the Phpactor integration
as follows:

```vimL
Plug 'Shougo/deoplete.nvim'
Plug 'kristijanhusak/deoplete-phpactor'
```

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

Input List
----------

The input list is the window shown to let you choose an item among a list.

### Strategies

This plugin provides two strategy to handle input lists:

- `inputlist`: Vim's internal `inputlist()` function.
- `fzf`: Fuzzy finder using [Fzf](https://github.com/junegunn/fzf) plugin.

You can choose between those strategies by specifying the option
[g:phpactorInputListStrategy](#configuration).

### Input list strategies auto-detection

When no strategy is defined the plugin will default to the `fzf` strategy if
the [Fzf](https://github.com/junegunn/fzf) plugin is loaded or to `inputlist`
if it's not.

### FZF Multi-selection

Some refactorings will allow you to select multiple entires (for example
[override
method](https://phpactor.github.io/phpactor/refactorings.html#override-method).
Use `<tab>` to toggle selection and CTRL-A/CTRL-D to select all/select none. 

See the
[Fzf](https://github.com/junegunn/fzf) documentation for more details.

Quickfix List
-------------

Phpactor will provide you with an alternative to the quickfix list. For each
actions which will populate the quickfix, like `find references`, Phpactor will
make use of [fzf](#fzf) to allow you to filter/open the results before hand.

You will still be able to get the result inside the quickfix by selecting the
elements you are interested in and pressing `ctrl-q` to populate the quickfix
with your selection and open it.

If you don't want this feature you can disable it like so:
```vim
let g:phpactorUseFzfForQuickfix = v:false
```

Extras
------

In order to get the best possible experience we suggests you a few extra tools
that will make using this plugin a lot more appreciable.

[fzf](https://github.com/junegunn/fzf)
--------------------------------------

This is actually not a vim plugin but a tool for the command-line.
It's shipped with a vim plugin that allows to use it inside Vim.

If you have it installed and properly configured for Vim then Phpactor will
make use of it to provide enhance functionalities, for instance:

- [inputlist](#input-list) will take advantage of fzf

[fzf.vim](https://github.com/junegunn/fzf.vim)
----------------------------------------------

This is the actual [fzf](https://github.com/junegunn/fzf) plugin for vim. It
requires you to have [fzf](https://github.com/junegunn/fzf) installed and
configured.

This plugin will allow us to use improved functionalities inside Vim. If you
want to enjoy the full possibilities of both fzf and phpactor we strongly
recommand you to install it!

[bat](https://github.com/sharkdp/bat)
---

This is also a tool for the command-line and not a Vim plugin.
It's ment to be used instead of the command `cat` and bring a lot to the table.

It's used by default, among other possible tools, by fzf to print the preview
window. Allowing you to have a preview of your files with syntaxic coloration!
