Debugging RPC
=============

When executing commands in an editor, it can be tricky to consistently
reproduce errors, or to isolate and debug them in Phpactor.

Thankfully there is a feature called RPC replay which allows you to
replay the last RPC command received by Phpactor.

Enable it in a Phpactor configuration file, for example
``$HOME/.config/phpactor/phpactor.yml``:

.. code:: yaml

   rpc.store_replay: true

Now, after you execute an RPC command via. your editor, you can execute
Phpactor from the shell and replay your last command:

.. code:: bash

   $ phpactor rpc --replay
   {"action":"open_file","parameters":{"path":"\/home\/daniel\/www\/phpactor\/phpactor\/lib\/Extension\/Rpc\/Handler\/AbstractHandler.php","offset":447}}

To see more information, including the initial request add the
``--verbose`` option:

.. code:: bash

   $ phpactor rpc --replay --verbose
   [2018-05-23T22:14:11.486488+02:00] phpactor.DEBUG: REQUEST {"action":"goto_definition","parameters":{"source":"[removed]","offset":1913,"path":"/home/daniel/somepath/SomeClass.php"}}
   [2018-05-23T22:14:11.494201+02:00] phpactor.DEBUG: Resolving: Microsoft\PhpParser\Node\Statement\ClassDeclaration [] []
   [2018-05-23T22:14:11.494545+02:00] phpactor.DEBUG: Resolving: Microsoft\PhpParser\Node\Parameter [] []
   ...[truncated]...
   [2018-05-23T22:14:11.508019+02:00] phpactor.DEBUG: RESPONSE {"action":"open_file","parameters":{"path":"/home/daniel/www/phpactor/phpactor/lib/Extension/Rpc/Handler/AbstractHandler.php","offset":447}} []
   {"action":"open_file","parameters":{"path":"\/home\/daniel\/www\/phpactor\/phpactor\/lib\/Extension\/Rpc\/Handler\/AbstractHandler.php","offset":447}}

The next time you run a command, you will lose your replay, in order to
consistently reproduce an action, you can copy the replay file and
execute it consistently as many times as required:

.. code:: bash

   $ cp ~/.local/share/phpactor/replay.json .
   $ cat replay.json | phpactor rpc --verbose

Logging
-------

Logging is disabled by default, but can provide some useful information
(such as errors encountered when parsing files etc).

Enable it as follows:

::

   {
       "logging.enabled": true,
       "logging.level": "debug",
       "logging.path": "phpactor.log",
   }

Var Dump Server
---------------

Phpactor includes the Symfony Var Dumper, this allows you to inspect values
while the server is running or an RPC request is being executed.

Start the server in the Phpactor project root:

::

   $ ./vendor/bin/var-dump
   ./vendor/bin/var-dump-server

   Symfony Var Dumper Server
   =========================

    [OK] Server listening on tcp://127.0.0.1:9912

    // Quit the server with CONTROL-C

You can use the `dump` function in the code and the variable will be shown in
the console output of the server you started: `dump($var)`.

