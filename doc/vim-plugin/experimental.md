Experimental
============

FZF and BAT
-----------

Experimental functionality with FZF and BAT depends on:

- [fzf](https://github.com/junegunn/fzf)
- [bat](https://github.com/sharkdp/bat)

In addition FZF support requires the FZF VIM plugin:

[fzf.vim](https://github.com/junegunn/fzf.vim)

### FZF Choice Selection

Some refactorings will allow you to select multiple entires (for example
[override
method](https://phpactor.github.io/phpactor/refactorings.html#override-method).

FZF provides a fuzzy search interface and the possiblity to select multiple
entries at once.

Use `<tab>` to toggle selection and CTRL-A/CTRL-D to select all/select none. 

See the [Fzf](https://github.com/junegunn/fzf) documentation for more details.

Enable this feature by configuring FZF as the `inputlist` strategy in your
`.vimrc':

```
let g:phpactorInputListStrategy = 'phpactor#input#list#fzf'
```

### FZF Qucikfix with BAT preview

The quickfix list is used to show a list of positions in files and access them
quickly. Phpactor use it for example to show the result of `find references`.

With the [fzf](#fzf) strategy you will still be able to get the result inside
the quickfix by selecting the elements you are interested in and pressing
`ctrl-q` to populate the quickfix with your selection and open it.

```
let g:phpactorQuickfixStrategy = 'phpactor#quickfix#fzf'
```
