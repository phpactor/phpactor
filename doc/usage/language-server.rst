Language Server
===============

Phpactor has a built-in language server.

In order to use the language server you will need to install a language server
client in your text editor:

.. toctree::
   :maxdepth: 1
   :glob:

   ../lsp/clients

The following methods of running the language server should only be
interesting in a development context.

STDIO
-----

STDIO is typically used by clients and it the default mode.

.. code:: bash

   $ phpactor language-server

Run with TCP Server
-------------------

The TCP server is useful for debugging:

.. code:: bash

   $ phpactor language-server --address=127.0.0.1 -vvv

You should see something like:

::

   Starting TCP server, use -vvv for verbose output
   [2018-09-30 17:15:25] phpactor.INFO: listening on address 127.0.0.1:8888 [] []
   [2018-09-30 17:15:25] phpactor.INFO: starting language server with pid: 9286 [] []
