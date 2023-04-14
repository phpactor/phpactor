PHPUnit
=======

`PHPUnit <https://github.com/phpunit/phpunit>`_ is a programmer-oriented
testing framework for PHP. It is an instance of the xUnit architecture for
unit testing frameworks. 

Phpactor can integrate with PHPUnit to provide:

- Type inference from assertions.
- Generate test case.

To do so you set :ref:`param_phpunit.enabled`:

.. code-block:: bash

   $ phpactor config:set phpunit.enabled true
