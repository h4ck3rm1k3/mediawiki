<?php
/** Czech (Česky)
 * @author Li-sung
 * @author SPQRobin
 */
$messages = array(
	'group-editor'                => 'Editoři',
	'reviewer'                    => 'Posuzovatel',
	'group-reviewer'              => 'Posuzovatelé',
	'group-reviewer-member'       => 'Posuzovatel',
	'revreview-current'           => 'Návrh',
	'tooltip-ca-current'          => 'Zobrazit nejnovější návrh této stránky',
	'revreview-edit'              => 'Editovat návrh',
	'revreview-source'            => 'zdroj návrhu',
	'revreview-stable'            => 'Stabilní',
	'tooltip-ca-stable'           => 'Zobrazit stabilní verzi této stránky',
	'revreview-oldrating'         => 'Bylo ohodnoceno:',
	'revreview-noflagged'         => 'Tato stránka nemá žádné posouzené verze, takže dosud nebyla [[{{MediaWiki:Validationpage}}|zkontrolována]] kvalita.',
	'stabilization-tab'           => 'stabilizace',
	'tooltip-ca-default'          => 'Nastavení stabilní a zobrazované verze',
	'revreview-quick-none'        => "'''Nejnovější verze'''. Žádné posouzené verze.",
	'revreview-quick-see-quality' => "'''Nejnovější verze'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Vizte stabilní verzi]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změny|změn}}])",
	'revreview-quick-see-basic'   => "'''Nejnovější verze'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Vizte stabilní verzi]]
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změny|změn}}])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Prohlédnuto]]'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Vizte nejnovější verzi]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změny|změn}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Kvalitní]]'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Vizte nejnovější verzi]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změny|změn}}])",
	'revreview-newest-basic'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Poslední prohlédnutá verze] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} seznam všech]) byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] {{plural:$3|potřebuje|potřebují|potřebuje}} posoudit.',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Poslední kvalitní verze] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} seznam všech]) byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] {{plural:$3|potřebuje|potřebují|potřebuje}} posoudit.',
	'revreview-basic'             => 'Toto je poslední [[{{MediaWiki:Validationpage}}|prohlédnutá]] verze. Byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Nejnovější verzi] lze [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} upravit]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] {{plural:$3|čeká|čekají|čeká}} na posouzení.',
	'revreview-quality'           => 'Toto je poslední [[{{MediaWiki:Validationpage}}|kvalitní]] verze. Byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Nejnovější verzi] lze [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} upravit]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] {{plural:$3|čeká|čekají|čeká}} na posouzení.',
	'revreview-static'            => "Toto je [[{{MediaWiki:Validationpage}}|posouzená]] verze '''[[:$3|této stránky]]''' [{{fullurl:Special:Log/review|page=$1}} schválená] <i>$2</i>. [{{fullurl:$3|stable=0}} Nejnovější verzi] můžete [{{fullurl:$3|action=edit}} změnit].",
	'revreview-note'              => 'Uživatel [[User:$1|$1]] doplnil své [[{{MediaWiki:Validationpage}}|posouzení]] této verze následující poznámkou:',
	'revreview-update'            => 'Posuďte všechny změny na této stránce vůči stabilní verzi. Šablony a obrázky se také mohly změnit.',
	'revreview-update-none'       => 'Posuďte všechny změny (zobrazené níže) provedené od stabilní verze.',
	'revreview-auto'              => '(automaticky)',
	'revreview-auto-w'            => "Editujete stabilní verzi, změny budou '''automaticky označeny jako posouzené'''. Měli byste zkontrolovat náhled stránky.",
	'revreview-auto-w-old'        => "Editujete starou verzi, změny budou '''automaticky označeny jako posouzené'''. Měli byste zkontrolovat náhled stránky.",
	'hist-stable'                 => '[prohlédnutá]',
	'hist-quality'                => '[kvalitní]',
	'flaggedrevs'                 => 'Označování verzí',
	'review-logpage'              => 'Kniha posuzování článků',
	'review-logpagetext'          => 'Tato kniha zobrazuje změny [[{{MediaWiki:Validationpage}}|schválení]] verzí stránek.',
	'review-logentry-app'         => 'posuzuje stránku $1',
	'review-logentry-dis'         => 'odmítá verzi stránky $1',
	'review-logaction'            => 'identifikace verze $1',
	'stable-logpage'              => 'Kniha stabilních verzí',
	'stable-logentry'             => 'nastavuje výběr stabilní verze stránky [[$1]]',
	'revisionreview'              => 'Posouzení verzí',
	'revreview-main'              => 'Musíte vybrat určitou verzi stránky, aby jste ji mohli posoudit. Vizte [[Special:Unreviewedpages|seznam neposouzených stránek]].',
	'revreview-selected'          => "Vybrané verze stránky '''$1:'''",
	'revreview-text'              => 'Stabilní verze je nastavena jako výchozí zobrazený obsah před nejnovější verzí.',
	'revreview-toolow'            => 'Aby byla verze označena jako posouzená, musíte označit každou vlastnost lepším hodnocením než "neschváleno". Pokud chcete verzi odmítnout nechte ve všech polích hodnocení "neschváleno".',
	'revreview-flag'              => 'Posoudit tuto verzi (#$1)',
	'revreview-legend'            => 'Ohodnoťte obsah verze',
	'revreview-notes'             => 'Poznámky k zobrazení:',
	'revreview-accuracy'          => 'Přesnost',
	'revreview-accuracy-0'        => 'Neschváleno',
	'revreview-accuracy-1'        => 'Prohlédnuto',
	'revreview-accuracy-2'        => 'Přesná',
	'revreview-accuracy-3'        => 'Dobře ozdrojovaná',
	'revreview-accuracy-4'        => 'Význačná',
	'revreview-depth'             => 'Hloubka',
	'revreview-depth-0'           => 'Neschváleno',
	'revreview-depth-1'           => 'Základní',
	'revreview-depth-2'           => 'Mírná',
	'revreview-depth-3'           => 'Vysoká',
	'revreview-depth-4'           => 'Význačná',
	'revreview-style'             => 'Čitelnost',
	'revreview-style-0'           => 'Neschváleno',
	'revreview-style-1'           => 'Přijatelná',
	'revreview-style-2'           => 'Dobrá',
	'revreview-style-3'           => 'Výstižná',
	'revreview-style-4'           => 'Význačná',
	'revreview-log'               => 'Komentář:',
	'revreview-submit'            => 'Odeslat posouzení',
	'revreview-changed'           => "'''Požadovanou akci nelze na této verzi provést.''' Šablona nebo obrázek byly vyžádány na neurčitou verzi. To se může stát pokud dynamická šablona vkládá jinou šablonu nebo obrázek v závislosti na proměnné, která se změnila zatímco jste posuzovali stránku. Obnovte stránku a znovu ji posuďte.",
	'stableversions'              => 'Stabilní verze',
	'stableversions-leg1'         => 'Přehled posouzených verzí stránky',
	'stableversions-page'         => 'Jméno stránky',
	'stableversions-none'         => '[[:$1]] nemá žádné posouzené verze.',
	'stableversions-list'         => 'Toto je seznam verzí stránky [[:$1]], které byly posouzeny:',
	'stableversions-review'       => 'Posouzeno <i>$1</i>',
	'review-diff2stable'          => 'Rozdíl oproti poslední stabilní verzi',
	'review-diff2oldest'          => 'Rozdíl oproti nejstarší verzi',
	'unreviewedpages'             => 'Neposouzené stránky',
	'viewunreviewed'              => 'Seznam neposouzených stránek',
	'unreviewed-outdated'         => 'Zobrazit stránky, které mají neposouzené verze do stabilní verze.',
	'unreviewed-category'         => 'Kategorie:',
	'unreviewed-diff'             => 'Změny',
	'unreviewed-list'             => 'Tato stránka obsahuje články, které nebyly posouzeny nebo mají nové, neposouzené, verze.',
	'revreview-visibility'        => 'Tato stránka má [[{{MediaWiki:Validationpage}}|stabilní verzi]], kterou lze [{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} nastavit].',
	'stabilization'               => 'Stabilizace stránky',
	'stabilization-text'          => 'Změňte nastavení, jak se vybírá stabilní verze stránky [[:$1|$1]] a co se zobrazí.',
	'stabilization-perm'          => 'Tento účet nemá povoleno měnit nastavení stabilní verze. Níže je současné nastavení stránky [[:$1|$1]]:',
	'stabilization-page'          => 'Jméno stránky:',
	'stabilization-leg'           => 'Nastavení stabilní verze stránky',
	'stabilization-select'        => 'Jako stabilní verze je vybrána',
	'stabilization-select1'       => 'Poslední kvalitní verze; pokud není k dispozici pak poslední prohlédnutá',
	'stabilization-select2'       => 'Poslední posouzená verze',
	'stabilization-def'           => 'Verze zobrazená jako výchozí',
	'stabilization-def1'          => 'Stabilní verze',
	'stabilization-def2'          => 'Současná verze',
	'stabilization-submit'        => 'Potvrdit',
	'stabilization-notexists'     => 'Neexistuje stránka "[[:$1|$1]]". Nastavení není možné.',
	'stabilization-notcontent'    => 'Stránka „[[:$1|$1]]“ nemůže být posouzena. Nastavení není možné.',
	'stabilization-success'       => 'Nastavení stabilní verze stránky [[:$1|$1]] bylo provedeno.',
	'stabilization-sel-short'     => 'Váha',
	'stabilization-sel-short-0'   => 'kvalitní',
	'stabilization-sel-short-1'   => 'žádná',
	'stabilization-def-short'     => 'výchozí',
	'stabilization-def-short-0'   => 'současná',
	'stabilization-def-short-1'   => 'stabilní',
	'reviewedpages'               => 'Posouzené stránky',
	'reviewedpages-leg'           => 'Seznam stránek posouzených s určitým celkovým hodnocením',
	'reviewedpages-list'          => 'Následující stránky byly posouzeny na určenou úroveň',
	'reviewedpages-none'          => 'Žádná stránka neodpovídá',
	'reviewedpages-lev-0'         => 'prohlédnuté',
	'reviewedpages-lev-1'         => 'kvalitní',
	'reviewedpages-lev-2'         => 'význačné',
	'reviewedpages-all'           => 'posouzené verze',
	'reviewedpages-best'          => 'verze s nejvyšším ohodnocením',

);
