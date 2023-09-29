Documentation
=============

Phpactor Documentation
~~~~~~~~~~~~~~~~~~~~~~

Phpactor uses `Sphinx <https://www.sphinx-doc.org>`_ (RST) for documentation.

Docs are located in the ``docs``.

A useful primer on RST can be found `here <https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html>`_.

.. tabs::

    .. tab:: Debian/Ubuntu

        ::

            $ apt-get install python3-sphinx
            $ pip install sphinx-tabs

You can then build the docs with:


    ::

        make sphinx

Or, to watch for changes (requires ``inotifywait``):

    ::

        make sphinxwatch

VIM Help
~~~~~~~~

The VIM plugin is documented in the *generated* ``doc/phpactor.txt``
file using `vimdoc <https://github.com/google/vimdoc>`_.

In order to add documentation just annotate properties / methods with
comments, for example:

.. code:: vim

    ""
    " Extract the selected expression and assign it to a variable before
    command! -buffer -range=% PhpactorExtractExpression call phpactor#ExtractExpression('v')

See `vimdoc <https://github.com/google/vimdoc>`_ for more information.

Use the following command to both install vimdoc and build the documentation:

.. code:: sh

    make vimdoc
