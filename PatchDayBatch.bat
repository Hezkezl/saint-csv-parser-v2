@echo off
php bin/console app:parse:csv GE:Achievement
php bin/console app:parse:csv GE:Actions
php bin/console app:parse:csv GE:Collectable
php bin/console app:parse:csv GE:GENPCEquipment
php bin/console app:parse:csv GE:Items
php bin/console app:parse:csv GE:Leves
php bin/console app:parse:csv GE:Minions
php bin/console app:parse:csv GE:Mounts
php bin/console app:parse:csv GE:Quests
php bin/console app:parse:csv GE:Satisfaction
php bin/console app:parse:csv GE:SpecialShop
php bin/console app:parse:csv GE:TripleTriad
Echo All files finished and up to current date defined in gamever.
setlocal
:PROMPT
SET /P AREYOUSURE=Would you like to run WinMerge on this? (Y/[N])?
IF /I "%AREYOUSURE%" NEQ "Y" GOTO END

cd "PATH TO FOLDER CONTAINING WINMERGE.EXE"

rem old -  new
set /p "a=old patch number: "
set /p "b=new patch number: "
WinMergeU /r "YOURPATH\saint-csv-parser-v2-master\output\%a%\" "YOURPATH\saint-csv-parser-v2-master\output\%b%"

:END
endlocal
pause
