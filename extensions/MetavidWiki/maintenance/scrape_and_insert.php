<?php
/*
 * scrape_and_insert.php Created on Oct 1, 2007
 * 
 * All Metavid Wiki code is Released Under the GPL2
 * for more info visit http:/metavid.ucsc.edu/code
 * 
 * @author Michael Dale
 * @email dale@ucsc.edu
 * @url http://metavid.ucsc.edu
 */

 $cur_path = $IP = dirname( __FILE__ );
 //include commandLine.inc from the mediaWiki maintenance dir: 
require_once( '../../../maintenance/commandLine.inc' );

 if ( count( $args ) == 0 || isset( $options['help'] ) ) {
 	print <<<EOT
 	
Scrapes External WebSites and updates relavent local semantic content.
 
Usage php scrape_and_insert.php insert_type [site] [options]
site:
	'cspan_chronicle' will take all it can from  http://www.c-spanarchives.org/
options: 		
	'-s --stream_name steam_name|all' the strean name or keyword "all" to proc all streams
	'--limit X' to only process X number of streams (when stream_name set to all)
	'--offset Y' to start on Y of streams (when stream_name set to all)

EOT;
exit();
}
/*
 * procc the request
 */ 
 function proc_args(){
 	global $args; 	
	switch($args[0]){
		case 'cspan_chronicle':
			$MV_CspanScraper = new MV_CspanScraper();
			$MV_CspanScraper->doScrapeInsert();
		break;
	}
 }

/*
 * set up the user:
 */
$userName = 'mvBot';
$wgUser = User::newFromName( $userName );
if ( !$wgUser ) {
	print "Invalid username\n";
	exit( 1 );
}
if ( $wgUser->isAnon() ) {
	$wgUser->addToDatabase();
}

class MV_CspanScraper extends MV_BaseScraper{	
	var $base_url = 'http://www.c-spanarchives.org/congress/?q=node/69850';
	function procArguments(){
		global $options, $args;		
		if( !isset($options['stream_name']) && !isset($options['s'])){				
			die("error missing stream name\n");
		}else{			
			$stream_inx = (isset($options['stream_name']))?$options['stream_name']:$options['s'];
			if($args[$stream_inx]=='all'){
				//put all in sync into stream list
				print "do all streams\n";
			}else{
				$stream_name = $args[$stream_inx];
				$this->streams[$stream_name]= new MV_Stream(array('name'=>$stream_name));		
				if(!$this->streams[$stream_name]->doesStreamExist()){
					die('error: stream '.$stream_name . ' does not exist');
				}
				print "Proccessing Stream: $stream_name \n";
			}
		}				
	}
	function doScrapeInsert(){
		foreach($this->streams as & $stream){
			if(!isset($stream->date_start_time))$stream->date_start_time=0;
			if($stream->date_start_time==0){
				die('error stream '. $stream->name . ' missing time info'."\n");
			}
			$hors =  (strpos($stream->name, 'house')!==false)?'h':'s';
			$date_req = date('Y-m-d', $stream->date_start_time);
			$cspan_url = $this->base_url . '&date='.$date_req.'&hors='.$hors;
			echo $cspan_url . "\n";			
			$rawpage = $this->doRequest($cspan_url);		
			//get the title and href if present:
			$patern = '/overlib\(\'(.*)\((Length: ([^\)]*)).*CAPTION,\'<font size=2>(.*)<((.*href="([^"]*))|.*)>/'; 
			preg_match_all($patern, $rawpage, $matches);
			$person_time_ary = array();
			//format matches: 
			foreach($matches[0] as $k=>$v){
				$href='';
				$href_match=array();
				preg_match('/href="(.*)"/',$matches[5][$k], $href_match);
				if(count($href_match)!=0)$href=$href_match[1];
				
				$porg = str_replace('<br>',' ',$matches[4][$k]);
				$porg = preg_replace('/[D|R]+\-\[.*\]/', '', $porg);
				$pparts = explode(',',$porg);
				$pname = trim($pparts[1]) . '_' . trim($pparts[0]);			
				$person_time_ary[]= array(
					'start_time'=>strip_tags($matches[1][$k]),
					'length'=>$matches[3][$k],
					'person_title'=>str_replace('<br>',' ',$matches[4][$k]),
					'spoken_by'=>$pname,
					'href'=>$href
				);
			}									
		    //group people in page matches
		    $g_person_time_ary=array();
		    $prev_person=null;		    
		    foreach($person_time_ary as $ptag){
		    	$g_person_time_ary[$ptag['spoken_by']][]=$ptag;		    			    			    
		    }
		   
		    //retrive rows to find match: 
		   	$dbr =& wfGetDB(DB_SLAVE);
		    $mvd_res = MV_Index::getMVDInRange($stream->id, null, null, $mvd_type='ht_en',false,$smw_properties=array('Spoken_by'), '');
		    $g_row_matches=array();
		    //group peole in db matches:
		   	while ($row = $dbr->fetchObject($mvd_res)) {
		   		if(!isset($row->Spoken_by))continue;  		   				   
   				if(!isset($g_row_matches[strtolower($row->Spoken_by)])){
   					$g_row_matches[strtolower($row->Spoken_by)]=get_object_vars($row);
   					$g_row_matches[strtolower($row->Spoken_by)]['end_time_sec']=$row->end_time;
   				}else{
   					$g_row_matches[strtolower($row->Spoken_by)]['end_time_sec']+=$row->end_time;
   				}		   		
	   			$cspan_person = next($g_person_time_ary);		   	
		   	}  
		   	//add in sync offset data for $g_person_time_ary
		    reset($g_person_time_ary);
		   	foreach($g_row_matches as $rp=>$rperson){
		   		
		   	}
            //find match person1->person2->person3
            
            
            //average switch time to get offset of stream
            //use offset to insert all $person_time_array data 
		}
	}
}
class MV_BaseScraper{
	function __construct(){
		$this->procArguments();
	}
	/*
	 * simple url cach using the mv_url_cache table
	 * 
	 * @@todo handle post vars
	 */
	function doRequest($url, $post_vars=array()){
		global $mvUrlCacheTable;
		$dbr = wfGetDB( DB_SLAVE );	
		$dbw = wfGetDB( DB_MASTER );
		//check the cache 
		//$sql = "SELECT * FROM `metavid`.`cache_time_url_text` WHERE `url` LIKE '$url'";	
		//select( $table, $vars, $conds='', $fname = 'Database::select', $options = array() )	
		$res = $dbr->select($mvUrlCacheTable, '*', array('url'=>$url), 'MV_BaseScraper::doRequest');
		//@@todo check date for experation
		if($res->numRows()==0){
			echo "do web request: " . $url . "\n";
			//get the content: 
			$page = file_get_contents($url);
			if($page===false){
				echo("error retriving $url retrying...\n");
				sleep(5);				
				return $this->doRequest($url);
			}
			if($page!=''){
				//insert back into the db: 
				//function insert( $table, $a, $fname = 'Database::insert', $options = array() )
				$dbw->insert($mvUrlCacheTable, array('url'=>$url, 'result'=>$page, 'req_time'=>time()));			
				return $page;
			}
		}else{			
			$row = $dbr->fetchObject( $res );
			return $row->result;			
		}
	}
}
//do procc args (now that classes are defined)
 proc_args();
?>
