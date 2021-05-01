@echo off
php bin/console app:parse:csv GE:SmallIconConverter
php bin/console app:parse:csv GE:Achievement
php bin/console app:parse:csv GE:Actions
php bin/console app:parse:csv GE:AquariumFish
php bin/console app:parse:csv GE:Collectable
php bin/console app:parse:csv GE:FishParameter
php bin/console app:parse:csv GE:Instances
php bin/console app:parse:csv GE:Items
php bin/console app:parse:csv GE:KeyItems
php bin/console app:parse:csv GE:Leves
php bin/console app:parse:csv GE:Minions
php bin/console app:parse:csv GE:Mounts
php bin/console app:parse:csv GE:NpcShops
php bin/console app:parse:csv GE:Quests
php bin/console app:parse:csv GE:RaceAbility
php bin/console app:parse:csv GE:Recipes
php bin/console app:parse:csv GE:Satisfaction
php bin/console app:parse:csv GE:Sightseeing
php bin/console app:parse:csv GE:Spearfish
php bin/console app:parse:csv GE:SpecialShop
php bin/console app:parse:csv GE:Storable
php bin/console app:parse:csv GE:TripleTriad
php bin/console app:parse:csv GE:Weather
Echo All files finished and up to current date defined in gamever.
setlocal
:PROMPT
SET /P AREYOUSURE=Would you like to run WinMerge on this? (Y/[N])?
IF /I "%AREYOUSURE%" NEQ "Y" GOTO END

cd "C:\Program Files (x86)\WinMerge"

rem old -  new
set /p "a=old patch number: "
set /p "b=new patch number: "
WinMergeU /r "F:\GitHub\saint-csv-parser-v2-master\output\%a%\" "F:\GitHub\saint-csv-parser-v2-master\output\%b%"

:END
endlocal
pause
