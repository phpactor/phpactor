---
currentMenu: extensions
---
Extensions
==========

Phpactor allows you to add custom extensions, for example to provide new
completors, or anything you can imagine.

The aim of extensions is for them to be as independent and decoupled as
possible, they can easily be used without Phpactor and can be easily combined
to create new applications.

Currently Phpactor ships with a dozen "hard coded" extensions which cannot be
removed.

For some extension examples see the [integrations](/integrations.html) page.

Usage
-----

To list all available extensions, run the following:

```
$ phpactor extension:list
+-------------------------------------------+--------------------+-------------------------------------+
| Name                                      | Version            | Description                         |
+-------------------------------------------+--------------------+-------------------------------------+
| phpactor/class-to-file-extension          | 0.1.x-dev 61007ff* | Converts classes to files and vice- |
| phpactor/completion-extension             | 0.1.x-dev 23fc826* | Phpactor Code Completion Extension  |
| phpactor/completion-rpc-extension         | 0.1.x-dev fc5cc03* | RPC support for the Completion Exte |
| phpactor/completion-worse-extension       |                    | Collection of completors based on W |
| phpactor/composer-autoloader-extension    | 0.1.x-dev ec979b7* | Composer Autoloader provider        |
| phpactor/console-extension                | 0.1.x-dev 970c787* | Integrate Symfony Console commands  |
+-------------------------------------------+--------------------+-------------------------------------+
```

Note that extensions without a version are not installed.

Pretending that `phpactor/completion-worse-extension` isn't installed, you can install it with:

```
$ phpactor extension:install phpactor/completion-worse-extension
Using version "^0.2.0"
Loading composer repositories with package information
Updating dependencies
Package operations: 1 install, 0 updates, 0 removals
  - Installing phpactor/language-server-extension (0.2.0): Loading from cache
Writing lock file
Generating autoload files
```

Removing is done via. `extension:remove`.

Extensions
----------

- [PHPUnit Extension](https://github.com/phpactor/phpunit-extension): Provides
  some integerations with PHPUnit.
