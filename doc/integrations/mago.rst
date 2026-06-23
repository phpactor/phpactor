Mago
====

`Mago <https://github.com/carthage-software/mago>`_ is a fast PHP linter, formatter and static analysis tool written in Rust.

Phpactor can integrate with Mago and provide diagnostics in your IDE from two of its tools:

- ``mago analyze`` (static analysis, the equivalent of PHPStan or Psalm), reported under the ``mago`` source.
- ``mago lint`` (style and code smells), reported under the ``mago-lint`` source.

To enable the integration set :ref:`param_language_server_mago.enabled`:

.. code-block:: bash

   $ phpactor config:set language_server_mago.enabled true

Both tools are enabled by default once the extension is on. Toggle them independently with :ref:`param_language_server_mago.analyze.enabled` and :ref:`param_language_server_mago.lint.enabled`.

- Specify the path to Mago if different to ``vendor/bin/mago`` via :ref:`param_language_server_mago.bin`. Mago is commonly installed globally, in which case set this to ``mago``.
- Override the Mago configuration file with :ref:`param_language_server_mago.config`.
- Adjust the run timeout (milliseconds) with :ref:`param_language_server_mago.timeout`.

Phpactor sends the current buffer to Mago on standard input, so diagnostics update as you type without saving the file.

.. note::

   Pointing :ref:`param_language_server_mago.config` at a ``mago.toml`` outside the project root may change how Mago resolves the relative paths declared inside that file.
