<?php

/*
 * Created on May 13, 2007
 *
 * API for MediaWiki 1.8+
 *
 * Copyright (C) 2006 Yuri Astrakhan <Firstname><Lastname>@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if (!defined('MEDIAWIKI')) {
	// Eclipse helper - will be ignored in production
	require_once ("ApiQueryBase.php");
}

/**
 * This query adds the <categories> subelement to all pages with the list of categories the page is in
 *
 * @ingroup API
 */
class ApiQueryCategoryInfo extends ApiQueryBase {

	public function __construct($query, $moduleName) {
		parent :: __construct($query, $moduleName, 'ci');
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$alltitles = $this->getPageSet()->getAllTitlesByNamespace();
		if ( empty( $alltitles[NS_CATEGORY] ) ) {
			return;
		}
		$categories = $alltitles[NS_CATEGORY];

		$titles = $this->getPageSet()->getGoodTitles() +
					$this->getPageSet()->getMissingTitles();
		$cattitles = array();
		foreach($categories as $c)
		{
			$t = $titles[$c];
			$cattitles[$c] = $t->getDBKey();
		}

		$this->addTables(array('category', 'page', 'page_props'));
		$this->addJoinConds(array(
			'page' => array('LEFT JOIN', array(
				'page_namespace' => NS_CATEGORY,
				'page_title=cat_title')),
			'page_props' => array('LEFT JOIN', array(
				'pp_page=page_id',
				'pp_propname' => 'hiddencat')),
		));
		$this->addFields(array('cat_title', 'cat_pages', 'cat_subcats', 'cat_files', 'pp_propname AS cat_hidden'));
		$this->addWhere(array('cat_title' => $cattitles));
		if(!is_null($params['continue']))
		{
			// We need to set a LIMIT in order to be able to set
			// an OFFSET
			$this->addOption('LIMIT', count($titles));
			$this->addOption('OFFSET', $params['continue']);
		}

		$db = $this->getDB();
		$res = $this->select(__METHOD__);

		$catids = array_flip($cattitles);
		$count = (int)@$params['continue'];
		while($row = $db->fetchObject($res))
		{
			$vals = array();
			$vals['size'] = $row->cat_pages;
			$vals['pages'] = $row->cat_pages - $row->cat_subcats - $row->cat_files;
			$vals['files'] = $row->cat_files;
			$vals['subcats'] = $row->cat_subcats;
			if($row->cat_hidden)
				$vals['hidden'] = '';
			$fit = $this->addPageSubItems($catids[$row->cat_title], $vals);
			if(!$fit)
			{
				$this->setContinueEnumParameter('continue', $count);
				break;
			}
			$count++;
		}
		$db->freeResult($res);
	}

	public function getAllowedParams() {
		return array (
			'continue' => null,
		);
	}

	public function getParamDescription() {
		return array (
			'continue' => 'When more results are available, use this to continue',
		);
	}

	public function getDescription() {
		return 'Returns information about the given categories';
	}

	protected function getExamples() {
		return "api.php?action=query&prop=categoryinfo&titles=Category:Foo|Category:Bar";
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}