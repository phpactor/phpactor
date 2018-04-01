---
currentMenu: standalone
---
Standalone
==========

Phpactor can be used as (and was originally designed to be) a standalone CLI application.

It exposes a number of commands, which can be used to move classes, perform
transformations, etc.

Installation
------------

### When already installed with VIM

If you have [installed](vim-plugin.md) Phpactor through VIM, then you should simply create a symlink
to make it globally available on your system:

```bash
$ cd /usr/local/bin
$ sudo ln -s ~/.vim/bundles/phpactor/bin/phpactor phpactor
```

### Otherwise

You can simply checkout the project and then create a symlink as above:

```
$ cd ~/your/projects
$ git clone git@github.com:phpactor/phpactor
$ cd phpactor
$ composer install
$ cd /usr/local/bin
$ sudo ln -s ~/your/projects/phpactor/bin/phpactor phpactor
```

Note that you may also use the composer global install method, but at time of
writing this isn't a good idea as the chances are good that it will conflict
with other libraries.

At some undefined point in the future we may also create a PHAR distribution.

Optimization
------------

Phpactor works best when used with Composer, and is slightly better when used
with Git.

Check support using the `status` command:

```
$ phpactor status
✔ Composer detected - faster class location and more features!
✔ Git detected - enables faster refactorings in your repository scope!
```
