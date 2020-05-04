Getting Started
===============

How you start depends on your editor.

Currently Phpactor has most support when used over it's own RPC protocol, but
an :ref:`increasing amount <lsp_support>` of support is offered from the
:ref:`language_server`.

In addition you can also so somethings from the CLI only.

.. tabs::

    .. tab:: VIM or Neovim

        :doc:`vim-plugin`, optionally supplement with :ref:`lsp_client_vim_coc`:

    .. tab:: Sublme Text

        :ref:`client_rpc_sublime` optionally supplement with
        :ref:`lsp_client_sublime`:

    .. tab:: Emacs

        :ref:`client_rpc_emacs`

    .. tab:: Other Editor 

        You should be able to use the :ref:`language_server`. The procedure should be similar to the ones outlined for :ref:`other clients <language_server_clients>`. When you get one working, make a pull request to add it here ☺

    .. tab:: CLI

        Phpactor exposes a number of commands over the CLI (e.g. moving
        classes, applying transformations).

        See :ref:`installation_global`
