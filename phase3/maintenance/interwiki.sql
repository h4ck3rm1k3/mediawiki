-- Based more or less on the public interwiki map from MeatballWiki
-- Default interwiki prefixes...

REPLACE INTO /*$wgDBprefix*/interwiki (iw_prefix,iw_url,iw_local) VALUES
('acronym','http://www.acronymfinder.com/af-query.asp?String=exact&Acronym=$1',0),
('advogato','http://www.advogato.org/$1',0),
('annotationwiki','http://www.seedwiki.com/page.cfm?wikiid=368&doc=$1',0),
('arxiv','http://www.arxiv.org/abs/$1',0),
('c2find','http://c2.com/cgi/wiki?FindPage&value=$1',0),
('cache','http://www.google.com/search?q=cache:$1',0),
('commons','http://commons.wikimedia.org/wiki/$1',0),
('corpknowpedia','http://corpknowpedia.org/wiki/index.php/$1',0),
('dictionary','http://www.dict.org/bin/Dict?Database=*&Form=Dict1&Strategy=*&Query=$1',0),
('disinfopedia','http://www.disinfopedia.org/wiki.phtml?title=$1',0),
('docbook','http://wiki.docbook.org/topic/$1',0),
('doi','http://dx.doi.org/$1',0),
('drumcorpswiki','http://www.drumcorpswiki.com/index.php/$1',0),
('dwjwiki','http://www.suberic.net/cgi-bin/dwj/wiki.cgi?$1',0),
('emacswiki','http://www.emacswiki.org/cgi-bin/wiki.pl?$1',0),
('elibre','http://enciclopedia.us.es/index.php/$1',0),
('foldoc','http://foldoc.org/?$1',0),
('foxwiki','http://fox.wikis.com/wc.dll?Wiki~$1',0),
('freebsdman','http://www.FreeBSD.org/cgi/man.cgi?apropos=1&query=$1',0),
('gej','http://www.esperanto.de/cgi-bin/aktivikio/wiki.pl?$1',0),
('gentoo-wiki','http://gentoo-wiki.com/$1',0),
('google','http://www.google.com/search?q=$1',0),
('googlegroups','http://groups.google.com/groups?q=$1',0),
('hammondwiki','http://www.dairiki.org/HammondWiki/$1',0),
('hewikisource','http://he.wikisource.org/wiki/$1',1),
('hrwiki','http://www.hrwiki.org/index.php/$1',0),
('imdb','http://us.imdb.com/Title?$1',0),
('jargonfile','http://sunir.org/apps/meta.pl?wiki=JargonFile&redirect=$1',0),
('jspwiki','http://www.jspwiki.org/wiki/$1',0),
('keiki','http://kei.ki/en/$1',0),
('kmwiki','http://kmwiki.wikispaces.com/$1',0),
('linuxwiki','http://linuxwiki.de/$1',0),
('lojban','http://www.lojban.org/tiki/tiki-index.php?page=$1',0),
('lqwiki','http://wiki.linuxquestions.org/wiki/$1',0),
('lugkr','http://lug-kr.sourceforge.net/cgi-bin/lugwiki.pl?$1',0),
('mathsongswiki','http://SeedWiki.com/page.cfm?wikiid=237&doc=$1',0),
('meatball','http://www.usemod.com/cgi-bin/mb.pl?$1',0),
('mediazilla','http://bugzilla.wikipedia.org/$1',1),
('mediawikiwiki','http://www.mediawiki.org/wiki/$1',0),
('memoryalpha','http://www.memory-alpha.org/en/index.php/$1',0),
('metawiki','http://sunir.org/apps/meta.pl?$1',0),
('metawikipedia','http://meta.wikimedia.org/wiki/$1',0),
('moinmoin','http://purl.net/wiki/moin/$1',0),
('mozillawiki','http://wiki.mozilla.org/index.php/$1',0),
('oeis','http://www.research.att.com/cgi-bin/access.cgi/as/njas/sequences/eisA.cgi?Anum=$1',0),
('openfacts','http://openfacts.berlios.de/index.phtml?title=$1',0),
('openwiki','http://openwiki.com/?$1',0),
('patwiki','http://gauss.ffii.org/$1',0), # 2008-02-27: lots of spambots
('pmeg','http://www.bertilow.com/pmeg/$1.php',0),
('ppr','http://c2.com/cgi/wiki?$1',0),
('pythoninfo','http://wiki.python.org/moin/$1',0),
('rfc','http://www.rfc-editor.org/rfc/rfc$1.txt',0),
('s23wiki','http://is-root.de/wiki/index.php/$1',0),
('seattlewiki','http://seattle.wikia.com/wiki/$1',0),
('seattlewireless','http://seattlewireless.net/?$1',0),
('senseislibrary','http://senseis.xmp.net/?$1',0),
('slashdot','http://slashdot.org/article.pl?sid=$1',0), # 2008-02-27: update me
('sourceforge','http://sourceforge.net/$1',0),
('squeak','http://wiki.squeak.org/squeak/$1',0),
('susning','http://www.susning.nu/$1',0),
('svgwiki','http://wiki.svg.org/$1',0),
('tavi','http://tavi.sourceforge.net/$1',0),
('tejo','http://www.tejo.org/vikio/$1',0),
('tmbw','http://www.tmbw.net/wiki/$1',0),
('tmnet','http://www.technomanifestos.net/?$1',0),
('tmwiki','http://www.EasyTopicMaps.com/?page=$1',0),
('theopedia','http://www.theopedia.com/$1',0),
('twiki','http://twiki.org/cgi-bin/view/$1',0),
('uea','http://www.tejo.org/uea/$1',0),
('unreal','http://wiki.beyondunreal.com/wiki/$1',0),
('usemod','http://www.usemod.com/cgi-bin/wiki.pl?$1',0),
('vinismo','http://vinismo.com/en/$1',0),
('webseitzwiki','http://webseitz.fluxent.com/wiki/$1',0),
('why','http://clublet.com/c/c/why?$1',0),
('wiki','http://c2.com/cgi/wiki?$1',0),
('wikia','http://www.wikia.com/wiki/$1',0),
('wikibooks','http://en.wikibooks.org/wiki/$1',1),
('wikicities','http://www.wikia.com/wiki/$1',0),
('wikif1','http://www.wikif1.org/$1',0),
('wikihow','http://www.wikihow.com/$1',0),
('wikinfo','http://www.wikinfo.org/index.php/$1',0),
('wikimedia','http://wikimediafoundation.org/wiki/$1',0),
('wikinews','http://en.wikinews.org/wiki/$1',1),
('wikiquote','http://en.wikiquote.org/wiki/$1',1),
('wikipedia', 'http://en.wikipedia.org/wiki/$1', 1),
('wikisource','http://wikisource.org/wiki/$1',1),
('wikispecies','http://species.wikimedia.org/wiki/$1',1),
('wikitravel','http://wikitravel.org/en/$1',0),
('wikiversity','http://en.wikiversity.org/wiki/$1',1),
('wikt','http://en.wiktionary.org/wiki/$1',1),
('wiktionary','http://en.wiktionary.org/wiki/$1',1),
('wlug','http://www.wlug.org.nz/$1',0),
('zwiki','http://zwiki.org/$1',0),
('zzz wiki','http://wiki.zzz.ee/index.php/$1',0);
