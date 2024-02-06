Behat
=====

`Behat <https://github.com/behat/behat>`_ is a BDD framework.

Phpactor can provide goto definition and some completion support within feature files.

Enabling
--------

The extension must be via. :ref:`param_behat.enabled`:

.. code-block:: bash

   $ phpactor config:set behat.enabled true

Symfony Integration
-------------------

If you are using Symfony and dependency injection to manage your contexts you
can specify the path to the XML debug file in
:ref:`param_behat.symfony.di_xml_path`:

For example:

.. code-block:: bash

   $ phpactor config:set behat.symfony.di_xml_path "var/cache/test/App_KernelTestDebugContainer.xml"

Language Server Support
-----------------------

This extension acts on cucumber files, you will need to configure your
_client_ to ensure that it will call Phpactor when in the feature files.


.. tabs::

    .. tab:: Neovim LSP (via. lspconfig)

        ::

            require'lspconfig'.phpactor.setup{
                -- ...
                filetypes = { 'php', 'cucumber' },
            }
