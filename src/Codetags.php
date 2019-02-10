<?php namespace Xoay\Codetags;

const DEFAULT_NAMESPACE = "CODETAGS";

class Codetags {
  private static $instance = array();
  private $presets = array();

  private function __construct() {
  }

  public function initialize($opts = array()) {
    foreach (array("namespace", "positiveTagsLabel", "negativeTagsLabel") as $fieldName) {
      if (array_key_exists($fieldName, $opts) && is_string($opts[$fieldName])) {
        $presets[$fieldName] = Nodash::labelify($opts[$fieldName]);
      }
    }
    foreach (array("version") as $fieldName) {
      if (array_key_exists($fieldName, $opts) && is_string($opts[$fieldName])) {
        $presets[$fieldName] = $opts[$fieldName];
      }
    }
    return $this;
  }

  public static function newInstance($name, $opts = array()) {
    $name = Nodash::labelify($name);
    if (!is_string($name)) {
      throw new \RuntimeException("The name of a codetags instance must be a string");
    }
    if ($name == DEFAULT_NAMESPACE) {
      throw new \RuntimeException(sprintf("%s is default instance name. Please provides another name.", DEFAULT_NAMESPACE));
    }
    self::$instance[$name] = new Codetags();
    self::$instance[$name]->initialize($opts);
    return self::$instance[$name];
  }

  public static function getInstance($name, $opts = array()) {
    $name = Nodash::labelify($name);
    if (array_key_exists($name, self::$instance) && is_object(self::$instance[$name])) {
      if (is_array($opts)) {
        self::$instance[$name]->initialize($opts);
      }
      return self::$instance[$name];
    } else {
      return self::newInstance($name, $opts);
    }
  }
}

?>
