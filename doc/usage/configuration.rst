.. _configuration:

Configuration
=============

**Trusted** configuration files are loaded from your current directory, and then
from the XDG standard user and system directories, for example:

-  ``/home/daniel/www/phpactor/phpactor/.phpactor.json``
-  ``/home/daniel/.config/phpactor/phpactor.json``
-  ``/etc/xdg/phpactor/phpactor.json``

Phpactor will merge configuration files, with more specific
configurations overriding the less specific ones.

Initializing
------------

To create a new configuration file with a reference to the JSON schema use:

.. code:: bash

    $ phpactor config:init

Trusting Configuration
----------------------

By default Phpactor will not load configuration files from the project root or
current working directory as a maliciously placed configuration file in a project
would allow arbitrary code execution. Therefore directories must be **trusted**.

When using the langauge server a dialog will show up asking if you trust the configuration
file if one is present. On the CLI you can use the `phpactor config:trust` command.

Trust entries may contain a ``*`` wildcard, which matches any sequence of
characters (including ``/``), and a leading ``~`` is expanded to your home
directory. This is useful to trust a whole workspace at once. Edit the trust
file (the ``core.trust.path`` shown by ``phpactor config:dump``) and replace an
entry with a pattern, for example:

.. code:: json

   {
       "~/workspace/*": true
   }

Exact paths take precedence over patterns and, when several patterns match, the
longest one wins, so a more specific pattern can exclude a directory from a
broader one. ``*`` is the only wildcard: ``?`` and ``[]`` are matched literally.

Note that trusting a directory allows Phpactor to load configuration files
which may execute arbitrary code: only trust directories you control and keep
patterns as narrow as possible.

Config Dump
-----------

Use the ``config:dump`` command to show the currently loaded
configuration files and all of the current settings:

.. code:: bash

   $ phpactor config:dump
   Config files:               
    [✔] /home/daniel/workspace/myproject/.phpactor.json
    [✔] /home/daniel/.config/phpactor/phpactor.json
    [𐄂] /etc/xdg/phpactor/phpactor.yml                                   

    code_transform.class_new.variants:
       exception:exception    

    # ... etc

File Paths
----------

Configured file paths can make use of some special tokens, for example
``%cache%/foobar`` will expand to ``/home/user/.cache/phpactor/foobar``:

-  ``%cache%``: The absolute path to the phpactor cache dir (e.g.
   ``/home/user/.cache/phpactor``).
-  ``%project_root%``: Will expand to the project root (e.g. the current
   working directory or the value provided by ``--working-dir``).
-  ``%config%``: The path to Phpactor’s config dir
   (e.g. ``/home/user/.config/phpactor``).
-  ``%application_root%``: The path to Phpactor’s own root directory.

Reference
---------

See: :doc:`../reference/configuration`
