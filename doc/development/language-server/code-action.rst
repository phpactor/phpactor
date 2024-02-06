Code actions
============

What is a code action
---------------------

A code action is an action that can be performed on the code. These can be
subdivided into Quickfixes, Refactoring and source fixes. For more information
on what the individual code action are doing please consult the documentation:
`Code action kind documentation
<https://microsoft.github.io/language-server-protocol/specifications/lsp/3.17/specification/#codeActionKind>`_

How to write a code action
--------------------------

In order to implement a new code action you need the following classes:

- ``CodeActionProvider``: Provides a list of ``CodeAction`` instances.
- ``CodeAction``: Describes an action that should be performed, usually providing a callback to a command.
- ``CodeActionCommand``: A command which can be executed from the language client.

.. note::

   You can programmatically execute commands from the language client (e.g.
   neovim) but it's not very intuitive. Code actions provide hints which can
   normally be actioned through commands.

Let's have a look at the concept by the example of the generate decorator
functionality. First off we have the ``GenerateDecoratorProvider`` this class
provides a list of ``CodeAction`` objects that are relevant to the file.

The ``CodeAction`` list can then reference a command by name and provide arguments. The following is an example from the Phpactor ``GenerateDecoratorProvider.php`` class:

.. code-block:: php

    <?php
    // ...
    return [
        new CodeAction(
            title: sprintf('Decorate "%s"', $interfaceFQN),
            kind: self::KIND,
            command: new Command(
                'Generate decorator',
                GenerateDecoratorCommand::NAME,
                [
                    $textDocument->uri,
                    $interfaceFQN,
                ]
            )
        ),
    ];

The command is defined in our example in the ``GenerateDecoratorCommand`` which
will receive the arguments you pass and can effectively execute the action, typically in Phpactor we will delegate to a service - in much the same way you would with an MVC controller.

In the case of the generate decorator command there is the
``WorseGenerateDecorator`` service which contains the logic for generating the
decorator or more generally to apply the code action.
