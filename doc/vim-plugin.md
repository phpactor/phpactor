---
currentMenu: vim-plugin
---
Phpactor VIM Plugin Guide
=========================

- [Installation](#installation)
- [Updating](#updating)
- [Configuration](#configuration)
- [Completion](#completion)
- [Completion plugins](#completion-plugins)
- [Context Menu](#context-menu)

Installation
------------

**Prerequisites**:

- [Composer](https://getcomposer.org/download)
- PHP 7.0
- [VIM 8](https://github.com/vim/vim) or
  [Neovim](https://github.com/neovim/neovim)

Using the [vim-plug](https://github.com/junegunn/vim-plug)
plugin manager add the following in your VIM configuration (e.g. `~/.vimrc` or
`~/.config/nvim/init.vim` when using Neovim):

```
Plug 'phpactor/phpactor', {'for': 'php', 'do': 'composer install --no-dev -o'}
```

Reload VIM (or `:source ~/.vimrc`) then update your plugins:

```
:PlugInstall
```

If you need to install the dependencies manually, then:

```
$ cd ~/.vim/plugged/phpactor
$ composer install
```

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

To find out more about the plugin type `:help phpactor`

### Troubleshooting

**E117: Unknown function: phpactor#Status**:

Vim-plug and most other package managers for Vim will lazy-load Phpactor when it's
needed, i.e. when opening a PHP file. If you get this error, open a PHP file and
run the command again.

**Composer not found** or **Git not detected**:

The Git and Composer checks are referring to the current "workspace" (i.e. where you
started Vim from). If you've already setup Git and Composer for your project, ensure
you are starting Vim from the project directory to enable detection.

Plugin Usage
------------

To find out how to use the plugin type `:help phpactor` or view the plugin documentation  [here](https://raw.githubusercontent.com/phpactor/phpactor/develop/doc/phpactor.txt).

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

Complementary Plugins
---------------------

The following plugins add more functionality to Phpactor

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

### Phpactor Mappings

[phpactor-mappings](https://github.com/elythyr/phpactor-mappings) provides
sensible mapping defaults for Phpactor.

