<?php

namespace App\Parsers\GE;

use App\Parsers\CsvParseTrait;
use App\Parsers\ParseInterface;

/**
 * php bin/console app:parse:csv GE:Storable
 */
class Storable implements ParseInterface
{
    use CsvParseTrait;

    // the wiki output format / template we shall use
    const WIKI_FORMAT = "{{-start-}}
    '''Storable'''
    {| class=\"itembox shadowed\" style=\"color:white; width:100%; cellpadding=0; cellspacing=1;\" border={{{border|0}}}
|-
|[[File:Armoire Icon.png|left|link=]]<onlyinclude>A private [[Armoire|armoire]] capable of holding some untradable items.  Though use of the armoire is free, the following restrictions apply:

: Only items in 100% condition can be stored.
: Multiple items of the same name cannot be stored.
: Stored items will have their [[spiritbond]] values reset to 0.
: [[Hermes' Shoes]] cannot be stored for a fixed period (approx. one day, Earth time) following use.

The same storage is accessible from the armoires in all four city inns. For example, an item stored using the armoire at the [[Mizzenmast]] in [[Limsa Lominsa]] can be retrieved using the armoire at the Roost in [[Gridania]].  Artifact armor for Jobs, Seasonal Event items, Achievement rewards, and \"Exclusive Extras\" such as Veteran Rewards are the primary types of items that can be stored.</onlyinclude> 
|}
{{Refreshlink}}
{{#tag:tabber|
Regular Items=
<tabber>
Weapons=
{{#dpl:
| uses=Template:ARR Infobox Item
|  category = Storable
|  category = Weapon
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-1a
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Tools=
{{#dpl:
| uses=Template:ARR Infobox Item
|  category = Storable
|  category = Tool
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-2a
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Shields=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Shield
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-3a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Head=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Head
|  notcategory = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-4a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Body=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Body
|  notcategory = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-5a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Hands=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Hands
|  notcategory = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-6a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Legs=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Legs
|  notcategory = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-7a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Feet=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Feet
|  notcategory = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-8a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Accessories=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Bracelets{{!}}Earrings{{!}}Ring{{!}}Necklace
|  notcategory = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-9a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
</tabber>
{{!}}-{{!}}
Glamour Items=
<tabber>
Glamour Head=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Head
|  category = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-10a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Glamour Body=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Body
|  category = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-11a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Glamour Hands=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Hands
|  category = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-12a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Glamour Legs=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Legs
|  category = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-13b
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Glamour Feet=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Feet
|  category = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-14a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
|-|
Glamour Accessories=
{{#dpl:
|  uses=Template:ARR Infobox Item
|  category = Storable
|  category = Bracelets{{!}}Earrings{{!}}Ring{{!}}Necklace
|  category = ILevel 0-9
|  dplcache=storabledpl-{{{1|{{PAGENAME}}}}}-15a
|  namespace     = 
|  includepage =  {ARR Infobox Item}:Name:Name:Item Level
|  format      =,,\\n
|  table        = class=\"GEtable sortable\" width=100%,-,width=\"10%\" class=\"unsortable\"{{!}},width=\"65%\" {{!}}Name,width=\"25%\" {{!}}Item Level
|  tablerow     = [[File:%%_Icon.png|40px]],[[%%]],%%
}}
</tabber>
}}
{{Refreshlink}}

{{{!}} 
|- style=\"display:none;\"
|Name
{output}

|}
{{-stop-}}";

    public function parse()
    {
        include (dirname(__DIR__) . '/Paths.php');

        // grab CSV files we want to use
        $CabinetCsv = $this->csv('Cabinet');
        $ItemCsv = $this->csv('Item');
        // (optional) start a progress bar
        $this->io->progressStart($CabinetCsv->total);

        // loop through data
        $outputarray = [];
        foreach ($CabinetCsv->data as $id => $CabinetData) {
            if (empty($ItemCsv->at($CabinetData['Item'])['Name'])) continue;
            $outputarray[] = "{{Storable|". $ItemCsv->at($CabinetData['Item'])['Name'] ."}}";
        }
        $output = implode("\n", $outputarray);
        // Save some data
        $data = [
            '{output}' => $output,
        ];

        // format using Gamer Escape formatter and add to data array
        // need to look into using item-specific regex, if required.
        $this->data[] = GeFormatter::format(self::WIKI_FORMAT, $data);
        

        // save our data to the filename: GeEventItemWiki.txt
        $this->io->progressFinish();
        $this->io->text('Saving ...');
        $info = $this->save("Storable.txt", 999999);

        $this->io->table(
            [ 'Filename', 'Data Count', 'File Size' ],
            $info
        );
    }
}