Prophecy
========

`Prophecy <https://github.com/phpspec/prophecy>`_ is a highly opinionated yet very powerful and flexible PHP object mocking framework. Though initially it was created to fulfil phpspec2 needs, it is flexible enough to be used inside any testing framework out there with minimal effort.

Phpactor can integrate with Prophecy to provide completion for mocks.

To do so you set :ref:`param_prophecy.enabled`:

.. code-block:: bash

   $ phpactor config:set prophecy.enabled true
