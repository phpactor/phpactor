VIM / NeoVim
============

.. _lsp_client_vim_coc:

Client Guides
-------------


.. tabs::

    .. tab:: CoC


        Install Phpactor with :ref:`installation_global` then install `CoC
        <https://github.com/neoclide/coc.nvim>`_:

        ::

           Plug 'neoclide/coc.nvim', {'branch': 'release'}
           
        Once you have both installed there are two ways of integrating `phpactor` into `coc`:

        - **Installing the coc phpactor extension**:
        
        Restart VIM and type ``:CocInstall coc-phpactor``.

        If Phpactor is already installed you can set ``phpactor.path`` in
        ``:CocConfig`` to point to the Phpactor binary.
        
        At the root level:
        
       ::
       
        {
            "phpactor.enable":true,
            "phpactor.path": "/home/vivo/phpactor/bin/phpactor",
        }
        
        - **Without phpactor extension**:
        
        Restart VIM and type `:CocConfig`, you can set up phpactor as a language server for php files directly to `coc-settings.json`:
        
       ::
       
        "languageserver": {
            "phpactor": {
                "command": "phpactor",
                "args": ["language-server"],
                "trace.server": "verbose",
                "filetypes": ["php"]
            }
        }

        See `coc-phpactor<https://github.com/phpactor/coc-phpactor>` for more
        information.

    .. tab:: Autozimu

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
    "languageserver": {
        "phpactor": {
            "command": "phpactor",
            "args": ["language-server"],
            "trace.server": "verbose",
            "filetypes": ["php"]
        }
    }

or configure Phpactor to trim the ``$`` prefix in ``.phpactor.json``:

::

   {
       "language_server_completion.trim_leading_dollar": true
   }
