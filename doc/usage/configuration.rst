.. _configuration:

Configuration
=============

**Trusted** configuration files are loaded from your current directory, and then
from the XDG standard user and system directories, for example:

-  ``/home/daniel/www/phpactor/phpactor/.phpactor.yml``
-  ``/home/daniel/.config/phpactor/phpactor.yml``
-  ``/etc/xdg/phpactor/phpactor.yml``

Phpactor will merge configuration files, with more specific
configurations overriding the less specific ones.

Trusting Configuration
----------------------

By default Phpactor will not load configuration files from the project root or
current working directory as a maliciously placed configuration file in a project
would allow arbitrary code execution. Therefore directories must be **trusted**.

When using the langauge server a dialog will show up asking if you trust the configuration
file if one is present. On the CLI you can use the `phpactor config:trust` command.

Config Dump
-----------

Use the ``config:dump`` command to show the currently loaded
configuration files and all of the current settings:

.. code:: bash

   $ phpactor config:dump
   Config files:               
    [✔] /home/daniel/workspace/myproject/.phpactor.yml
    [✔] /home/daniel/.config/phpactor/phpactor.yml
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
