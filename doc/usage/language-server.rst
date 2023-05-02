.. _language_server:

Language Server
===============

Phpactor implements the `Language Server Protocol`_
which is supported by many text editors and IDEs.

Features
--------

See :doc:`/lsp/support` for the list of supported features.

Usage
-----

.. toctree::
   :maxdepth: 2
   :glob:

   ../lsp/clients

Other Clients
-------------

In general it should be possible to enable it on any LSP compatible client.
First perform a :ref:`installation_global` and then configure a generic
language server in your client, typically using :ref:`lsp_running_stdio`. See
:ref:`lsp_client_sublime` for an example.

.. _Language Server Protocol: https://microsoft.github.io/language-server-protocol/specification
