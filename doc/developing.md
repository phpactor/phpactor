---
currentMenu: developing
---

Debugging RPC
-------------

When executing commands in an editor, it can be tricky to consistently
reproduce errors, or to isolate and debug them in Phpactor.

Thankfully there is a feature called RPC replay which allows you to replay
the last RPC command received by Phpactor.

Enable it in a Phpactor configuration file, for example
`$HOME/.config/phpactor/phpactor.yml`:

```yaml
rpc.store_replay: true
```

Now, after you execute an RPC command via. your editor, you can execute
Phpactor from the shell and replay your last command:

```bash
$ phpactor rpc --replay 
{"action":"open_file","parameters":{"path":"\/home\/daniel\/www\/phpactor\/phpactor\/lib\/Extension\/Rpc\/Handler\/AbstractHandler.php","offset":447}}
```

To see more information, including the initial request add the `--verbose`
option:

```bash
$ phpactor rpc --replay --verbose
[2018-05-23T22:14:11.486488+02:00] phpactor.DEBUG: REQUEST {"action":"goto_definition","parameters":{"source":"[removed]","offset":1913,"path":"/home/daniel/somepath/SomeClass.php"}}
[2018-05-23T22:14:11.494201+02:00] phpactor.DEBUG: Resolving: Microsoft\PhpParser\Node\Statement\ClassDeclaration [] []
[2018-05-23T22:14:11.494545+02:00] phpactor.DEBUG: Resolving: Microsoft\PhpParser\Node\Parameter [] []
...[truncated]...
[2018-05-23T22:14:11.508019+02:00] phpactor.DEBUG: RESPONSE {"action":"open_file","parameters":{"path":"/home/daniel/www/phpactor/phpactor/lib/Extension/Rpc/Handler/AbstractHandler.php","offset":447}} []
{"action":"open_file","parameters":{"path":"\/home\/daniel\/www\/phpactor\/phpactor\/lib\/Extension\/Rpc\/Handler\/AbstractHandler.php","offset":447}}
```

The next time you run a command, you will lose your replay, in order to
consistently reproduce an action, you can copy the replay file and execute it
consistently as many times as required:

```bash
$ cp ~/.local/share/phpactor/replay.json .
$ cat replay.json | phpactor rpc --verbose
```

Logging
-------

Logging is disabled by default, but can provide some useful information (such
as errors encountered when parsing files etc).

Enable it as follows:

```
logging.enabled: true
logging.level: DEBUG
logging.path: phpactor.log
```

Documentation
-------------

Phpactor Documentation
----------------------

Phpactor uses [couscous](https://github.com/CouscousPHP/Couscous). In order to
develop the documentation (located in `doc/`) download the PHAR:

```bash
$ curl -OS http://couscous.io/couscous.phar 
```

and run:

```
$ php couscous.phar preview
```

To run serve the documentation locally.

VIM Help
--------

The VIM plugin is documented in the _generated_ `doc/phpactor.txt` file.

Install the [vimdoc](https://github.com/google/vimdoc) tool, followw the
installation instructions and then run:

```bash
$ vimdoc .
```
