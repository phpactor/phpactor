---
currentMenu: standalone
---
Standalone
==========

Phpactor can be used as (and was designed be) a standalone CLI application,
and this will act as a compliment if it is being used as a backend for a text
editor.

The standalone application can be used to perform many of the actions exposed through the text editor
but can also be used to compliment the text editor by providing debug information and in some cases
more detail.

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


Configuration
-------------

Phpactor is configured with a YAML file. You can dump the configuration using the `config:dump` command.

```bash
$ phpactor config:dump
Config files:               
 [✔] /home/daniel/www/phpactor/phpactor/.phpactor.yml
 [✔] /home/daniel/.config/phpactor/phpactor.yml
 [𐄂] /etc/xdg/phpactor/phpactor.yml                                   

 code_transform.class_new.variants:
	exception:exception    
	autoload:vendor/autoload.php

 # ... etc
```

Note the `Config files` section above. This is a list of config files that
Phpactor has attempted to load:

- From the current directory.
- From the users home directory.
- From the systems configuration directory.

Phpactor will merge configuration files, with more specific configurations
overriding the less specific ones.
