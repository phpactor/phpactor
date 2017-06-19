Phpactor
========

PHP refactoring tool.

Commands
--------

### Move classes

Move classes by file or directory and update references to them.

```bash
$ phpactor mv lib/Path/To/MyClass.php lib/NewLocation.php --path=lib --path=test
```

The above will move the single class from one path to another and update all
references in the `lib` and `test` directories.

```bash
$ phpactor mv lib/Acme lib/Foobar --path=lib --path=test
```

The above will relocate all classes under `Acme` to `Foobar`.

![Class mover](https://user-images.githubusercontent.com/530801/27299917-d0f6da86-5525-11e7-901e-f3881e3afd83.gif)

- Moves single class *files* or *directories*.
- Updates references for all moved classes (if one or more `--path` options
  are given).
- Use statements are updated or added when required.

By default the class names (source and target) are "guessed" from the composer
configuration the code is then searched for references to the source class
(using a PHP parser) and then the references are replaced.

About this project
------------------

This project aims to provide refactoring tools which can be used with editors
such as VIM. It aims to package functionality in separate, dedicated,
repositories, currently:

- [dantleech/class-to-file](https://github.com/dantleech/class-to-file): Library to convert files to class names and vice-versa.
- [dantleech/class-mover](https://github.com/dantleech/class-mover): Library to find and update class references.
