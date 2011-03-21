<?php

/**
 * Internationalization file for the Include WP extension.
 *
 * @since 0.1
 *
 * @file IncludeWP.i18n.php
 * @ingroup IncludeWP
 *
 * @licence GNU GPL v3 or later
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

$messages = array();

/** English
 * @author Jeroen De Dauw
 */
$messages['en'] = array(
	'includewp-desc' => 'IncludeWP is a lightweight extension for including Wikipedia content',

	'includewp-loading-page' => 'Loading page...',
	'includewp-show-full-page' => 'Show full page',                                 
	'includewp-show-fragment' => 'Only show the first paragraph',
	'includewp-loading-failed' => 'Failed to load page.',

	'includewp-licence-notice' => 'The above content comes from the $1 page <a href="$2">$3</a> licenced under the <a href="$4">$5</a>.
There is a <a href="$6">full list of authors</a> available.',
	
	'includewp-parserhook-desc' => 'Parser hook that allows displaying content of a Wikipedia page.',
	'includewp-include-par-page' => 'The name of the (remote) page you want to display.',
	'includewp-include-par-wiki' => 'The name of the wiki you want to pull content from. Needs to be one of the allowed wikis, by default only Wikipedia.',
	'includewp-include-par-paragraphs' => 'The number of paragraphs you want to display initially.',
	'includewp-include-par-height' => 'The maximum height in pixels of the HTML div into which the content is loaded. Set to 0 for no limit.',
);

/** Message documentation (Message documentation)
 * @author Kghbln
 */
$messages['qqq'] = array(
	'includewp-licence-notice' => '$1 stands for the sitename',
);

/** Afrikaans (Afrikaans)
 * @author Naudefj
 */
$messages['af'] = array(
	'includewp-desc' => "IncludeWP is 'n liggewig uitbreiding om Wikipedia inhoud by u wiki in te sluit",
	'includewp-loading-page' => 'Laai bladsy...',
	'includewp-show-full-page' => 'Wys volledige bladsy',
	'includewp-loading-failed' => 'Laai van bladsy het gefaal.',
	'includewp-licence-notice' => 'Bostaande inhoud kom vanaf die $1-artikel <a href="$2">$3</a> gelisensieerd onder die <a href="$4">$5</a>. \'n Volledige lys van outeurs is <a href="$6">hier</a> beskikbaar.',
	'includewp-parserhook-desc' => 'Ontleder-hoek wat die vertoon van inhoud vanuit Wikipedia-artikels moontlik maak.',
	'includewp-include-par-page' => 'Die naam van die (afgeleë) bladsy om te vertoon.',
);

/** Belarusian (Taraškievica orthography) (‪Беларуская (тарашкевіца)‬)
 * @author EugeneZelenko
 * @author Jim-by
 */
$messages['be-tarask'] = array(
	'includewp-desc' => 'IncludeWP — невялікае пашырэньне для ўключэньня зьместу Вікіпэдыі',
	'includewp-loading-page' => 'Загрузка старонкі…',
	'includewp-show-full-page' => 'Паказаць старонку цалкам',
	'includewp-show-fragment' => 'Паказаць толькі першы параграф',
	'includewp-loading-failed' => 'Немагчыма загрузіць старонку.',
	'includewp-licence-notice' => 'Пададзены вышэй зьмест паходзіць са старонкі $1 <a href="$2">$3</a> на ўмовах ліцэнзіі <a href="$4">$5</a>. Поўны сьпіс аўтараў можна знайсьці <a href="$6">тут</a>.',
	'includewp-parserhook-desc' => 'Працэдура-перахопнік парсэру, які дазваляе паказваць зьмест старонкі Вікіпэдыі.',
	'includewp-include-par-page' => 'Назва (аддаленай) старонкі, якую Вы жадаеце паказаць.',
	'includewp-include-par-wiki' => 'Назва вікі, з якой Вы жадаеце ўзяць зьмест. Трэба, каб гэта была адна з дазволеных вікі, па змоўчваньні толькі Вікіпэдыя.',
	'includewp-include-par-paragraphs' => 'Колькасьць параграфаў, якія Вы жадаеце каб паказваліся пачаткова.',
	'includewp-include-par-height' => 'Максымальная вышыня (у піксэлях) разьдзела, у які загружаецца зьмест. Пастаўце 0, каб яна была неабмежаваная.',
);

/** Breton (Brezhoneg)
 * @author Fulup
 */
$messages['br'] = array(
	'includewp-loading-page' => 'O kargañ ar bajenn...',
	'includewp-show-full-page' => 'Diskouez ar bajenn en he hed',
	'includewp-show-fragment' => 'Diskouez ar rannbennad kentañ nemetken',
	'includewp-loading-failed' => "N'eus ket bet gallet kargañ ar bajenn",
	'includewp-include-par-page' => "Anv ar bajenn (a-bell) a fell deoc'h diskwel.",
	'includewp-include-par-paragraphs' => "An niver a rannbennadoù a fell deoc'h diskwel e penn-kentañ.",
);

/** German (Deutsch)
 * @author Kghbln
 */
$messages['de'] = array(
	'includewp-desc' => 'Ermöglicht das Einbeziehen von Inhalten der Wikipedia in ein Wiki',
	'includewp-loading-page' => 'Lade Seite …',
	'includewp-show-full-page' => 'Seite vollständig anzeigen',
	'includewp-show-fragment' => 'Lediglich den ersten Absatz anzeigen',
	'includewp-loading-failed' => 'Das Laden der Seite ist gescheitert.',
	'includewp-licence-notice' => 'Der obige Inhalt stammt vom $1-Artikel <a href="$2">„$3“</a>, der gemäß <a href="$4">$5</a> lizenziert wurde. Eine vollständige Liste der Autoren ist <a href="$6">hier</a> verfügbar.',
	'includewp-parserhook-desc' => 'Parserhook der das Anzeigen von Inhalten eines Wikipedia-Artikels ermöglicht.',
	'includewp-include-par-page' => 'Der Name der Seite, die angezeigt werden soll.',
	'includewp-include-par-wiki' => 'Der Name des Wikis aus dem die Inhalte einbezogen werden sollen. Es muss sich dabei um ein freigegebenes Wiki handeln, was standardmäßig nur auf Wikipedia zutrifft.',
	'includewp-include-par-paragraphs' => 'Die Anzahl der Absätze, die zunächst angezeigt werden sollen.',
	'includewp-include-par-height' => 'Die maximale Höhe des Bereichs in Pixeln (px) in dem die Inhalte angezeigt werden soll. Keine Höhenbeschränkung wird mit Null (0) angegeben.',
);

/** French (Français)
 * @author IAlex
 */
$messages['fr'] = array(
	'includewp-desc' => 'IncludeWP est une extension légère pour inclure le contenu de Wikipedia',
	'includewp-loading-page' => 'Chargement de la page ...',
	'includewp-show-full-page' => 'Afficher la page entière',
	'includewp-show-fragment' => 'Afficher uniquement le premier paragraphe',
	'includewp-loading-failed' => 'Échec du chargement de page.',
	'includewp-licence-notice' => 'Le contenu ci-dessus vient de la page $1 <a href="$2">$3</a> sous licence <a href="$4">$5</a>. Une liste complète des auteurs peut être trouvée <a href="$6">ici</a> .',
	'includewp-parserhook-desc' => "Crochet de l'analyseur qui permet d'afficher le contenu d'une page de Wikipédia.",
	'includewp-include-par-page' => 'Le nom de la page (à distance) que vous souhaitez afficher.',
	'includewp-include-par-wiki' => 'Le nom du wiki duquel vous voulez extraire le contenu. Doit être un des wikis autorisés, par défaut seulement wikipedia.',
	'includewp-include-par-paragraphs' => 'Le nombre de paragraphes que vous souhaitez afficher initialement.',
	'includewp-include-par-height' => 'La hauteur maximum (en px) de la balise div dans lequel le contenu est chargé. Mettre à 0 pour aucune limite.',
);

/** Galician (Galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'includewp-loading-page' => 'Cargando a páxina...',
	'includewp-show-full-page' => 'Mostrar a páxina ao completo',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'includewp-desc' => 'IncludeWP היא הרחבה פשוטה להכללת תוכן של ויקיפדיה',
	'includewp-loading-page' => 'הדף בטעינה...',
	'includewp-show-full-page' => 'להציג דף מלא',
	'includewp-show-fragment' => 'להציג רק את הפסקה הראשונה',
	'includewp-loading-failed' => 'טעינת הדף נכשלה.',
	'includewp-licence-notice' => 'התוכן לעיל מגיע מהדף <a href="$2">$3</a> באתר $1, והוא מתפרסם ברישיון <a href="$4">$5</a>. רשימה מלאה של מחבריו נמצאת <a href="$6">כאן</a>.',
	'includewp-parserhook-desc' => 'וו מפענח שמאפשר הצגת תוכן של דף ויקיפדיה.',
	'includewp-include-par-page' => 'שם הדף המרוחק שאתם רוצים להציג.',
	'includewp-include-par-wiki' => 'שם הוויקי שממנו רתם רוצים למשוף תוכן. צריך להיות אחד מאתרי הוויקי המורשים, לפי בררת המחדל – רק ויקיפדיה.',
	'includewp-include-par-paragraphs' => 'מספר הפסקאות שאתם רוצים להציג בתחילה.',
	'includewp-include-par-height' => 'הגובה המרבי (בפיקסלים) של ה־div שאליו אתם רוצים לטעון את התוכן. הערך 0 מציין גודל לא מוגבל.',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'includewp-desc' => 'IncludeWP je jednore rozšěrjenje za zapřijimowanje wobsaha Wikipedije',
	'includewp-loading-page' => 'Strona so začituje...',
	'includewp-show-full-page' => 'Dospołnu stronu pokazać',
	'includewp-show-fragment' => 'Jenož prěni wotstawk pokazać',
	'includewp-loading-failed' => 'Začitowanje strony je so njeporadźiło.',
	'includewp-licence-notice' => 'Wyši wobsah pochadźa ze strony $1 <a href="$2">$3</a> steji pod licencu <a href="$4">$5</a>. Dospołna lisćina awtorow je <a href="$6">tu</a> k dispoziciji.',
	'includewp-parserhook-desc' => 'Parserowa hóčka, kotraž zwobraznjenje wobsaha strony Wikipedije zmóžnja.',
	'includewp-include-par-page' => 'Mjeno strony, kotraž ma so zwobraznić.',
	'includewp-include-par-wiki' => 'Mjeno wikija, z kotrež chceš wobsah brać. To dyrbi jedyn z dowolenych wikijow być, po standardźe je to jenož Wikipedija.',
	'includewp-include-par-paragraphs' => 'Ličba wotstawkow, kotrež maja so zwobraznić na spočatku.',
	'includewp-include-par-height' => 'Maksimalna wysokosć (w px) wobwoda, do kotrehož so wobsah začituje. Staj hódnotu na 0 za njewobmjezowanu wysokosć.',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'includewp-desc' => 'IncludeWP es un extension legier pro includer contento de Wikipedia',
	'includewp-loading-page' => 'Carga pagina…',
	'includewp-show-full-page' => 'Monstra pagina complete',
	'includewp-show-fragment' => 'Monstrar solmente le prime paragrapho',
	'includewp-loading-failed' => 'Cargamento del pagina fallite.',
	'includewp-licence-notice' => 'Le contento hic supra proveni del pagina <a href="$2">$3</a> de $1, e es disponibile sub licentia <a href="$4">$5</a>. Un lista complete de autores se trova <a href="$6">hic</a>.',
	'includewp-parserhook-desc' => 'Uncino del analysator syntactic que permitte presentar contento de un pagina de Wikipedia.',
	'includewp-include-par-page' => 'Le nomine del pagina (remote) a presentar.',
	'includewp-include-par-wiki' => 'Le nomine del wiki ex le qual importar contento. Debe esser un del wikis permittite; per predefinition, solo Wikipedia.',
	'includewp-include-par-paragraphs' => 'Le numero de paragraphos a presentar initialmente.',
	'includewp-include-par-height' => 'Le altitude maxime (in pixels) del div in le qual le contento es cargate. Defini como 0 pro non haber un limite.',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'includewp-desc' => 'IncludeWP ass eng einfach Erweiderung fir Wikipedia Inhalt anzebannen',
	'includewp-loading-page' => "D'Säit gëtt gelueden...",
	'includewp-show-full-page' => 'Säit ganz weisen',
	'includewp-show-fragment' => 'Nëmmen den éischte Abschnitt weisen',
	'includewp-loading-failed' => "D'Säit konnt net geluede ginn.",
	'includewp-licence-notice' => 'Den Inhalt hei driwwer kënnt vun der Säit $1 <a href="$2">$3</a> déi ënnert der <a href="$4">$5</a> lizenzéiert ass. Eng komplett Lëscht vun den Auteure fannt Dir <a href="$6">hei</a>.',
	'includewp-parserhook-desc' => 'Parserhook deen et erlaabt den Inhalt vun enger Wikipedia-Säit ze weisen.',
	'includewp-include-par-page' => 'Den Numm vun der Säit (vun der anerer Wiki), déi Dir wëllt gewise kréien.',
	'includewp-include-par-wiki' => 'Den Numm vun der Wiki aus där Dir den Inhalt iwwerhuele wëllt muss eng vun den zougeloossene Wikie sinn, standardméisseg nëmme Wikipedia.',
	'includewp-include-par-paragraphs' => "D'Zuel vun den Abschnitter déi Dir am Ufank weise wëllt.",
	'includewp-include-par-height' => 'Déi maximal Héicht (px) vum Beräich an deen den Inhalt geluede gëtt. Setzt op 0 fir datt keng Limit gëllt.',
);

/** Macedonian (Македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'includewp-desc' => 'IncludeWP е едноставен додаток за вклучување на содржини од Википедија',
	'includewp-loading-page' => 'Ја вчитувам страницата...',
	'includewp-show-full-page' => 'Прикажи цела страница',
	'includewp-show-fragment' => 'Прикажи го само првиот пасус',
	'includewp-loading-failed' => 'Не успеав да ја вчитам страницата.',
	'includewp-licence-notice' => 'Горенаведената содржина е преземена од страницата $1 <a href="$2">$3</a> која е под лиценцата <a href="$4">$5</a>. Целосен список на автори ќе најдете <a href="$6">тука</a>.',
	'includewp-parserhook-desc' => 'Парсерската кука што овозможува секогаш да се прикажува содржина на страница од Википедија.',
	'includewp-include-par-page' => 'Името на онаа (далечинска) страница што сакате да се прикаже.',
	'includewp-include-par-wiki' => 'Името на викито од кајшто сакате да извлечете содржини. Треба да е од дозволените викија (по основно: само Википедија)',
	'includewp-include-par-paragraphs' => 'Број на пасуси што сакате првично да се прикажат.',
	'includewp-include-par-height' => 'Максимална височина во пиксели (п) на полето кајшто ќе се прикажува содржината. Ако сакате да нема ограничување, ставете нула (0).',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'includewp-desc' => 'IncludeWP is een lichtgewicht uitbreiding voor het opnemen van inhoud vanuit Wikipedia',
	'includewp-loading-page' => 'Bezig met het laden van de pagina...',
	'includewp-show-full-page' => 'Volledige pagina weergeven',
	'includewp-show-fragment' => 'Alleen de eerste paragraaf weergeven',
	'includewp-loading-failed' => 'Fout bij het laden van de pagina.',
	'includewp-licence-notice' => 'De bovenstaande tekst is overgenomen van de pagina <a href="$2">$3</a> van $1 en heeft de licentie <a href="$4">$5</a>. Een volledige lijst van auteurs is <a href="$6">hier</a> te vinden.',
	'includewp-parserhook-desc' => 'Parserhook die het weergeven van de inhoud van een pagina van Wikipedia mogelijk maakt.',
	'includewp-include-par-page' => 'De naam van de pagina op de andere wiki die moet worden weergegeven.',
	'includewp-include-par-wiki' => "De naam van de wiki waar u inhoud van wilt weergeven. Dit moet een van de toegestane wiki's zijn; standaard is dit alleen Wikipedia.",
	'includewp-include-par-paragraphs' => 'Het aantal paragrafen dat u aanvankelijk wilt weergeven.',
	'includewp-include-par-height' => 'De maximale hooghte (in pixels) voor de "div" waarin de inhoud wordt gelaten. Stel dit in op 0 voor geen limiet.',
);

/** Portuguese (Português)
 * @author Hamilton Abreu
 */
$messages['pt'] = array(
	'includewp-desc' => 'IncludeWP é uma extensão ligeira para inclusão de conteúdo da Wikipédia',
	'includewp-loading-page' => 'A carregar a página...',
	'includewp-show-full-page' => 'Mostrar a página inteira',
	'includewp-show-fragment' => 'Mostrar só o primeiro parágrafo',
	'includewp-loading-failed' => 'Não foi possível carregar a página.',
	'includewp-licence-notice' => 'O conteúdo acima provém da página <a href="$2">$3</a> da $1, licenciada com a <a href="$4">$5</a>. Pode encontrar uma lista completa dos autores <a href="$6">aqui</a>.',
	'includewp-parserhook-desc' => 'Hook do analisador sintáctico para apresentar o conteúdo de uma página da Wikipédia.',
	'includewp-include-par-page' => 'O nome da página (remota) que pretende apresentar.',
	'includewp-include-par-wiki' => 'O nome da wiki de onde deseja importar conteúdo. Tem de ser uma das wikis permitidas; por padrão, só a Wikipédia.',
	'includewp-include-par-paragraphs' => 'O número de parágrafos que pretende mostrar inicialmente.',
	'includewp-include-par-height' => 'A altura máxima (em pixels) da secção div na qual o conteúdo é carregado. Defina como 0 para ilimitado.',
);

/** Russian (Русский)
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'includewp-desc' => 'IncludeWP — это лёгкое расширение для включения содержания Википедии',
	'includewp-loading-page' => 'Загрузка страницы…',
	'includewp-show-full-page' => 'Показать полную страницу',
	'includewp-show-fragment' => 'Показывать только первый абзац',
	'includewp-loading-failed' => 'Ошибка загрузки страницы.',
	'includewp-licence-notice' => 'Приведённый выше материал получен со страницы <a href="$2">$3</a> проекта $1, он доступен на условиях <a href="$4">$5</a>. Полный список авторов можно найти <a href="$6">здесь</a>.',
	'includewp-parserhook-desc' => 'Обработчик парсера, позволяющий отображать содержание Википедии.',
	'includewp-include-par-page' => 'Имя страницы (в другом проекте), которую вы хотите отобразить.',
	'includewp-include-par-wiki' => 'Имя вики, из которой вы хотите выводить содержимое. Должно быть в списке разрешённых, по умолчанию это только Википедии.',
	'includewp-include-par-paragraphs' => 'Количество абзацев, которые вы хотите отображать.',
	'includewp-include-par-height' => 'Максимальная высота (в пикселях) элемента DIV, в который загружается содержание. Установите 0 для снятия ограничения.',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'includewp-loading-page' => 'Ikinakarga ang pahina...',
	'includewp-show-full-page' => 'Ipakita ang buong pahina',
	'includewp-show-fragment' => 'Ipakita lamang ang unang talata',
	'includewp-loading-failed' => 'Nabigong maikarga ang pahina.',
	'includewp-parserhook-desc' => 'Kawit ng pambanghay na nagpapahintulot sa pagpapakita ng nilalaman ng isang pahina ng Wikipedia.',
	'includewp-include-par-page' => 'Ang pangalan ng pahinang (malayo) nais mong ipakita.',
	'includewp-include-par-wiki' => 'Ang pangalan ng wiki na nais mong paghilahan ng nilalaman.  Kailangang maging isa sa pinapahintulutang mga wiki, ayon sa wikipediang likas na nakatakda lamang.',
	'includewp-include-par-paragraphs' => 'Ang bilang ng mga talatang nais mong unang maipakita.',
);

/** Ukrainian (Українська)
 * @author Тест
 */
$messages['uk'] = array(
	'includewp-loading-page' => 'Завантаження сторінки...',
	'includewp-show-full-page' => 'Показати всю сторінку',
	'includewp-show-fragment' => 'Показувати лише перший абзац',
	'includewp-loading-failed' => 'Не вдалося завантажити сторінку.',
	'includewp-include-par-page' => 'Назва (віддаленої) сторінки, яку ви хочете показати.',
);

