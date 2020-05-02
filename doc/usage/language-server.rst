.. _language_server:

Language Server
===============

Phpactor implements the `Language Server Protocol`_ which is supported by many
text editors and IDEs.

Client Integration
------------------

In general it should be possible to enable it on any LSP compatible client,
the following is a list of integration guides for *some* clients:

.. toctree::
   :maxdepth: 1
   :glob:

   ../lsp/clients

Manually Running the Server
---------------------------

Typically you should never need to run the Language Server yourself, the
following methods are useful for development.

STDIO
~~~~~

STDIO is typically used by clients and it the default mode.

.. code:: bash

   $ phpactor language-server

This is the method you should use when configuring an LSP client.

Run with TCP Server
~~~~~~~~~~~~~~~~~~~

The TCP server is useful for debugging:

.. code:: bash

   $ phpactor language-server --address=127.0.0.1 -vvv

You should see something like:

::

   Starting TCP server, use -vvv for verbose output
   [2018-09-30 17:15:25] phpactor.INFO: listening on address 127.0.0.1:8888 [] []
   [2018-09-30 17:15:25] phpactor.INFO: starting language server with pid: 9286 [] []

.. _Language Server Protocol: https://microsoft.github.io/language-server-protocol/specification
