---
currentMenu: language-server
---
Language Server
===============

<div class="alert alert-danger">
The Language Server is currently experimental and currently supports only a
small amount of the functionality covered by Phpactor's own RPC protocol.
</div>

Phpactor has some initial support for running as a server supporting the
[Language Server
Protocol](https://microsoft.github.io/language-server-protocol/specification).

There are many clients which can be used both for VIM and other editors.

Getting Started
---------------

Try it out as follows:

```bash
$ phpactor server:start -vvv
```

You should see something like:

```
Starting TCP server, use -vvv for verbose output
Phpactor handlers:: textDocument/completion, textDocument/definition
[2018-09-30 17:15:25] phpactor.INFO: listening on address 127.0.0.1:8888 [] []
[2018-09-30 17:15:25] phpactor.INFO: starting language server with pid: 9286 [] []
```

This is the language server running in TCP mode, which is great for debugging,
but when configuring your client you may want to invoke the server in STDIO
mode. _your client_ should invoke Phpactor as:

```bash
$ phpactor server:start --stdio
```

Clients
-------

There are lots of clients for different editors.

### Autozimu Language Client for Neovim

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
