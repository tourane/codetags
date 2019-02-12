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
    return $this->refreshEnv();
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

  public function isActive() {
    $arguments = array();
    for($i = 0; $i < func_num_args(); $i++) {
      array_push($arguments, func_get_arg($i));
    }
    return $this->isArgumentsSatisfied($arguments);
  }

  public function getDeclaredTags() {
    if (is_array($this->store["declaredTags"])) {
      return $this->store["declaredTags"];
    }
    return array();
  }

  public function clearCache() {
    foreach(array_keys($this->store["cachedTags"]) as $tag) {
      unset($this->store["cachedTags"][$tag]);
    }
    return $this->refreshEnv();
  }

  public function reset() {
    $this->clearCache();
    array_splice($this->store["declaredTags"], 0);
    foreach(array_keys($this->presets) as $fieldName) {
      unset($this->presets[$fieldName]);
    }
    return $this;
  }

  private function getLabel($label) {
    $prefix = "_";
    if (array_key_exists("namespace", $this->presets) && is_string($this->presets["namespace"])) {
      $prefix = $this->presets["namespace"] . $prefix;
    } else {
      $prefix = DEFAULT_NAMESPACE . $prefix;
    }
    switch ($label) {
      case "positiveTags":
        return $prefix . (array_key_exists("positiveTagsLabel", $this->presets) ? $this->presets["positiveTagsLabel"] : "POSITIVE_TAGS");
        break;
      case "negativeTags":
        return $prefix . (array_key_exists("negativeTagsLabel", $this->presets) ? $this->presets["negativeTagsLabel"] : "NEGATIVE_TAGS");
        break;
    }
    return $prefix . (array_key_exists($label, $this->presets) ? $this->presets[$label] : Nodash::labelify($label));
  }

  private function getEnv($label, $default_value = Null) {
    if (!is_string($label)) return Null;
    if (array_key_exists($label, $this->store["env"])) return $this->store["env"][$label];
    $this->store["env"][$label] = Null;
    if (is_string($default_value)) {
      $this->store["env"][$label] = $default_value;
    }
    $env_value = getenv($label);
    if (is_string($env_value)) {
      $this->store["env"][$label] = $env_value;
    }
    $this->store["env"][$label] = Nodash::stringToArray($this->store["env"][$label]);
    return $this->store["env"][$label];
  }

  private function refreshEnv() {
    foreach(array_keys($this->store["env"]) as $envName) {
      unset($this->store["env"][$envName]);
    }
    foreach(["positiveTags", "negativeTags"] as $tagType) {
      $this->store[$tagType] = $this->getEnv($this->getLabel($tagType));
    }
    return $this;
  }

  private function isArgumentsSatisfied($arguments) {
    foreach($arguments as $arg) {
      if ($this->evaluateExpression($arg)) return True;
    }
    return False;
  }

  private function isAllOfLabelsSatisfied($labels) {
    if (is_array($labels)) {
      foreach($labels as $label) {
        if (!$this->evaluateExpression($label)) return False;
      }
      return True;
    }
    return $this->evaluateExpression($labels);
  }

  private function isAnyOfLabelsSatisfied($labels) {
    if (is_array($labels)) {
      foreach($labels as $label) {
        if ($this->evaluateExpression($label)) return True;
      }
      return False;
    }
    return $this->evaluateExpression($labels);
  }

  private function isNotOfLabelsSatisfied($labels) {
    return !($this->evaluateExpression($labels));
  }

  private function evaluateExpression($exp) {
    if (is_array($exp)) {
      if (Nodash::is_associate_array($exp)) {
        foreach($exp as $op => $subexp) {
          switch($op) {
            case '$all':
              if ($this->isAllOfLabelsSatisfied($subexp) === False) return False;
              break;
            case '$any':
              if ($this->isAnyOfLabelsSatisfied($subexp) === False) return False;
              break;
            case '$not':
              if ($this->isNotOfLabelsSatisfied($subexp) === False) return False;
              break;
            default:
              return False;
              break;
          }
        }
        return True;
      } else {
        return $this->isAllOfLabelsSatisfied($exp);
      }
    }
    return $this->checkLabelActivated($exp);
  }

  private function checkLabelActivated($label) {
    if (array_key_exists($label, $this->store["cachedTags"])) {
      return $this->store["cachedTags"][$label];
    }
    $this->store["cachedTags"][$label] = $this->forceCheckLabelActivated($label);
    return $this->store["cachedTags"][$label];
  }

  private function forceCheckLabelActivated($label) {
    if (in_array($label, $this->store["negativeTags"])) return False;
    if (in_array($label, $this->store["positiveTags"])) return True;
    return in_array($label, $this->store["declaredTags"]);
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
