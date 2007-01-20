<?php

/**
 * Internationalisation file for User Image Gallery extension
*
 * @addtogroup Extensions
 * @author Rob Church <robchur@gmail.com>
 */

function efUserImagesMessages() {
	$messages = array(

/* English */
'en' => array(
'userimages-caption' => 'Images uploaded by $1',
'userimages-noname' => 'Invalid username or none provided.',
'userimages-noimages' => '$1 has no image uploads.',
),
/* French */
'fr' => array(
'userimages-caption' => 'Images importées par $1',
'userimages-noname' => 'Nom d’utilisateur invalide ou manquant.',
'userimages-noimages' => '$1 n’a importé aucune image.',
),
/* Serbian default */
'sr' => array(
'userimages-caption' => 'Слике које је послао корисник $1',
'userimages-noname' => 'Погрешно корисничко име или корисник није послао ни једну слику.',
'userimages-noimages' => '$1 нема послатих слика.',
),
/* Serbian cyrillic */
'sr-ec' => array(
'userimages-caption' => 'Слике које је послао корисник $1',
'userimages-noname' => 'Погрешно корисничко име или корисник није послао ни једну слику.',
'userimages-noimages' => '$1 нема послатих слика.',
),
/* Serbian latin */
'sr-el' => array(
'userimages-caption' => 'Slike koje je poslao korisnik $1',
'userimages-noname' => 'Pogrešno korisničko ime ili korisnik nije poslao ni jednu sliku.',
'userimages-noimages' => '$1 nema poslatih slika.',
),
	);
	return $messages;
}

?>