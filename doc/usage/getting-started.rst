Getting Started
===============

How you start depends on your editor.

Currently Phpactor has most support when used over it's own :ref:`legacy RPC protocol <>`.
Most of the functionality is implemented with a language server, which :ref:`those editors <lsp_support>`
support. For this you need to run Phpactor as a :ref:`language_server`.

In addition you can also do somethings from the CLI only for that a
:ref:`global installation <installation_global>` is recommended.

Select one of the following to get started:

.. tabs::

    .. tab:: VIM or Neovim

        Use the :doc:`vim-plugin` or :ref:`set it up as an LSP<lsp_client_vim>`.

    .. tab:: Sublime Text

        Use :ref:`client_rpc_sublime` optionally supplement with
        :ref:`Sublime LSP <lsp_client_sublime>`:

    .. tab:: Emacs

        Use the :ref:`Emacs RPC client <client_rpc_emacs>`

    .. tab:: Other Editor

        You should be able to use the :ref:`language_server`. The procedure should be similar to the ones outlined for :ref:`other clients <language_server_clients>`. When you get one working, make a pull request to add it here ☺
