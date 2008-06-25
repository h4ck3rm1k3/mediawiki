<?php
/** Assamese (অসমীয়া)
 *
 * @ingroup Language
 * @file
 *
 * @author Priyankoo
 * @author Siebrand
 */

$fallback='hi';

$digitTransformTable = array(
	'0' => '০', # &#x09e6;
	'1' => '১', # &#x09e7;
	'2' => '২', # &#x09e8;
	'3' => '৩', # &#x09e9;
	'4' => '৪', # &#x09ea;
	'5' => '৫', # &#x09eb;
	'6' => '৬', # &#x09ec;
	'7' => '৭', # &#x09ed;
	'8' => '৮', # &#x09ee;
	'9' => '৯', # &#x09ef;
);

$messages = array(
# User preference toggles
'tog-underline'               => 'সংযোগ সমুহ অধোৰেখিত কৰক:',
'tog-justify'                 => 'অধ্যায় সমুহৰ দুয়োফাল সমান কৰক',
'tog-hideminor'               => 'সাম্প্রতিক সাল-সলনিত অগুৰুত্বপূর্ণ সম্পাদনা নেদেখুৱাব',
'tog-extendwatchlist'         => 'লক্ষ্য-তালিকাৰ সকলো সাল-সলনি বহলাই দেখুৱাওক',
'tog-usenewrc'                => 'বর্দ্ধিত সাম্প্রতিক সাল-সলনি (JavaScript)',
'tog-numberheadings'          => 'শীর্ষকত স্বয়ংক্রীয়ভাৱে ক্রমিক নং দিয়ক',
'tog-showtoolbar'             => 'সম্পাদনা দণ্ডিকা দেখুৱাওক(JavaScript)',
'tog-editondblclick'          => 'একেলগে দুবাৰ টিপা মাৰিলে পৃষ্ঠা সম্পদনা কৰক (JavaScript)',
'tog-editsection'             => '[সম্পাদনা কৰক] সংযোগৰ দ্বাৰা সম্পাদনা কৰা সক্রীয় কৰক',
'tog-editsectiononrightclick' => 'বিষয়ৰ শিৰোণামাত সো-বুটাম টিপা মাৰি সম্পাদনা কৰাতো সক্রীয় কৰক (JavaScript)',
'tog-showtoc'                 => 'শিৰোণামাৰ সুচী দেখুৱাওক (যিবোৰ পৃষ্ঠাত তিনিতাতকৈ বেছি শিৰোণামা আছে)',
'tog-rememberpassword'        => 'মোৰ প্রৱেশ এই কম্পিউটাৰত মনত ৰাখক',
'tog-editwidth'               => 'সম্পাদনা বাকছ সম্পূর্ণ বহল',
'tog-watchcreations'          => 'মই বনোৱা সকলো পৃষ্ঠা মোৰ লক্ষ্য-তালিকাত যোগ কৰক',
'tog-watchdefault'            => 'মই সম্পাদনা কৰা সকলো পৃষ্ঠা মোৰ লক্ষ্য-তালিকাত যোগ কৰক',
'tog-watchmoves'              => 'মই স্থানান্তৰ কৰা সকলো পৃষ্ঠা মোৰ লক্ষ্য-তালিকাত যোগ কৰক',
'tog-watchdeletion'           => 'মই বিলোপ কৰা সকলো পৃষ্ঠা মোৰ লক্ষ্য-তালিকাত যোগ কৰক',
'tog-minordefault'            => 'সকলো সম্পাদনা অগুৰুত্বপূর্ণ বুলি নিজে নিজে চিহ্নিত কৰক',
'tog-previewontop'            => 'সম্পাদনা বাকছৰ ওপৰত খচৰা দেখুৱাওক',
'tog-previewonfirst'          => 'প্রথম সম্পাদনাৰ পিছ্ত খচৰা দেখুৱাওক',
'tog-nocache'                 => 'পৃষ্ঠা Caching নিষ্ক্রীয় কৰক',
'tog-enotifwatchlistpages'    => 'মোৰ লক্ষ্য-তালিকাত থকা পৃষ্ঠা সলনি হলে মোলৈ ই-মেইল পঠাব',
'tog-enotifusertalkpages'     => 'মোৰ বার্তা পৃষ্ঠা সলনি হলে মোলৈ ই-মেইল পঠাব',
'tog-enotifminoredits'        => 'অগুৰুত্বপূর্ণ সম্পাদনা হলেও মোলৈ ই-মেইল পঠাব',
'tog-enotifrevealaddr'        => 'জাননী ই-মেইল বোৰত মোৰ ই-মেইল ঠিকনা দেখুৱাব',
'tog-shownumberswatching'     => 'লক্ষ্য কৰি থকা সদস্য সমুহৰ সংখ্যা দেখুৱাওক',
'tog-fancysig'                => 'কেঁচা স্বাক্ষৰ সমুহ (স্বয়ংক্রীয় সংযোগ অবিহনে)',
'tog-externaleditor'          => 'সদায়ে বাহ্যিক সম্পাদক ব্যৱহাৰ কৰিব (কেৱল জনা সকলৰ বাবে, ইয়াৰ বাবে আপোনাৰ কম্পিউটাৰত বিশেষ ব্যৱস্থা থাকিব লাগিব)',
'tog-showjumplinks'           => '"জপিয়াই যাওক" সংযোগ সক্রীয় কৰক',
'tog-uselivepreview'          => 'সম্পাদনাৰ লগে লগে খচৰা দেখুৱাওক (JavaScript) (পৰীক্ষামূলক)',
'tog-forceeditsummary'        => 'সম্পাদনাৰ সাৰাংশ নিদিলে মোক জনাব',
'tog-watchlisthideown'        => 'মোৰ লক্ষ্য-তালিকাত মোৰ সম্পাদনা নেদেখুৱাব',
'tog-watchlisthidebots'       => 'মোৰ লক্ষ্য-তালিকাত বটে কৰা সম্পাদনা নেদেখুৱাব',
'tog-watchlisthideminor'      => 'মোৰ লক্ষ্য-তালিকাত অগুৰুত্বপূর্ণ সম্পাদনা নেদেখুৱাব',
'tog-ccmeonemails'            => 'মই অন্য সদস্যলৈ পঠোৱা ই-মেইলৰ প্রতিলিপী এটা মোলৈও পঠাব',
'tog-showhiddencats'          => 'গোপন শ্রেণী সমুহ দেখুৱাওক',

'underline-always'  => 'সদায়',
'underline-never'   => 'কেতিয়াও নহয়',
'underline-default' => 'ব্রাউজাৰ ডিফল্ট',

'skinpreview' => '(খচৰা)',

# Dates
'sunday'        => 'দেওবাৰ',
'monday'        => 'সোমবাৰ',
'tuesday'       => 'মঙ্গলবাৰ',
'wednesday'     => 'বুধবাৰ',
'thursday'      => 'বৃহস্পতিবাৰ',
'friday'        => 'শুক্রবাৰ',
'saturday'      => 'শণিবাৰ',
'sun'           => 'দেও',
'mon'           => 'সোম',
'tue'           => 'মংগল',
'wed'           => 'বুধ',
'thu'           => 'বৃহস্পতি',
'fri'           => 'শুক্র',
'sat'           => 'শনি',
'january'       => 'জানুৱাৰী',
'february'      => 'ফেব্রুৱাৰী',
'march'         => 'মাৰ্চ',
'april'         => 'এপ্ৰিল',
'may_long'      => "মে'",
'june'          => 'জুন',
'july'          => 'জুলাই',
'august'        => 'আগষ্ট',
'september'     => 'চেপ্তেম্বৰ',
'october'       => 'অক্টোবৰ',
'november'      => 'নৱেম্বৰ',
'december'      => 'ডিচেম্বৰ',
'january-gen'   => 'জানুৱাৰী',
'february-gen'  => 'ফেব্রুৱাৰী',
'march-gen'     => 'মার্চ',
'april-gen'     => 'এপ্ৰিল',
'may-gen'       => 'মে’',
'june-gen'      => 'জুন',
'july-gen'      => 'জুলাই',
'august-gen'    => 'আগষ্ট',
'september-gen' => 'চেপ্তেম্বৰ',
'october-gen'   => 'অক্টোবৰ',
'november-gen'  => 'নবেম্বৰ',
'december-gen'  => 'ডিচেম্বৰ',
'jan'           => 'জানু:',
'feb'           => 'ফেব্রু:',
'mar'           => 'মার্চ',
'apr'           => 'এপ্ৰিল',
'may'           => 'মে',
'jun'           => 'জুন',
'jul'           => 'জুলাই',
'aug'           => 'আগষ্ট',
'sep'           => 'চেপ্ত:',
'oct'           => 'অক্টো:',
'nov'           => 'নৱে:',
'dec'           => 'ডিচে:',

# Categories related messages
'pagecategories'                => '{{PLURAL:$1|শ্রেণী|শ্রেণী}}',
'category_header'               => '"$1" শ্রেণীৰ পৄষ্ঠাসমূহ',
'subcategories'                 => 'অপবিভাগ',
'category-media-header'         => '"$1" শ্রেণীৰ মেডিয়া',
'category-empty'                => "''এই শ্রেণীত বর্তমান কোনো লিখনী বা মাধ্যম নাই''",
'hidden-categories'             => '{{PLURAL:$1|গোপন শ্রেণী|গোপন শ্রেণী}}',
'hidden-category-category'      => 'গোপন শ্রেণী সমুহ', # Name of the category where hidden categories will be listed
'category-subcat-count'         => '{{PLURAL:$2|এই শ্রেণীত নিম্নলিখিত উপশ্রেণী আছে| এই শ্রেণীত নিম্নলিখিত {{PLURAL:$1|উপশ্রেণীটো|$1 উপশ্রেণীসমুহ}} আছে, মুঠতে $2  তা উপশ্রেণী।}}',
'category-subcat-count-limited' => 'এই শ্রেণীত নিম্নলিখিত {{PLURAL:$1|উপশ্রেণী আছে|$1 উপশ্রেণী আছে}}.',
'listingcontinuesabbrev'        => 'আগলৈ',

'about'          => 'বিষয়ে',
'article'        => 'লিখনী',
'newwindow'      => '(নতুন উইণ্ডোত খোল খায়)',
'cancel'         => 'ৰদ কৰক',
'qbfind'         => 'সন্ধান কৰক',
'qbbrowse'       => 'চাবলৈ(ব্রাওজ)',
'qbedit'         => 'সম্পাদনা',
'qbpageoptions'  => 'এই পৃষ্ঠা',
'qbpageinfo'     => 'প্রসংগ(কনটেক্স্ট)',
'qbmyoptions'    => 'মোৰ পৃষ্ঠাসমুহ',
'qbspecialpages' => 'বিশেষ পৃষ্ঠাসমুহ',
'moredotdotdot'  => 'ক্রমশ:...',
'mypage'         => 'মোৰ পৃষ্ঠা',
'mytalk'         => 'মোৰ কথা-বতৰা',
'anontalk'       => 'এই IP-ত যোগাযোগ কৰক',
'navigation'     => 'দিকদৰ্শন',
'and'            => 'আৰু',

# Metadata in edit box
'metadata_help' => 'মেটাডাটা:',

'errorpagetitle'    => 'ভুল',
'returnto'          => '$1 লৈ ঘুৰি যাঁওক|',
'tagline'           => '{{SITENAME}} -ৰ পৰা',
'help'              => 'সহায়',
'search'            => 'সন্ধান',
'searchbutton'      => 'সন্ধান কৰক',
'go'                => 'গমন',
'searcharticle'     => 'গমন',
'history'           => 'খতিয়ান',
'history_short'     => 'খতিয়ান',
'updatedmarker'     => 'মোৰ শেহতীয়া আগমনৰ পাছৰ পৰিবৰ্তনবিলাক',
'info_short'        => 'বিবৰণ',
'printableversion'  => 'প্রিণ্ট কৰাৰ উপযোগী',
'permalink'         => 'স্থায়ী সুত্র(লিংক)',
'print'             => 'প্রিন্ট কৰিবলৈ',
'edit'              => 'সম্পাদন',
'create'            => 'প্রাৰম্ভন(ক্রিয়েট)',
'editthispage'      => 'বৰ্তমান পৃষ্ঠাটো সম্পাদন কৰিবলৈ',
'create-this-page'  => 'নতুন পৃষ্ঠা সৃষ্টি কৰক',
'delete'            => 'বিলোপন(ডিলিট)',
'deletethispage'    => 'বৰ্তমান পৃষ্ঠাৰ বিলোপন(ডিলিট)',
'undelete_short'    => '{{PLURAL:$1|বিলোপিত পৃষ্ঠাৰ|$1 সংখ্যক বিলোপিত পৃষ্ঠাৰ}} পূৰ্ববৎকৰণ',
'protect'           => 'সংৰক্ষ(প্রটেক্ট)',
'protect_change'    => 'সংৰক্ষণবিধিৰ পৰিবৰ্তন',
'protectthispage'   => 'বৰ্তমান পৃষ্ঠাৰ সংৰক্ষণবিধিৰ পৰিবৰ্তন',
'unprotect'         => 'সংৰক্ষণমুক্ত কৰক',
'unprotectthispage' => 'এই পৃষ্ঠা সংৰক্ষণমুক্ত কৰক',
'newpage'           => 'নতুন পৃষ্ঠা',
'talkpage'          => 'এই পৃষ্ঠা সম্পৰ্কে আলোচনা',
'talkpagelinktext'  => 'বাৰ্তালাপ',
'specialpage'       => 'বিশেষ পৃষ্ঠা',
'personaltools'     => 'ব্যক্তিগত সৰঞ্জাম',
'postcomment'       => 'আপোনাৰ মতামত দিয়ক',
'articlepage'       => 'প্রবন্ধ',
'talk'              => 'বাৰ্তালাপ',
'views'             => 'দৰ্শ(ভিউ)',
'toolbox'           => 'সাজ-সৰঞ্জাম',
'userpage'          => 'ভোক্তাৰ(ইউজাৰ) পৃষ্ঠা',
'projectpage'       => 'প্রকল্প পৃষ্ঠা',
'imagepage'         => 'চিত্র পৃষ্ঠা চাওক',
'mediawikipage'     => 'বার্তা পৃষ্ঠা চাওক',
'templatepage'      => 'সাঁচ পৃষ্ঠা চাওক',
'viewhelppage'      => 'সহায় পৃষ্ঠা চাওক',
'categorypage'      => 'শ্রেণী পৃষ্ঠা চাওক',
'viewtalkpage'      => 'আলোচনা চাওক',
'otherlanguages'    => 'আন ভাষাত',
'redirectedfrom'    => '($1 ৰ পৰা)',
'redirectpagesub'   => 'পূণঃনির্দেশিত পৃষ্ঠা',
'lastmodifiedat'    => 'এই পৃষ্ঠাটো শেষবাৰৰ কাৰণে $1 তাৰিখে $2 বজাত সলনি কৰা হৈছিল', # $1 date, $2 time
'viewcount'         => 'এই পৃষ্ঠাটো {{PLURAL:$1|এবাৰ|$1}} বাৰ চোৱা হৈছে',
'protectedpage'     => 'সুৰক্ষিত পৃষ্ঠা',
'jumpto'            => 'গম্যাৰ্থে',
'jumptonavigation'  => 'দিকদৰ্শন',
'jumptosearch'      => 'সন্ধানাৰ্থে',

# All link text and link target definitions of links into project namespace that get used by other message strings, with the exception of user group pages (see grouppage) and the disambiguation template definition (see disambiguations).
'aboutsite'            => '{{SITENAME}}ৰ ইতিবৃত্ত',
'aboutpage'            => 'Project:ইতিবৃত্ত',
'bugreports'           => 'বাগ ৰিপোর্ট',
'bugreportspage'       => 'Project:বাগ ৰিপোর্ট',
'copyright'            => 'এই লিখনী $1 ৰ অন্তর্গত উপলব্ধ।',
'copyrightpagename'    => '{{SITENAME}} স্বত্বাধিকাৰ',
'copyrightpage'        => '{{ns:project}}:স্বত্ব',
'currentevents'        => 'সাম্প্রতিক ঘটনাৱলী',
'currentevents-url'    => 'Project:শেহতীয়া ঘটনাৱলী',
'disclaimers'          => 'দায়লুপ্তি',
'disclaimerpage'       => 'Project:সাধাৰণ দায়লুপ্তি',
'edithelp'             => 'সম্পাদনাৰ বাবে সহায়',
'edithelppage'         => 'Help:সম্পাদনা',
'faq'                  => 'প্রায়ে উঠা প্রশ্ন',
'faqpage'              => 'Project:প্রায়ে উঠা প্রশ্ন',
'helppage'             => 'Help:সুচী',
'mainpage'             => 'বেটুপাত',
'mainpage-description' => 'বেটুপাত',
'policy-url'           => 'Project:নীতি',
'portal'               => 'সমজুৱা পৃষ্ঠা',
'portal-url'           => 'Project:সমজুৱা পৃষ্ঠা',
'privacy'              => 'গোপনীয়তা নিতী',
'privacypage'          => 'Project:গোপনীয়তাৰ নীতি',
'sitesupport'          => 'দান-বৰঙনি',
'sitesupport-url'      => 'Project:চাইট সাহায্য',

'badaccess'        => 'অনুমতি ভুল',
'badaccess-group0' => 'আপুনি কৰিব বিছৰা কামতো কৰাৰ আধিকাৰ আপোনাৰ নাই।',
'badaccess-group1' => '$1 গোটৰ সদস্যৰহে এই কামতো কৰাৰ অধিকাৰ আছে।',
'badaccess-group2' => '$1 গোটবোৰৰ মাজৰ যিকোনো এটা গোটৰ সদস্যৰহে এই কামতো কৰাৰ অধিকাৰ আছে।',
'badaccess-groups' => '$1 গোটবোৰৰ মাজৰ যিকোনো এটা গোটৰ সদস্যৰহে এই কামতো কৰাৰ অধিকাৰ আছে।',

'versionrequired'     => 'মেডিয়াৱিকিৰ $1 সংকলন থাকিব লাগিব ।',
'versionrequiredtext' => 'এই পৃষ্ঠাটো ব্যৱহাৰ কৰিবলৈ মেডিয়াৱিকিৰ $1 সংকলন থাকিব লাগিব । [[Special:Version|সংকলন সুচী]] চাওক।',

'ok'                      => 'ওকে',
'retrievedfrom'           => '"$1" -ৰ পৰা সংকলিত',
'youhavenewmessages'      => 'আপোনাৰ কাৰণে $1 আছে। ($2)',
'newmessageslink'         => 'নতুন বার্তা',
'newmessagesdifflink'     => 'শেহতিয়া সাল-সলনি',
'youhavenewmessagesmulti' => '$1 ত আপোনাৰ কাৰণে নতুন বার্তা আছে',
'editsection'             => 'সম্পাদন কৰক',
'editold'                 => 'সম্পাদনা',
'viewsourceold'           => 'উত্‍স চাওক',
'editsectionhint'         => '$1 খণ্ডৰ সম্পাদনা',
'toc'                     => 'সূচী',
'showtoc'                 => 'দেখুৱাব লাগে',
'hidetoc'                 => 'দেখুৱাব নালাগে',
'thisisdeleted'           => '$1 চাওক বা সলনি কৰক?',
'viewdeleted'             => '$1 চাওক?',
'page-rss-feed'           => '"$1" আৰ-এচ-এচ ফীড',

# Short words for each namespace, by default used in the namespace tab in monobook
'nstab-main'      => 'পৃষ্ঠা',
'nstab-user'      => 'সদস্য পৃষ্ঠা',
'nstab-media'     => 'মেডিয়া পৃষ্ঠা',
'nstab-special'   => 'বিশেষ',
'nstab-project'   => 'আচনী পৃষ্ঠা',
'nstab-image'     => 'চিত্র',
'nstab-mediawiki' => 'বার্তা',
'nstab-template'  => 'সাঁচ',
'nstab-help'      => 'সহায় পৃষ্ঠা',
'nstab-category'  => 'শ্রেণী',

# Main script and global functions
'nosuchaction'      => 'এনেকুৱা কোনো ক্রীয়া নাই',
'nosuchactiontext'  => 'এই URL তোৱে দিয়া নির্দেশ ৱিকিয়ে বুজি পোৱা নাই',
'nosuchspecialpage' => 'এনেকুৱা কোনো বিশেষ পৃষ্ঠা নাই',
'nospecialpagetext' => "<big>'''আপুনি অস্তিত্বত নথকা বিশেষ পৃষ্ঠা এটা বিচাৰিছে '''</big>
<br>
   বিশেষ পৃষ্ঠাহমুহৰ তালিকা ইয়াত পাব [[Special:Specialpages|{{int:specialpages}}]].",

# General errors
'error'                => 'ভুল',
'databaseerror'        => 'তথ্যকোষৰ ভুল',
'nodb'                 => 'তথ্যকোষ $1 বাচনী কৰিব পৰা নগল',
'cachederror'          => 'এয়া আগতে জমা কৰি থোৱা(cached) প্রতিলিপী, আৰু এয়া সাম্প্রতিক নহব পাৰে।',
'laggedslavemode'      => 'সাবধান: ইয়াত সাম্প্রতিক সাল-সলনি নাথাকিব পাৰে',
'readonly'             => 'তথ্যকোষ বন্ধ কৰা আছে',
'enterlockreason'      => 'বন্ধ কৰাৰ কাৰণ দিয়ক, লগতে কেতিয়ামানে খোলা হব তাকো জনাব।',
'readonlytext'         => 'নতুন সম্পাদন আৰু আন সাল-সলনিৰ কাৰণে তথ্যকোষ বর্তমানে বন্ধ আছে, হয়তো নিয়মিয়া চোৱ-চিতা কৰিবলৈ, কিছু সময় পিছ্ত এয়া সধাৰণ অৱস্থালৈ আহিব।

যিজন প্রৱন্ধকে বন্ধ কৰিছে তেও কাৰণ দিছে: $1',
'internalerror'        => 'ভিতৰুৱা গণ্ডোগোল',
'internalerror_info'   => 'ভিতৰুৱা গণ্ডোগোল: $1',
'filecopyerror'        => '"$1" ফাইলটো "$2" লৈ প্রতিলিপী কৰিব পৰা নগল।',
'filerenameerror'      => '"$1" ফাইলৰ নাম সলনি কৰি "$2" কৰিব পৰা নগল ।',
'filedeleteerror'      => '"$1" ফাইলতো বিলোপ কৰিব পৰা নগল।',
'badtitle'             => 'অনুপোযোগি শিৰোণামা',
'badtitletext'         => 'আপুনি বিচৰা পৃষ্ঠাটোৰ শিৰোণামা অযোগ্য, খালী বা ভুলকে জৰিত আন্তর্ভাষিক বা আন্তর্ৱিকি শিৰোণামা। ইয়াত এক বা ততোধিক বর্ণ থাকিব পাৰে যাক শিৰোণামাত ব্যৱহাৰ কৰিব নোৱাৰি।',
'perfdisabled'         => 'ক্ষমা কৰিব! এই সুবিধাতো সাময়িক ভাবে বন্ধ কৰা হৈছে, কাৰণ ই তথ্যকোষ ইমানেই লেহেম কৰি দিয়ে যে কোনেও ৱিকি ব্যৱহাৰ কৰিব নোৱাৰে।',
'perfcached'           => 'তলত দিয়া তথ্য খিনি আগতে জমা কৰি থোৱা (cached) আৰু সাম্প্রতিক নহব পাৰে।',
'perfcachedts'         => 'তলত দিয়া তথ্য খিনি আগতে জমা কৰি থোৱা (cached) আৰু শেষবাৰৰ কাৰণে $1 ত নৱীকৰণ কৰা হৈছিল।',
'querypage-no-updates' => 'এই পৃষ্ঠাটো নৱীকৰণ কৰা ৰোধ কৰা হৈছে। ইয়াৰ তথ্য এতিয়া সতেজ কৰিব নোৱাৰি।',
'viewsource'           => 'উত্‍স চাঁওক',
'viewsourcefor'        => '$1 ৰ কাৰণে',
'protectedpagetext'    => 'এই পৃষ্ঠাটোৰ সম্পাদনা ৰোধ কৰিবলৈ সুৰক্ষিত কৰা হৈছে।',
'viewsourcetext'       => 'আপুনি এই পৃষ্ঠাটোৰ উত্‍স চাব আৰু নকল কৰিব পাৰে',
'namespaceprotected'   => "আপোনাৰ '''$1''' নামস্থানৰ পৃষ্ঠাহমুহ সম্পাদনা কৰাৰ অধিকাৰ নাই।",
'customcssjsprotected' => 'এই পৃষ্ঠা সম্পাদনা কৰাৰ আধিকাৰ আপোনাৰ নাই, কাৰণ ইয়াত আন সদস্যৰ ব্যক্তিগত চেটিংচ আছে।',
'ns-specialprotected'  => 'বিশেষ পৃষ্ঠা সম্পাদিত কৰিব নোৱাৰি।',
'titleprotected'       => "[[User:$1|$1]] সদস্যজনে এই শিৰোণামাৰ লিখনী লিখা ৰোধ কৰিছে ।
ইয়াৰ কাৰণ হৈছে ''$2'' ।",

# Login and logout pages
'logouttitle'                => 'সদস্য প্রস্থান',
'logouttext'                 => '<strong>আপুনি প্রস্থান কৰিলে ।</strong>

আপুনি বেনামী ভাবেও {{SITENAME}} ব্যৱহাৰ কৰিব পাৰে, অথবা আকৌ সেই একে বা বেলেগ নামেৰে প্রৱেশ কৰিব পাৰে।
যেতিয়ালৈকে আপোনাৰ ব্রাউজাৰৰ অস্থায়ী-স্মৃতি (cache memory) খালী নকৰে, তেতিয়ালৈকে কিছুমান পৃষ্ঠাত আপুনি প্রৱেশ কৰা বুলি দেখুৱাই থাকিব পাৰে।',
'welcomecreation'            => '<h2>স্বাগতম, $1!</h2><p>আপোনাৰ সদস্যভুক্তি হৈ গৈছে|
{{SITENAME}} ত আপোনাৰ পচন্দমতে ব্যক্তিগতকৰণ কৰি লবলৈ লব নাপাহৰিব|',
'loginpagetitle'             => 'সদস্য প্র্ৱেশ',
'yourname'                   => 'সদস্যনাম:',
'yourpassword'               => 'আপোনাৰ গুপ্তশব্দ',
'yourpasswordagain'          => 'গুপ্তশব্দ আকৌ এবাৰ লিখক',
'remembermypassword'         => 'মোৰ প্রৱেশ এই কম্পিউটাৰত মনত ৰাখিব',
'yourdomainname'             => 'আপোনাৰ দমেইন:',
'loginproblem'               => '<b>আপোনাৰ প্রৱেশত সমস্যা হৈছে ।</b><br />আকৌ চেষ্টা কৰক!',
'login'                      => 'প্রৱেশ',
'nav-login-createaccount'    => 'প্রৱেশ/সদস্যভুক্তি',
'loginprompt'                => '{{SITENAME}}ত প্রৱেশ কৰিবলৈ আপুনি কুকী সক্রীয় কৰিব লাগিব',
'userlogin'                  => 'প্রৱেশ/সদস্যভুক্তি',
'logout'                     => 'প্রস্থান',
'userlogout'                 => 'প্রস্থান',
'notloggedin'                => 'প্রৱেশ কৰা নাই',
'nologin'                    => 'আপুনি সদস্য নহয়? $1।',
'nologinlink'                => 'নতুন সদস্যভুক্তি কৰক',
'createaccount'              => 'নতুন সদস্যভুক্তি কৰক',
'gotaccount'                 => 'আপুনি সদস্য হয়নে? $1',
'gotaccountlink'             => 'প্রবেশ',
'createaccountmail'          => 'ই-মেইলেৰে',
'badretype'                  => 'আপুনি দিয়া গুপ্ত শব্দ দুটা মিলা নাই।',
'userexists'                 => 'আপুনি দিয়া সদস্যনাম আগৰে পৰাই ব্যৱহাৰ হৈ আছে।
অনুগ্রহ কৰি বেলেগ সদস্যনাম এটা বাচনী কৰক।',
'youremail'                  => 'আপোনাৰ ই-মেইল *',
'username'                   => 'সদস্যনাম:',
'uid'                        => 'সদস্য চিহ্ন:',
'prefs-memberingroups'       => 'এই {{PLURAL:$1|গোটৰ|গোটবোৰৰ}} সদস্য:',
'yourrealname'               => 'আপোনাৰ আচল নাম*',
'yourlanguage'               => 'ভাষা:',
'yournick'                   => 'আপোনাৰ স্বাক্ষ্যৰ:',
'badsig'                     => 'অনুপোযোগী স্বাক্ষ্যৰ, HTML টেগ পৰীক্ষা কৰি লওক।',
'badsiglength'               => 'অত্যাধিক দীঘলিয়া স্বাক্ষৰ; $1 তাতকৈ কম আখৰৰ হব লাগে',
'email'                      => 'ই-মেইল',
'prefs-help-realname'        => 'আপোনাৰ আচল নাম দিয়াতো জৰুৰি নহয়, কিন্তু দিলে আপোনাৰ কামবোৰ আপোনাৰ নামত দেখুওৱা হব।',
'loginerror'                 => 'প্রৱেশ সমস্যা',
'prefs-help-email'           => 'ই-মেইল ঠিকন দিয়া বৈকল্পিক, কিন্তু দিলে আন সদস্যই আপোনাৰ চিনাকি নোপোৱাকৈয়ে আপোনাৰ লগত সম্পর্ক স্থাপন কৰিব পাৰিব।',
'prefs-help-email-required'  => 'ই-মেইল ঠিকনা দিবই লাগিব',
'nocookiesnew'               => 'আপোনাৰ সদস্যভুক্তি হৈ গৈছে, কিন্তু আপুনি প্রৱেশ কৰা নাই।
{{SITENAME}} ত প্রৱেশ কৰিবলৈ কুকী সক্রিয় থাকিব লাগিব।
আপুনি কুকী নিস্ক্রিয় কৰি থৈছে।
অনুগ্রহ কৰি কুকী সক্রীয় কৰক, আৰু তাৰ পাছত আপোনাৰ সদস্যনামেৰে প্রৱেশ কৰক।',
'nocookieslogin'             => '{{SITENAME}} ত প্রৱেশ কৰিবলৈ কুকী সক্রিয় থাকিব লাগিব।
আপুনি কুকী নিস্ক্রিয় কৰি থৈছে।
অনুগ্রহ কৰি কুকী সক্রীয় কৰক, আৰু তাৰ পাছত চেষ্টা কৰক।',
'noname'                     => 'আপুনি বৈধ সদস্যনাম এটা দিয়া নাই।',
'loginsuccesstitle'          => 'সফলতাৰে প্রবেশ কৰা হল',
'loginsuccess'               => "''' আপুনি {{SITENAME}}ত \"\$1\" নামেৰে প্রবেশ কৰিলে '''",
'nosuchuser'                 => '"$1" নামৰ কোনো সদস্য নাই।
আপোনাৰ বানানতো চাওক, বা নতুন সদস্যভুক্তি কৰক।',
'nosuchusershort'            => '"<nowiki>$1</nowiki>" এই নামৰ কোনো সদস্য নাই ।
বানানতো আকৌ এবাৰ ভালদৰে চাওক ।',
'nouserspecified'            => 'অপুনি সদস্যনাম এটা দিবই লাগিব।',
'wrongpassword'              => 'আপুনি ভুল গুপ্তশব্দ দিছে। অনুগ্রহ কৰি আকৌ এবাৰ চেষ্টা কৰক।',
'wrongpasswordempty'         => 'দিয়া গুপ্তশব্দতো খালী; অনুগ্রহ কৰি আকৌ এবাৰ চেষ্টা কৰক। ।',
'passwordtooshort'           => 'আপোনাৰ গুপ্তশব্দ অযোগ্য বা একেবাৰ চুটি ।
ইয়াত কমেও $1 তা আখৰ থাকিব লাগিব আৰু আপোনাৰ সদস্যনামৰ লগত একে হব নোৱাৰিব।',
'mailmypassword'             => 'ই-মেইলত গুপ্তশব্দ পঠাওক',
'passwordremindertitle'      => '{{SITENAME}} ৰ কাৰণে নতুন অস্থায়ী গুপ্তশব্দ',
'passwordremindertext'       => 'কোনোবাই (হয়তো আপুনি, $1 IP ঠিকনাৰ পৰা)
{{SITENAME}} ত ব্যৱহাৰ কৰিবলৈ ’নতুন গুপ্তশব্দ’ বিছাৰিছে ($4) ।
"$2" সদস্যজনৰ কাৰনে এতিয়া নতুন গুপ্তশব্দ হৈছে "$3" ।
আপুনি এতিয়া প্রবেশ কৰক আৰু গুপ্তশব্দতো সলনি কৰক।

যদি আপুনি এই অনুৰোধ কৰা নাছিল অথবা যদি আপোনাৰ গুপ্তশব্দতো মনত আছে আৰু তাক সলাব নিবিছাৰে, তেনেহলে আপুনি এই বার্তাতো অবজ্ঞা কৰিব পাৰে আৰু আপোনাৰ আগৰ গুপ্তশব্দতোকে ব্যৱহাৰ কৰি থাকিব পাৰে।',
'noemail'                    => '"$1" সদস্যজনৰ কোনো ই-মেইল ঠিকনা সঞ্চিত কৰা নাই।',
'passwordsent'               => '"$1" ৰ ই-মেইল ঠিকনাত নতুন গুপ্তশব্দ এটা পঠোৱা হৈছে। অনুগ্রহ কৰি সেয়া পোৱাৰ পাছত পুনৰ প্রবেশ কৰক।',
'eauthentsent'               => 'সঞ্চিত ই-মেইল ঠিকনাত নিশ্বিতকৰণ ই-মেইল এখন পঠোৱা হৈছে।
আৰু অন্যান্য ই-মেইল পঠোৱাৰ আগতে, আপোনাৰ সদস্যতাৰ নিশ্বিত কৰিবলৈ সেই ই-মেইলত দিয়া নির্দেশনা আপুনি অনুসৰন কৰিব লাগিব।',
'throttled-mailpassword'     => 'যোৱা $1 ঘণ্টাত গুপ্তশব্দ পুনৰুদ্ধাৰ সুচনা পঠিওৱা হৈছে ।
অবৈধ ব্যৱহাৰ ৰোধ কৰিবলৈ $1 ঘণ্টাত এবাৰহে গুপ্তশব্দ পুনৰুদ্ধাৰ সুচনা পঠিওৱা হয়।',
'mailerror'                  => 'ই-মেইল পঠোৱাত সমস্যা হৈছে: $1',
'acct_creation_throttle_hit' => 'ক্ষমা কৰিব, আপুনি ইতিমধ্যে $1 টা সদস্যভুক্তি কৰিছে। 
আপুনি আৰু অধিক সদস্যভুক্তি কৰিব নোৱাৰে।',
'emailauthenticated'         => 'আপোনাৰ ই-মেইল ঠিকনাটো  $1 ত প্রমানিত কৰা হৈছে।',
'emailnotauthenticated'      => 'আপোনাৰ ই-মেইল ঠিকনাতো এতিয়ালৈ প্রমনিত হোৱা নাই ।
আপুনি তলৰ বিষয়বোৰৰ কাৰণে মেইল পঠাব নোৱাৰে ।',
'noemailprefs'               => 'এই সুবিধাবোৰ ব্যৱহাৰ কৰিবলৈ এটা ই-মেইল ঠিকনা দিয়ক।',
'emailconfirmlink'           => 'আপোনাৰ ই-মেইল ঠিকনতো প্রমানিত কৰক',
'invalidemailaddress'        => 'আপুনি দিয়া ই-মেইল ঠিকনাতো গ্রহনযোগ্য নহয়, কাৰণ ই অবৈধ প্রকাৰৰ যেন লাগিছে।
অনুগ্রহ কৰি এটা বৈধ ই-মেইল ঠিকনা লিখক অথবা একো নিলিখিব।',
'accountcreated'             => 'সদস্যতা সৃষ্টি কৰা হল',
'accountcreatedtext'         => '$1 ৰ কাৰণে সদস্যভুক্তি কৰা হল।',
'createaccount-title'        => '{{SITENAME}} ৰ কাৰণে সদস্যভুক্তি কৰক।',

# Password reset dialog
'resetpass'               => 'গুপ্তশব্দ পূণর্স্থাপন কৰক',
'resetpass_announce'      => 'আপুনি ই-মেইলত পোৱা অস্থায়ী গুপ্তশব্দৰে প্রৱেশ কৰিছে।
প্রৱেশ সম্পুর্ণ কৰিবলৈ, আপুনি এটা নতুন গুপ্তশব্দ দিব লাগিব:',
'resetpass_header'        => 'গুপ্তশব্দ পূণর্স্থাপন কৰক',
'resetpass_submit'        => 'গুপ্তশব্দ বনাওক আৰু প্রৱেশ কৰক',
'resetpass_success'       => 'আপোনাৰ গুপ্তশব্দ সফলতাৰে সলনি কৰা হৈছে, এতিয়া আপুনি প্রৱেশ কৰি আছে...',
'resetpass_bad_temporary' => 'অস্থায়ী গুপ্তশব্দ ভুল ।
হয়তো আপুনি আগতেই গুপ্তশব্দ সলনি কৰিছে, অথবা নতুন গুপ্তশব্দৰ কাৰণে অনুৰোধ পঠাইছে।',
'resetpass_forbidden'     => '{{SITENAME}} ত গুপ্তশব্দ সলনি কৰিব নোৱাৰি',

# Edit page toolbar
'bold_sample'     => 'শকত পাঠ্য',
'bold_tip'        => 'শকত পাঠ্য',
'italic_sample'   => 'বেঁকা পাঠ্য',
'italic_tip'      => 'বেঁকা পাঠ্য',
'link_sample'     => 'শিৰোণামা সংযোগ',
'link_tip'        => 'ভিতৰুৱা সংযোগ',
'extlink_sample'  => 'http://www.example.com শীর্ষক সংযোগ',
'extlink_tip'     => 'বাহিৰৰ সংযোগ (http:// নিশ্বয় ব্যৱহাৰ কৰিব)',
'headline_sample' => 'শিৰোণামা পাঠ্য',
'headline_tip'    => 'দ্বিতীয় স্তৰৰ শিৰোণামা',
'math_sample'     => 'ইয়াত গণিতীয় সুত্র সুমুৱাওক',
'math_tip'        => 'গণিতীয় সুত্র (LaTeX)',
'nowiki_sample'   => 'নসজোৱা পাঠ্য ইয়াত অন্তর্ভুক্ত কৰক',
'nowiki_tip'      => 'ৱিকি-সম্মত সাজ-সজ্জা অৱজ্ঞা কৰক',
'image_tip'       => 'এম্বেডেড ফাইল',
'media_tip'       => 'ফাইল সংযোগ',
'sig_tip'         => 'সময়ৰ সৈতে আপোনাৰ স্বাক্ষৰ',
'hr_tip'          => 'পথালী ৰেখা (কমকৈ ব্যৱহাৰ কৰিব)',

# Edit pages
'summary'                => 'সাৰাংশ',
'subject'                => 'বিষয় / শীর্ষক',
'minoredit'              => 'এইটো এটা সৰু সম্পদনা',
'watchthis'              => 'এই পৃষ্ঠাটো অনুসৰণ-সূচীভুক্ত কৰক',
'savearticle'            => 'পৃষ্ঠা সংৰাক্ষিত কৰক',
'preview'                => 'খচৰা',
'showpreview'            => 'খচৰা',
'showdiff'               => 'সালসলনিবোৰ দেখুৱাওক',
'anoneditwarning'        => "'''সাৱধান:''' আপুনি প্রৱেশ কৰা নাই, এই পৃষ্ঠাৰ ইতিসাহত আপোনাৰ আই পি ঠিকনা সংৰক্ষিত কৰা হব|",
'summary-preview'        => 'সাৰাংশৰ খচৰা',
'accmailtext'            => '"$1"ৰ পাছৱৰ্ড $2 লৈ পঠোৱা হ\'ল|',
'newarticle'             => '(নতুন)',
'newarticletext'         => 'আপুনি বিছৰা পৃষ্ঠাটো এতিয়ালৈকে লিখা হোৱা নাই। 
এই লিখনীতো লিখিবলৈ তলত লিখা আৰম্ভ কৰক। (সহায়ৰ কাৰণে [[{{MediaWiki:Helppage}}|ইয়াত]] টিপা মাৰক।)

আপুনি যদি ইয়ালৈ ভূলতে আহিছে, তেনেহলে আপোনাৰ ব্রাউজাৰত (BACK) বুতামত টিপা মাৰক।',
'noarticletext'          => 'এই পৃষ্ঠাত বর্তমান কোনো পাঠ্য নাই| আপুনি ৱিকিপিডিয়াৰ আন পৃষ্ঠাত [[Special:Search/{{PAGENAME}}| শিৰোণামাতো বিচাৰিব পাৰে, বা [{{fullurl:{{FULLPAGENAME}}|action=edit}} লিখা আৰম্ভ কৰিব পাৰে] ।',
'previewnote'            => '<strong>মনত ৰাখিব যে এয়া কেৱল খচৰা হে, সাল-সলনিবোৰ এতিয়াও সংৰক্ষিত কৰা হোৱা নাই!</strong>',
'editing'                => '$1 সম্পাদনা',
'editingsection'         => '$1 (বিভাগ) সম্পদনা কৰি থকা হৈছে',
'copyrightwarning'       => "অনুগ্ৰহ কৰি মন কৰক যে {{SITENAME}}লৈ কৰা সকলো অৱদান $2 ৰ চর্তাৱলীৰ মতে প্রদান কৰা বুলি ধৰি লোৱা হব (আৰু অধিক জানিবলৈ $1 চাঁওক)। যদি আপুনি আপোনাৰ লিখনি নিৰ্দয়ভাৱে সম্পাদনা কৰা আৰু ইচ্ছামতে পুনৰ্বিতৰণ কৰা ভাল নাপায়, তেনেহ'লে নিজৰ লিখনি ইয়াত নিদিব|
<br />

ইয়াত আপোনাৰ লিখনি দিয়াৰ লগে লগে আপুনি আপোনা-আপুনি প্ৰতিশ্ৰুতি দিছে যে এই লিখনিটো আপোনাৰ মৌলিক লিখনি, বা কোনো স্বত্বাধিকাৰ নথকা বা কোনো ৰাজহুৱা ৱেবছাইট বা তেনে কোনো মুকলি উৎসৰ পৰা আহৰণ কৰা| 
<strong>স্বত্বাধিকাৰযুক্ত কোনো সমল অনুমতি অবিহনে দাখিল নকৰে যেন!</strong>",
'longpagewarning'        => '<strong>সাবধান: এই পৃষ্ঠাটো $1 কিলোবাইট আকাৰৰ; কিছুমান ব্রাউজাৰে 32kb বা তাতকৈ বেছি আকাৰৰ পৃষ্ঠা দেখুৱাবলৈ বা সম্পাদনা কৰিবলৈ অসুবিধা পাব পাৰে ।
অনুগ্রহ কৰি এই পৃষ্ঠাটোক সৰু সৰু খণ্ডত বিভক্ত কৰাৰ কথা বিবেচনা কৰক ।</strong>',
'templatesused'          => 'এই পৃষ্ঠাত ব্যৱহৃত সাঁচ সমুহ',
'templatesusedpreview'   => 'এই খচৰাত ব্যৱহৃত সাঁচ সমুহ',
'template-protected'     => '(সুৰক্ষিত)',
'template-semiprotected' => '(অর্ধ-সংৰক্ষিত)',
'nocreatetext'           => '{{SITENAME}} ত নতুন লিখনী লিখা ৰদ কৰা হৈছে।
আপুনি ঘুৰি গৈ অস্তিত্বত থকা পৃষ্ঠা এটা সম্পাদনা কৰিব পাৰে, বা [[Special:Userlogin| নতুন সদস্যভর্তি হওক/ প্রবেশ কৰক]] ।',
'recreate-deleted-warn'  => "'''সাৱধান: আপুনি আগতে বিলোপিত কৰা পৃষ্ঠা এটা পূণঃনির্মান কৰি আছে। '''

এই পৄষ্ঠাটো সম্পাদনা কৰা উচিত হব নে নাই আপুনি বিবেচনা কৰি চাওক।
এই পৃষ্ঠাটো বিলোপ কৰাৰ অভিলেখ আপোনাৰ সুবিধার্থে ইয়াত দিয়া হৈছে।",

# History pages
'viewpagelogs'        => 'এই পৃষ্ঠাৰ লগ চাঁওক|',
'currentrev'          => 'এতিয়াৰ অৱতৰন',
'revisionasof'        => '$1 তম ভাষ্য',
'previousrevision'    => '← আগৰ সংসোধন',
'nextrevision'        => 'নতুন সংসোধন →',
'currentrevisionlink' => 'সাম্প্রতিক সংসোধন',
'cur'                 => 'বর্তমান',
'last'                => 'আগৰ',
'page_first'          => 'প্রথম',
'page_last'           => 'অন্তিম',
'histfirst'           => 'আটাইতকৈ পূৰণি',
'histlast'            => 'শেহতীয়া',

# Diffs
'history-title'           => '"$1" ৰ সাল-সলনিৰ ইতিহাস',
'difference'              => '(খঁচৰা সমুহৰ মাজৰ পার্থক্য)',
'lineno'                  => 'পংক্তি $1:',
'compareselectedversions' => 'নির্বাচিত কৰা সংকলন সমুহৰ মাজত পার্থক্য চাঁওক|',
'editundo'                => 'পূৰ্ববতাৰ্থে',

# Search results
'noexactmatch' => "'''\"\$1\" শিৰোণামাৰ কোনো লিখনী নাই।''' আপুনী এই লিখনী [[:\$1|লিখিব পাৰে]]।",
'prevn'        => 'পিছলৈ $1',
'nextn'        => 'পৰৱর্তি $1',
'viewprevnext' => 'চাঁওক ($1) ($2) ($3)',
'powersearch'  => 'অতিসন্ধান',

# Preferences page
'preferences'   => 'পচন্দ',
'mypreferences' => 'মোৰ পচন্দ',
'retypenew'     => 'নতুন গুপ্তশব্দ আকৌ টাইপ কৰক',

'grouppage-sysop' => '{{ns:project}}:প্রবন্ধক',

# User rights log
'rightslog' => 'সদস্যৰ অধিকাৰ সুচী',

# Recent changes
'nchanges'                       => '$1 {{PLURAL:$1|সাল-সলনি|সাল-সলনি}}',
'recentchanges'                  => 'অলপতে হোৱা সাল-সলনি',
'recentchanges-feed-description' => 'ৱিকিত হোৱা শেহতিয়া সাল-সলনি এই ফীডত অনুসৰন কৰক।',
'rcnotefrom'                     => "তলত '''$2''' ৰ পৰা হোৱা ('''$1''' লৈকে) পৰিৱর্তন দেখুওৱা হৈছে ।",
'rclistfrom'                     => '$1 ৰ নতুন সাল-সলনি দেখুওৱাওক',
'rcshowhideminor'                => '$1 -সংখ্যক নগণ্য সম্পাদনা',
'rcshowhidebots'                 => 'বট $1',
'rcshowhideliu'                  => 'প্রবেশ কৰা সদস্যৰ সাল-সলনি $1',
'rcshowhideanons'                => 'বেনাম সদস্যৰ সাল-সলনি $1',
'rcshowhidepatr'                 => '$1 পহৰা দিয়া সম্পাদনা',
'rcshowhidemine'                 => 'মোৰ সম্পাদনা $1',
'rclinks'                        => 'যোৱা $2 দিনত হোৱা $1 টা সাল-সলনি চাঁওক|<br />$3',
'diff'                           => 'পার্থক্য',
'hist'                           => 'ইতিবৃত্ত',
'hide'                           => 'দেখুৱাব নালাগে',
'show'                           => 'দেখুওৱাওক',
'minoreditletter'                => 'ন:',
'newpageletter'                  => 'ন:',
'boteditletter'                  => 'য:',

# Recent changes linked
'recentchangeslinked'          => 'প্রাসংগিক সম্পাদনানমূহ',
'recentchangeslinked-title'    => '"$1"ৰ লগত জৰিত সাল-সলনি',
'recentchangeslinked-noresult' => 'দিয়া সময়ৰ ভিতৰত সংযোজিত পৃষ্ঠা সমূহত সাল-সলনি হোৱা নাই |',

# Upload
'upload'        => "ফাইল আপল'ড",
'uploadbtn'     => 'ফাইল আপলোড কৰক',
'uploadlogpage' => 'আপলোড সুচী',
'uploadedimage' => '"[[$1]]" আপলোড কৰা হ’ল',

# Special:Imagelist
'imagelist' => 'ফাইলৰ তালিকা',

# Image description page
'filehist'                  => 'ফাইলৰ ইতিবৃত্ত',
'filehist-help'             => 'ফাইলৰ আগৰ অৱ্স্থা চাবলৈ সেই তাৰিখ/সময়ত টিপা মাৰক|',
'filehist-current'          => 'বর্তমান',
'filehist-datetime'         => 'তাৰিখ/সময়',
'filehist-user'             => 'সদস্য',
'filehist-dimensions'       => 'আকাৰ',
'filehist-filesize'         => 'ফাইলৰ আকাৰ (বাইট)',
'filehist-comment'          => 'মন্তব্য',
'imagelinks'                => 'সূত্ৰসমূহ',
'linkstoimage'              => 'তলত দিয়া পৃষ্ঠাবোৰ এই চিত্র খনৰ লগত জৰিত :{{PLURAL:$1|page links|$1 pages link}}',
'nolinkstoimage'            => 'এই চিত্রখনলৈ কোনো পৃষ্ঠা সংযোজিত নহয়',
'sharedupload'              => 'এই ফাইলতো অন্যান্য বিষয়তো ব্যৱহাৰ হব পাৰে|',
'noimage'                   => 'এই নামৰ কোনো ফাইল নাই, আপুনি $1 কৰিব পাৰে ।',
'noimage-linktext'          => 'বোজাই কৰক',
'uploadnewversion-linktext' => 'এই ফাইলতোৰ নতুন সংশোধন এটা বোজাই কৰক',

# List redirects
'listredirects' => 'পূণঃনির্দেশিত তালিকা',

# Random page
'randompage' => 'আকস্মিক পৃষ্ঠা',

# Statistics
'statistics'    => 'পৰিসংখ্যা',
'sitestatstext' => "তথ্যকোষত {{PLURAL:\$1|'''१''' পৃষ্ঠা আছে|'''\$1''' খন পৃষ্ঠা আছে}}।
ইয়াৰ ভিতৰত \"বার্তা\" পৃষ্ঠা, {{SITENAME}} ৰ বিষয়ে পৃষ্ঠা, সুক্ষ্ম \"ঠুঠ\" পৃষ্ঠা, নির্দেশিত পৃষ্ঠা, আৰু অন্যান্য পৃষ্ঠা চামিল আছে, যিবোৰ হয়তো সাধাৰণ পৃষ্ঠা হিচাপে যোগ্যতা লাভ কৰা নাই।

ইয়াৰ বাহিৰেও, {{PLURAL:\$2|'''१'''  পৃষ্ঠা আছে|'''\$2''' পৃষ্ঠা আছে}}, যিবোৰ হয়তো যুক্তিসম্মত নির্দেশিত পৃষ্ঠা।

'''\$8''' তা ফাইল আপলোড কৰা {{PLURAL:\$8|হৈছে|হৈছে}}।

যেতিয়াৰ পৰা {{SITENAME}}ৰ নির্মাণ কৰা হৈছে, মুঠ {{PLURAL:\$3|পৃষ্ঠা|পৃষ্ঠা}}ক  '''\$3''' বাৰ দর্শণ কৰা হৈছে, আৰু '''\$4''' বাৰ সম্পাদনা কৰা হৈছে।
ইয়াৰ দ্বাৰা বুজা যায় যে, প্রতি পৃষ্ঠা '''\$5''' বাৰ সম্পাদিত হয়, আৰু প্রতি সম্পাদনা '''\$6''' বাৰ চোৱা হয়।

The [http://www.mediawiki.org/wiki/Manual:Job_queue job queue] length is '''\$7'''.",

'doubleredirects' => 'দ্বি-পূণঃনির্দেশিত',

# Miscellaneous special pages
'nbytes'         => '$1 {{PLURAL:$1|বাইট|বাইট}}',
'nlinks'         => '$1 {{PLURAL:$1|সংযোগ|সংযোগ}}',
'nmembers'       => '{{PLURAL:$1|সদস্য|$1 সদস্যবৃন্দ}}',
'prefixindex'    => 'উপশব্দ সুচী',
'longpages'      => 'দিঘলীয়া পৃষ্ঠাসমুহ',
'deadendpages'   => 'ডেড এণ্ড পৃষ্ঠাসমুহ',
'protectedpages' => 'সুৰক্ষিত পৃষ্ঠাসমুহ',
'listusers'      => 'সদস্য-সুচী',
'newpages'       => 'নতুন পৃষ্ঠা',
'ancientpages'   => 'আটাইটকৈ পুৰণি পৃষ্ঠাসমুহ',
'move'           => 'স্থানান্তৰন',
'movethispage'   => 'এই পৃষ্ঠাটো স্থানান্তৰিত কৰক',

# Book sources
'booksources' => 'গ্রন্থৰ উত্‍স সমুহ',

# Special:Log
'specialloguserlabel'  => 'সদস্য:',
'speciallogtitlelabel' => 'শিৰোণামা:',
'log'                  => 'আলেখ',
'all-logs-page'        => 'সকলো সুচী',

# Special:Allpages
'allpages'       => 'সকলোবোৰ পৃষ্ঠা',
'alphaindexline' => '$1 -ৰ পৰা $2 -লৈ',
'nextpage'       => 'পৰৱর্তী পৃষ্ঠা ($1)',
'prevpage'       => 'পিছৰ পৃষ্ঠা($1)',
'allpagesfrom'   => 'ইয়াৰে আৰম্ভ হোৱা পৃষ্ঠাবোৰ দেখুৱাওক:',
'allarticles'    => 'সকলো পৃষ্ঠা',
'allpagesprev'   => 'আগৰ',
'allpagessubmit' => 'যাওক',
'allpagesprefix' => 'এই উপশব্দৰে আৰম্ভ হোৱা পৃষ্ঠা দেখুৱাওক:',

# Special:Categories
'categories' => '{{PLURAL:$1|শ্রেণী|শ্রেণী}}',

# E-mail user
'emailuser' => 'এই সদস্যজনলৈ ই-মেইল পথাওক',

# Watchlist
'watchlist'            => 'মই অনুসৰণ কৰা পৃষ্ঠাবিলাকৰ তালিকা',
'mywatchlist'          => 'মোৰ অনুসৰণ-তালিকা',
'watchlistfor'         => "('''$1''' ৰ কাৰনে)",
'addedwatch'           => 'লক্ষ্য তালিকাত অন্তর্ভুক্তি কৰা হল',
'addedwatchtext'       => 'আপোনাৰ [[Special:Watchlist|লক্ষ্য তালিকাত ]]  "<nowiki>$1</nowiki>" অন্তর্ভুক্তি কৰা হল ।
ভৱিশ্যতে ইয়াত হোৱা সাল-সলনি আপুনি আপোনাৰ লক্ষ্য তালিকাত দেখিব, লগতে [[Special:Recentchanges|সম্প্রতিক সাল-সলনিৰ তালিকাত]] এই পৃষ্ঠাটো শকট আখৰত দেখিব যাতে আপুনি সহজে ধৰিব পাৰে ।',
'removedwatch'         => 'লক্ষ্য-তালিকাৰ পৰা আতৰোৱা হল',
'removedwatchtext'     => '"[[:$1]]" পৃষ্ঠাটো আপোনাৰ লক্ষ্য-তালিকাৰ পৰা আতৰোৱা হৈছে ।',
'watch'                => 'অনুসৰণাৰ্থে',
'watchthispage'        => 'এই পৃষ্ঠাটো লক্ষ্য কৰক',
'unwatch'              => 'অনুসৰণ কৰিব নালাগে',
'watchlist-details'    => 'বার্তা পৃষ্ঠা সমুহ নধৰি {{PLURAL:$1|$1 পৃষ্ঠা|$1 পৃষ্ঠা}} লক্ষ্য-তালিকাত আছে।',
'wlshowlast'           => 'যোৱা $1 ঘণ্টা $2 দিন $3 চাওক',
'watchlist-hide-bots'  => 'বটে কৰা সম্পাদনা লুকুৱাওক',
'watchlist-hide-own'   => 'মই কৰা সম্পাদনা লুকুৱাওক',
'watchlist-hide-minor' => 'অগুৰুত্ব্পূর্ণ সম্পাদনা লুকুৱাওক',

# Displayed when you click the "watch" button and it is in the process of watching
'watching'   => 'অনুসৰণভুক্ত কৰা হৈ আছে.....',
'unwatching' => 'অনুসৰণমুক্ত কৰা হৈ আছে.....',

# Delete/protect/revert
'deletepage'                  => 'পৃষ্ঠা বিলোপ কৰক',
'historywarning'              => 'সাবধান: আপুনি বিলোপ কৰিব বিছৰা পৃষ্ঠাটোৰ ইতিহাস খালী নহয়।',
'confirmdeletetext'           => 'আপুনি পৃষ্ঠা এটা তাৰ ইতিহাসৰ সৈতে বিলোপ কৰিব ওলাইছে।
অনুগ্রহ কৰি নিশ্বিত কৰক যে এয়া [[{{MediaWiki:Policy-url}}|নীতিসম্মত]] । লগতে আপুনি ইয়াৰ পৰিণাম জানে আৰু আপুনি এয়া কৰিব বিছাৰিছে।',
'actioncomplete'              => 'কার্য্য সম্পূর্ণ',
'deletedtext'                 => '"<nowiki>$1</nowiki>" ক বিলোপন কৰা হৈছে।
সাম্প্রতিক বিলোপনসমুহৰ তালিকা চাবলৈ $2 চাঁওক।',
'deletedarticle'              => '"$1" ক বাতিল কৰা হৈছে|',
'dellogpage'                  => 'বাতিল কৰা সুচী',
'deletecomment'               => 'বিলোপনৰ কাৰণ।',
'deleteotherreason'           => 'আন/অতিৰিক্ত কাৰণ:',
'deletereasonotherlist'       => 'আন কাৰণ:',
'rollbacklink'                => 'অগ্রায্য',
'protectlogpage'              => 'সুৰক্ষা সুচী',
'protectcomment'              => 'মন্তব্য:',
'protectexpiry'               => 'সময় শেষ:',
'protect_expiry_invalid'      => 'শেষ সময় ভুল ।',
'protect_expiry_old'          => 'শেষ সময় পাৰ হৈ গৈছে।',
'protect-unchain'             => 'স্থানান্তৰৰ অনুমতি দিয়ক',
'protect-text'                => '<strong><nowiki>$1</nowiki></strong> পৃষ্ঠাটোৰ সুৰক্ষা-স্তৰ আপুনি চাব আৰু সলনি কৰিব পাৰে।',
'protect-locked-access'       => 'এই পৃষ্ঠাটোৰ সুৰক্ষা-স্তৰ সলনি কৰাৰ অনুমতি আপোনাক দিয়া হোৱা নাই ।
<strong>$1</strong> এই পৃষ্ঠাটোৰ সুৰক্ষা-স্তৰৰ গাঠনী ইয়াত আছে:',
'protect-default'             => '(ডিফল্ট)',
'protect-fallback'            => '"$1" অনুমতি লাগিব',
'protect-level-autoconfirmed' => 'নথিভুক্ত নোহোৱা সদস্যক বাৰণ কৰক',
'protect-expiring'            => ' $1 (UTC) ত সময় শেষ হব',
'protect-cascade'             => 'এই পৃষ্ঠাটোৰ লগত জৰিত সকলো পৃষ্ঠা সুৰক্ষিত কৰক (সুৰক্ষা জখলা)',
'protect-cantedit'            => 'আপুনি এই পৃষ্ঠাটোৰ সুৰক্ষা-স্তৰ সলনি কৰিব নোৱৰে, কাৰণ আপোনাক সেই অনুমতি দিয়া হোৱা নাই।',
'restriction-type'            => 'অনুমতি:',
'restriction-level'           => 'সুৰক্ষা-স্তৰ:',

# Undelete
'undeletebtn' => 'পূণঃসংস্থাপন কৰক',

# Namespace form on various pages
'namespace'      => 'নামস্থান:',
'invert'         => 'নির্বাচন ওলোটা কৰক',
'blanknamespace' => '(মুখ্য)',

# Contributions
'contributions' => 'সদস্যৰ অৱদান',
'mycontris'     => 'মোৰ অৱদানসমুহ',
'contribsub2'   => '$1 ৰ কাৰণে($2)',
'uctop'         => '(ওপৰত)',
'month'         => 'এই মাহৰ পৰা (আৰু আগৰ):',
'year'          => 'এই বছৰৰ পৰা (আৰু আগৰ):',

'sp-contributions-newbies-sub' => 'নতুন সদস্যৰ কাৰনে',
'sp-contributions-blocklog'    => 'বাৰণ সুচী',

# What links here
'whatlinkshere'       => 'এই পৃষ্ঠা ব্যৱ্হাৰ কৰিছে...',
'whatlinkshere-title' => '$1 লৈ সংযোগ কৰা পৃষ্ঠাসমুহ',
'linklistsub'         => '(সংযোগবোৰৰ সুচী)',
'linkshere'           => "এই পৃষ্ঠাটো '''[[:$1]]''' ৰ লগত সংযোজিত:",
'nolinkshere'         => "'''[[:$1]]''' ৰ লগত কোনো পৃষ্ঠা সংযোজিত নহয়।",
'isredirect'          => 'পূণঃনির্দেশন পৃষ্ঠা',
'istemplate'          => 'অন্তর্ভুক্ত কৰক',
'whatlinkshere-prev'  => '{{PLURAL:$1|পিছৰ|পিছৰ $1}}',
'whatlinkshere-next'  => '{{PLURAL:$1|আগৰ|আগৰ $1}}',
'whatlinkshere-links' => '← সূত্রসমূহ',

# Block/unblock
'blockip'       => 'সদস্য বাৰণ কৰক',
'ipboptions'    => '২ ঘ্ণ্টা:2 hours,১ দিন:1 day,৩ দিন:3 days,১ সপ্তাহ:1 week,২ সপ্তাহ:2 weeks,১ মাহ:1 month,৩ মাহ:3 months,৬ মাহ:6 months,১ বছৰ:1 year,অনির্দিস্ট কাল:infinite', # display1:time1,display2:time2,...
'ipblocklist'   => 'বাৰণ কৰা সদস্য আৰু IP ঠিকনাৰ তালিকা',
'blocklink'     => 'সদস্যভুক্তি ৰদ',
'contribslink'  => 'অবদান',
'blocklogpage'  => 'বাৰণ কৰা সুচী',
'blocklogentry' => '"[[$1]]" ক $2 $3 লৈ সাল-সলনি কৰাৰ পৰা বাৰণ কৰা হৈছে।',

# Move page
'movepagetext'   => "ইয়াৰ সহায়েৰে পৃষ্ঠাৰ শিৰোণামা সলনি কৰিব পাৰি, পৃষ্ঠাৰ সকলো বস্তু নতুন শিৰোণামাৰ অধিনত আহিব। পুৰণি শিৰোণামাটোৱে নতুন পৃষ্ঠাটোলৈ টোৱাব। 
পুৰণি পৃষ্ঠাটোলৈ থকা সংযোগ সমুহ সলনি কৰা নহব। সেয়েহে আপুনি নিশ্বিত কৰিব লাগিব যে ইয়াত কোনো ভুল সংযোগ নাথাকে, ভঙা বা দ্বি-পূণঃনির্দেশনা নথকাতো নিশ্বিত কৰক।

মন কৰিব যে নতুন শিৰোণামাতো যদি আগৰ পৰাই আছে, তেনেহলে '''পৃষ্ঠাটো স্থানান্তৰ কৰা নহব'''। অবশ্যে সেই আগৰ পৃষ্ঠাটো যদি খালী হয়, বা ই যদি পূণঃনির্দেশনা আৰু তাক যদি আগতে সম্পাদনা কৰা হোৱা নাই, তেনেহলে স্থানান্তৰ হব। 
ইয়াৰ অর্থ এয়ে যে কিবা ভুল হলে পৃষ্ঠাটো আগৰ ঠাইতে থাকিব, আৰু আপুনি অস্তিত্বত থকা পৃষ্ঠা এখনৰ সলনি বেলেগ পৃষ্ঠা দিব নোৱাৰে। 

'''সাৱধান!'''
জনপ্রীয় পৃষ্ঠা এটাৰ কাৰণে এয়া এক ডাঙৰ আৰু অনাপেক্ষিত সাল-সলনি হব পাৰে;
আপুনি কি কৰি আছে তাক ভালদৰে বুজি লব আৰু তাৰ পৰিণাম ভালদৰে বিবেচনা কৰিব।",
'movearticle'    => 'পৃস্থা স্থানান্তৰ কৰক',
'newtitle'       => 'নতুন শিৰোণামালৈ:',
'move-watch'     => 'এই পৃষ্ঠাটো লক্ষ্য কৰক',
'movepagebtn'    => 'পৃষ্ঠাটো স্থানান্তৰ কৰক',
'pagemovedsub'   => 'স্থানান্তৰ সফল হল',
'movepage-moved' => "<big>'''“$1” ক “$2” লৈ স্থানান্তৰ কৰা হৈছে'''</big>", # The two titles are passed in plain text as $3 and $4 to allow additional goodies in the message.
'articleexists'  => 'সেই নামৰ পৃষ্ঠা এটা আগৰ পৰাই আছে, বা সেই নামতো অযোগ্য।
বেলেগ নাম এটা বাছি লওক।',
'talkexists'     => "'''পৃষ্ঠাটো স্থানান্তৰ কৰা হৈছে, কিন্তু ইয়াৰ লগত জৰিত বার্তা পৃষ্ঠাটো স্থানান্তৰ কৰা নহল, কাৰণ নতুন ঠাইত বার্তা পৃষ্ঠা এটা আগৰ পৰাই আছে।
অনুগ্রহ কৰি আপুনি নিজে স্থানান্তৰ কৰক ।'''",
'movedto'        => 'লৈ স্থানান্তৰ কৰা হল',
'movetalk'       => 'সংলগ্ন বার্তা পৃষ্ঠা স্থানান্তৰ কৰক',
'1movedto2'      => '$1 ক $2 লৈ স্থানান্তৰিত কৰা হল',
'movelogpage'    => 'স্থানান্তৰন সুচী',
'movereason'     => 'কাৰণ:',
'revertmove'     => 'আগৰ অৱ্স্থালৈ ঘুৰি যাওক',

# Export
'export' => 'পৃষ্ঠা ৰপ্টানি কৰক|',

# Namespace 8 related
'allmessages' => 'ব্যৱস্থাৰ বতৰা',

# Thumbnails
'thumbnail-more'  => 'বিবৰ্ধনাৰ্থে',
'thumbnail_error' => 'থাম্বনেইল বনাব অসুবিধা হৈছে: $1',

# Import log
'importlogpage' => 'আমদানী সুচী',

# Tooltip help for the actions
'tooltip-pt-userpage'             => 'মোৰ সদস্য পৃষ্ঠা',
'tooltip-pt-mytalk'               => 'মোৰ বাৰ্তালাপ-পৃষ্ঠা',
'tooltip-pt-preferences'          => 'মোৰ পচন্দ',
'tooltip-pt-watchlist'            => 'আপুনি সালসলনিৰ গতিবিধি লক্ষ কৰি থকা পৃষ্ঠাসমূহৰ সুচী',
'tooltip-pt-mycontris'            => 'মোৰ আৰিহনাৰ সুচী',
'tooltip-pt-login'                => 'অত্যাবশ্যক নহলেও লগ-ইন কৰা বাঞ্চনীয়',
'tooltip-pt-logout'               => 'লগ-আউট',
'tooltip-ca-talk'                 => 'সংশ্লিষ্ট প্রৱন্ধ সম্পৰ্কীয় আলোচনা',
'tooltip-ca-edit'                 => 'আপুনি এই পৃষ্ঠাটো সালসলনি কৰিব পাৰে, অনুগ্রহ কৰি সালসলনি সাচী থোৱাৰ আগতে খচৰা চাই লব',
'tooltip-ca-addsection'           => 'এই আলোচনাত আপোনাৰ মন্তব্য দিয়ক|',
'tooltip-ca-viewsource'           => 'এই পৃষ্ঠাটো সংৰক্ষিত কৰা হৈছে, আপুনি ইয়াৰ উত্‍স চাব পাৰে|',
'tooltip-ca-protect'              => 'এই পৃষ্ঠাটো সুৰক্ষিত কৰক',
'tooltip-ca-delete'               => 'এই পৃষ্ঠাটো বিলোপ কৰক',
'tooltip-ca-move'                 => 'এই পৃষ্ঠাটো স্থানান্তৰিত কৰক',
'tooltip-ca-watch'                => 'এই পৃষ্ঠাটো আপোনাৰ অনুসৰণ-তালিকাত যোগ কৰক',
'tooltip-ca-unwatch'              => 'এই পৃষ্ঠাটো আপোনাৰ লক্ষ-সূচীৰ পৰা আতৰোৱাওক',
'tooltip-search'                  => '{{SITENAME}} -ত সন্ধানাৰ্থে',
'tooltip-n-mainpage'              => 'বেটুপাত খুলিবৰ কাৰণে',
'tooltip-n-portal'                => "এই প্রকল্পৰ ইতিবৃত্ত, আপুনি কেনেকৈ সহায় কৰিব পাৰে, ইত্যাদি (কি, ক'ত কিয় বিখ্যাত!!)।",
'tooltip-n-currentevents'         => 'এতিয়াৰ ঘটনাৰাজীৰ পটভূমী',
'tooltip-n-recentchanges'         => 'শেহতীয়া সালসলনিসমূহৰ সূচী',
'tooltip-n-randompage'            => 'অ-পূৰ্বনিৰ্ধাৰিতভাবে যিকোনো এটা পৃষ্ঠা দেখুৱাবৰ কাৰণে',
'tooltip-n-help'                  => 'সহায়ৰ বাবে ইয়াত ক্লিক কৰক',
'tooltip-n-sitesupport'           => 'আমাক সহায় কৰক!',
'tooltip-t-whatlinkshere'         => 'ইয়ালৈ সংযোজিত সকলো পৃষ্ঠাৰ সুচী',
'tooltip-t-contributions'         => 'এই সদস্যজনৰ অৰিহনাসমূহৰ সূচী চাঁওক ।',
'tooltip-t-emailuser'             => 'এই সদস্যজনলৈ ই-মেইল পঠাওক',
'tooltip-t-upload'                => "ফাইল আপল'ড-অৰ অৰ্থে",
'tooltip-t-specialpages'          => 'বিশেষ পৃষ্ঠাসমূ্হৰ সূচী',
'tooltip-ca-nstab-user'           => 'সদস্যৰ পৃষ্ঠা চাঁওক',
'tooltip-ca-nstab-project'        => 'আচনী পৃষ্ঠা চাঁওক।',
'tooltip-ca-nstab-image'          => 'নথি পৃষ্ঠা চাঁওক',
'tooltip-ca-nstab-template'       => 'সাঁচ চাওক',
'tooltip-ca-nstab-help'           => 'সহায় পৃষ্ঠা চাওক',
'tooltip-ca-nstab-category'       => 'শ্রেণীসমূহৰ পৃষ্ঠা চাঁওক|',
'tooltip-minoredit'               => 'ইয়াক অগুৰুত্বপূর্ণ সম্পাদনা ৰূপে চিহ্নিত কৰক|',
'tooltip-save'                    => 'আপুনি কৰা সালসলনি সাচী থঁওক',
'tooltip-preview'                 => 'অপুনি কৰা সালসলনিবোৰৰ খচৰা চাওক, আনুগ্রহ কৰি সালসলনি সাচী থোৱাৰ আগতে ব্যৱহাৰ কৰক!',
'tooltip-diff'                    => 'ইয়াত আপুনি কৰা সালসলনিবোৰ দেখুৱাওক',
'tooltip-compareselectedversions' => 'এই পৃষ্ঠাত নির্বাচিত কৰা দুটা অৱতৰৰ মাজত পার্থক্য দেখুৱাওক|',
'tooltip-watch'                   => 'এই পৃষ্ঠাটোক আপোনাৰ লক্ষ-তালিকাত সংলগ্ন কৰক|',

# Browsing diffs
'previousdiff' => '← পিছৰ পার্থক্য',
'nextdiff'     => 'পৰৱর্তী পার্থক্য →',

# Media information
'file-nohires'         => '<small>ইয়াতকৈ ডাঙৰকৈ দেখুৱাব নোৱাৰি|</small>',
'show-big-image'       => 'সম্পূর্ণ দৃশ্য',
'show-big-image-thumb' => '<small>এই খচৰাৰ আকাৰ: $1 × $2 পিক্সেল </small>',

# Special:Newimages
'newimages' => 'নতুন ফাইলৰ বিথীকা',

# Metadata
'metadata'          => 'মেটাডাটা',
'metadata-help'     => 'এই ফাইলত অতিৰিক্ত খবৰ আছে, হয়তো ডিজিটেল কেমেৰা বা স্কেনাৰ ব্যৱহাৰ কৰি সৃস্তি বা পৰিৱর্তন কৰা হৈছে|
এই ফাইলটো আচলৰ পৰা পৰিৱর্তন  কৰা হৈছে, সেয়েহে পৰিৱর্তিত ফাইলটোৰ সৈতে নিমিলিব পাৰে|',
'metadata-expand'   => 'বহলাই ইয়াৰ বিষয়ে জনাওক',
'metadata-collapse' => 'বহলোৱা বিষয়বোৰ লুকুৱাওক',
'metadata-fields'   => 'এই সুচীত থকা বিষয়বোৰ চিত্রৰ পৃষ্ঠাৰ তলত সদায় দেখা যাব ।
বাকী বিষয়বোৰ গুপ্ত থাকিব ।
* make
* model
* datetimeoriginal
* exposuretime
* fnumber
* focallength', # Do not translate list items

# External editor support
'edit-externally'      => 'বাহিৰা আহিলা ব্যৱহাৰ কৰি এই ফাইলটো সম্পাদনা কৰক|',
'edit-externally-help' => 'অধিক জানিবলৈ [http://meta.wikimedia.org/wiki/Help:External_editors নির্দেশনা] চাঁওক ।',

# 'all' in various places, this might be different for inflected languages
'watchlistall2' => 'সকলো',
'namespacesall' => 'সকলোবোৰ',
'monthsall'     => 'সকলো',

# Watchlist editing tools
'watchlisttools-view' => 'সংগতি থকা সাল-সলনিবোৰ চাওক',
'watchlisttools-edit' => 'লক্ষ্য-তালিকা চাওক আৰু সম্পাদনা কৰক',
'watchlisttools-raw'  => 'কেঁচা লক্ষ্য-তালিকা সম্পাদনা কৰক',

# Special:SpecialPages
'specialpages' => 'বিশেষ পৃষ্ঠাসমূহ',

);
