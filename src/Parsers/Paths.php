<?php
$ini = parse_ini_file('config.ini');
$MainPath = $ini['MainPath'];
$PatchID = file_get_contents("". $MainPath ."\game\\ffxivgame.ver");
$cache = "{$ini['Cache']}/$PatchID/rawexd";
$Patch = $ini['Patch'];