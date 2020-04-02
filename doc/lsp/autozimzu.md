---
currentMenu: lsp
---
Autozimu Language Client for Neovim
===================================

Include the language client:

```
Plug 'autozimu/LanguageClient-neovim', {
    \ 'branch': 'next',
    \ 'do': 'bash install.sh',
    \ }
```

And let it know about Phpactor:

```
let g:LanguageClient_serverCommands = {
    \ 'php': [ '/path/to/bin/phpactor', 'server:start', '--stdio']
    \}
```

See the [github repository](https://github.com/autozimu/LanguageClient-neovim)
for more details.
