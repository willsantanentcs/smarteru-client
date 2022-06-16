<?php

namespace Tests\Core\SmarterU;

use PHPUnit\Framework\TestCase;

class FalseTest extends TestCase {
    public function testFail() {
        $this->assertTrue(false);
    }
}
