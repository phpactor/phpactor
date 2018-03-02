---
currentMenu: integrations
---
Integrations
============

Any PHP application or framework which uses composer in a _normal_ way will
play nicely with Phpactor, for the others there is this page.

Drupal 8
--------

Drupal automatically adds its modules to the autoloader during the kernel
boot process. It is therefore necessary to boot the kernel to have a fully
useful autoloader.

Create the following bootstrap file `autoload_phpactor.php`, in (for example)
`web/`:

```php
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
```

Then edit `.phpactor.yml` to use that:

```
# Use the special autoloader above
autoload: web/phpactor_autoload.php

# Drupal CS is 2 spaces
code_transform.indentation: 2

# Bootstrapping Drupal creates lots of implicit global
# dependencies, so we will just keep the Drupal autoloader
# registered and hope for the best.
autoload.deregister: false
```
