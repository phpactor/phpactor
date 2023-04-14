Manually Running the Server
---------------------------

Typically you should never need to run the Language Server yourself, the
following methods are useful for development.

.. _lsp_running_stdio:

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

   $ phpactor language-server --address=127.0.0.1:8888 -vvv

You should see something like:

::

   Starting TCP server, use -vvv for verbose output
   [2018-09-30 17:15:25] phpactor.INFO: listening on address 127.0.0.1:8888 [] []
   [2018-09-30 17:15:25] phpactor.INFO: starting language server with pid: 9286 [] []

.. _Language Server Protocol: https://microsoft.github.io/language-server-protocol/specification
