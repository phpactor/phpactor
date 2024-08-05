Symfony
=======

`Symfony <https://www.symfony.com>`_ is a popular web framework.

Phpactor can provide:

- Service ID completion when using the container as a service locator.
- Type inference when accessing services from the container.

Enabling
--------

The extension must be via. :ref:`param_symfony.enabled`:

.. code-block:: bash

   $ phpactor config:set symfony.enabled true

Dependency Injection Features
-----------------------------

When in development mode Symfony will dump an XML file which describes the
current container. In Symfony 5.x this file is located in
``var/cache/dev/App_KernelDevDebugContainer.xml`` but this may be different
for earlier versions.

You can set the path to this file with :ref:`param_symfony.xml_path`

Troubleshooting
---------------

### I don't get any completion suggestions or type inference.

Ensure that your Symfony cache has been warmed up and that you are in
development mode.

Try running ``./bin/console cache:clear``
