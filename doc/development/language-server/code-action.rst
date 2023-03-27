Code actions
============

What is a code action
---------------------

A code action is an action that can be performed on the code. These can be subdivided into Quickfixes, Refactoring
and source fixes. For more information on what the individual code action are doing please consult the documentation:
`Code action kind documentation <https://microsoft.github.io/language-server-protocol/specifications/lsp/3.17/specification/#codeActionKind>`_

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
