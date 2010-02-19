<?php
require_once(dirname(__FILE__)."/wwutils.php");

	/** Unknown type, SHOULD not occurr in final data. MAY be used for
	 * resources that are referenced but where not available for analysis,
	 * or have not yet been analyzed. 
	 **/
	define('WW_RC_TYPE_UNKNOWN', 0);
	
	/**
	 * A "real" page, describing a concept.
	 */
	define('WW_RC_TYPE_ARTICLE', 10);
	
	/**
	 * This page is a supplemental part of an article, typically a transcluded
	 * subpage or simmilar.   
	 */
	define('WW_RC_TYPE_SUPPLEMENT', 15);
	
	
	/**
	 * A page solely defining a redirect/alias for another page
	 */
	define('WW_RC_TYPE_REDIRECT', 20);

	/**
	 * A disambuguation page, listing different meanings for the page title, 
	 * each linking to a article page.
	 */
	define('WW_RC_TYPE_DISAMBIG', 30);
	
	/**
	 * A page that contains a list of concepts that share some common property or quality,
	 * usually each linking to a page describing that concept.
	 */
	define('WW_RC_TYPE_LIST', 40);
	
	/**
	 * A category page.
	 */
	define('WW_RC_TYPE_CATEGORY', 50);
	
	/**
	 * This page does not contain relevant information for WikiWord
	 */
	define('WW_RC_TYPE_OTHER', 99);
	
	/**
	 * A page that is broken in some way, or was marked as bad or disputed. Such pages
	 * SHOULD generally be treated as if theys didn't exist.
	 */
	define('WW_RC_TYPE_BAD', 100);
	
	/**
	 * A resource that is not a page by itself, but merely a section of a page. Sections
	 * SHOULD always be part of a page of type ARTICLE, and are expected to descibe
	 * a narrower concept than the "parent" page.
	 */
	define('WW_RC_TYPE_SECTION', 200);


class WWThesaurus extends WWUTils {

    function queryConceptsForTerm($lang, $term, $limit = 100) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$term = trim($term);

	$sql = "SELECT O.global_concept as id, M.*, O.*, definition FROM {$wwTablePrefix}_{$lang}_meaning as M"
	      . " LEFT JOIN {$wwTablePrefix}_{$lang}_definition as D ON M.concept = D.concept "
	      . " JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($lang) . "\" AND M.concept = O.local_concept "
	      . " WHERE term_text = \"" . mysql_real_escape_string($term) . "\""
	      . " ORDER BY freq DESC "
	      . " LIMIT " . (int)$limit;

	return $this->query($sql);
    }

    function getConceptsForTerm($lang, $term, $limit = 100) {
	$rs = $this->queryConceptsForTerm($lang, $term);
	$list = WWUtils::slurpRows($rs);
	mysql_free_result($rs);
	return $list;
    }

    function queryConceptsForPage($lang, $page, $limit = 100) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$page = trim($page);

	$sql = "SELECT O.global_concept as id, O.* FROM {$wwTablePrefix}_{$lang}_resource as R "
	      . " JOIN {$wwTablePrefix}_{$lang}_about as A ON A.resource = R.id "
	      . " JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($lang) . "\" AND A.concept = O.local_concept "
	      . " WHERE R.name = \"" . mysql_real_escape_string($page) . "\""
	      . " LIMIT " . (int)$limit;

	return $this->query($sql);
    }

    function getConceptsForPage($lang, $page, $limit = 100) {
	$rs = $this->queryConceptsForPage($lang, $page);
	$list = WWUtils::slurpRows($rs);
	mysql_free_result($rs);
	return $list;
    }

    function queryLocalConcepts($id) {
	global $wwTablePrefix, $wwThesaurusDataset;
	$sql = "SELECT O.lang, O.local_concept_name from {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ";
	$sql .= " WHERE O.global_concept = " . (int)$id;

	return $this->query($sql);
    }

    function getLocalConcepts($id) { //NOTE: deprecated alias for backward compat
	return getPagesForConcept($id);
    }

    /*
    function queryLocalConceptInfo($lang, $id) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT O.*, C.*, F.*, definition FROM {$wwTablePrefix}_{$lang}_concept_info as F "
	      . " JOIN {$wwTablePrefix}_{$lang}_concept as C ON F.concept = C.id "
	      . " JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($lang) . "\" AND F.concept = O.local_concept "
	      . " LEFT JOIN {$wwTablePrefix}_{$lang}_definition as D ON F.concept = D.concept "
	      . " WHERE O.local_concept = $id ";

	return $this->query($sql);
    }

    function queryConceptInfo($id, $lang) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT O.*, C.*, F.*, definition FROM  {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O "
	      . " LEFT JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_concept_info as F ON O.global_concept = F.concept "
	      . " LEFT JOIN {$wwTablePrefix}_{$lang}_concept as C ON O.local_concept = C.id "
	      . " LEFT JOIN {$wwTablePrefix}_{$lang}_definition as D ON O.local_concept = D.concept "
	      . " WHERE O.global_concept = $id AND O.lang = \"" . mysql_real_escape_string($lang) . "\" ";

	return $this->query($sql);
    }*/

    function getConceptInfo( $id, $lang = null ) {
	$result = $this->getConcept($id, $lang);

	$result['broader'] = $this->getBroaderForConcept($id);
	$result['narrower'] = $this->getNarrowerForConcept($id);
	$result['related'] = $this->getRelatedForConcept($id);

	if ( $lang ) {
	    $d = $this->getDefinitionForConcept($id);
	    $result['related'] = $d[$lang];
	}

	return $result;
    }

    function unpickle($s, $lang, $hasId=true, $hasName=true, $hasConf=true) {
	$ss = explode("\x1E", $s);
	$items = array();

	$fetchNames = false;

	foreach ($ss as $i) {
	    $r = explode("\x1F", $i);
	    $offs = -1;

	    if ($hasId)   $r['id']   = @$r[$offs += 1];
	    if ($hasName) $r['name'] = @$r[$offs += 1];
	    if ($hasConf) $r['conf'] = @$r[$offs += 1];

	    if ($hasId && !isset($r['name'])) 
	      $fetchNames = true;

	    if ($hasId) $items[ $r['id'] ] = $r;
	    else $items[] = $r;
	}

	if ($fetchNames) {
	    $names = $this->fetchNames(array_keys($items), $lang);

	    $keys = array_keys($items);
	    foreach ($keys as $k) {
		$id = $items[$k]['id'];
		$items[$k]['name'] = $names[$id];
	    }
	}

	return $items;
    }

    function fetchNames($ids, $lang) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$names = array();
	if (!$ids) return $names;

	$set = NULL;
	foreach ($ids as $id) {
	   if ($set===NULL) $set = "";
	   else $set .= ", ";
	   $set .= $id;
	}

	$sql = "select global_concept as id, local_concept_name as name from {$wwTablePrefix}_{$wwThesaurusDataset}_origin ";
	$sql .= "where global_concept in ($set) and lang = \"" . mysql_real_escape_string($lang) . "\" ";

	$res = $this->query($sql);

	while ($row = mysql_fetch_assoc($res)) {
	    $id = $row['id'];
	    $names[$id] = $row['name'];
	}
	
	mysql_free_result($res);

	return $names;
    }

    /////////////////////////////////////////////////////////
    function getConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset;

	if ($lang) $sql = "SELECT C.*, O.* FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept as C "
		      . " LEFT JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O " 
		      . " ON C.id = O.global_concept " . " AND O.lang = \"".mysql_real_escape_string($lang)."\""
		      . " WHERE C.id = ".(int)$id ;
	else $sql = "SELECT C.* FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept as C "
		. " WHERE C.id = ".(int)$id;

	$r = $this->getRows($sql);

	if ( !$r ) return false;
	else return $r[0];
    }

    function getRelatedForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT " . ($lang ? " C.*, O.* " : " C.* " ) . " FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept as C "
	      . " JOIN  {$wwTablePrefix}_{$wwThesaurusDataset}_relation as R ON R.concept2 = C.id ";

	if ( $lang ) $sql .= " LEFT JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($lang) . "\" AND C.id = O.global_concept ";

	$sql .= " WHERE R.concept1 = ".(int)$id
	      . " AND ( R.bilink > 0 OR R.langref > 0 OR R.langmatch > 0 )"
	      . " GROUP BY C.id "
	      . " LIMIT " . (int)$limit;

	return $this->getRows($sql);
    }

    function getBroaderForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT " . ($lang ? " C.*, O.* " : " C.* " ) . " FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept as C "
	      . " JOIN  {$wwTablePrefix}_{$wwThesaurusDataset}_broader as R ON R.broad = C.id ";

	if ( $lang ) $sql .= " LEFT JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($lang) . "\" AND C.id = O.global_concept ";

	$sql .= " WHERE R.narrow = ".(int)$id
	      . " GROUP BY C.id "
	      . " LIMIT " . (int)$limit;

	return $this->getRows($sql);
    }

    function getNarrowerForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT " . ($lang ? " C.*, O.* " : " C.* " ) . "  FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept as C "
	      . " JOIN  {$wwTablePrefix}_{$wwThesaurusDataset}_broader as R ON R.narrow = C.id ";

	if ( $lang ) $sql .= " LEFT JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($lang) . "\" AND C.id = O.global_concept ";

	$sql .= " WHERE R.broad = ".(int)$id
	      . " GROUP BY C.id "
	      . " LIMIT " . (int)$limit;

	return $this->getRows($sql);
    }

    function getPagesForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset, $wwLanguages;

	if ( !$lang ) $lang = array_keys( $wwLanguages );
	if ( !is_array($lang) ) $lang = preg_split('![\\s,;|/:]\\s*!', $lang);
	$result = array();
	
	foreach ($lang as $ll) {
		$sql = "SELECT R.name FROM {$wwTablePrefix}_{$ll}_resource as R "
		      . " JOIN {$wwTablePrefix}_{$ll}_about as A ON A.resource = R.id "
		      . " JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($ll) . "\" AND A.concept = O.local_concept "
		      . " WHERE O.global_concept = " . (int)$id
		      . " WHERE R.type IN ( " . WW_RC_TYPE_ARTICLE . ", " . WW_RC_TYPE_CATEGORY . " ) "
		      . " LIMIT " . (int)$limit;

		$pages = $this->getList($sql, "name");
		if ( $pages === false || $pages === null ) return false;
		if ( !$pages ) continue;

		$result[$ll] = $pages;
	}

	return $result;
    }

    function getNamesForConcept( $id, $lang = null ) {
	global $wwTablePrefix, $wwThesaurusDataset, $wwLanguages;

	if ( !$lang ) $lang = array_keys( $wwLanguages );
	if ( !is_array($lang) ) $lang = preg_split('![\\s,;|/:]\\s*!', $lang);
	$result = array();
	
	foreach ($lang as $ll) {
		$sql = "SELECT O.local_name FROM {$wwTablePrefix}_{$ll}_resource as O ";
		$sql .= " WHERE O.global_concept = " . (int)$id;
		$sql .= " AND O.lang = " . (int)$ll;

		$pages = $this->getList($sql, "name");
		if ( $pages === false || $pages === null ) return false;
		if ( !$pages ) continue;

		$result[$ll] = $pages;
	}

	return $result;
    }

    function getTermsForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset, $wwLanguages;

	if ( !$lang ) $lang = array_keys( $wwLanguages );
	if ( !is_array($lang) ) $lang = preg_split('![\\s,;|/:]\\s*!', $lang);
	$result = array();

	foreach ($lang as $ll) {
	    $sql = "SELECT M.term_text FROM {$wwTablePrefix}_{$ll}_meaning as M"
		  . " JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($ll) . "\" AND M.concept = O.local_concept "
		  . " WHERE O.global_concept = " . (int)$id
		  . " ORDER BY freq DESC "
		  . " LIMIT " . (int)$limit;

	    $terms = $this->getList($sql, "term_text");
	    if ( $terms === false || $terms === null ) return false;
	    if ( !$terms ) continue;

	    $result[$ll] = $terms;
	}

	return $result;
    }

    function getDefinitionForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset, $wwLanguages;

	if ( !$lang ) $lang = array_keys( $wwLanguages );
	if ( !is_array($lang) ) $lang = preg_split('![\\s,;|/:]\\s*!', $lang);
	$result = array();

	foreach ($lang as $ll) {
	    $sql = "SELECT D.definition FROM {$wwTablePrefix}_{$ll}_definition as D"
		  . " JOIN {$wwTablePrefix}_{$wwThesaurusDataset}_origin as O ON O.lang = \"" . mysql_real_escape_string($ll) . "\" AND D.concept = O.local_concept "
		  . " WHERE O.global_concept = " . (int)$id
		  . " LIMIT " . (int)$limit;

	    $definitions = $this->getList($sql, "definition");
	    if ( $definitions === false || $definitions === null ) return false;
	    if ( !$definitions ) continue;

	    $result[$ll] = $definitions[0];
	}

	return $result;
    }

    function getLinksForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT C.* FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept as C "
	      . " JOIN  {$wwTablePrefix}_{$wwThesaurusDataset}_link as L ON L.target = C.id "
	      . " WHERE L.anchor = ".(int)$id
	      . " LIMIT " . (int)$limit;

	return $this->getRows($sql);
    }

    function getReferencesForConcept( $id, $lang = null, $limit = 100 ) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT C.* FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept as C "
	      . " JOIN  {$wwTablePrefix}_{$wwThesaurusDataset}_link as L ON L.anchor = C.id "
	      . " WHERE L.target = ".(int)$id
	      . " LIMIT " . (int)$limit;

	return $this->getRows($sql);
    }

    function getScoresForConcept( $id, $lang = null ) {
	global $wwTablePrefix, $wwThesaurusDataset;

	$sql = "SELECT S.* FROM {$wwTablePrefix}_{$wwThesaurusDataset}_concept_stats as S "
	      . " WHERE S.concept = ".(int)$id
	    ;

	$r = $this->getRows($sql);
	if ( !$r ) return false;

	return $r;
    }

}
