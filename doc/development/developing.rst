General
=======

.. toctree::
   :maxdepth: 2
   :glob:

   language-server/*

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


Profiling the Language Server
-----------------------------

You can selectively profile the language server using `Blackfire <https://blackfire.io>`_.

- Enable the blackfire via. :ref:`param_blackfire.enabled`
- Call the LSP methods `blackfire/start` and `blackfire/finish`, for NVIM see
  :ref:`nvim_configuration_snippet_commands`
