Drupal 8
========

Inc Files
^^^^^^^^^

By default Phpactor will not index `.inc` files.

Run the following on your project to enable the indexing of `.inc` files.

```
phpactor config:set indexer.supported_extensions '["php", "inc"]'
```

Bootstrapping
~~~~~~~~~~~~~

Drupal automatically adds its modules to the autoloader during the
kernel boot process. It is therefore necessary to either 1) boot the
kernel to have a fully useful autoloader *or* 2) to use a different
mechanism to add the modules to the Composer autoloader.

Depending on your setup option 1 or 2 will be preferable.

Option 1: Bootstrap Drupal on the fly to generate the autoloader
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Create the following bootstrap file ``phpactor_autoload.php``, in (for
example) ``web/``:

.. code:: php

   // web/phpactor_autoload.php
   <?php

   use Drupal\Console\Core\Utils\DrupalFinder;
   use Drupal\Console\Bootstrap\DrupalKernel;
   use Symfony\Component\HttpFoundation\Request;

   $autoload = require_once __DIR__ . '/autoload.php';

   $root = getcwd();

   $drupalFinder = new DrupalFinder();
   if (!$drupalFinder->locateRoot($root)) {
       die('DrupalConsole must be executed within a Drupal Site.');
   }

   chdir($drupalFinder->getDrupalRoot());

   $drupalKernel = DrupalKernel::createFromRequest(
     Request::createFromGlobals(),
     $autoload,
     'dev',
     true,
     $drupalFinder->getDrupalRoot()
   );
   $drupalKernel->boot();
   chdir($root);

   return $autoload;

Then edit ``.phpactor.yml`` to use that:

.. code:: yaml

   # Use the special autoloader above
   composer.autoloader_path: web/phpactor_autoload.php

The downside to this option is that it requires access to the DB from
your current environment which may be tricky if you are running Drupal
inside a VM.

Option 2: Add the modules into the Composer autoloader
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This option requires merging in any modules (Drupal, contrib, custom)
into the Composer autoloader via a discovery mechanism offered by the
`Drupal Autoloader <https://github.com/fenetikm/autoload-drupal>`__
composer plugin.

Full details are available in the README for this plugin, however, the
short version is that you will need to put the following into your
``composer.json`` file:

.. code:: json

   {
       "require": {
           "fenetikm/autoload-drupal": "0.1"
       },
       "extra": {
           "autoload-drupal": {
               "modules": [
                 "app/modules/contrib/",
                 "app/modules/custom/",
                 "app/core/modules/"
               ]
           }
       }
   }

and then rebuild your Composer autoloader e.g.

.. code:: sh

   composer autoload-dump

The upside to this option is that it won’t require the relatively slow
Drupal bootstrap (which will hit the DB) but the downside is that you
will have to regenerate the autoloader every time you add / remove a
module.

Coding Standards
^^^^^^^^^^^^^^^^

Change your local ``.phpactor.yml`` to use 2 spaces for indentation:

::

   # Drupal CS is 2 spaces
   code_transform.indentation: "  "

.. container:: alert alert-info

   Code will still be generated using the PSR-2 standard. It would be
   necessary to override twig templates in ``.phpactor/templates`` to
   rectify this (or just use a CS fixer).


