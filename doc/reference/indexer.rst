.. _indexer:

Indexer
=======

The indexer scans your project directory and records meta-information about
classes and functions in your project.

The indexer *only required* for some features (such as
:ref:`navigation_goto_implementation`).

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

    .. tab:: VIM (CoC)

        The index is built automatically on LS initialize.

        Build from scratch:

        .. code:: sh

            :call CocRequest('phpactor','indexer/reindex')


.. _watcher:

Watching
--------

File watchers are used to keep the index up-to-date.

The type of watcher used depends on your system. Currently the following
watchers will be used, in order of priority:

1. ``inotifywait``: **Linux** only, react immediately[1] to file changes.

2. ``find``: **Linux/Mac/POSIX** Poll the system for changes every 5 seconds.

3. ``php``: **Any system**: Poll system using PHP (slow) every 5 seconds.

To contribute to the available watchers see `amp-fswatch
<https://github.com/phpactor/amp-fswatch>`_.

If you want to find out which watcher your system is using, enable _`logging`.

Configuration
-------------

List the possible configuration options with ``phpactor config:dump | grep
indexer``, explanations of some important ones:

- ``indexer.enabled_watchers``: List of watchers to enable (e.g. `inotify`,
  `find`).
- ``indexer.include_patterns``: List of glob patterns to include
- ``indexer.exclude_patterns``: List of glob patterns to exclude
- ``indexer.poll_time``: Poll time used for polling watchers (e.g. ``find``,
  ``php``
- ``indexer.buffer_time``: Time to wait to collect batch messages from
  "realtime" watchers (e.g. ``inotify``)
- ``indexer.follow_syminks``: If the indexer should follow symlinks (default
  ``true``).

Troubleshooting
---------------

Inotify: Why isn't ``inotifywait`` used when I'm on Linux?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It may not be installed, on Debian/Ubuntu

.. code:: sh

   $ sudo apt-get install inotify-tools

Inotify: ``inotify`` limit reached
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default number of watchers is quite low by default, try increasing the
number of watchers:

.. code:: sh

   $ sudo sysctl fs.inotify.max_user_watches=100000

Note this still may not be sufficient, so increase as necessary, make the
change permanent by writing to ``/etc/sysctl.conf``

.. _Watchman: https://facebook.github.io/watchman/
