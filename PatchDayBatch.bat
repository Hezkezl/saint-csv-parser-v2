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
pause
