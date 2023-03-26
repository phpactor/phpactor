Development
===========

.. contents::
   :depth: 2
   :backlinks: none
   :local:

Package Structure
-----------------

Phpactor is divided into _packages_. Each package occupies a directory in
`lib/`. In addition there are extensions which integrate packages with
Phpactor which are in `lib/Extension`.

Tests and benchmarks are maintained in the package's directory, e.g.
`lib/WorseReflection/Tests`.

How to write a code action
--------------------------

In order to implement a new code action you need the following classes:
* CodeActionProvider
* CodeActionCommand
* CodeAction

Let's have a look at the concept by the example of the generate decorator functionality. First off we have the
`GenerateDecoratorProvider` this class provides a list of code actions for the file. Here we can do some pre checks e.g.
if the file is usable to generate a decorator in. In the end the `GenerateDecoratorProvider` will return a list of
commands that are available.

The command is defined in our example in the `GenerateDecoratorCommand` which is just some glue between the actual
code action logic and the provider.

And then there is the implementation of the `WorseGenerateDecorator` which contains the logic for generating the
decorator or more generally to apply the code action.

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

Debugging RPC
-------------

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

Documentation
-------------

Phpactor Documentation
~~~~~~~~~~~~~~~~~~~~~~

Phpactor uses `Sphinx <https://www.sphinx-doc.org>`_ (RST) for documentation.

Docs are located in the ``docs``.

A useful primer on RST can be found `here <https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html>`_.

.. tabs::

    .. tab:: Debian/Ubuntu

        ::

            $ apt-get install python3-sphinx
            $ pip install sphinx-tabs

You can then build the docs with:


    ::

        make sphinx

Or, to watch for changes (requires ``inotifywait``):

    ::

        make sphinxwatch

VIM Help
~~~~~~~~

The VIM plugin is documented in the *generated* ``doc/phpactor.txt``
file using `vimdoc <https://github.com/google/vimdoc>`_.

In order to add documentation just annotate properties / methods with
comments, for example:

.. code:: vim

    ""
    " Extract the selected expression and assign it to a variable before
    command! -buffer -range=% PhpactorExtractExpression call phpactor#ExtractExpression('v')

See `vimdoc <https://github.com/google/vimdoc>`_ for more information.

Use the following command to both install vimdoc and build the documentation:

.. code:: sh

    make vimdoc

.. _developing_blackfire_profiling:

Profiling the Language Server
-----------------------------

You can selectively profile the language server using `Blackfire <https://blackfire.io>`_.

- Enable the blackfire via. :ref:`param_blackfire.enabled`
- Call the LSP methods `blackfire/start` and `blackfire/finish`, for NVIM see
  :ref:`nvim_configuration_snippet_commands`
