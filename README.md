Phpactor
========

[![Build Status](https://travis-ci.org/dantleech/phpactor.svg?branch=master)](https://travis-ci.org/dantleech/phpactor)

PHP refactoring tool.

Commands
--------

### Move classes

All of the examples below will move the class and update all references in the
source code to it.

Move the single class from one path to another:

```bash
$ phpactor mv lib/Path/To/MyClass.php lib/NewLocation.php
```

Relocate all classes under `Acme` to `Foobar`:

```bash
$ phpactor mv lib/Acme lib/Foobar
```

Move a class by name:

```bash
$ phpactor mv "Acme\\BlogPost" "Acme\\Article"
```

![Class mover](https://user-images.githubusercontent.com/530801/27299917-d0f6da86-5525-11e7-901e-f3881e3afd83.gif)

- Moves individual class *files* or *directories*.
- Move by fully qualified class name of file path.
- Updates references for all moved classes (if one or more `--path` options
  are given).
- Use statements are updated or added when required.

About this project
------------------

This project aims to provide refactoring tools which can be used with editors
such as VIM. It aims to package functionality in separate, dedicated,
repositories, currently:

- [dantleech/class-to-file](https://github.com/dantleech/class-to-file): Convert files to class names and vice-versa.
- [dantleech/class-mover](https://github.com/dantleech/class-mover): Find and update class references.
- [dantleech/source-code-filesystem](https://github.com/dantleech/source-code-filesystem): Find and manage source code files.
