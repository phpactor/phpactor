---
currentMenu: lsp
---
CoC
===

Installation
------------

```
Plug 'neoclide/coc.nvim', {'branch': 'release'}
```

Restart VIM and type `:CocConfig` to edit the CoC configuration, enter the
follwing:

```
{
    "languageserver": {
        "phpactor": {
            "trace.server": "verbose",
            "command": "/home/you/.vim/plugged/phpactor/bin/phpactor",
            "args": ["language-server"],
            "filetypes": ["php","cucumber"],
            "initializationOptions": {
            },
            "settings": {
            }
        }
    },
}
```

Replace the path (`command`) to Phpactor and customize as appropriate, the CoC
configuration schema can be seen
[here](https://github.com/neoclide/coc.nvim/blob/master/data/schema.json)

Troubleshooting
---------------

### Two dollars on variables

This can happen because of the `iskeyword` setting in VIM.

You can try adding `$` to the list of keywords to solve the problem:

```
autocmd FileType php set iskeyword+=$
```

or configure Phpactor to trim the `$` prefix in `.phpactor.json`:

```
{
    "language_server_completion.trim_leading_dollar": true
}
```
