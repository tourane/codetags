<?php namespace Tourane\Codetags;

use Composer\Semver\Comparator;

class Nodash {
  public static function labelify($label) {
    if (!is_string($label)) return $label;
    return preg_replace('/\W{1,}/i', "_", strtoupper($label));
  }

  public static function is_associate_array($array) {
    return !self::is_sequence_array($array);
  }

  public static function is_sequence_array($array) {
    if (!is_array($array)) return False;
    foreach(array_keys($array) as $index) {
      if (is_string($index)) return False;
      break;
    }
    return True;
  }

  public static function isVersionValid($version) {
    return Comparator::greaterThanOrEqualTo($version, "0.0.1");
  }

  public static function isVersionLTE($version1, $version2) {
    return Comparator::lessThanOrEqualTo($version1, $version2);
  }

  public static function isVersionLT($version1, $version2) {
    return Comparator::lessThan($version1, $version2);
  }

  public static function stringToArray($str) {
    if (is_string($str)) {
      $arr = preg_split("/[,]/", $str);
      // $arr = explode(",", $str);
      $arr = array_map(function ($item) {
        return trim($item);
      }, $arr);
      $arr = array_filter($arr, function($item) {
        return strlen($item) > 0;
      });
      return $arr;
    }
    return array();
  }
}

?>
