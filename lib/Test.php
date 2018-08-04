<?php

namespace Phpactor;

// 姓名
//
class Test
{
    public $bar;
}

class A
{
  public function foo(): B
  {
  }
}

$a = new A();
$a->foo;
