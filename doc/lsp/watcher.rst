Watcher
=======

When the language server starts Phpactor will index all the PHP files in your
working directory, and then *watch* for changes.

The type of watcher used depends on your system. Currently the following
watchers will be used, in order of priority:

1. ``inotifywait``: **Linux** only, react immediately[1] to file changes.
2. ``find``: **Linux/Mac/POSIX** Poll the system for changes every 5 seconds.
3. ``php``: **Any system**: Poll system using PHP (slow) every 5 seconds.

To contribute to the available watchers see `amp-fswatch
<https://github.com/phpactor/amp-fswatch>`_.

If you want to find out which watcher your system is using, enable _`logging`.

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
