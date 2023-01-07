<?php

use SomeOtherNamespace\Foo;
use SomeOtherClass;

function (\SomeNamespace\Testing\Foo $foo): void {
   echo Foo::class;
   echo SomeOtherClass::class;
}
