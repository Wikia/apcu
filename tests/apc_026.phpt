--TEST--
apcu_inc/dec() should not inc/dec soft expired entries based on global TTL setting
--SKIPIF--
<?php
require_once(__DIR__ . '/skipif.inc');
if (!function_exists('apcu_inc_request_time')) die('skip APC debug build required');
?>
--INI--
apc.enabled=1
apc.enable_cli=1
apc.use_request_time=1
apc.ttl=2
--FILE--
<?php

/* Keys chosen to collide */
apcu_store("EzEz", 0);
apcu_store("EzFY", 0, 100);
apcu_store("FYEz", "xxx");

echo "T+0:\n";
apcu_store("FYEz", "xxx");
var_dump(apcu_inc("EzEz"));
var_dump(apcu_fetch("EzEz"));
var_dump(apcu_dec("EzFY"));
var_dump(apcu_fetch("EzFY"));

echo "T+1:\n";
apcu_inc_request_time(1);
apcu_store("FYEz", "xxx");
var_dump(apcu_inc("EzEz"));
var_dump(apcu_fetch("EzEz"));
var_dump(apcu_dec("EzFY"));
var_dump(apcu_fetch("EzFY"));

echo "T+4:\n";
apcu_inc_request_time(3);
apcu_store("FYEz", "xxx");
var_dump(apcu_inc("EzEz"));
var_dump(apcu_fetch("EzEz"));
var_dump(apcu_dec("EzFY"));
var_dump(apcu_fetch("EzFY"));

?>
--EXPECT--
T+0:
int(1)
int(1)
int(-1)
int(-1)
T+1:
int(2)
int(2)
int(-2)
int(-2)
T+4:
int(1)
int(1)
int(-1)
int(-1)
