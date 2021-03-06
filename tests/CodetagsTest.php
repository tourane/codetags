<?php namespace Tourane\Codetags;

use PHPUnit\Framework\TestCase;

class TagManagerTest extends TestCase {

  /**
   * Call protected/private method of a class.
   *
   * @param object &$object    Instantiated object that we will run method on.
   * @param string $methodName Method name to call
   * @param array  $parameters Array of parameters to pass into method.
   *
   * @return mixed Method return.
   */
  public function invokeMethod(&$object, $methodName, array $parameters = array()) {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($object, $parameters);
  }

  public function printJson($data) {
    print(json_encode($data, JSON_PRETTY_PRINT));
  }

  protected function setUp() {
    TagManager::instance()->reset();
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
    TagManager::newInstance(1024);
  }

  /**
   * @dataProvider loop_illegal_instance_name
   */
  public function test_illegal_instance_name($instanceName) {
    $this->setExpectedException(\RuntimeException::class,
        "CODETAGS is default instance name. Please provides another name.");
    TagManager::newInstance($instanceName);
  }

  public function loop_illegal_instance_name() {
    return array(
      array("codetags"),
      array("CODETAGS"),
      array("CodeTags"),
    );
  }

  public function testCreateNewInstanceSuccessfully() {
    $this->assertInstanceOf(TagManager::class, TagManager::newInstance("Tourane", array(
      "namespace" => "Xoay",
      "version" => "0.1.2"
    )));
  }

  public function testGetInstanceSuccessfully() {
    $mission1 = TagManager::getInstance("mission");
    $mission2 = TagManager::getInstance("mission");
    $this->assertInstanceOf(TagManager::class, $mission1);
    $this->assertInstanceOf(TagManager::class, $mission2);
    $this->assertEquals($mission1, $mission2);
  }

  public function test_register_return_itself() {
    $this->assertEquals(TagManager::instance(), TagManager::instance()->register());
  }

  /**
   * @dataProvider loop_register_add_descriptors
   */
  public function test_register_add_descriptors($version, $featureList) {
    TagManager::instance()->initialize(array(
      "version" => $version
    ));
    TagManager::instance()->register(array(
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
      array(
        "name" => "feature-14",
        "plan" => array(
          "enabled" => False,
          "maxBound" => "0.2.9"
        )
      ),
    ));
    $actual = TagManager::instance()->getDeclaredTags();
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
      array("0.2.9", ["feature-01", "feature-02", "feature-11", "feature-14"]),
      array("0.2.10", ["feature-01", "feature-02", "feature-11", "feature-14"]),
    );
  }

  /**
   * @dataProvider loop_getLabel_by_default
   */
  public function test_getLabel_by_default($label_type, $expected) {
    $output = $this->invokeMethod(TagManager::instance(), 'getLabel', array($label_type));
    $this->assertEquals($expected, $output);
  }

  public function loop_getLabel_by_default() {
    return array(
      array("INCLUDED_TAGS", "CODETAGS_INCLUDED_TAGS"),
      array("EXCLUDED_TAGS", "CODETAGS_EXCLUDED_TAGS")
    );
  }

  /**
   * @dataProvider loop_getLabel
   */
  public function test_getLabel($namespace, $label_type, $label, $expected) {
    $init_params = array($label_type => $label);
    if (is_string($namespace)) {
      $init_params["namespace"] = $namespace;
    }
    TagManager::instance()->initialize($init_params);
    $output = $this->invokeMethod(TagManager::instance(), 'getLabel', array($label_type));
    $this->assertEquals($expected, $output);
  }

  public function loop_getLabel() {
    return array(
      [ Null, "INCLUDED_TAGS", "POSITIVE_TAGS", "CODETAGS_POSITIVE_TAGS" ],
      [ Null, "EXCLUDED_TAGS", "NEGATIVE_TAGS", "CODETAGS_NEGATIVE_TAGS" ],
      [ "testing", "INCLUDED_TAGS", "POSITIVE_TAGS", "TESTING_POSITIVE_TAGS" ],
      [ "TESTING", "EXCLUDED_TAGS", "NEGATIVE_TAGS", "TESTING_NEGATIVE_TAGS" ],
    );
  }

  /**
   * @dataProvider loop_getEnv
   */
  public function test_getEnv($putenv_str, $label, $default_value, $expected) {
    putenv($putenv_str);
    $output = $this->invokeMethod(TagManager::instance(), 'getEnv', array($label, $default_value));
    // $this->printJson($output);
    $this->assertEquals($expected, $output);
  }

  public function loop_getEnv() {
    return array(
      ["CODETAGS_INCLUDED_TAGS=abc, , , ", "CODETAGS_INCLUDED_TAGS", Null, ["abc"]],
      ["CODETAGS_INCLUDED_TAGS=abc , rst", "CODETAGS_INCLUDED_TAGS", Null, ["abc", "rst"]],
      ["CODETAGS_INCLUDED_TAGS=abc,def,xyz", "CODETAGS_INCLUDED_TAGS", Null, ["abc", "def", "xyz"]]
    );
  }

  public function test_isActive() {
    putenv("CODETAGS_INCLUDED_TAGS=abc, def, xyz, tag-4");
    putenv("CODETAGS_EXCLUDED_TAGS=disabled, tag-2");
    TagManager::instance()->register(['tag-1', 'tag-2']);
    // An arguments-list presents the OR conditional operator
    $this->assertTrue(TagManager::instance()->isActive('abc'));
    $this->assertTrue(TagManager::instance()->isActive('abc', 'xyz'));
    $this->assertTrue(TagManager::instance()->isActive('abc', 'disabled'));
    $this->assertTrue(TagManager::instance()->isActive('disabled', 'abc'));
    $this->assertTrue(TagManager::instance()->isActive('abc', 'nil'));
    $this->assertTrue(TagManager::instance()->isActive('undefined', 'abc', 'nil'));
    $this->assertFalse(TagManager::instance()->isActive());
    $this->assertFalse(TagManager::instance()->isActive(null));
    $this->assertFalse(TagManager::instance()->isActive('disabled'));
    $this->assertFalse(TagManager::instance()->isActive('nil'));
    $this->assertFalse(TagManager::instance()->isActive('nil', 'disabled'));
    $this->assertFalse(TagManager::instance()->isActive('nil', 'disabled', 'abc.xyz'));
    // An array argument presents the AND conditional operator
    $this->assertTrue(TagManager::instance()->isActive(['abc', 'xyz'], 'nil'));
    $this->assertTrue(TagManager::instance()->isActive(['abc', 'xyz'], null));
    $this->assertFalse(TagManager::instance()->isActive(['abc', 'nil']));
    $this->assertFalse(TagManager::instance()->isActive(['abc', 'def', 'nil']));
    $this->assertFalse(TagManager::instance()->isActive(['abc', 'def', 'disabled']));
    $this->assertFalse(TagManager::instance()->isActive(['abc', '123'], ['def', '456']));
    // pre-defined tags are overridden by values of environment variables
    $this->assertTrue(TagManager::instance()->isActive('abc'));
    $this->assertTrue(TagManager::instance()->isActive('tag-1'));
    $this->assertTrue(TagManager::instance()->isActive('abc', 'tag-1'));
    $this->assertTrue(TagManager::instance()->isActive('disabled', 'tag-1'));
    $this->assertTrue(TagManager::instance()->isActive('tag-4'));
    $this->assertFalse(TagManager::instance()->isActive('tag-2'));
    $this->assertFalse(TagManager::instance()->isActive('tag-3'));
    $this->assertFalse(TagManager::instance()->isActive(['nil', 'tag-1']));
    $this->assertFalse(TagManager::instance()->isActive('nil', 'tag-3'));
    $this->assertFalse(TagManager::instance()->isActive('tag-3', 'disabled'));
  }
}

?>