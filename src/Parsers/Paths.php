<?php

//Change to the default path of your game installation
$MainPath = "C:\Program Files (x86)\SquareEnix\FINAL FANTASY XIV - A Realm Reborn";
//Change to current patch number
//$Patch should now be global
$Patch = "5.41";

/**
 To Use:
    This needs to be placed the line above "// grab CSV files we want to use"
        include (dirname(__DIR__) . '/Paths.php');

    Path names need to be as such now :
        $ItemCsv = $this->csv("$CurrentPatch/Item");

    Custom files can be placed in just cache/ folder and called as such:
        $LevemetePatchCsv = $this->csv("LevemetePatch");

    Output Path name needs to be as such :
        $info = $this->save("$CurrentPatchOutput/GeLeveWiki - ". $Patch .".txt", 9999999);

    If you prefer to use short patch for output then use :
        $info = $this->save("$CurrentShortPatchOutput/GeLeveWiki - ". $patch .".txt", 9999999);
*/

//get the current patch long ID
$PatchID = file_get_contents("". $MainPath ."\game\\ffxivgame.ver");
$CurrentPatch = "$PatchID/rawexd";
$CurrentPatchOutput = "$PatchID";
$CurrentShortPatchOutput = "$Patch";

if (!file_exists("output/$PatchID/")) {
    mkdir("output/$PatchID/", 0777, true);
}
