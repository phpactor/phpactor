.. _vim_plugin:

VIM RPC Plugin
==============

This is the original VIM plugin, and is bundled with Phpactor by default.

Installation
------------

**Prerequisites**:

-  `Composer <https://getcomposer.org/download>`__
-  PHP 7.3
-  `VIM 8 <https://github.com/vim/vim>`__ or
   `Neovim <https://github.com/neovim/neovim>`__

Using the `vim-plug <https://github.com/junegunn/vim-plug>`__ plugin
manager add the following in your VIM configuration (e.g. ``~/.vimrc``
or ``~/.config/nvim/init.vim`` when using Neovim):

::

   Plug 'phpactor/phpactor', {'for': 'php', 'tag': '*', 'do': 'composer install --no-dev -o'}

Reload VIM (or ``:source ~/.vimrc``) then update your plugins:

::

   :PlugInstall

If you need to install the dependencies manually, then:

::

   $ cd ~/.vim/plugged/phpactor
   $ composer install

Now open a PHP file and issue the following command ``:PhpactorStatus``:

::

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

To find out more about the plugin type ``:help phpactor``

Troubleshooting
~~~~~~~~~~~~~~~

``E492: Not an editor command: PhpactorStatus``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You need to open a PHP file before using Phpactor.

``Phpactor requires at least PHP 8.0``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you run an older version of PHP by default, you will need to install
another version and set ``:phpactorPhpBin`` in your ``.vimrc`` (or equivalent):

.. code:: vim

     let g:phpactorPhpBin = "/usr/bin/php7.3"

``Composer not found** or **Git not detected``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The Git and Composer checks are referring to the current “workspace”
(i.e. where you started Vim from). If you’ve already setup Git and
Composer for your project, ensure you are starting Vim from the project
directory to enable detection.

Usage and Configuration
-----------------------

To find out how to use the plugin type ``:help phpactor`` or view the
:doc:`../vim-plugin/man`.

Complementary Plugins
---------------------

The following plugins add more functionality to Phpactor

-  `ncm2-phpactor <https://github.com/phpactor/ncm2-phpactor>`__:
   Integrates with the `ncm2 <https://github.com/ncm2/ncm2>`__
   autocompletion manager (for Neovim).
-  `deoplete-phpactor <https://github.com/kristijanhusak/deoplete-phpactor>`__:
   Integrates with
   `deoplete <https://github.com/Shougo/deoplete.nvim>`__
-  `coc-phpactor <https://github.com/phpactor/coc-phpactor>`__:
   Integrates with
   `CoC <https://github.com/neoclide/coc.nvim>`__
-  `phpactor-mappings <https://github.com/camilledejoye/phpactor-mappings>`__:
   Provides sensible default key mappings for Phpactor.
