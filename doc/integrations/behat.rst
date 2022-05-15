Behat
=====

`Behat <https://github.com/behat/behat>`_ is a BDD framework which makes use
of Gherkin files.

Phpactor can provide goto definition support within feature files.

Enabling
--------

The extension must be via. :ref:`param_behat.enabled`:

.. code-block:: bash

   $ phpactor config:set behat.enabled true

Symfony Integration
-------------------

If you are using Syfmony and depdency injection to manage your contexts you
can specify the path to the XML debug file in
:ref:`param_behat.symfony.di_xml_path`:

For example:

.. code-block:: bash

   $ phpactor config:set behat.symfony.di_xml_path "var/cache/test/App_KernelTestDebugContainer.xml"
