.. _installation:

Installation
============

Requirements
------------

Phpactor requires PHP 8.1.

.. _installation_phar:

PHAR Installation
-----------------

You can download ``phpactor.phar`` as follows:

.. code-block:: bash

    $ curl -Lo phpactor.phar https://github.com/phpactor/phpactor/releases/latest/download/phpactor.phar

Then make it executable and symlink it somewhere in your PATH_:

.. code:: bash

   $ chmod a+x phpactor.phar
   $ mv phpactor.phar ~/.local/bin/phpactor

.. _installation_global:

Manual Installation
-------------------

You can checkout the project and then create a symlink.

.. code:: bash

   $ cd ~/home/you/somewhere
   $ git clone git@github.com:phpactor/phpactor
   $ cd phpactor
   $ composer install
   $ cd /usr/local/bin
   $ sudo ln -s ~/your/projects/phpactor/bin/phpactor phpactor

This is the best approach for bleeding edge and local development.

Arch Linux (AUR)
----------------

Also available in the AUR:

.. code:: bash

   $ yay -S phpactor

Nix/OS
------

Phpactor is avialable in NixOS.

.. code-block:: bash

   $ nix-shell -p phpactor


Health Check
------------

Phpactor works best when used with Composer, and is slightly better when
used with Git.

Check support using the ``status`` command:

::

   $ phpactor status
   ✔ Composer detected - faster class location and more features!
   ✔ Git detected - enables faster refactorings in your repository scope!

.. _PATH: https://en.wikipedia.org/wiki/PATH_(variable)
