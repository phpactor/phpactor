Psalm
=====

`Psalm <https://github.com/vimeo/psalm>`_ is a static analysis tool that can be used to expose bugs in your PHP code.

Phpactor can integrate with Psalm to provide diagnostics in your IDE.

To do so you set :ref:`param_language_server_psalm.enabled`:

.. code-block:: bash

   $ phpactor config:set language_server_psalm.enabled true

- Specify the path to Psalm if different to ``/vendor/bin/psalm`` via. :ref:`param_language_server_psalm.bin`.
