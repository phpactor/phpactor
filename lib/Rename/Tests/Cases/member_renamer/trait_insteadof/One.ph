<?php

trait A {
    public function smallTalk() {
        return 'a';
    }
    public function bigTalk() {
        return 'A';
    }
}

trait B {
    public function smallTalk() {
        return 'b';
    }
    public function bigTalk() {
        return 'B';
    }
}

class Talker {
    use A, B {
        A::smallTalk insteadof B;
        B::bigTalk insteadof A;
    }
}
