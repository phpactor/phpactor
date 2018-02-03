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

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;

$autoload = require __DIR__ . '/autoload.php';
$kernel = new DrupalKernel('prod', $autoload);
$kernel->setSitePath(__DIR__);
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request,$autoload, 'prod' );
$kernel->boot();

return $autoload;
```

Then edit `.phpactor.yml` to use that:

```
# .phpactor.yml
autoload: web/phpactor_autoload.php
```
