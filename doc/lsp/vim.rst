VIM / NeoVim
============

.. _lsp_client_vim:

Client Guides
-------------


.. tabs::

    .. tab:: Neovim LSP

        Prerequisites:
            - Neovim 0.5.0 or higher.
            - `nvim-lspconfig <https://github.com/neovim/nvim-lspconfig>`_ package (this contains common LSP configurations)
            - The ``phpactor`` binary is :ref:`installed<installation>` and executable in your path

        For example: include it in your ``.vimrc`` with Plug:

        ::

            Plug 'neovim/nvim-lspconfig'

        Then enable it in your ``.vimrc.`` (note that `on_attach` is a
        `callback
        <https://github.com/neovim/nvim-lspconfig#suggested-configuration>`_
        which can be used for key bindings):

        ::

            lua << EOF
            require'lspconfig'.phpactor.setup{
                on_attach = on_attach,
                init_options = {
                    ["language_server_phpstan.enabled"] = false,
                    ["language_server_psalm.enabled"] = false,
                }
            }
            EOF

        The ``init_options`` key maps directly to Phpactors :ref:`ref_configuration`.

        Please refer to the `nvim-lspconfig
        <https://github.com/neovim/nvim-lspconfig>`_ package for keybindings
        (also see ``:help lsp``).

        See :doc:`vim-lsp` for useful snippets (e.g. reindex, show config, etc).

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
               "phpactor.enable": true,
               "phpactor.path": "/home/vivo/phpactor/bin/phpactor"
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

        I am using the following CoC key bindings and configuration:

        ::

            " Select range based on AST
            nmap <silent><Leader>r <Plug>(coc-range-select)
            xmap <silent><Leader>r <Plug>(coc-range-select)

            " Navigations
            nmap <Leader>o <Plug>(coc-definition)
            nmap <Leader>O <Plug>(coc-type-definition)
            nmap <Leader>I <Plug>(coc-implementation)
            nmap <Leader>R <Plug>(coc-references)

            " List code actions available for the current buffer
            nmap <leader>ca  <Plug>(coc-codeaction)

            " Use <CR> to validate completion (allows auto import on completion)
            inoremap <expr> <cr> pumvisible() ? "\<C-y>" : "\<C-g>u\<CR>"

            " Hover
            nmap K :call <SID>show_documentation()<CR>
            function! s:show_documentation()
              if (index(['vim','help'], &filetype) >= 0)
                execute 'h '.expand('<cword>')
              else
                call CocAction('doHover')
              endif
            endfunction

            " Text objects for functions and classes (uses document symbol provider)
            xmap if <Plug>(coc-funcobj-i)
            omap if <Plug>(coc-funcobj-i)
            xmap af <Plug>(coc-funcobj-a)
            omap af <Plug>(coc-funcobj-a)
            xmap ic <Plug>(coc-classobj-i)
            omap ic <Plug>(coc-classobj-i)
            xmap ac <Plug>(coc-classobj-a)
            omap ac <Plug>(coc-classobj-a)
            autocmd CursorHold * silent call CocActionAsync('highlight')

        See `coc-phpactor <https://github.com/phpactor/coc-phpactor>`_ for more
        information.

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
