<?php

namespace App\Parsers\GE;

class GeFormatter
{
    public static function format($format, $data)
    {
        // sort keys by their length so long ones are formatted before smaller ones
        // this prevents keys such as "Job" affecting "ClassJobLevel"
        $keys = array_map('strlen', array_keys($data));
        array_multisort($keys, SORT_DESC, $data);

        // set format
        $format = str_ireplace(array_keys($data), $data, $format);
        $format = str_ireplace('        |','|', $format);
        $format = str_ireplace("        \n","\n", $format);
        //$format = str_ireplace('    |', '|', $format);
        $format = str_ireplace('        }}','}}', $format);
        $format = str_ireplace('        {{','{{', $format);
        $format = preg_replace("/\n\n\n+/", "\n\n", $format);
        $format = preg_replace("/(QuestReward.*)\n\n(?!\\|Issuing NPC)/", "$1\n", $format);
        $format = preg_replace("/(QuestReward.*)\n(\\|Issuing NPC.*)/", "$1\n\n$2", $format);
        $format = preg_replace("/\s+|\s+/", null, $format);
        $format = preg_replace("/<Emphasis>|<\\/Emphasis>/", "''", $format);
        $format = preg_replace("/<If\\(LessThan\\(PlayerParameter\\(11\\),12\\)\\)><If\\(LessThan\\(PlayerParameter\\(11\\),4\\)\\)>([^>]+)<Else\\/>([^>]+)<\\/If><Else\\/><If\\(LessThan\\(PlayerParameter\\(11\\),17\\)\\)>([^>]+)<Else\\/>([^>]+)<\\/If><\\/If>/", "{{Loremtextconditional|$1|or '$2' or '$3', depending on the time of day.}}", $format);
        $format = preg_replace("/{{Loremquote\\|(?:System|A\d+)\\|link=y\\|(.*)}}/", "<div>'''$1'''</div>", $format);
        $format = preg_replace("/<Color\\(-3917469\\)>(.*)<\\/Color>/", "{{Loremascianspeak|$1}}", $format);
        $format = preg_replace("/<If\\(PlayerParameter\\(4\\)\\)>([\w\s']+)<Else\\/>([\w\s']+)<\\/If>/", "{{Loremtextmale|$2|$1}}", $format);
        $format = preg_replace("/<Color\\(-34022\\)>([\w\s,.\\/<>&'-]+)<\\/Color>/", "{{Color|Orange|$1}}", $format);
        $format = str_replace("(-???-)", null, $format);
        $format = preg_replace("/{{Loremquote\\|.*\\|link=y\\|\\(-(.*)-\\)/", "{{Loremquote|$1|link=y|", $format);
        $format = str_replace("<If(GreaterThan(PlayerParameter(52),0))><Clickable(<If(GreaterThan(PlayerParameter(52),0))><Sheet(GCRankLimsaMaleText,PlayerParameter(52),8)/><Else/></If><If(GreaterThan(PlayerParameter(53),0))><Sheet(GCRankGridaniaMaleText,PlayerParameter(53),8)/><Else/></If><If(GreaterThan(PlayerParameter(54),0))><Sheet(GCRankUldahMaleText,PlayerParameter(54),8)/><Else/></If>)/> <Split(<Highlight>ObjectParameter(1)</Highlight>, ,2)/><Else/><If(GreaterThan(PlayerParameter(53),0))><Split(<Highlight>ObjectParameter(1)</Highlight>, ,1)/><Else/><Split(<Highlight>ObjectParameter(1)</Highlight>, ,1)/></If></If>", "{{Loremtextconditional|<GC Rank/Surname>|The player's Grand Company Rank. If not in a GC, then their last name}}", $format);
        $format = preg_replace("/<If\(GreaterThan\(PlayerParameter\(52\),0\)\)>([^<]+)<Clickable\(<If\(GreaterThan\(PlayerParameter\(52\),0\)\)><Sheet\(GCRankLimsaMaleText,PlayerParameter\(52\),8\)\/><Else\/><\/If><If\(GreaterThan\(PlayerParameter\(53\),0\)\)><Sheet\(GCRankGridaniaMaleText,PlayerParameter\(53\),8\)\/><Else\/><\/If><If\(GreaterThan\(PlayerParameter\(54\),0\)\)><Sheet\(GCRankUldahMaleText,PlayerParameter\(54\),8\)\/><Else\/><\/If>\)\/> <Split\(<Highlight>ObjectParameter\(1\)<\/Highlight>, ,2\)\/><Else\/><If\(GreaterThan\(PlayerParameter\(53\),0\)\)>([^<]+)<Split\(<Highlight>ObjectParameter\(1\)<\/Highlight>, ,1\)\/><Else\/>[^<]+<Split\(<Highlight>ObjectParameter\(1\)<\/Highlight>, ,1\)\/><\/If><\/If>/", "{{Loremtextconditional|$1|If player is in a Grand Company. Otherwise, this will say \"$2\"", $format);
        $format = str_replace("<Sheet(GCRankLimsaMaleText,PlayerParameter(52),8)/><Else/></If><If(GreaterThan(PlayerParameter(53),0))><Sheet(GCRankGridaniaMaleText,PlayerParameter(53),8)/><Else/></If><If(GreaterThan(PlayerParameter(54),0))><Sheet(GCRankUldahMaleText,PlayerParameter(54),8)/><Else/></If>", "{{Loremtextconditional|<Player's Grand Company Rank>|Player's GC Rank is shown here}}", $format);
        $format = str_replace("<Split(<Highlight>ObjectParameter(1)</Highlight>, ,1)/>", "{{Loremforename}}", $format);
        $format = str_replace("<Split(<Highlight>ObjectParameter(1)</Highlight>, ,2)/>", "{{Loremsurname}}", $format);
        $format = str_replace("<Highlight>ObjectParameter(1)</Highlight>", "{{Loremforename}} {{Loremsurname}}", $format);
        $format = str_replace("<Sheet(Addon,9,0)/>", "{{HQ|2}}", $format);
        $format = preg_replace("/\\*<If\\(LessThan\\(IntegerParameter\\(\d+\\),IntegerParameter\\(\d+\\)\\)\\)>([^<]+)<Else\\/>([^<]+)<\\/If>\\./", "*$1.\n*$2.", $format);
        //Same as above, except without the \\. which was used to match a period after the </If>
        $format = preg_replace("/\\*<If\\(LessThan\\(IntegerParameter\\(\d+\\),IntegerParameter\\(\d+\\)\\)\\)>([^<]+)<Else\\/>([^<]+)<\\/If>/", "*$1\n*$2", $format);
        //below string replacement is for adding "an" before Armorer, Alchemist, Archer, Arcanist, Astrologian, or "a" before the other Job names (due to the vowel at the beginning of the name. Silly English language...)
        $format = str_replace("<If(Equal(PlayerParameter(68),10))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),14))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),5))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),26))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/><If(Equal(PlayerParameter(68),33))>an <Sheet(ClassJob,PlayerParameter(68),0)/><Else/>a <Sheet(ClassJob,PlayerParameter(68),0)/></If></If></If></If></If>","{{Loremtextconditional|an Armorer|or 'an Alchemist/Archer/Arcanist/Astrologian', or 'a JobName' (depending on your current job)}}", $format);

        return trim($format) . "\n\n";
    }
}
