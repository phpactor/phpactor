Experimental
============

FZF and BAT
-----------

Experimental functionality with FZF and BAT depends on:

-  `fzf <https://github.com/junegunn/fzf>`__
-  `bat <https://github.com/sharkdp/bat>`__

In addition FZF support requires the FZF VIM plugin:

`fzf.vim <https://github.com/junegunn/fzf.vim>`__

FZF Choice Selection
~~~~~~~~~~~~~~~~~~~~

Some refactorings will allow you to select multiple entries (for example
`override
method <https://phpactor.github.io/phpactor/refactorings.html#override-method>`__.

FZF provides a fuzzy search interface and the possibility to select
multiple entries at once.

Use ``<tab>`` to toggle selection and CTRL-A/CTRL-D to select all/select
none.

See the `Fzf <https://github.com/junegunn/fzf>`__ documentation for more
details.

Enable this feature by configuring FZF as the ``inputlist`` strategy in
your \`.vimrc’:

::

   let g:phpactorInputListStrategy = 'phpactor#input#list#fzf'

FZF Qucikfix with BAT preview
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The VIM quickfix list is used to navigate through a set of references
(where a reference is a file / character position).

The FZF strategy provides a layer on top this to allow you to
efficiently filter, preview and select only those entries you want to
navigate to to the quickfix list.

Enable it as follows:

::

   let g:phpactorQuickfixStrategy = 'phpactor#quickfix#fzf'
