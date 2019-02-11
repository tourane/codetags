<?php namespace Tourane\Codetags;

use Composer\Semver\Comparator;

class Nodash {
  public static function labelify($label) {
    if (!is_string($label)) return $label;
    return preg_replace('/\W{1,}/i', "_", strtoupper($label));
  }

  public static function is_associate_array($array) {
    return is_array($array) && count(array_filter(array_keys($array), 'is_string')) > 0;
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
}

?>
