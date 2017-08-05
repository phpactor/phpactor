Phpactor
========

![phpactor2sm](https://user-images.githubusercontent.com/530801/27995098-82e72c4c-64c0-11e7-96d2-f549c711ca8b.png)

[![Build Status](https://travis-ci.org/phpactor/phpactor.svg?branch=master)](https://travis-ci.org/phpactor/phpactor)

This project aims to provide heavy-lifting *refactoring* and *introspection*
tools which can be used standalone or as the backend for a text editor to
provide intelligent code completion.

Features
--------

- **No indexing**: [Composer](https://getcomposer.org) is used to determine where things should be.
- [Move](#move-classes) and [copy](#copy-classes): Move and copy classes, updating PHP references to them.
- [Class generation](#class-generation): Generate classes.
- [Class inflection](#class-inflect): Generate classes from other classes.
- [Reflection API](#reflect-class): Get reflection data for a given class or file.
- **Type inference**: Determine the type of something at a given offset.
- [Transformation](#transformation): Apply "transformations" to code (e.g. implement
  interfaces, add missing properties).
- [Class search](#class-search): Search for a class by its name.
- **VIM Plugin**: see [plugin README](https://github.com/phpactor/phpactor/tree/master/plugin/README.md).

Prerequisites
-------------

- Projects MUST use Composer and GIT.
- PHP 7.

Configuration
-------------

Configuration files are loaded and merged from the current working directory
and then by the XDG base dir standard.

To debug (and inspect) the configuration:

```bash
$ phpactor config:dump
Config files:
 [-] /home/daniel/www/phpactor/phpactor/.phpactor.yml
 [-] /home/daniel/.config/phpactor/phpactor.yml
 [x] /etc/xdg/phpactor/phpactor.yml

autoload:vendor/autoload.php
cwd:/home/daniel/www/phpactor/phpactor
console_dumper_default:indented
reflector_stub_directory:/home/daniel/www/phpactor/phpactor/lib/Container/../../vendor/jetbrains/phpstorm-stubs
cache_dir:/home/daniel/www/phpactor/phpactor/lib/Container/../../cache
code_transform.indentation: "    "
code_transform.class_new.variants:
  - exception
  - symfony_command
  - phpunit_test
code_transform.template_paths:
  - /home/daniel/www/phpactor/phpactor/.phpactor/templates
```

Commands
--------

- [Move classes](#move-classes): Move a class, or a glob of classes, to a new
  location and update all reference to it/them.
- [Copy classes](#copy-classes): As with move but copy to a new file.
- [Class search](#class-search): Search for a class by it's short name.
- [Information at offset](#information-at-offset): Return the type information
- [Reflect class](#reflect-class): Return reflection data for a given class
  or file.
- [Transform](#transform): Apply a transformation to a given file or from
  STDIN.
    - [Implement Contracts](#implement-contracts): Implement interface/abstract methods.
    - [Complete Constructor](#complete-constructor): Finish off constructor definition.

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

![recording](https://user-images.githubusercontent.com/530801/27604530-7357d9d2-5b71-11e7-86ad-1921462b2f43.gif)

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

```bash
$ phpactor offset:info lib/Application/InformationForOffset/InformationForOffset.php 1382
type:Phpactor\ClassFileConverter\ClassName
path:/.../vendor/dtl/class-to-file/lib/ClassName.php
```
Also returns JSON with `--format=json`

### Reflect class

Return reflection information for a given class name or file:

```bash
$ phpactor class:reflect lib/Application/Transformer.php
class:Phpactor\Application\Transformer
class_namespace:Phpactor\Application
class_name:Transformer
methods:
  __construct:
    name:__construct
    abstract:
    visibility:public
    parameters:
      transform:
        name:transform
        has_type:1
        type:CodeTransform
        has_default:
        default:
    static:0
    type:<unknown>
    synopsis:public function __construct(Phpactor\CodeTransform\CodeTransform $transform)
    docblock:
  transform:
# ...
```

Also returns JSON with `--format=json`

### Transform

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

#### Implement contracts

Name: `implement_contracts`

This transformer will implement any missing interface methods or abstract
methods:

In:

```php
<?php

class Post implements \Countable
{
}
```

Out:

```php
<?php

class Post implements \Countable
{
    /**
     * {@inheritdoc}
     */
    public function count()
    {
    }
}
```

#### Add missing assignments

Name: `add_missing_assignments`

This transformer will add any missing assignments from the current class as
private properties.

In:

```php
<?php

class Post
{
    public function hello()
    {
        $this->mutate = new Turtle();
    }
}
```

Out:

```php
<?php

class Post
{
    /**
     * @var Turtle
     */
    private $mutate;

    public function hello()
    {
        $this->mutate = new Turtle();
    }
}
```

### Class generation

Generates a new class at a given file path or class name:

```bash
$ phpactor class:new lib/Registry/Generator.php
path: lib/Registry/Generator.php
```

Different variants can be specified (contrived example):

```
$ phpactor class:new tests/Registry/GeneratorTest.php --variant=test
```

Variants are registered in `.phpactor.yml`:

```yaml
new_class_variants:
    phpunit_test: phpunit_test
```

In order to create the above variant we need to create a template locally in
`.phpactor/templates` (note you can also create them globally in the XDG
directories, in a `templates` folder):

```twig
{# /path/to/project/.phpactor/templates/SourceCode.php.twig #}
namespace {{ prototype.namespace }};

use PHPUnit\Framework\TestCase;

{% for class in prototype.classes %}
class {{ class.name }} extends TestCase
{
}
{% endfor %}
```

Class Inflect
-------------

Inflect a new class from an existing class.

The following will generate an interface from an existing class:

```
$ phpactor class:inflect lib/TestGenerator.php lib/Api/TestGenerator.php interface
```

Packages
--------

- [phpactor/class-to-file](https://github.com/phpactor/class-to-file): Convert files to class names and vice-versa.
- [phpactor/class-mover](https://github.com/phpactor/class-mover): Find and update class references.
- [phpactor/source-code-filesystem](https://github.com/phpactor/source-code-filesystem): Find and manage source code files.
- [phpactor/type-inference](https://github.com/phpactor/type-inference): Determine type of thing at a given offset.
- [phpactor/code-transform](https://github.com/phpactor/code-transform): Transform code.
- [phpactor/worse-reflection](https://github.com/phpactor/worse-reflection): Lightweight class reflection API

About this project
------------------

This project attempts to close the gap between text editors such as VIM and
IDEs such as PHPStorm.

One of the interesting things about Phpactor is that it does not require any
indexing before it is used. It leverages the Composer to determine class
locations and to determine class FQNs from file locations. Introspection is
done in realtime (using the excellent [Tolereant PHP
Parser](https://github.com/Microsoft/tolerant-php-parser).

Using Composer we can locate a file using a fully qualified class name, when
we have located the file we can parse it. This is enough for common
auto-completion.

For other use cases, such as searching for a class, we simply perform a file
search, but only in those directories mapped by Composer. Even in large
projects searching for a class by its (short) name is pretty fast.
