PhpActor
========

PHP introspection tool and code completion tool.

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
