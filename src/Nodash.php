<?php namespace Xoay\Codetags;

class Nodash {
  public static function labelify($label) {
    if (!is_string($label)) return $label;
    return preg_replace('/\W{1,}/i', "_", strtoupper($label));
  }
}

?>
