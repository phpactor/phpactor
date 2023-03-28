.. _developing_blackfire_profiling:

Profiling the Language Server
=============================

You can selectively profile the language server using `Blackfire <https://blackfire.io>`_.

- Enable the blackfire via. :ref:`param_blackfire.enabled`
- Call the LSP methods `blackfire/start` and `blackfire/finish`, for NVIM see
  :ref:`nvim_configuration_snippet_commands`
