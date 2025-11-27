.. _stubs:

Stubs
=====

Phpactor has two types of "stubs":

PHP Core Stubs
--------------

These stubs fill in for definitions (classes, functions and constants) that are
defined in the PHP core or in PHP extensions. Phpactor expects the path to be a
single directory and that directory is configured by default to be the one
containing the `PHPStorm stubs <https://github.com/JetBrains/phpstorm-stubs/>`_
that are bundled with Phpactor.

You probably do **not** want to change this setting, but the directory *can* be
changed at as :ref:`param_worse_reflection.additive_stubs`.

Additive Stubs
--------------

These stubs **augment** existing classes. For example, in Laravel you may use the `Laravel IDE Helper <https://github.com/barryvdh/laravel-ide-helper>`_ package to provide missing "magic" methods and properties. New definitions cannot be provided with additive stubs.


You can specify these stub files with :ref:`param_worse_reflection.additive_stubs` and the stubs will merge onto the existing definitions.

.. note:: 

   Additive stubs should *not* be indexed as Phpactor will be unable to
   determine which definition is the canonical one. If you use additive stubs
   ensure that you also manually specify which directories contain your project code (typically `src` and `tests`).

   The indexer include patterns can be specified with :ref:`param_indexer.include_patterns`.
