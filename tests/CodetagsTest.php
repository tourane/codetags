<?php namespace Tourane\Codetags;

use PHPUnit\Framework\TestCase;

class CodetagsTest extends TestCase {

  protected function setUp() {
    Codetags::instance()->reset();
  }

  protected function tearDown() {
  }

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
   * @dataProvider loop_illegal_instance_name
   */
  public function test_illegal_instance_name($instanceName) {
    $this->setExpectedException(\RuntimeException::class,
        "CODETAGS is default instance name. Please provides another name.");
    Codetags::newInstance($instanceName);
  }

  public function loop_illegal_instance_name() {
    return array(
      array("codetags"),
      array("CODETAGS"),
      array("CodeTags"),
    );
  }

  public function testCreateNewInstanceSuccessfully() {
    $this->assertInstanceOf(Codetags::class, Codetags::newInstance("Tourane", array(
      "namespace" => "Xoay",
      "version" => "0.1.2"
    )));
  }

  public function testGetInstanceSuccessfully() {
    $mission1 = Codetags::getInstance("mission");
    $mission2 = Codetags::getInstance("mission");
    $this->assertInstanceOf(Codetags::class, $mission1);
    $this->assertInstanceOf(Codetags::class, $mission2);
    $this->assertEquals($mission1, $mission2);
  }

  public function test_register_return_itself() {
    $this->assertEquals(Codetags::instance(), Codetags::instance()->register());
  }

  /**
   * @dataProvider loop_register_add_descriptors
   */
  public function test_register_add_descriptors($version, $featureList) {
    Codetags::instance()->initialize(array(
      "version" => $version
    ));
    Codetags::instance()->register(array(
      "feature-01",
      array(
        "name" => "feature-02"
      ),
      array(
        "name" => "feature-03",
        "enabled" => False
      ),
      array(
        "name" => "feature-04",
        "plan" => array(
          "enabled" => False
        )
      ),
      array(
        "name" => "feature-11",
        "plan" => array(
          "enabled" => True,
          "minBound" => "0.1.2"
        )
      ),
      array(
        "name" => "feature-12",
        "plan" => array(
          "enabled" => True,
          "maxBound" => "0.2.8"
        )
      ),
      array(
        "name" => "feature-13",
        "plan" => array(
          "enabled" => True,
          "minBound" => "0.2.1",
          "maxBound" => "0.2.7"
        )
      ),
    ));
    $actual = Codetags::instance()->getDeclaredTags();
    $expected = $featureList;
    False && print(json_encode($actual, JSON_PRETTY_PRINT));
    $this->assertEmpty(array_diff($expected, $actual));
    $this->assertEmpty(array_diff($actual, $expected));
  }

  public function loop_register_add_descriptors() {
    return array(
      array("0.1.0", ["feature-01", "feature-02", "feature-12"]),
      array("0.1.3", ["feature-01","feature-02","feature-11","feature-12"]),
      array("0.2.2", ["feature-01", "feature-02", "feature-11", "feature-12", "feature-13"]),
      array("0.2.8", ["feature-01", "feature-02", "feature-11"]),
    );
  }
}

?>