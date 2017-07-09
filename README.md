Phpactor
========

[![Build Status](https://travis-ci.org/phpactor/phpactor.svg?branch=master)](https://travis-ci.org/phpactor/phpactor)

This project aims to provide heavy-lifting *refactoring* and *introspection*
tools which can be used with editors such as VIM.

The tool is currently limited to working with projects using **GIT** and **Composer**.

It is currently under development.

VIM Plugin
----------

This application standalone, but it does include a plugin for VIM, see the
[plugin README](https://github.com/phpactor/phpactor/tree/master/plugin/README.md).

![recording](https://user-images.githubusercontent.com/530801/27839804-2b309e8e-60ec-11e7-8df4-f5467cf56c8d.gif)

Configuration
-------------

Configuration files are merged from the XDG open base dir standard, and from
the current working directory, for example:

```
/etc/xdg/phpactor/phpactor.yml
/home/daniel/.config/phpactor/phpactor.yml
<currnent directory>/.phpactor.yml
```

All configuration options are defined in the
`Phpactor\Container\CoreExtension` class.

Commands
--------

### Move classes

All of the examples below will move the class and update all references in the
source code to it.

Move the single class from one path to another:

```bash
$ phpactor class:move lib/Path/To/MyClass.php lib/NewLocation.php
```

Relocate all classes under `Acme` to `Foobar`:

```bash
$ phpactor class:move lib/Acme lib/Foobar
```

Relocate all classes in the `lib` directory to a new subfolder:

```bash
$ phpactor class:move lib/* lib/Core
```

Move a class by name:

```bash
$ phpactor class:move "Acme\\BlogPost" "Acme\\Article"
```

![Class mover](https://user-images.githubusercontent.com/530801/27299917-d0f6da86-5525-11e7-901e-f3881e3afd83.gif)

- Moves individual class *files* or *directories*.
- Move by fully qualified class name of file path.
- Updates references for all moved classes in currently **GIT tree**.
- Use statements are updated or added when required.

### Copy classes

As with move, except only update the class names of the copied class(es).

```bash
$ phpactor class:copy lib/Path/To/MyClass.php lib/Path/To/CopyOfMyClass.php
$ cat lib/Path/To/CopyOfMyClass.php | grep class
class CopyOfMyClass
```

### Class Search

Search for a class by its (short) name and return a list of fully qualified
names => absolute paths.

```bash
./bin/phpactor class:search Filesystem
Phpactor\Filesystem\Domain\Filesystem:/.../vendor/phpactor/source-code-filesystem/lib/Domain/Filesystem.php
Symfony\Component\Filesystem\Filesystem:/.../vendor/symfony/filesystem/Filesystem.php
```

Also returns JSON with `--format=json`

### Information at offset

Return the fully qualified name of the class at the offset in the given file:

```php
$ phpactor offset:info lib/Application/InformationForOffset/InformationForOffset.php 1382
type:Phpactor\ClassFileConverter\ClassName
path:/.../vendor/dtl/class-to-file/lib/ClassName.php
```
Also returns JSON with `--format=json`

### Transformations

The transformation command accepts either a file name or `stdin` and applies
the specified transformations.

```bash
$ phpactor class:transform lib/MyClass.php --transform=complete_constructor
```

#### Complete Constructor

Name: `complete_constructor`

This transformation will add any missing assignments in a constructor and add
the class properties required.

In:

```php
<?php

class Post
{
    public function __construct(string $hello, Foobar $foobar)
    {
    }
}
```

Out:

```php
<?php

class Post
{
    /**
     * @var string
     */
    private $hello;

    /**
     * @var Foobar
     */
    private $foobar;

    public function __construct(string $hello, Foobar $foobar)
    {
        $this->hello = $hello;
        $this->foobar = $foobar;
    }
}
```

Child Libraries
---------------

It will package libraries in separate, decoupled libraries

- [phpactor/class-to-file](https://github.com/phpactor/class-to-file): Convert files to class names and vice-versa.
- [phpactor/class-mover](https://github.com/phpactor/class-mover): Find and update class references.
- [phpactor/source-code-filesystem](https://github.com/phpactor/source-code-filesystem): Find and manage source code files.
- [phpactor/type-inference](https://github.com/phpactor/type-inference): Determine type of thing at a given offset.
- [phpactor/worse-reflection](https://github.com/phpactor/worse-reflection): Lightweight class reflection API

About this project
------------------

Previous attempts at writing this tool involved building a single
"intellisense" core which is a database containing meta information about all
the code in the project. Whilst this approach would provide a solid core, it
is also very ambitious and offers little immediate payback (and continuing
instability) to justify the investment in time required.

The approach taken this time around is to design libraries which do *one thing
and do one thing well*. 

Some of the these libraries (e.g. class mover) may later be superceded by a
"reference replacer" which handles, f.e. refactoring method names. But as the
pre-requisite for this is writing an type inference system, it may be a long
way off, whereas replacing class references is comparatively easy and provides
a large value return in a short amount of time.

Other libraries are more generic (e.g. class to file, source code filesystem)
and can be used by other future libraries (e.g. go-to-definition,
use-statement include, snippet generation, interface implementation,
intellisense database...).

None of the child libraries are currently intended to be consumed
independently and cannot be found on packagist.
