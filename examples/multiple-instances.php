<?php
/*
For default instance:
CODETAGS_INCLUDED_TAGS=new-version \
  php examples/multiple-instances.php
CODETAGS_INCLUDED_TAGS=new-version,foo \
  php examples/multiple-instances.php
CODETAGS_INCLUDED_TAGS=new-version,bar \
  php examples/multiple-instances.php
CODETAGS_INCLUDED_TAGS=new-version,bar,foo \
  php examples/multiple-instances.php

For default, oldflow, newflow:

CODETAGS_INCLUDED_TAGS=bar,foo \
OLDFLOW_INCLUDED_TAGS=google-map \
NEWFLOW_INCLUDED_TAGS=couchdb \
  php examples/multiple-instances.php
*/
$loader = require __DIR__ . '/../vendor/autoload.php';

use Tourane\Codetags\TagManager;

$default = TagManager::instance();

$oldFlow = TagManager::getInstance("oldflow");
$newFlow = TagManager::getInstance("current", array(
  "namespace" => "newflow"
));

if ($default->isActive("new-version")) {
  echo sprintf("%s is activated\n", "new-version");
}

if ($default->isActive(["foo", "bar"])) {
  echo sprintf("Both %s are activated\n", implode(",", ["foo", "bar"]));
}

if ($newFlow->isActive("couchdb", "mongodb")) {
  echo sprintf("One of %s is activated\n", implode(",", ["couchdb", "mongodb"]));
}

if ($newFlow->isActive(["couchdb", "mongodb"])) {
  echo sprintf("All of %s are activated\n", implode(",", ["couchdb", "mongodb"]));
}
?>
