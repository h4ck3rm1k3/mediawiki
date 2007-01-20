<?php

require_once('Wikidata.php');
require_once('Transaction.php');
require_once('Expression.php');
require_once('forms.php');
require_once('Attribute.php');
require_once('type.php');
require_once('languages.php');
require_once('HTMLtable.php');
require_once('WiktionaryZRecordSets.php');
require_once('WiktionaryZEditors.php');

/**
 * Load and modify content in a WiktionaryZ-enabled
 * namespace.
 *
 */
class WiktionaryZ extends DefaultWikidataApplication {
	public function view() {
		global
			$wgOut, $wgTitle;

		parent::view();

		$spelling = $wgTitle->getText();
		
		$wgOut->addHTML(
			getExpressionsEditor($spelling, $this->filterLanguageId, $this->possiblySynonymousRelationTypeId, false, $this->shouldShowAuthorities)->view(
				$this->getIdStack(), 
				getExpressionsRecordSet(
					$spelling, 
					$this->filterLanguageId, 
					$this->possiblySynonymousRelationTypeId, 
					$this->viewQueryTransactionInformation
				)
			)
		);
		
		$wgOut->addHTML(DefaultEditor::getExpansionCss());
		$wgOut->addHTML("<script language='javascript'><!--\nexpandEditors();\n--></script>");
	}

	public function history() {
		global
			$wgOut, $wgTitle;

		parent::history();

		$spelling = $wgTitle->getText();
		
		$wgOut->addHTML(
			getExpressionsEditor($spelling, $this->filterLanguageId, $this->possiblySynonymousRelationTypeId, $this->showRecordLifeSpan, false)->view(
				$this->getIdStack(), 
				getExpressionsRecordSet(
					$spelling, 
					$this->filterLanguageId, 
					$this->possiblySynonymousRelationTypeId, 
					$this->queryTransactionInformation
				)
			)
		);
		
		$wgOut->addHTML(DefaultEditor::getExpansionCss());
		$wgOut->addHTML("<script language='javascript'><!--\nexpandEditors();\n--></script>");
	}

	protected function save($referenceTransaction) {
		global
			$wgTitle;

		parent::save($referenceTransaction);

		$spelling = $wgTitle->getText();
		
		getExpressionsEditor($spelling, $this->filterLanguageId, $this->possiblySynonymousRelationTypeId, false, false)->save(
			$this->getIdStack(), 
			getExpressionsRecordSet(
				$spelling, 
				$this->filterLanguageId, 
				$this->possiblySynonymousRelationTypeId, 
				$referenceTransaction
			)
		);
	}

	public function edit() {
		global
			$wgOut, $wgTitle, $wgUser;

		parent::edit();
		$this->outputEditHeader();

		$spelling = $wgTitle->getText();

		$wgOut->addHTML(
			getExpressionsEditor($spelling, $this->filterLanguageId, $this->possiblySynonymousRelationTypeId, false, false)->edit(
				$this->getIdStack(), 
				getExpressionsRecordSet(
					$spelling, 
					$this->filterLanguageId, 
					$this->possiblySynonymousRelationTypeId, 
					new QueryLatestTransactionInformation()
				)
			)
		);

		$this->outputEditFooter();
	}
	
	public function getTitle() {
		global
			$wgTitle;
			
		return "Disambiguation: " . $wgTitle->getText();
	}
	
	protected function getIdStack() {
		return new IdStack("expression");
	}
}

?>
