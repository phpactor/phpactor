Standalone
==========

Requirements
------------

Phpactor requires PHP 8.0

.. _installation_global:

Global Installation
-------------------

You can checkout the project and then create a symlink:

.. code:: bash

   $ cd ~/home/you/somewhere
   $ git clone git@github.com:phpactor/phpactor
   $ cd phpactor
   $ composer install
   $ cd /usr/local/bin
   $ sudo ln -s ~/your/projects/phpactor/bin/phpactor phpactor

Note that you may also use the composer global install method, but at
time of writing this isn’t a good idea as the chances are good that it
will conflict with other libraries.

Arch Linux (AUR)
----------------

Also available in the AUR:

.. code:: bash

   $ yay -S phpactor

Health Check
------------

Phpactor works best when used with Composer, and is slightly better when
used with Git.

Check support using the ``status`` command:

::

   $ phpactor status
   ✔ Composer detected - faster class location and more features!
   ✔ Git detected - enables faster refactorings in your repository scope!
