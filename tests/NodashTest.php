<?php namespace Tourane\Codetags;

use PHPUnit\Framework\TestCase;

class NodashTest extends TestCase {
  public function testLabelify() {
    $this->assertEquals(null, Nodash::labelify(null));
    $this->assertEquals("", Nodash::labelify(""));
    $this->assertEquals("HELLO_WORLD", Nodash::labelify("Hello  world"));
    $this->assertEquals("UNDERSCORE_WITH_123", Nodash::labelify("Underscore_with 123"));
    $this->assertEquals("USER_EXAMPLE_COM", Nodash::labelify("user@example.com"));
  }

  public function test_is_sequence_array() {
    $this->assertTrue(Nodash::is_sequence_array(array(0, "Hello World", True, array(1, 2, 3))));
  }
}

?>
