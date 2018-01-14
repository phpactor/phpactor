---
currentMenu: navigation
---
Navigation
==========

- [Class References](#class-references)
- [Class Member References](#class-member-references)
- [Jump to Definition](#jump-to-definition)
- [Jump to or generate related file](#jump-to-or-generate-related-file)

Class References
----------------

Navigate / list all references to a given class.

- **Command**: `$ phpactor references:class path/to/Class.php`
- **VIM Context Menu**: _Class context menu > Find references_.
- **VIM Command**: `call phpactor#FindReferences()`

### Description

Keep track of where a class is being used or perform an initial survey before
deciding to rename a class.

The VIM plugin will load the class references into a quick fix list which you can navigate through (see `:help quickfix`).

The CLI command will list the references and show a highlighted line where the references were found.

Class Member References
-------------------------

Navigate / list all references to a given class member (method, property or constant).

- **Command**: `$ phpactor references:member path/to/Class.php memberName`
- **VIM Context Menu**: _Member context menu > Find references_.
- **VIM Command**: `call phpactor#FindReferences()`

### Description

Scan for all references to a class member in the project.

This functionality is very similar to [Class References](#class-references)
with the exception that it is possible that not all members will be found as
PHP is a loosely typed language and it may not be possible to determine all the
class types of methods matching the query.

Jump to definition
------------------

Jump to the definition of a class or class member.

- **Command**: _RPC Only_
- **VIM Context Menu**: _Member/class context menu > Goto definition_.
- **VIM Command**: `call phpactor#GotoDefinition()`

### Description

Open the file containing the class or class member under the cursor and move the cursor to the place where class or class member is defined.

This feature is **extremely useful**! Be sure to map it to a keyboard shortcut and use it often to quickly navigate through your source code.

Jump to or generate related file
--------------------------------

Jump to a related file (e.g. unit test, integration test, benchmark), and optionally generate it if it doesn't exist.

- **Command**: _RPC Only_
- **VIM Context Menu**: _Class context menu > Navigate_.
- **VIM Command**: `call phpactor#Navigate()`

### Description

Often classes will have a one-to-one relationship with another class, for example a single class will often have a matching unit test.

Phpactor provides a way to define this relationship:

```yaml
# .phpactor.yml
navigator.destinations:
    source: lib/<kernel>.php
    unit_test: tests/Unit/<kernel>Test.php

navigator.autocreate:
    source: default
    unit_test: phpunit_test
```

Above we define a pattern which will match the source code of the project (and assign it an identifier `source`). We also identify a pattern to identify `unit_test` classes.

When you are in a `source` file, the navigate option will offer you the possiblity of jumping to the unit test, and vice-versa.

Above we additionally (and optionally) tell Phpactor that it can autogenerate
these classes based on [templates](templates.md).
