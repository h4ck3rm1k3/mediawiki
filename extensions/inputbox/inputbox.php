<?php

/**
 * This file contains the main include file for the Inputbox extension of 
 * MediaWiki. 
 *
 * Usage: require_once("path/to/inputbox.php" in LocalSettings.php
 *
 * @author Erik Moeller <moeller@scireview.de>
 * @copyright Public domain
 * @license Public domain
 * @package MediaWikiExtensions
 * @version 0.1
 */
 
/**
 * Register the Inputbox extension with MediaWiki
 */ 
$wgExtensionFunctions[] = 'registerInputboxExtension';

/**
 * Sets the tag that this extension looks for and the function by which it
 * operates
 */
function registerInputboxExtension()
{
    global $wgParser;
    $wgParser->setHook('inputbox', 'renderInputbox');
}


/**
 * Renders an inputbox based on information provided by $input.
 */
function renderInputbox($input)
{
	$inputbox=new Inputbox();
	getBoxOption($inputbox->type,$input,"type");
	getBoxOption($inputbox->width,$input,"width",true);	
	getBoxOption($inputbox->preload,$input,"preload");
	getBoxOption($inputbox->editintro,$input,"editintro");
	getBoxOption($inputbox->defaulttext,$input,"default");	
	getBoxOption($inputbox->bgcolor,$input,"bgcolor");
	getBoxOption($inputbox->buttonlabel,$input,"buttonlabel");	
	getBoxOption($inputbox->searchbuttonlabel,$input,"searchbuttonlabel");		
	
	$boxhtml=$inputbox->render();
	if($boxhtml) {
		return $boxhtml;
	} else {
		return "<br /> <font color='red'>Input box '{$inputbox->type}' not defined.</font>";
	}
}


function getBoxOption(&$value,&$input,$name,$isNumber=false) {

	if(preg_match("/^$name\s*=\s*(.*)/mi",$input,$matches)) {
		if($isNumber) {
			$value=intval($matches[1]);
		} else {
			$value=htmlspecialchars($matches[1]);
		}
	}
}

class Inputbox {
	var $type,$width,$preload,$editintro;
	var $defaulttext,$bgcolor,$buttonlabel,$searchbuttonlabel;
	
	function render() {
		if($this->type=="create" || $this->type=="comment") {
			return $this->getCreateForm();		
		} elseif($this->type=="search") {
			return $this->getSearchForm();		
		} else {
			return false;
		}	
	}
	function getSearchForm() {
		global $wgUser;
		
		$sk=$wgUser->getSkin();
		$searchpath = $sk->escapeSearchLink();		
		if(!$this->buttonlabel) {
			$this->buttonlabel = wfMsgHtml( 'tryexact' );
		}
		if(!$this->searchbuttonlabel) {
			$this->searchbuttonlabel = wfMsgHtml( 'searchfulltext' );
		}
		
		$searchform=<<<ENDFORM
<table border="0" width="100%">
<tr>
<td align="center" bgcolor="{$this->bgcolor}" cellspacing="0">
<form name="searchbox" action="$searchpath" class="searchbox">
	<input class="searchboxInput" name="search" type="text"
	value="{$this->defaulttext}" size="{$this->width}"/><br />
	<input type='submit' name="go" class="searchboxGoButton"
	value="{$this->buttonlabel}"
	/>&nbsp;<input type='submit' name="fulltext"
	class="searchboxSearchButton"
	value="{$this->searchbuttonlabel}" />
</form>
</td>
</tr>
</table>
ENDFORM;
		return $searchform;
	}
	
	function getCreateForm() {
		global $wgScript;	
		
		$action = htmlspecialchars( $wgScript );		
		if($this->type=="comment") {
			$comment='<input type="hidden" name="section" value="new">';
			if(!$this->buttonlabel) {
				$this->buttonlabel = wfMsgHtml( "postcomment" );
			}
		} else {
			$comment='';
			if(!$this>buttonlabel) {			
				$this->buttonlabel = wfMsgHtml( "createarticle" );
			}
		}		
		$createform=<<<ENDFORM
<table border="0" width="100%" cellspacing="0">
<tr>
<td align="center" bgcolor="{$this->bgcolor}">
<form name="createbox" action="$action" method="get" class="createbox">
	<input type='hidden' name="action" value="edit">
	<input type="hidden" name="preload" value="{$this->preload}" />
	<input type="hidden" name="editintro" value="{$this->editintro}" />	
	{$comment}
	<input class="createboxInput" name="title" type="text"
	value="{$this->defaulttext}" size="{$this->width}"/><br />		
	<input type='submit' name="create" class="createboxButton"
	value="{$this->buttonlabel}"/>	
</form>
</td>
</tr>
</table>
ENDFORM;
		return $createform;
	}
	
}
