<?php namespace Tourane\Codetags;

use PHPUnit\Framework\TestCase;

class CodetagsTest extends TestCase {

  /**
   * @expectedException \RuntimeException
   */
  public function testInvalidInstanceName() {
    // $this->expectException(\RuntimeException::class);
    // $this->expectExceptionMessage("The name of a codetags instance must be a string");
    // for PHPUnit < 5.2
    $this->setExpectedException(\RuntimeException::class,
        "The name of a codetags instance must be a string");
    Codetags::newInstance(1024);
  }

  /**
   * @dataProvider dataIllegalInstanceNameProvider
   */
  public function testIllegalInstanceName($instanceName) {
    $this->setExpectedException(\RuntimeException::class,
        "CODETAGS is default instance name. Please provides another name.");
    Codetags::newInstance($instanceName);
  }

  public function dataIllegalInstanceNameProvider() {
    return array(
      array("codetags"),
      array("CODETAGS"),
      array("CodeTags"),
    );
  }

  public function testCreateNewInstanceSuccessfully() {
    $this->assertInstanceOf(Codetags::class, Codetags::newInstance("Tourane"));
  }

  public function testGetInstanceSuccessfully() {
    $mission1 = Codetags::getInstance("mission");
    $mission2 = Codetags::getInstance("mission");
    $this->assertInstanceOf(Codetags::class, $mission1);
    $this->assertInstanceOf(Codetags::class, $mission2);
    $this->assertEquals($mission1, $mission2);
  }
}

?>