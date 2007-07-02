<?php

if (!defined('MEDIAWIKI')) die();


$wgExtensionFunctions[] = 'wfSpecialSuggest';
function wfSpecialSuggest() {
	class SpecialSuggest extends SpecialPage {
		function SpecialSuggest() {
			SpecialPage::SpecialPage('Suggest','UnlistedSpecialPage');
	
		}
		
		function execute( $par ) {
			global
				$wgOut,	$IP;

			$wgOut->disable();
			require_once("$IP/includes/Setup.php");
			require_once("Attribute.php");
			require_once("WikiDataBootstrappedMeanings.php");
			require_once("RecordSet.php");
			require_once("Editor.php");
			require_once("HTMLtable.php");
			#require_once("WikiDataAPI.php");
			require_once("Transaction.php");
			require_once("OmegaWikiEditors.php");
			require_once("Utilities.php");
			require_once("Wikidata.php");
			require_once("WikiDataTables.php");
			echo getSuggestions();
		}
	}
	
	SpecialPage::addPage(new SpecialSuggest());
}


function getSuggestions() {

	global $idAttribute;
	global $wgUser;
	$dc=wdGetDataSetContext();	
	@$search = ltrim($_GET['search-text']);
	@$prefix = $_GET['prefix'];
	@$query = $_GET['query'];
	@$objectId = $_GET['objectId'];
	@$offset = $_GET['offset'];
	@$attributesLevel = $_GET['attributesLevel'];
	$sql='';
	
	$dbr =& wfGetDB( DB_SLAVE );
	$rowText = 'spelling';
	switch ($query) {
		case 'relation-type':
			$sqlActual = getSQLForCollectionOfType('RELT', $wgUser->getOption('language'));
			$sqlFallback = getSQLForCollectionOfType('RELT', 'en');
			$sql=constructSQLWithFallback($sqlActual, $sqlFallback, array("member_mid", "spelling", "collection_mid"));
			break;
		case 'class':
			$sqlActual = getSQLForCollectionOfType('CLAS', $wgUser->getOption('language'));
			$sqlFallback = getSQLForCollectionOfType('CLAS', 'en');
			$sql=constructSQLWithFallback($sqlActual, $sqlFallback, array("member_mid", "spelling", "collection_mid"));
			break;
		case 'option-attribute':
			$sql = getSQLToSelectPossibleAttributes($objectId, $attributesLevel, 'OPTN');
			break;
		case 'translated-text-attribute':
			$sql = getSQLToSelectPossibleAttributes($objectId, $attributesLevel, 'TRNS');
			break;
		case 'text-attribute':	
			$sql = getSQLToSelectPossibleAttributes($objectId, $attributesLevel, 'TEXT');
			break;
		case 'url-attribute':	
			$sql = getSQLToSelectPossibleAttributes($objectId, $attributesLevel, 'URL');
			break;
		case 'language':
			require_once('languages.php');
			$sql = getSQLForLanguageNames($wgUser->getOption('language'));
			$rowText = 'language_name';
			break;
		case 'defined-meaning':
			$sql = 
				"SELECT {$dc}_syntrans.defined_meaning_id AS defined_meaning_id, {$dc}_expression_ns.spelling AS spelling, {$dc}_expression_ns.language_id AS language_id ".
				" FROM {$dc}_expression_ns, {$dc}_syntrans ".
	            " WHERE {$dc}_expression_ns.expression_id={$dc}_syntrans.expression_id " .
	            " AND {$dc}_syntrans.identical_meaning=1 " .
	            " AND " . getLatestTransactionRestriction("{$dc}_syntrans").
	            " AND " . getLatestTransactionRestriction("{$dc}_expression_ns");
	        break;
	    case 'class-attributes-level':
	    	$sql = getSQLForLevels($wgUser->getOption('language'));
	    	break;
	    case 'collection':
	 	$sqlActual=getSQLForCollection($wgUser->getOption('language'));
	 	$sqlFallback=getSQLForCollection('en');
		$sql=constructSQLWithFallback($sqlActual, $sqlFallback, array("collection_id", "spelling"));
	    	break;
	    case 'transaction':
	    	$sql = 
				"SELECT transaction_id, user_id, user_ip, " .
	    		" CONCAT(SUBSTRING(timestamp, 1, 4), '-', SUBSTRING(timestamp, 5, 2), '-', SUBSTRING(timestamp, 7, 2), ' '," .
	    		" SUBSTRING(timestamp, 9, 2), ':', SUBSTRING(timestamp, 11, 2), ':', SUBSTRING(timestamp, 13, 2)) AS time, comment" .
	    		" FROM {$dc}_transactions WHERE 1";
	    		
	    	$rowText = "CONCAT(SUBSTRING(timestamp, 1, 4), '-', SUBSTRING(timestamp, 5, 2), '-', SUBSTRING(timestamp, 7, 2), ' '," .
	    			" SUBSTRING(timestamp, 9, 2), ':', SUBSTRING(timestamp, 11, 2), ':', SUBSTRING(timestamp, 13, 2))";
	    	break;
	}
	                          
	if ($search != '') {
		if ($query == 'transaction')
			$searchCondition = " AND $rowText LIKE " . $dbr->addQuotes("%$search%");
		else if ($query == 'language')
			$searchCondition = " HAVING $rowText LIKE " . $dbr->addQuotes("$search%");
		else if ($query == 'relation-type' or
			$query == 'class' or
			$query == 'option-attribute' or
			$query == 'translated-text-attribute' or
			$query == 'text-attribute' or
			$query == 'collection') 
			$searchCondition = " WHERE $rowText LIKE " . $dbr->addQuotes("$search%");
		else	
			$searchCondition = " AND $rowText LIKE " . $dbr->addQuotes("$search%");
	}
	else
		$searchCondition = "";
	
	if ($query == 'transaction')
		$orderBy = 'transaction_id DESC';
	else
		$orderBy = $rowText;
	
	$sql .= $searchCondition . " ORDER BY $orderBy LIMIT ";
	
	if ($offset > 0)
		$sql .= " $offset, ";
		
	$sql .= "10";
	
	# == Actual query here
	//wfdebug("]]]".$sql."\n");
	$queryResult = $dbr->query($sql);
	
	$idAttribute = new Attribute("id", "ID", "id");
	
	# == Process query
	switch($query) {
		case 'relation-type':
			list($recordSet, $editor) = getRelationTypeAsRecordSet($queryResult);
			break;		
		case 'class':
			list($recordSet, $editor) = getClassAsRecordSet($queryResult);
			break;
		case 'text-attribute':
			list($recordSet, $editor) = getTextAttributeAsRecordSet($queryResult);
			break;
		case 'translated-text-attribute':
			list($recordSet, $editor) = getTranslatedTextAttributeAsRecordSet($queryResult);
			break;
		case 'option-attribute':
			list($recordSet, $editor) = getOptionAttributeAsRecordSet($queryResult);
			break;
		case 'url-attribute':
			list($recordSet, $editor) = getURLAttributeAsRecordSet($queryResult);
			break;
		case 'defined-meaning':
			list($recordSet, $editor) = getDefinedMeaningAsRecordSet($queryResult);
			break;
		case 'class-attributes-level':
			list($recordSet, $editor) = getClassAttributeLevelAsRecordSet($queryResult);
			break;				
		case 'collection':
			list($recordSet, $editor) = getCollectionAsRecordSet($queryResult);
			break;	
		case 'language':
			list($recordSet, $editor) = getLanguageAsRecordSet($queryResult);
			break;
		case 'transaction':
			list($recordSet, $editor) = getTransactionAsRecordSet($queryResult);
			break;
	}
	ob_start();
	var_dump($queryResult);
	var_dump($recordSet);
	var_dump($editor);
	wfDebug(ob_get_contents());
	ob_end_clean();

	$output=$editor->view(new IdStack($prefix . 'table'), $recordSet);
	//$output="<table><tr><td>HELLO ERIK!</td></tr></table>";
	//wfDebug($output);
	return $output;
}

# Constructs a new SQL query from 2 other queries such that if a field exists
# in the fallback query, but not in the actual query, the field from the
# fallback query will be returned. Fields not in the fallback are ignored.
# You will need to state which fields in your query need to be returned.
# As a (minor) hack, the 0th element of $fields is assumed to be the key field. 
function constructSQLWithFallback($actual_query, $fallback_query, $fields){

	#if ($actual_query==$fallback_query)
	#	return $actual_query; 

	$sql="SELECT * FROM (SELECT ";

	$sql_with_comma=$sql;
	foreach ($fields as $field) {
		$sql=$sql_with_comma;
		$sql.="COALESCE(actual.$field, fallback.$field) as $field";
		$sql_with_comma=$sql;
		$sql_with_comma.=", ";
	}
		
	$sql.=" FROM ";
	$sql.=	" ( $fallback_query ) AS fallback";
	$sql.=	" LEFT JOIN ";
	$sql.=	" ( $actual_query ) AS actual";
	
	$field0=$fields[0]; # slightly presumptuous
	$sql.=  " ON actual.$field0 = fallback.$field0";
	$sql.= ") as coalesced";
	return $sql;
}

function getSQLToSelectPossibleAttributes($objectId, $attributesLevel, $attributesType) {
	global
		$wgUser;
	
	$sqlActual = getSQLToSelectPossibleAttributesForLanguage($objectId, $attributesLevel, $attributesType, $wgUser->getOption('language'));
	$sqlFallback = getSQLToSelectPossibleAttributesForLanguage($objectId, $attributesLevel, $attributesType, 'en');
	
	return constructSQLWithFallback($sqlActual, $sqlFallback, array("attribute_mid", "spelling"));
}

# language is the 2 letter wikimedia code. use "<ANY>" if you don't want language filtering
# (any does set limit 1 hmph)
function getSQLToSelectPossibleAttributesForLanguage($objectId, $attributesLevel, $attributesType, $language="<ANY>") {
	global $wgDefaultClassMids;
	global $wgUser;
	$dc=wdGetDataSetContext();

	if (count($wgDefaultClassMids) > 0)
		$defaultClassRestriction = " OR {$dc}_class_attributes.class_mid IN (" . join($wgDefaultClassMids, ", ") . ")";
	else
		$defaultClassRestriction = "";

	$dbr =& wfGetDB(DB_SLAVE);
	$sql = 
		'SELECT attribute_mid, spelling' .
		" FROM {$dc}_bootstrapped_defined_meanings, {$dc}_class_attributes, {$dc}_syntrans, {$dc}_expression_ns" .
		" WHERE {$dc}_bootstrapped_defined_meanings.name = " . $dbr->addQuotes($attributesLevel) .
		" AND {$dc}_bootstrapped_defined_meanings.defined_meaning_id = {$dc}_class_attributes.level_mid" .
		" AND {$dc}_class_attributes.attribute_type = " . $dbr->addQuotes($attributesType) .
		" AND {$dc}_syntrans.defined_meaning_id = {$dc}_class_attributes.attribute_mid" .
		" AND {$dc}_expression_ns.expression_id = {$dc}_syntrans.expression_id";

	if ($language!="<ANY>") {
		$sql .=
		' AND language_id=( '. 
				' SELECT language_id'.
				' FROM language'.
				' WHERE wikimedia_key = '. $dbr->addQuotes($language).
				' )';
	}

	$sql .=	
		' AND ' . getLatestTransactionRestriction("{$dc}_class_attributes") .
		' AND ' . getLatestTransactionRestriction("{$dc}_expression_ns") .
		' AND ' . getLatestTransactionRestriction("{$dc}_syntrans") .
		" AND ({$dc}_class_attributes.class_mid IN (" .
				' SELECT class_mid ' .
				" FROM   {$dc}_class_membership" .
				" WHERE  {$dc}_class_membership.class_member_mid = " . $objectId .
				' AND ' . getLatestTransactionRestriction("{$dc}_class_membership") .
				' )'.
				$defaultClassRestriction .
		')';

	//if ($language="<ANY>") {
	//	$sql .=
	//	' LIMIT 1 ';
	//}


	return $sql;
}

function getSQLForCollectionOfType($collectionType, $language="<ANY>") {
	$dc=wdGetDataSetContext();
	$sql="SELECT member_mid, spelling, collection_mid " .
        " FROM {$dc}_collection_contents, {$dc}_collection_ns, {$dc}_syntrans, {$dc}_expression_ns " .
        " WHERE {$dc}_collection_contents.collection_id={$dc}_collection_ns.collection_id " .
        " AND {$dc}_collection_ns.collection_type='$collectionType' " .
        " AND {$dc}_syntrans.defined_meaning_id={$dc}_collection_contents.member_mid " .
        " AND {$dc}_expression_ns.expression_id={$dc}_syntrans.expression_id " .
        " AND {$dc}_syntrans.identical_meaning=1 " .
        " AND " . getLatestTransactionRestriction("{$dc}_syntrans") .
        " AND " . getLatestTransactionRestriction("{$dc}_expression_ns") .
        " AND " . getLatestTransactionRestriction("{$dc}_collection_ns") .
        " AND " . getLatestTransactionRestriction("{$dc}_collection_contents");
	if ($language!="<ANY>") {
		$dbr =& wfGetDB(DB_SLAVE);
		$sql .=
			' AND language_id=( '. 
				' SELECT language_id'.
				' FROM language'.
				' WHERE wikimedia_key = '. $dbr->addQuotes($language).
				' )';
	}
	return $sql;
}

function getSQLForCollection($language="<ANY>") {
	$dc=wdGetDataSetContext();
	$sql = 
		"SELECT collection_id, spelling ".
		" FROM {$dc}_expression_ns, {$dc}_collection_ns, {$dc}_syntrans " .
		" WHERE {$dc}_expression_ns.expression_id={$dc}_syntrans.expression_id" .
		" AND {$dc}_syntrans.defined_meaning_id={$dc}_collection_ns.collection_mid " .
		" AND {$dc}_syntrans.identical_meaning=1" .
		" AND " . getLatestTransactionRestriction("{$dc}_syntrans") .
		" AND " . getLatestTransactionRestriction("{$dc}_expression_ns") .
		" AND " . getLatestTransactionRestriction("{$dc}_collection_ns");
	
	if ($language!="<ANY>") {
		$dbr =& wfGetDB(DB_SLAVE);
		$sql .=
			' AND language_id=( '. 
				' SELECT language_id'.
				' FROM language'.
				' WHERE wikimedia_key = '. $dbr->addQuotes($language).
				' )';
	}

	return $sql;
}

function getSQLForLevels($language="<ANY>") {
	global
		$definedMeaningTable, $expressionTable, $bootstrappedDefinedMeaningsTable, $classAttributeLevels;
	
	// TO DO: Add support for multiple languages here
	return
		selectLatest(
			array($bootstrappedDefinedMeaningsTable->definedMeaningId, $expressionTable->spelling), 
			array($definedMeaningTable, $expressionTable, $bootstrappedDefinedMeaningsTable),
			array(
				'name IN (' . implodeFixed($classAttributeLevels) . ')',
				equals($definedMeaningTable->definedMeaningId, $bootstrappedDefinedMeaningsTable->definedMeaningId),
				equals($definedMeaningTable->expressionId, $expressionTable->expressionId) 
			)
		);
}

function getRelationTypeAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	
	$relationTypeAttribute = new Attribute("relation-type", "Relation type", "short-text");
	$collectionAttribute = new Attribute("collection", "Collection", "short-text");
	
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $relationTypeAttribute, $collectionAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->member_mid, $row->spelling, definedMeaningExpression($row->collection_mid)));			

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($relationTypeAttribute));
	$editor->addEditor(createShortTextViewer($collectionAttribute));
	
	return array($recordSet, $editor);		
}

function getClassAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	$classAttribute = new Attribute("class", "Class", "short-text");
	$collectionAttribute = new Attribute("collection", "Collection", "short-text");
	
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $classAttribute, $collectionAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->member_mid, $row->spelling, definedMeaningExpression($row->collection_mid)));

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($classAttribute));
	$editor->addEditor(createShortTextViewer($collectionAttribute));

	return array($recordSet, $editor);		
}

function getTextAttributeAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	
	$textAttributeAttribute = new Attribute("text-attribute", "Text attribute", "short-text");
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $textAttributeAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->attribute_mid, $row->spelling));

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($textAttributeAttribute));

	return array($recordSet, $editor);		
}

function getURLAttributeAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	
	$urlAttributeAttribute = new Attribute("url-attribute", "URL attribute", "short-text");
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $urlAttributeAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->attribute_mid, $row->spelling));

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($urlAttributeAttribute));

	return array($recordSet, $editor);		
}

function getTranslatedTextAttributeAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	$translatedTextAttributeAttribute = new Attribute("translated-text-attribute", "Translated text attribute", "short-text");
	
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $translatedTextAttributeAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->attribute_mid, $row->spelling));

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($translatedTextAttributeAttribute));

	return array($recordSet, $editor);		
}

function getOptionAttributeAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	
	$optionAttributeAttribute = new Attribute("option-attribute", "Option attribute", "short-text");
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $optionAttributeAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->attribute_mid, $row->spelling));

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($optionAttributeAttribute));

	return array($recordSet, $editor);		
}

function getDefinedMeaningAsRecordSet($queryResult) {
	global
		$idAttribute;

	$dbr =& wfGetDB(DB_SLAVE);
	$spellingAttribute = new Attribute("spelling", "Spelling", "short-text");
	$languageAttribute = new Attribute("language", "Language", "language");
	
	$expressionStructure = new Structure("defined-meaning", $spellingAttribute, $languageAttribute);
	$definedMeaningAttribute = new Attribute(null, "Defined meaning", $expressionStructure);
	$definitionAttribute = new Attribute("definition", "Definition", "definition");
	
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $definedMeaningAttribute, $definitionAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) {
		$definedMeaningRecord = new ArrayRecord($expressionStructure);
		$definedMeaningRecord->setAttributeValue($spellingAttribute, $row->spelling);
		$definedMeaningRecord->setAttributeValue($languageAttribute, $row->language_id);
		
		$recordSet->addRecord(array($row->defined_meaning_id, $definedMeaningRecord, getDefinedMeaningDefinition($row->defined_meaning_id)));
	}			

	$expressionEditor = new RecordTableCellEditor($definedMeaningAttribute);
	$expressionEditor->addEditor(createShortTextViewer($spellingAttribute));
	$expressionEditor->addEditor(createLanguageViewer($languageAttribute));

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor($expressionEditor);
	$editor->addEditor(new TextEditor($definitionAttribute, new SimplePermissionController(false), false, true, 75));

	return array($recordSet, $editor);		
}

function getClassAttributeLevelAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	
	$classAttributeLevelAttribute = new Attribute("class-attribute-level", "Level", "short-text");
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $classAttributeLevelAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->defined_meaning_id, $row->spelling));			

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($classAttributeLevelAttribute));
	
	return array($recordSet, $editor);		
}

function getCollectionAsRecordSet($queryResult) {
	global
		$idAttribute;

	$dbr =& wfGetDB(DB_SLAVE);
	$collectionAttribute = new Attribute("collection", "Collection", "short-text");
	
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $collectionAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->collection_id, $row->spelling));			

	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($collectionAttribute));

	return array($recordSet, $editor);		
}

function getLanguageAsRecordSet($queryResult) {
	global
		$idAttribute;

	$dbr =& wfGetDB(DB_SLAVE);
	$languageAttribute = new Attribute("language", "Language", "short-text");
	
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $languageAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult))  {
		$recordSet->addRecord(array($row->row_id, $row->language_name));			
	}
	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($languageAttribute));

	return array($recordSet, $editor);		
}

function getTransactionAsRecordSet($queryResult) {
	global
		$idAttribute;
	
	$dbr =& wfGetDB(DB_SLAVE);
	
	$userAttribute = new Attribute("user", "User", "short-text");
	$timestampAttribute = new Attribute("timestamp", "Time", "timestamp");
	$summaryAttribute = new Attribute("summary", "Summary", "short-text");
	
	$recordSet = new ArrayRecordSet(new Structure($idAttribute, $userAttribute, $timestampAttribute, $summaryAttribute), new Structure($idAttribute));
	
	while ($row = $dbr->fetchObject($queryResult)) 
		$recordSet->addRecord(array($row->transaction_id, getUserLabel($row->user_id, $row->user_ip), $row->time, $row->comment));			
	
	$editor = createSuggestionsTableViewer(null);
	$editor->addEditor(createShortTextViewer($timestampAttribute));
	$editor->addEditor(createShortTextViewer($idAttribute));
	$editor->addEditor(createShortTextViewer($userAttribute));
	$editor->addEditor(createShortTextViewer($summaryAttribute));

	return array($recordSet, $editor);		
}


