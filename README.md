PhpActor
========

NOTE: This is a POC and certainly does not do much of anything described
below. Come back next year.

PHP refactoring and introspection tool. It can be used in combination with VIM
or any other editor to provide features similar to those found in PHPStorm.
That is the goal.

What this thing aims to do:

- Create and maintain an SQlite database for your code base.
- Provide autocompletion (given a string, and optionally a namespace and
  class).
- Provide the filename for any given class name (jump to a class).
- Provide method help.
- Rename classes or methods, and update all references to them.
- Move classes, and update all refering files.
- etc.

Features
--------

- [ ] Fix namespaces based on file paths.
- [ ] Rename files / classes globally.
- [ ] Method completion.
- [ ] Class generation.
- [ ] Add properties and and assign them for class constructors, inc. type docs.


How
---

It uses standard `Reflection` within the *applications* autoloader environment
to obtain facts about classes. Each request is made in a separate process so
that invalid classes do not break the world.

Furthermore it will probably use PHP-Doc to determine method return values.

Usage
-----

```bash
$ # Update or create the database
$ ./bin/phpactor scan /path/to/lib --bootstrap=/path/to/lib/vendor/autoload.php
```

```bash
$ # do other stuff
$ ./bin/phpactor autocomplete "Foo" --namespace="Foobar\\Bar" --class="MyClass"
$ ./bin/phpactor filename "Class\\Bar\\Boo"
$ ./bin/phpactor move "Class\\Bar\\Boo" "Class\\Bar\\Bar"
$ ./bin/phpactor rename "Class\\Bar\\Boo::foobar()" "Class\\Bar\\Boo\\::barfoo()"
````
