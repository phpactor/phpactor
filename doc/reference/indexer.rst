.. _indexer:

Indexer
=======

The indexer scans your project directory and records meta-information about
classes and functions in your project.

The indexer required only for some features (such as
:ref:`navigation_goto_implementation`).

.. _indexer_building:

Building the index
------------------

It will be *automatically enabled* when used with the language server but can
also be used with RPC if run manually.

.. tabs::

    .. tab:: CLI

        Build index and watch for changes

        .. code:: sh

            $ phpactor index:build --watch

        Build from scratch

        .. code:: sh

            $ phpactor index:build --reset

    .. tab:: Language Server CoC

        The index is built automatically on LS initialize and subsequently
        updated as necessary.

        You can however force a reindex:

        Build from scratch:

        .. code:: sh

            :CocCommand phpactor.reindex

    .. tab:: Language Server General

       Make a request to `indexer/reindex`.


.. _watcher:

Watching
--------

File watchers are used to keep the index up-to-date.

Several watching systems can be used, by default Phpactor will choose the
first supported one:

lsp
~~~

**Any platform**

This watcher depends on file events from the LSP client (e.g. VSCode).

inotifywait
~~~~~~~~~~~

**Linux** only, react immediately to file changes.

Installation

.. tabs::

    .. tab:: Debian/Ubuntu
       
        .. code-block:: bash

            apt install inotify-tools

watchman
~~~~~~~~

**Linux/Mac** cross platform, reacts immediately to file changes, see Watchman_ documentation.

Watchman is the recommended watcher.

Installation:

.. tabs::

    .. tab:: Debian/Ubuntu
       
        .. code-block:: bash

            apt install watchman

    .. tab:: MacOS
       
        .. code-block:: bash

            brew install watchman

find
~~~~

**Linux/Mac/POSIX** Poll the system for changes every 5 seconds.

This tool should be installed by default.

php
~~~

**Any system**: Poll system using PHP (slow) every 5 seconds.

.. _indexer_querying:

Querying from the CLI
---------------------

You can query the index from the CLI:

.. tabs::

    .. tab:: Show class index information

        .. code:: sh

            $ phpactor index:query "Symfony\\Component\\Console\\Output\\OutputInterface"

    .. tab:: Show function information

        .. code:: sh

            $ phpactor index:query "sprintf"

    .. tab:: Show member information

        .. code:: sh

            $ phpactor index:query "method#createFoobar"
            $ phpactor index:query "property#createFoobar"
            $ phpactor index:query "constant#createFoobar"

Note that this information is primarily intended for the indexer and is not
yet intended to provide a true "querying" facility.

Configuration
-------------

List the possible configuration options with ``phpactor config:dump | grep
indexer``, explanations of some important ones:

- :ref:`param_indexer.enabled_watchers`: List of watchers to enable (e.g. `inotify`,
  `find`).
- :ref:`param_indexer.include_patterns`: List of glob patterns to include
- :ref:`param_indexer.exclude_patterns`: List of glob patterns to exclude
- :ref:`param_indexer.stub_paths`: List of external paths to index
- :ref:`param_indexer.poll_time`: Poll time used for polling watchers (e.g. ``find``, ``php``)
- :ref:`param_indexer.buffer_time`: Time to wait to collect batch messages from
  "realtime" watchers (e.g. ``inotify``)

Troubleshooting
---------------

Inotify: Why isn't ``inotifywait`` used when I'm on Linux?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It may not be installed, on Debian/Ubuntu

.. code:: sh

   $ sudo apt install inotify-tools

Inotify: ``inotify`` limit reached
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default number of watchers is quite low by default, try increasing the
number of watchers:

.. code:: sh

   $ sudo sysctl fs.inotify.max_user_watches=100000

Note this still may not be sufficient, so increase as necessary, make the
change permanent by writing to ``/etc/sysctl.conf``

.. _Watchman: https://facebook.github.io/watchman/
