<?php
/*
 * MV_Index.php Created on May 16, 2007
 *
 * All Metavid Wiki code is Released under the GPL2
 * for more info visit http:/metavid.ucsc.edu/code
 *
 * @author Michael Dale
 * @email dale@ucsc.edu
 * @url http://metavid.ucsc.edu
 *
 */
 /*
  * maintains the index table state on various updates
  * (need for indexed time ranged queries)
  * (currently stored in a mysql table)
  * in the future we should shift this to a search engine of some sort
  * (ie lucene or sphinx) or just do everything with semantic relations
  * if we can get the speed to an acceptable level...
  *
  * keep an additional copy of the text table is not ideal but...
  * we keep a copy of every version of the page so its not so bad ;P
  */
if ( !defined( 'MEDIAWIKI' ) )  die( 1 );

 class MV_Index {
	var $mvTitle =null;
	function __construct(& $mvTitle=null){
		if($mvTitle!=null)
			$this->mvTitle=$mvTitle;
	}
	/*
	 * grabs near options count
	 * (similar to getNearStreams but returns a count instead of a result set)
	 *
	 * @params:
	 * $range_offset
	 *
	 * options:
	 * limit_by_current_type *default ...only include meta chunks of the current mvTitle type
	 * limit_by_type = $type	...include stream of given $type
	 * all_in_rage		*default...include all meta that have any portion in range
	 * contained_in_range		...include only meta that are contained in the given range
	 * start_or_end_in_range	...include only meta that start or end in the given range
	 */
	/*function getNearCount($range_offset='', $options=array()){
		global $mvDefaultClipLength, $mvIndexTableName;
		if($range_offset=='')$range_offset=$mvDefaultClipLength;
		$dbr =& wfGetDB(DB_SLAVE);
		//set up the count sql query:
		$sql = "SELECT COUNT(1) as `count` FROM {$dbr->tableName($mvIndexTableName)} " .
			   "WHERE `stream_id`={$this->mvTitle->getStreamId()} ";
		if(isset($options['limit_by_type'])){
			$sql.="AND `mvd_type`='{$options[limit_by_type]}' ";
		}else{
			$sql.="AND `mvd_type`='{$this->mvTitle->getMvdTypeKey()}' ";
		}
		$st = $this->mvTitle->getStartTimeSeconds() - $range_offset;
		if($st<0)$st=0;
		//if end time not present use startTime
		if($this->mvTitle->getStartTimeSeconds()){
			$et = $this->mvTitle->getStartTimeSeconds() + $range_offset;
		}else{
			$et = $this->mvTitle->getStartTimeSeconds() + $range_offset;
		}
		//make sure we don't exceed the stream duration
		if($et > $this->mvTitle->getDuration())$et=$this->mvTitle->getDuration();
		//default all in range:
		$sql.=' AND ( ';
			// double or set for null or non-null end time range queries:
			$sql.=" (`end_time` IS NULL
						AND `start_time` > {$st}
						AND `start_time` < {$et}
					)
					 OR
				 	(`end_time` IS NOT NULL
				  	AND `end_time` > {$st}
					AND `start_time` < {$et}
					)
				)";
		$result = $dbr->query($sql, 'MV_Index:getNearCount');
		$row = $dbr->fetchObject( $result );
		//print_r($row);
		return $row->count;
	}*/
	function countMVDInRange($stream_id, $start_time=null, $end_time=null, $mvd_type='all'){
		global $mvDefaultClipLength;
		$dbr =& wfGetDB(DB_SLAVE);

		$cond=array('stream_id'=>$stream_id);
		if($end_time)
			$cond[]='AND start_time <= '. $dbr->addQuotes($end_time);
		if($start_time)
			$cond[]='AND end_time >= '. $dbr->addQuotes($start_time);
		return $dbr->selectField('mv_mvd_index', 'COUNT(*)', $cond,  __METHOD__ );
	}
	/*
	 * getMVDInRange returns the mvd titles that are in the given range
	 * param list got kind of crazy long... @@todo re-factor int a request object or something cleaner
	 */
	function getMVDInRange($stream_id, $start_time=null, $end_time=null, $mvd_type='all',$getText=false,$smw_properties='', $limit='200'){
		global $mvIndexTableName, $mvDefaultClipLength;
		$dbr =& wfGetDB(DB_SLAVE);
		//set up select vars:
		$conds=$options=$vars=array();
		$from_tables ='';
		//
		//set mvd_type if empty:
		if($mvd_type==null)$mvd_type='all';
		if($start_time<0)$start_time=0;
		//add base select vars:
		$vars= array('mv_page_id as id', 'mvd_type', 'wiki_title', 'stream_id', 'start_time', 'end_time');
		//add in base from:
		$from_tables.= $dbr->tableName('mv_mvd_index');
		if($smw_properties!=''){
			if(!isset($options['STRAIGHT_JOIN']))
				$options['STRAIGHT_JOIN']='';
			if(!is_array($smw_properties))
				$smw_properties = explode(',',$smw_properties);
			foreach($smw_properties as $prop_name){
				$vars[] = mysql_real_escape_string($prop_name).'.object_title as '.mysql_real_escape_string($prop_name);
				$from_tables.=' LEFT JOIN '. $dbr->tableName('smw_relations') .
					' as ' . mysql_real_escape_string($prop_name) .
					' ON (' . $dbr->tableName('mv_mvd_index') . '.mv_page_id'.
					' = ' . mysql_real_escape_string($prop_name) . '.subject_id'.
					' AND '. mysql_real_escape_string($prop_name).'.relation_title'.
					' = ' . $dbr->addQuotes($prop_name) . ')';
			}
		}
		$conds = array('stream_id'=>$stream_id);
		if($mvd_type!='all'){
			$mvd_type=(is_object($mvd_type))?get_object_vars($mvd_type):$mvd_type;
			if(is_array($mvd_type)){
				$mvd_type_cond = $or='';			
				foreach($mvd_type as $mtype){
					//@@todo confirm its a valid mvd_type:
					$mvd_type_cond.= $or."mvd_type=".$dbr->addQuotes($mtype);
					$or=' OR ';
				}
				$conds[] = $mvd_type_cond;
			}else if(is_string($mvd_type)){
				$conds['mvd_type']=$mvd_type;
			}

		}
		if($end_time)
			$conds[]='start_time <= '. $dbr->addQuotes($end_time);
		if($start_time)
			$conds[]='end_time >= '. $dbr->addQuotes($start_time);

		//add in ordering
		$options['ORDER BY']= 'start_time ASC';
		//add in limit
		$options['LIMIT'] = $limit;
		//run query:
		$result = $dbr->select( $from_tables,
			$vars,
			$conds,
			__METHOD__,
			$options);
		//print $dbr->lastQuery();
		//die;
		//echo $sql;
		//$result =& $dbr->query( $sql, 'MV_Index:time_index_query');
		return $result;
	}
	/*@@todo figure another way to get at this data...this is not a very fast query: */
	function getMVDTypeInRange($stream_id, $start_time=null, $end_time=null){
		$dbr =& wfGetDB(DB_SLAVE);
		//init vars
		$from_tables=$vars=	$conds= $options=array();

		$from_tables = $dbr->tableName('mv_mvd_index');
		$vars = 'COUNT(*) as count, mvd_type';
		$conds = array('stream_id'=>$stream_id);
		if($end_time)
			$cond[] = ' AND start_time <= '. $dbr->addQuotes($end_time);
		if($start_time)
			$cond[] = ' AND end_time >= '. $dbr->addQuotes($start_time);
		$options['GROUP BY'] =	'mvd_type';
		return $dbr->select( $from_tables,
			$vars,
			$conds,
			__METHOD__,
			$options);	;
	}
	function remove_by_stream_id($stream_id){
		global $mvIndexTableName;
		$dbw =& wfGetDB(DB_WRITE);
		$dbw->delete($mvIndexTableName, array('stream_id'=>$stream_id));
	}
	/*
	 * removes a single entry by wiki_title name
	 */
	function remove_by_wiki_title($wiki_title){
		global $mvIndexTableName;
		$dbw =& wfGetDB(DB_WRITE);
		$dbw->delete($mvIndexTableName, array('wiki_title'=>$wiki_title));
		//print "ran sql:" . $dbw->lastQuery() . "\n";
		return true;
	}
	/*function doUnifiedFiltersQuery(&$filters, $metaDataIncludes=null){
		global $wgRequest, $mvDo_SQL_CALC_FOUND_ROWS, $mvMediaSearchResultsLimit;
		$dbr =& wfGetDB(DB_SLAVE);
		//init vars
		$from_tables='';
		$vars=	$conds= $options=array();

		//set options
		if( $mvDo_SQL_CALC_FOUND_ROWS)
			$options['SQL_CALC_FOUND_ROWS']=true;
		//set limit offset:
		list( $this->limit, $this->offset ) = $wgRequest->getLimitOffset( 20, 'searchlimit' );
		if($this->limit > $mvMediaSearchResultsLimit)$this->limit = $mvMediaSearchResultsLimit;
		$this->order = strtolower($wgRequest->getVal('order'));
		//force order type:
		if( !($this->order=='relevant' || $this->order=='recent' || $this->order=='viewed') )$this->order='relevant';

		foreach($filters as $f){
			$cur_cond=$aon=$asql='';
			//proocc and or for fulltext:
			if(!isset($f['a']))$f['a']='and';
			switch($f['a']){
				case 'and':$aon='+';$asql='AND';break;
				case 'or':$aon='';$asql='OR';break;
				case 'not':$aon='-';$asql='NOT';break;
			}

		}


		$result = $dbr->select( $from_tables,
			$vars,
			$conds,
			__METHOD__,
			$options);
	}*/

	function doUnifiedFiltersQuery(&$filters, $metaDataIncludes=null){
		global $mvIndexTableName,$mvStreamFilesTable, $mvDefaultClipLength,$mvStreamTable,
		 $wgRequest, $mvDo_SQL_CALC_FOUND_ROWS, $mvMediaSearchResultsLimit;

		global $mvSpokenByInSearchResult, $mvCategoryInSearchResult, $mvBillInSearchResult;

		//init vars
		$from_tables='';
		$vars=	$conds= $options=array();
		//init top range generation query
		$from_tables_top='';
		$vars_top=	$conds_top= $options_top=array();

		$do_top_range_query = false;
	

		$dbr =& wfGetDB(DB_SLAVE);
		//organize the queries (group full-text searches and category/attributes)
		//if the attribute is not a numerical just add it to the fulltext query
		$ftq_match_asql=$last_person_aon=$ftq_match=$ftq=$snq=$toplq=$toplq_cat=$date_range_join=$date_range_where=$asql=''; //top query and full text query =''
		if($filters=='')return array();
		$ftq_match_asql=$date_cond='';
		
		$date_range_join=true;

		//$selOpt = ($mvDo_SQL_CALC_FOUND_ROWS)?'SQL_CALC_FOUND_ROWS':'';
		if( $mvDo_SQL_CALC_FOUND_ROWS)
			$options[]='SQL_CALC_FOUND_ROWS';

		//set limit offset:
		list( $this->limit, $this->offset ) = $wgRequest->getLimitOffset( 20, 'searchlimit' );
		if($this->limit > $mvMediaSearchResultsLimit)$this->limit = $mvMediaSearchResultsLimit;

		$this->order = strtolower($wgRequest->getVal('order'));
		//force order type:
		if( !($this->order=='relevant' || $this->order=='recent' || $this->order=='viewed') )$this->order='relevant';


		$group_spoken=true;
		//$categoryTable =  ;
		$valid_filter_count=0;
		foreach($filters as $f){
			//proocc and or for fulltext:
			if(!isset($f['a']))$f['a']='and';
			switch($f['a']){
				case 'and':$aon='+';$asql='AND';break;
				case 'or':$aon='';$asql='OR';break;
				case 'not':$aon='-';$asql='NOT';break;
			}
			//add to the fulltext query:
			switch($f['t']){
				case 'spoken_by':
					//skip if empty value:
					if(trim($f['v'])=='')continue;
					//if we have an OR set prev to OR
					if($last_person_aon=='+' && $aon==''){
						$ftq=str_replace('+"spoken by', '"spoken by', $ftq);
						$group_spoken=false;
					}
					//full text based semantic query:
					$ftq.=' '.$aon.'"spoken by '. mysql_real_escape_string($f['v']).'" ';
					//table based query:
					$last_person_aon=$aon;
					$valid_filter_count++;
					//$conds[]=
				break;
				case 'match':
					//skip if empty value:
					if(trim($f['v'])=='')continue;
					$ftq_match.=$aon.'"'.mysql_real_escape_string($f['v']).'"';
					//only need to split out ftq match if spoken by is more than one
					if($ftq_match_asql!='')
						$ftq_match_asql = $asql;
					$valid_filter_count++;
				break;
				//top level queries  (sets up time ranges )
				case 'category':
					//skip if empty value:
					if(trim($f['v'])=='')continue;
					$do_top_range_query=true;
					//full text based category query:
					$toplq.=' '.$aon.'"category '.mysql_real_escape_string($f['v']).'" ';
					//$ftq.=' '.$aon.'category:'.mysql_escape_string($f['v']);

					//table based query:
					switch($f['a']){
						case 'and':$toplq_cat='AND';break;
						case 'or':$toplq_cat='OR';break;
						case 'not':$toplq_cat='NOT';break;
					}
					$toplq_cat.=$dbr->tableName( 'categorylinks').'.cl_to='.$dbr->addQuotes($f['v']);
					$valid_filter_count++;
				break;
				case 'date_range':					
					list($month, $day, $year) = explode('/',$f['vs']);
					$sts = mktime(0,0,0,$month, $day, $year);
					list($month, $day, $year) = explode('/',$f['ve']);
					$ets = mktime(0,0,0,$month, $day+1, $year); //(the start of the next day)
					//add date condtion:
					//note dissable and or for date range for now: $asql
					$conds[] = ' ( `mv_streams`.`date_start_time` > '
														. $dbr->addQuotes($sts) .
												 ' AND `mv_streams`.`date_start_time` < '. $dbr->addQuotes($ets) .
										') ';
					//print	$date_cond;
					$valid_filter_count++;
				break;
				case 'stream_name':
					//skip if empty value:
					if(trim($f['v'])=='')continue;
					$stream =& mvGetMVStream($f['v']);
					//add stream cond
					$conds[]=$asql." stream_id = ". $dbr->addQuotes($stream->getStreamId());
					$valid_filter_count++;
				break;
				case 'smw_property':
					//more complicated query work needed
				break;
			}
		}
		if($valid_filter_count==0){
			return array();
		}
		//add the top query to the base query:
		$ftq.=$toplq;
		$vars = "mv_page_id as id,". $dbr->tableName('mv_mvd_index').'.stream_id,
			('.$dbr->tableName('mv_streams').'.date_start_time+'.$dbr->tableName('mv_mvd_index').
			'.start_time) AS mvd_date_start_time, '.
			'start_time, end_time, view_count, wiki_title,'.
			$dbr->tableName( 'searchindex' ).'.si_text AS `text` ';

		if($mvSpokenByInSearchResult)
			$vars.=', smw_relations.object_title as spoken_by ';

		$from_tables.=$dbr->tableName('mv_mvd_index') . ' ';
		$from_tables.='JOIN '.$dbr->tableName( 'searchindex' ).
				' ON (' .
					 $dbr->tableName('mv_mvd_index').'.mv_page_id = '.
					 $dbr->tableName( 'searchindex' ).'.si_page ) ';

		if($date_range_join)
			$from_tables.='LEFT JOIN '. $dbr->tableName('mv_streams').
				' ON (' .
					$dbr->tableName('mv_mvd_index').'.stream_id = ' .
					$dbr->tableName('mv_streams').'.id ) ';

		//print "FROM TABLES:". $from_tables;

		//restrict to streams with valid $mvDefaultVideoQualityKey files:
		global $mvDefaultVideoQualityKey;
		$from_tables.= 'JOIN '. $dbr->tableName('mv_stream_files').
				' ON ' .
				'( '.$dbr->tableName('mv_mvd_index').'.stream_id = '.
					$dbr->tableName('mv_stream_files').'.stream_id '.
					' AND '. $dbr->tableName('mv_stream_files').'.file_desc_msg = '.
					$dbr->addQuotes($mvDefaultVideoQualityKey).
				') ';

		//date range join:

		//include spoken by relation in results (LEFT JOIN should not be *that* costly )
		if($mvSpokenByInSearchResult){
			/*$sql.="LEFT JOIN `smw_relations` ON (`mv_mvd_index`.`mv_page_id`=`smw_relations`.`subject_id` " .
				"AND `smw_relations`.`relation_title`='Spoken_By') ";*/
			$from_tables.='LEFT JOIN '.$dbr->tableName('smw_relations').
				' ON ' .
				'( '.$dbr->tableName('mv_mvd_index').'.mv_page_id = '.
					$dbr->tableName('smw_relations').'.subject_id '.
					' AND '.$dbr->tableName('smw_relations').'.relation_title = \'Spoken_By\''.
				 ') ';
		}
		//$sql.="WHERE ";
		//$sql.=" ( `{$mvIndexTableName}`.`mvd_type`='ht_en' OR  `{$mvIndexTableName}`.`mvd_type`='anno_en') AND" ;

		//add conditions to last condition element (cuz we have to manually mannage and or):

		$conds[count($conds)]= ' '.$dbr->tableName('mv_mvd_index') . '.mvd_type = \'ht_en\' '.
					' OR '. $dbr->tableName('mv_mvd_index') . '.mvd_type=\'anno_en\'  ';

		//limit to ht_en & anno_en (for now) (future allow selection
		//$conds_inx = (count($conds)==0)?0:count($conds)-1;
		$two_part_anor='';
		if($group_spoken){
			$ftq.=$ftq_match;
		}else{
			if($ftq_match!=''){
				$conds[].=$ftq_match_asql.' MATCH ( '. $dbr->tableName( 'searchindex' ).'.si_text )'.
					  ' AGAINST(\''.$ftq_match.'\' IN BOOLEAN MODE) ';
				//if($ftq!='')$sql.=' AND ';
			}
		}
		if($ftq!=''){
			$conds[].="	MATCH ( ".$dbr->tableName( 'searchindex' ).'.si_text ) '.
				' AGAINST(\''.$ftq.'\' IN BOOLEAN MODE) ';
		}
		//print_r($conds);
		//die;
		//date range stuff is SLOW when its the only filter (pulls up matches for everything)
		/*if($snq!='' || $ftq!='' && isset($date_range_andor))
			$sql.=$date_range_andor;
		$sql.=" $date_range_where ";*/

		switch($this->order){
			case 'relevant':
				//@@todo need to add in some relevence metrics
			break;
			case 'recent':
				$options['ORDER BY']='mvd_date_start_time DESC ';
			break;
			case 'viewed':
				$options['ORDER BY']='view_count DESC ';
			break;
		}
		//echo $this->order;
		//$sql.="LIMIT {$this->offset}, {$this->limit} ";
		$options['LIMIT']=$this->limit;
		$options['OFFSET']=$this->offset;

		$result = $dbr->select( $from_tables,
			$vars,
			$conds,
			__METHOD__,
			$options);

		//echo "SQL:".$dbr->lastQuery($result)." \n";
		//die;
		//$result = $dbr->query($sql,  'MV_Index:doFiltersQuery_base');

		$this->numResults=$dbr->numRows($result);
		if($dbr->numRows($result)==0) return array();

		if($mvDo_SQL_CALC_FOUND_ROWS){
			$resFound = $dbr->query('SELECT FOUND_ROWS() as `count`;');
			$found = $dbr->fetchObject( $resFound );
			$this->numResultsFound = $found->count;
		}else{
			$this->numResultsFound =null;
		}

		//@@TODO hide empty categories (if limit > rows found )
		//while($row = $dbr->fetchObject( $result )){
		//	$ret_ary[]=$row;
		//}
		//return $ret_ary;
		//group by stream_name & time range:

		//init ret_ary & stream_group
		$ret_ary = $stream_groups = array();
		while($row = $dbr->fetchObject( $result )){
		 	$ret_ary[$row->id]=$row;
			if(!isset($stream_groups[$row->stream_id])){
				$stream_groups[$row->stream_id]=array();
			}
			if(count($stream_groups[$row->stream_id])==0){
				$new_srange = array('s'=>$row->start_time,
									'e'=> $row->end_time,
									'rows'=>array($row));
				$stream_groups[$row->stream_id][]=$new_srange;
			}else{
				MV_Index::insert_merge_range($stream_groups[$row->stream_id], $stream_groups, $row);
			}
		}
		//print "<pre>";
		//print_r($ret_ary);
		//die;
		//throw out empty top level ranges
		/*foreach($ret_ary as &$stream_set){
			foreach($stream_set as $k=> &$srange){
				if(count($srange['rows'])==0){
					//print "throw out: ". $srange['s'] . $srange['e'];
					unset($stream_set[$k]);
				}
			}
		}*/
		//do category & bill lookup for search result ranges
		if($mvCategoryInSearchResult || $mvBillInSearchResult){
			$from_tables=$conds='';
			$vars= $options=array();

			//set up selected vars:
			$vars[]=$dbr->tableName('categorylinks').'.cl_to';
			$vars[]=$dbr->tableName('mv_mvd_index').'.stream_id';
			$vars[]=$dbr->tableName('mv_mvd_index').'.start_time';
			$vars[]=$dbr->tableName('mv_mvd_index').'.end_time';
			$vars[]=$dbr->tableName('smw_relations').'.object_title as bill_to ';

			//set up from_tables
			$from_tables.=$dbr->tableName('mv_mvd_index') .
				' LEFT JOIN '.$dbr->tableName('categorylinks') .
					' ON ( ' .
						$dbr->tableName('mv_mvd_index').'.mv_page_id = ' .
						$dbr->tableName('categorylinks').'.cl_from ' .
					' ) ' .
				' LEFT JOIN '. $dbr->tableName('smw_relations') .
					' ON ( ' .
							$dbr->tableName('mv_mvd_index').'.mv_page_id = ' .
							$dbr->tableName('smw_relations').'.subject_id '.
						' AND ' .
							$dbr->tableName('smw_relations').'.relation_title=\'bill\' '.
					' ) ';
			//generate stream_id, range sets
			$conds.= $dbr->tableName('mv_mvd_index').'.mvd_type=\'Anno_en\' ' .
				' AND (';
				$or='';
				foreach($stream_groups as $stream_id=>$rangeSet){
					foreach($rangeSet as $range){
						$conds.= $or . ' ( '.$dbr->tableName('mv_mvd_index').'.stream_id ='.
											$dbr->addQuotes($stream_id);
						$conds.=" AND `start_time` <= " . $dbr->addQuotes($range['s'])."";
						$conds.=" AND `end_time` >= " . $dbr->addQuotes($range['e']) . ' ) ';
						$or = ' OR ';

					}
				}
			$conds.=' ) ';
			$options['LIMIT']=200;

			$result = $dbr->select( $from_tables,
				$vars,
				$conds,
				__METHOD__,
				$options);
			//print "BEFORE merge: ";
			//print_r($ret_ary);
			//print_r($stream_groups);

			//merge category info back into base results:
			//$result = $dbr->query($sql,  'MV_Index:doCategorySearchResult');
			while($cl_row = $dbr->fetchObject( $result )){
				//print_r($cl_row);
				foreach($stream_groups[$cl_row->stream_id] as &$range){
					foreach($range['rows'] as &$result_row){
						if($result_row->start_time <= $cl_row->end_time &&
						  $result_row->end_time >= $cl_row->start_time){
						 	//print "result row:"; print_r($result_row);
						 	if(isset($cl_row->cl_to) && isset($ret_ary[$result_row->id]))
								$ret_ary[$result_row->id]->categories[$cl_row->cl_to]=true;
							if(isset($cl_row->bill_to) && isset($ret_ary[$result_row->id]))
								$ret_ary[$result_row->id]->bills[$cl_row->bill_to]=true;
					 	}
					}
				}
			}
			//print "AFTER MERGE: ";
			//print_r($ret_ary);
		}
		//print "<pre>";
		//print_r($ret_ary);
		//die;
		return $ret_ary;
	}
	/* do filter query with stream grouping */
	/*function doFiltersQuery(&$filters, $metaDataIncludes=null){
		global $mvIndexTableName,$mvStreamFilesTable, $mvDefaultClipLength,
		 $wgRequest, $mvDo_SQL_CALC_FOUND_ROWS, $mvMediaSearchResultsLimit;

		global $mvSpokenByInSearchResult, $mvCategoryInSearchResult, $mvBillInSearchResult;

		$dbr =& wfGetDB(DB_SLAVE);
		//organize the queries (group full-text searches and category/attributes)
		//if the attribute is not a numerical just add it to the fulltext query
		$ftq_match_asql=$last_person_aon=$ftq_match=$ftq=$snq=$toplq_cat=$date_range_join=$date_range_where=$asql=''; //top query and full text query =''
		if($filters=='')return array();

		$selOpt = ($mvDo_SQL_CALC_FOUND_ROWS)?'SQL_CALC_FOUND_ROWS':'';

		list( $this->limit, $this->offset ) = $wgRequest->getLimitOffset( 20, 'searchlimit' );
		if($this->limit > $mvMediaSearchResultsLimit)$this->limit = $mvMediaSearchResultsLimit;

		$group_spoken=true;
		$categoryTable =  $dbr->tableName( 'categorylinks');
		foreach($filters as $f){
			//proocc and or for fulltext:
			if(!isset($f['a']))$f['a']='and';
			switch($f['a']){
				case 'and':$aon='+';$asql='AND';break;
				case 'or':$aon='';$asql='OR';break;
				case 'not':$aon='-';$asql='NOT';break;
			}
			//add to the fulltext query:
			switch($f['t']){
				case 'spoken_by':
					//if we have an OR set prev to OR
					if($last_person_aon=='+' && $aon==''){
						$ftq=str_replace('+"spoken by', '"spoken by', $ftq);
						$group_spoken=false;
					}
					//full text based semantic query:
					$ftq.=' '.$aon.'"spoken by '.mysql_escape_string($f['v']).'"';
					//table based query:
					$last_person_aon=$aon;
				break;
				case 'match':
					$ftq_match.=' '.$aon.'"'.mysql_escape_string($f['v']).'"';
					//only need to split out ftq match if spoken by is more than one
					if($ftq_match_asql!='')
						$ftq_match_asql = $asql;
				break;
				//top level queries  (sets up time ranges )
				case 'category':
					//full text based category query:
					$toplq.=' '.$aon.'"category '.mysql_escape_string($f['v']).'" ';
					//$ftq.=' '.$aon.'category:'.mysql_escape_string($f['v']);

					//table based query:
					switch($f['a']){
						case 'and':$toplq_cat='AND';break;
						case 'or':$toplq_cat='OR';break;
						case 'not':$toplq_cat='NOT';break;
					}
					$toplq_cat.=" $categoryTable.`cl_to`='".mysql_escape_string($f['v'])."'";
				break;
				case 'date_range':
					$date_range_join = ' JOIN  `mv_streams` ' .
							'ON `'.$mvIndexTableName.'`.`stream_id` =`mv_streams`.`id` ';

					list($month, $day, $year) = explode('/',$f['vs']);
					$sts = mktime(0,0,0,$month, $day, $year);
					list($month, $day, $year) = explode('/',$f['ve']);
					$ets = mktime(0,0,0,$month, $day+1, $year); //(the start of the next day)
					$date_range_where.= '( `mv_streams`.`date_start_time` > '
														. mysql_escape_string($sts) .
												 ' AND `mv_streams`.`date_start_time` < '. mysql_escape_string($ets) .
												 ')';
					$date_range_andor = ' '.$asql.' ';
				break;
				case 'stream_name':
					if($snq!=''){
						switch($f['a']){
							case 'and':$snq='AND';break;
							case 'or':$snq='OR';break;
							case 'not':$snq='NOT';break;
						}
					}
					//get stream name:
					//print "f: " . $f['v'];
					$stream =& mvGetMVStream($f['v']);
					$snq.=" `stream_id` = {$stream->getStreamId()} ";
				break;
				case 'smw_property':
					//more complicated query work needed ;)
				break;
			}
		}
		$searchindexTable = $dbr->tableName( 'searchindex' );
		$ret_ary = array();
		//a join operation to restrict search results to streams with files
		$join_streams_with_low_ogg_sql = "JOIN `$mvStreamFilesTable` ON (`$mvIndexTableName`.`stream_id` = `$mvStreamFilesTable`.`stream_id` AND `$mvStreamFilesTable`.`file_desc_msg`='mv_ogg_low_quality') ";
		//only run the top range query if we have no secondary query
		if($toplq_cat!='' && $ftq==''){
			//@@todo unify top query with rannged query ... kind of tricky

			//@@todo we should only look in annotative layer for top level queries? ...
			//@@todo paging for top level queries? ... 200 hit limit is probably ok

			$sql = "SELECT `mv_page_id` as `id`, `$mvIndexTableName`.`stream_id`,`$mvIndexTableName`.`start_time`,`$mvIndexTableName`.`end_time`,`$mvIndexTableName`.`view_count`, `wiki_title`, $searchindexTable.`si_text` as `text`
				FROM `$mvIndexTableName`
				$date_range_join
				JOIN $categoryTable ON `$mvIndexTableName`.`mv_page_id` = $categoryTable.`cl_from`
				$join_streams_with_low_ogg_sql
				LEFT JOIN $searchindexTable ON `$mvIndexTableName`.`mv_page_id` = $searchindexTable.`si_page`
				WHERE
				`mvd_type`='Anno_en' " .
				" $toplq_cat " .
				" $snq " .
				"$date_range_andor $date_range_where " .
				"LIMIT 0, 200";
			//echo "topQ: $sql \n\n";
			$top_result = $dbr->query($sql, 'MV_Index:doFiltersQuery_topQ');
			if($dbr->numRows($top_result)==0)return array();
			//set up ranges sql query
			$sql="SELECT $selOpt `mv_page_id` as `id`, `$mvIndexTableName`.`stream_id`,`start_time`,`end_time`,`view_count`, `wiki_title`, $searchindexTable.`si_text` as `text` ";
				if($mvSpokenByInSearchResult)$sql.=",`smw_relations`.`object_title` as `spoken_by` ";
				$sql.="FROM `$mvIndexTableName` " .
				$join_streams_with_low_ogg_sql .
				"JOIN $searchindexTable ON `$mvIndexTableName`.`mv_page_id` = $searchindexTable.`si_page` ";
				if($mvSpokenByInSearchResult){
					$sql.="LEFT JOIN `smw_relations` ON (`mv_mvd_index`.`mv_page_id`=`smw_relations`.`subject_id` " .
						"AND `smw_relations`.`relation_title`='Spoken_By') ";
				}
				$sql.="WHERE  ";
			$or='';
			$sql.='( ';
			while($row = $dbr->fetchObject( $top_result )){
				//also set initial sranges:
				if(!isset($ret_ary[$row->stream_id]))$ret_ary[$row->stream_id]=array();
				//insert into return ary:
				$insertRow = ($ftq=='')?true:false;
				//add that its a top level query to the row:
				$row->toplq=true;
				MV_Index::insert_merge_range($ret_ary[$row->stream_id], $ret_ary, $row, $insertRow);

				$sql.=$or. " (`$mvIndexTableName`.`stream_id`='{$row->stream_id}' AND " .
						'`start_time`>='.$row->start_time.' AND '.
						'`end_time`<='.$row->end_time.' ) ';
				$or=' OR ';
			}
			$sql.=') ';
			//if($ftq!='')
			//	$sql.=" AND MATCH (text)
			//		AGAINST('$ftq' IN BOOLEAN MODE) ";
			$sql.="LIMIT {$this->offset}, {$this->limit} ";
		}else{
			//add the top query to the base query:
			$ftq.=$toplq;
			$sql = "SELECT $selOpt `mv_page_id` as `id`,`$mvIndexTableName`.`stream_id`,`start_time`,`end_time`, `view_count`,`wiki_title`, $searchindexTable.`si_text` AS `text` ";
			if($mvSpokenByInSearchResult)$sql.=",`smw_relations`.`object_title` as `spoken_by` ";
			$sql.="FROM `$mvIndexTableName`
				JOIN $searchindexTable ON `$mvIndexTableName`.`mv_page_id` = $searchindexTable.`si_page`
				$join_streams_with_low_ogg_sql
				$date_range_join ";

			//include spoken by relation in results (LEFT JOIN should not be *that* costly )
			if($mvSpokenByInSearchResult){
				$sql.="LEFT JOIN `smw_relations` ON (`mv_mvd_index`.`mv_page_id`=`smw_relations`.`subject_id` " .
					"AND `smw_relations`.`relation_title`='Spoken_By') ";
			}
			$sql.="WHERE ";
			$sql.=" ( `{$mvIndexTableName}`.`mvd_type`='ht_en' OR  `{$mvIndexTableName}`.`mvd_type`='anno_en') AND" ;
			$sql.=" $snq ";
			//limit to ht_en & anno_en (for now)

			$two_part_anor='';
			if($group_spoken){
				$ftq.=$ftq_match;
			}else{
				if($ftq_match_asql)$sql.=' '.$ftq_match_asql.' ';
				if($ftq_match!=''){
					$sql.="	MATCH ( $searchindexTable.`si_text` )
							AGAINST('$ftq_match' IN BOOLEAN MODE) ";
					if($ftq!='')$sql.=' AND ';
				}
			}
			if($ftq!=''){
				$sql.="	MATCH ( $searchindexTable.`si_text` )
					AGAINST('$ftq' IN BOOLEAN MODE) ";
			}
			//date range stuff is SLOW when its the only filter (pulls up matches for everything)
			if($snq!='' || $ftq!='' && isset($date_range_andor))
				$sql.=$date_range_andor;
			$sql.=" $date_range_where ";
			$sql.="LIMIT {$this->offset}, {$this->limit} ";
		}
		//echo "SQL:".$sql." \n";
		//die;
		$result = $dbr->query($sql,  'MV_Index:doFiltersQuery_base');

		$this->numResults=$dbr->numRows($result);
		if($dbr->numRows($result)==0) return array();

		if($mvDo_SQL_CALC_FOUND_ROWS){
			$resFound = $dbr->query('SELECT FOUND_ROWS() as `count`;');
			$found = $dbr->fetchObject( $resFound );
			$this->numResultsFound = $found->count;
		}else{
			$this->numResultsFound =null;
		}


		//@@TODO hide empty categories (if limit > rows found )

		//group by time range in a given stream

		//while($row = $dbr->fetchObject( $result )){
		//	$ret_ary[]=$row;
		//}
		//return $ret_ary;
		//group by stream_name & time range:

		//@@todo we should deprecate stream grouping
		while($row = $dbr->fetchObject( $result )){
			if(!isset($ret_ary[$row->stream_id])){
				$ret_ary[$row->stream_id]=array();
			}
			if(count($ret_ary[$row->stream_id])==0){
				$new_srange = array('s'=>$row->start_time,
									'e'=> $row->end_time,
									'rows'=>array($row));
				$ret_ary[$row->stream_id][]=$new_srange;
			}else{
				MV_Index::insert_merge_range($ret_ary[$row->stream_id], $ret_ary, $row);
			}
		}
		//throw out empty top level ranges
		foreach($ret_ary as &$stream_set){
			foreach($stream_set as $k=> &$srange){
				if(count($srange['rows'])==0){
					//print "throw out: ". $srange['s'] . $srange['e'];
					unset($stream_set[$k]);
				}
			}
		}
		//do category & bill lookup for search result ranges
		if($mvCategoryInSearchResult || $mvBillInSearchResult){
			$sql="SELECT {$dbr->tableName('categorylinks')}.`cl_to`, `mv_mvd_index`.`stream_id`,
						  `$mvIndexTableName`.`start_time`,
						  `$mvIndexTableName`.`end_time`,
						  {$dbr->tableName('smw_relations')}.`object_title` as bill_to";
			$sql.=" FROM `$mvIndexTableName`
				   LEFT JOIN {$dbr->tableName('categorylinks')} ON (`$mvIndexTableName`.`mv_page_id` = {$dbr->tableName('categorylinks')}.`cl_from`)
				   LEFT JOIN {$dbr->tableName('smw_relations')} ON (
						`$mvIndexTableName`.`mv_page_id` = {$dbr->tableName('smw_relations')}.`subject_id` AND
						{$dbr->tableName('smw_relations')}.`relation_title`='bill' ) ";
			//generate stream_id, range sets
			$sql.=" WHERE `$mvIndexTableName`.`mvd_type`='Anno_en' AND (";
			$or='';
			foreach($ret_ary as $stream_id=>$rangeSet){
				foreach($rangeSet as $range){
					$sql.=$or . "( `$mvIndexTableName`.`stream_id`='$stream_id'";
					$sql.=" AND `start_time` <= '" . $range['s']."'";
					$sql.=" AND `end_time` >= '" . $range['e'] . '\' ) ';
					$or = ' OR ';
				}
			}
			$sql.=') LIMIT 0, 200';
			//merge category info back into base results:
			$result = $dbr->query($sql,  'MV_Index:doCategorySearchResult');
			while($cl_row = $dbr->fetchObject( $result )){
				foreach($ret_ary[$cl_row->stream_id] as &$range){
					foreach($range['rows'] as &$result_row){
						if($result_row->start_time <= $cl_row->end_time &&
						  $result_row->end_time >= $cl_row->start_time){
						 	if($cl_row->cl_to)
								$result_row->categories[$cl_row->cl_to]=true;
							if($cl_row->bill_to)
								$result_row->bills[$cl_row->bill_to]=true;
						}
					}
				}
			}
		}
		//print "<pre>";
		//print_r($ret_ary);
		//die;
		return $ret_ary;
	}*/
	function numResultsFound(){
		if(isset($this->numResultsFound)){
			return $this->numResultsFound;
		}else{
			return null;
		}
	}
	function numResults(){
		if(isset($this->numResults))
			return $this->numResults;
		return 0;
	}
	/*inserts search result into proper range and stream */
	function insert_merge_range(& $sranges, &$ret_ary, $row, $doRowInsert=true){
		foreach($sranges as & $srange){
				//skip if srange row 0 has same mvd (topq already inserted)
				//if($srange['rows'][0]->id==$row->id )continue ;
				//check if current $srow encapsulate and insert
				if($row->start_time <= $srange['s']  && $row->end_time >= $srange['e']){
					$srange['s']= $row->start_time;
					$srange['e']= $row->end_time;
					if($doRowInsert)
						$srange['rows'][]=$row;
					//grab rows from any other stream segment that fits in new srange:
					foreach($ret_ary as &$sbrange){
						if($row->start_time <= $sbrange['s']  && $row->end_time >= $sbrange['e']){
							foreach($sbrange['rows'] as $sbrow){
								$srange['rows'][]=$sbrow;
							}
							unset($sbrange);
						}
					}
					return ;
				}//else if current fits into srange insert
				else if($row->start_time >= $srange['s']  &&  $row->end_time <= $srange['e']){
					if($doRowInsert)
						$srange['rows'][]=$row;
					return ;
				}
				//make sure the current row does not already exist:
				foreach($srange['rows'] as $sbrow){
					if($sbrow->wiki_title == $row->wiki_title){
						return ;
					}
				}
		}
		//just add as new srange
		$new_srange = array('s'=>$row->start_time,
							'e'=> $row->end_time);
		if($doRowInsert){
			$new_srange['rows']=array($row);
		}else{
			$new_srange['rows']=array();
		}
		$ret_ary[$row->stream_id][]=$new_srange;
	}
	function getMVDbyId($id, $fields='*'){
		global $mvIndexTableName;
		$dbr =& wfGetDB(DB_SLAVE);
		$result = $dbr->select( $mvIndexTableName, $fields,
			array('mv_page_id'=>$id) );
		if($dbr->numRows($result)==0){
			return array();
		}else{
			//(mvd->id got renamed to more accurate mv_page_id)
			$row = $dbr->fetchObject( $result );
			$row->id=$row->mv_page_id;
			return $row;
		}
	}
	function getMVDbyTitle($title_key, $fields='*'){
		global $mvIndexTableName;
		$dbr =& wfGetDB(DB_SLAVE);
		$result = $dbr->select( $mvIndexTableName, $fields,
			array('wiki_title'=>$title_key) );
		if($dbr->numRows($result)==0){
			return null;
		}else{
			$row =  $dbr->fetchObject( $result );
			$row->id=$row->mv_page_id;
			return $row;
		}
	}
	function update_index_title($old_title, $new_title){
		global $mvIndexTableName;

		//make sure the new title is valid:
		$mvTitle = new MV_Title( $new_title );
		if( $mvTitle->validRequestTitle() ){
			//build the update row
			$update_ary = array(
				'wiki_title'=>$mvTitle->getWikiTitle(),
				'mvd_type'=>$mvTitle->getTypeMarker(),
				'stream_id'=>$mvTitle->getStreamId(),
				'start_time'=>$mvTitle->getStartTimeSeconds(),
				'end_time'=>$mvTitle->getEndTimeSeconds() );
			//get old row
			$mvd_row = MV_Index::getMVDbyTitle( $old_title );
			$dbw =& wfGetDB(DB_WRITE);
			$dbw->update($mvIndexTableName, $update_ary,
				array('mv_page_id'=>$mvd_row->mv_page_id));
		}else{
			//print "NOT VALID MOVE";
			//@@todo better error handling (tyring to move a MVD data into bad request form)
			throw new MWException("Invalid Page name for MVD namespace \n");
		}
	}
	/*
	 * update_index_page updates the `mv_mvd_index` table (on MVD namespace saves)
	 */
	function update_index_page(&$article, &$text){
		global $mvgIP, $mvIndexTableName;
		//check static or $this usage context
		//use mv title to split up the values:
		$mvTitle = new MV_Title($article->mTitle->getDBkey());
		//print "Wiki title: " . $mvTitle->getWikiTitle();
		//fist check if an mvd entry for this stream already exists:
		$mvd_row = MV_Index::getMVDbyTitle( $mvTitle->getWikiTitle() );
		//set up the insert values:
		$insAry = array(
			'mv_page_id'=>$article->mTitle->getArticleID(),
			'wiki_title'=>$mvTitle->getWikiTitle(),
			'mvd_type'=>$mvTitle->getTypeMarker(),
			'stream_id'=>$mvTitle->getStreamId(),
			'start_time'=>$mvTitle->getStartTimeSeconds(),
			'end_time'=>$mvTitle->getEndTimeSeconds(),
		);

		$dbw =& wfGetDB(DB_WRITE);
		if(count($mvd_row)==0){
			return $dbw->insert( $mvIndexTableName , $insAry);
		}else{
			$dbw->update($mvIndexTableName, $insAry,
				array('mv_page_id'=>$mvd_row->mv_page_id));
		}
	}
}
