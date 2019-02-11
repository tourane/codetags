<?php namespace Tourane\Codetags;

const DEFAULT_NAMESPACE = "CODETAGS";

class Codetags {
  private static $instance = array();
  private $store = array(
    "env" => array(),
    "declaredTags" => array(),
    "cachedTags" => array()
  );
  private $presets = array();

  private function __construct() {
  }

  public function initialize($opts = array()) {
    foreach (array("namespace", "positiveTagsLabel", "negativeTagsLabel") as $fieldName) {
      if (array_key_exists($fieldName, $opts) && is_string($opts[$fieldName])) {
        $this->presets[$fieldName] = Nodash::labelify($opts[$fieldName]);
      }
    }
    foreach (array("version") as $fieldName) {
      if (array_key_exists($fieldName, $opts) && is_string($opts[$fieldName])) {
        $this->presets[$fieldName] = $opts[$fieldName];
      }
    }
    return $this;
  }

  public function register($descriptors = array()) {
    if (is_array($descriptors)) {
      $that = $this;
      $defs = array_filter($descriptors, function ($def) use ($that) {
        if (is_string($def)) return True;
        if (is_array($def) && array_key_exists("name", $def)) {
          if (array_key_exists("plan", $def)) {
            $plan = $def["plan"];
            if (is_array($plan) && array_key_exists("enabled", $plan) && is_bool($plan["enabled"])) {
              if (array_key_exists("version", $that->presets)) {
                $validated = True;
                $satisfied = True;
                if (array_key_exists("minBound", $plan) && is_string($plan["minBound"])) {
                  $validated = $validated && Nodash::isVersionValid($plan["minBound"]);
                  if ($validated) {
                    $satisfied = $satisfied && Nodash::isVersionLTE($plan["minBound"], $that->presets["version"]);
                  }
                }
                if (array_key_exists("maxBound", $plan) && is_string($plan["maxBound"])) {
                  $validated = $validated && Nodash::isVersionValid($plan["maxBound"]);
                  if ($validated) {
                    $satisfied = $satisfied && Nodash::isVersionLT($that->presets["version"], $plan["maxBound"]);
                  }
                }
                if ($validated) {
                  if ($satisfied) {
                    return $plan["enabled"];
                  } else {
                    if (array_key_exists("enabled", $def) && is_bool($def["enabled"])) return $def["enabled"];
                    return !($plan["enabled"]);
                  }
                }
              }
            }
          }
          if (array_key_exists("enabled", $def) && is_bool($def["enabled"])) return $def["enabled"];
          return True;
        }
        return False;
      });
      $tags = array_map(function ($def) use($that) {
        if (is_string($def)) return $def;
        if (array_key_exists("name", $def)) return $def["name"];
        return NULL;
      }, $defs);
      foreach($tags as $tag) {
        if (!in_array($tag, $that->store["declaredTags"])) {
          array_push($that->store["declaredTags"], $tag);
        }
      }
    }
    return $this;
  }

  public function getDeclaredTags() {
    if (is_array($this->store["declaredTags"])) {
      return $this->store["declaredTags"];
    }
    return array();
  }

  public function clearCache() {
    foreach(array_keys($this->store["env"]) as $envName) {
      unset($this->store["env"][$envName]);
    }
    foreach(array_keys($this->store["cachedTags"]) as $tag) {
      unset($this->store["cachedTags"][$tag]);
    }
    unset($this->store["positiveTags"]);
    unset($this->store["negativeTags"]);
    return $this;
  }

  public function reset() {
    $this->clearCache();
    array_splice($this->store["declaredTags"], 0);
    foreach(array_keys($this->presets) as $fieldName) {
      unset($this->presets[$fieldName]);
    }
    return $this;
  }

  public static function newInstance($name, $opts = array()) {
    $name = Nodash::labelify($name);
    if (!is_string($name)) {
      throw new \RuntimeException("The name of a codetags instance must be a string");
    }
    if ($name === DEFAULT_NAMESPACE && array_key_exists($name, self::$instance)) {
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

  public static function instance() {
    return self::getInstance(DEFAULT_NAMESPACE);
  }
}

Codetags::instance();

?>
