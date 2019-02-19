<?php
/*
CODETAGS_INCLUDED_TAGS=new-version \
  php examples/default-instance.php
CODETAGS_INCLUDED_TAGS=new-version,foo \
  php examples/default-instance.php
CODETAGS_INCLUDED_TAGS=new-version,bar \
  php examples/default-instance.php
CODETAGS_INCLUDED_TAGS=new-version,bar,foo \
  php examples/default-instance.php
*/
$loader = require __DIR__ . '/../vendor/autoload.php';

use Tourane\Codetags\TagManager;

$default = TagManager::instance();

if ($default->isActive("new-version")) {
  echo sprintf("%s is activated\n", "new-version");
}

if ($default->isActive(["foo", "bar"])) {
  echo sprintf("Both %s are activated\n", implode(",", ["foo", "bar"]));
}
?>
