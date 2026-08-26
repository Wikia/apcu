--TEST--
Test default expunge logic wrt global and per-entry TTLs
--SKIPIF--
<?php
require_once(__DIR__ . '/skipif.inc');
if (!function_exists('apcu_inc_request_time')) die('skip APC debug build required');
?>
--INI--
apc.enabled=1
apc.enable_cli=1
apc.use_request_time=1
apc.ttl=1
apc.shm_size=1M
--FILE--
<?php

apcu_store("no_ttl_unaccessed", str_repeat('x', 500));
apcu_store("no_ttl_accessed", 24);
apcu_store("ttl", 42, 3);

// Fill the cache without triggering an expunge.
$entry_size = apcu_sma_info(true)['avail_mem'];
apcu_store(sprintf("key%06d", 0), str_repeat('x', 500));
$entry_size -= apcu_sma_info(true)['avail_mem'];
$i = 1;
while (apcu_sma_info(true)['avail_mem'] >= $entry_size) {
    apcu_store(sprintf("key%06d", $i), str_repeat('x', 500));
    $i++;
}

apcu_inc_request_time(1);
apcu_fetch("no_ttl_accessed");

apcu_inc_request_time(1);

// Trigger a default expunge after the entries have soft-expired.
apcu_store("large_entry", str_repeat('x', 1000));

var_dump(apcu_fetch("no_ttl_unaccessed"));
var_dump(apcu_fetch("no_ttl_accessed"));
var_dump(apcu_fetch("ttl"));

?>
--EXPECT--
bool(false)
int(24)
bool(false)
