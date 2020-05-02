VIM / NeoVim
============

.. _lsp_client_vim_coc:

CoC
---

Install Phpactor with :ref:`installation_global` then:

::

   Plug 'neoclide/coc.nvim', {'branch': 'release'}

Restart VIM and type ``:CocConfig`` to edit the CoC configuration, enter
the follwing:

::

   {
       "languageserver": {
           "phpactor": {
               "trace.server": "verbose",
               "command": "phpactor",
               "args": ["language-server"],
               "filetypes": ["php","cucumber"],
               "initializationOptions": {
               },
               "settings": {
               }
           }
       },
   }

You can pass Phpactor :ref:`configuration` in the
``initializationOptions``.

Autozimu
--------

Install Phpactor with :ref:`installation_global` then:

::

   Plug 'autozimu/LanguageClient-neovim', {
       \ 'branch': 'next',
       \ 'do': 'bash install.sh',
       \ }

And let it know about Phpactor:

::

   let g:LanguageClient_serverCommands = {
       \ 'php': [ 'phpactor', 'server:start', '--stdio']
       \}

See the `github
repository <https://github.com/autozimu/LanguageClient-neovim>`__ for
more details.

Troubleshooting
---------------

Two dollars on variables
~~~~~~~~~~~~~~~~~~~~~~~~

This can happen because of the ``iskeyword`` setting in VIM.

You can try adding ``$`` to the list of keywords to solve the problem:

::

   autocmd FileType php set iskeyword+=$

or configure Phpactor to trim the ``$`` prefix in ``.phpactor.json``:

::

   {
       "language_server_completion.trim_leading_dollar": true
   }
