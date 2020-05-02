Installing
==========

Phpactor allows you to add custom extensions which can extend Phpactor.

For some extension examples see the `integration list <extensions/list>`__
page.

Usage
-----

To list all available extensions, run the following:

::

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

New extensions can be installed as follows:

::

   $ phpactor extension:install phpactor/completion-worse-extension
   Using version "^0.2.0"
   Loading composer repositories with package information
   Updating dependencies
   Package operations: 1 install, 0 updates, 0 removals
     - Installing phpactor/language-server-extension (0.2.0): Loading from cache
   Writing lock file
   Generating autoload files

Removing is done via. ``extension:remove``.
