<?
# See user.doc

include_once( "WatchedItem.php" );

class User {
	/* private */ var $mId, $mName, $mPassword, $mEmail, $mNewtalk;
	/* private */ var $mRights, $mOptions;
	/* private */ var $mDataLoaded, $mNewpassword;
	/* private */ var $mSkin;
	/* private */ var $mBlockedby, $mBlockreason;
	/* private */ var $mTouched;
	/* private */ var $mCookiePassword;

	function User()
	{
		$this->loadDefaults();
	}

	# Static factory method
	#
	function newFromName( $name )
	{
		$u = new User();

		# Clean up name according to title rules

		$t = Title::newFromText( $name );
		$u->setName( $t->getText() );
		return $u;
	}

	/* static */ function whoIs( $id )
	{
		return wfGetSQL( "user", "user_name", "user_id=$id" );
	}

	/* static */ function idFromName( $name )
	{
		$nt = Title::newFromText( $name );
		$sql = "SELECT user_id FROM user WHERE user_name='" .
		  wfStrencode( $nt->getText() ) . "'";
		$res = wfQuery( $sql, "User::idFromName" );

		if ( 0 == wfNumRows( $res ) ) { return 0; }
		else {
			$s = wfFetchObject( $res );
			return $s->user_id;
		}
	}

	# does the string match an anonymous user IP address?
	/* static */ function isIP( $name ) {
		return preg_match("/^\d{1,3}\.\d{1,3}.\d{1,3}\.\d{1,3}$/",$name);

	}

	/* static */ function randomPassword()
	{
		$pwchars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz";
		$l = strlen( $pwchars ) - 1;

		wfSeedRandom();
		$np = $pwchars{mt_rand( 0, $l )} . $pwchars{mt_rand( 0, $l )} .
		  $pwchars{mt_rand( 0, $l )} . chr( mt_rand(48, 57) ) .
		  $pwchars{mt_rand( 0, $l )} . $pwchars{mt_rand( 0, $l )} .
		  $pwchars{mt_rand( 0, $l )};
		return $np;
	}

	function loadDefaults()
	{
		global $wgLang ;
		global $wgNamespacesToBeSearchedDefault;

		$this->mId = $this->mNewtalk = 0;
		$this->mName = getenv( "REMOTE_ADDR" );
		$this->mEmail = "";
		$this->mPassword = $this->mNewpassword = "";
		$this->mRights = array();
		$defOpt = $wgLang->getDefaultUserOptions() ;
		foreach ( $defOpt as $oname => $val ) {
			$this->mOptions[$oname] = $val;
		}
		foreach ($wgNamespacesToBeSearchedDefault as $nsnum => $val) {
			$this->mOptions["searchNs".$nsnum] = $val;
		}
		unset( $this->mSkin );
		$this->mDataLoaded = false;
		$this->mBlockedby = -1; # Unset
		$this->mTouched = '0'; # Allow any pages to be cached
		$this->cookiePassword = "";
	}

	/* private */ function getBlockedStatus()
	{
                global $wgBadRanges, $wgBadUserAgents, $wgRangeBlockUser, $wgRangeBlockReason;
		
		if ( -1 != $this->mBlockedby ) { return; }
		
		# Range/user-agent blocking
		$fBlock = false;
		if ( ( !is_array( $wgBadUserAgents ) || 
			array_key_exists( getenv( "HTTP_USER_AGENT" ), $wgBadUserAgents ) ) &&
			is_array( $wgBadRanges ) ) 
		{
			$iIp = ip2long( getenv( "REMOTE_ADDR" ) );
			foreach ( $wgBadRanges as $range ) {	
				$start = ip2long( $range[0] );
				$end = ip2long( $range[1] );
				if ( $iIp >= $start && $iIp <= $end ) {
					$fBlock = true;
					break;
				}
			}
		}

		if ( $fBlock ) {
			$this->mBlockedby = $wgRangeBlockUser;
			$this->mBlockReason = $wgRangeBlockReason;
			return;
		}

		# User/IP blocking

		$block = new Block();
		if ( !$block->load( getenv( "REMOTE_ADDR" ), $this->mId ) ) {
			wfDebug( getenv( "REMOTE_ADDR" ) ." is not blocked\n" );
			$this->mBlockedby = 0;
			return;
		}
		
		$this->mBlockedby = $block->mBy;
		$this->mBlockreason = $block->mReason;
	}

	function isBlocked()
	{
		$this->getBlockedStatus();
		if ( 0 == $this->mBlockedby ) { return false; }
		return true;
	}

	function blockedBy() {
		$this->getBlockedStatus();
		return $this->mBlockedby;
	}

	function blockedFor() {
		$this->getBlockedStatus();
		return $this->mBlockreason;
	}

	/* static */ function loadFromSession()
	{
		global $HTTP_COOKIE_VARS, $wsUserID, $wsUserName, $wsUserPassword;
		global $wgMemc, $wgDBname;

		if ( isset( $wsUserID ) ) {
			if ( 0 != $wsUserID ) {
				$sId = $wsUserID;
			} else {
				return new User();
			}
		} else if ( isset( $HTTP_COOKIE_VARS["{$wgDBname}UserID"] ) ) {
			$sId = $HTTP_COOKIE_VARS["{$wgDBname}UserID"];
			$wsUserID = $sId;
		} else {
			return new User();
		}
		if ( isset( $wsUserName ) ) {
			$sName = $wsUserName;
		} else if ( isset( $HTTP_COOKIE_VARS["{$wgDBname}UserName"] ) ) {
			$sName = $HTTP_COOKIE_VARS["{$wgDBname}UserName"];
			$wsUserName = $sName;
		} else {
			return new User();
		}

		$passwordCorrect = FALSE;
		$user = $wgMemc->get( $key = "$wgDBname:user:id:$sId" );
		if($makenew = !$user) {
			wfDebug( "User::loadFromSession() unable to load from memcached\n" );
			$user = new User();
			$user->mId = $sId;
			$user->loadFromDatabase();
		} else {
			wfDebug( "User::loadFromSession() got from cache!\n" );
		}

		if ( isset( $wsUserPassword ) ) {
			$passwordCorrect = $wsUserPassword == $user->mPassword;
		} else if ( isset( $HTTP_COOKIE_VARS["{$wgDBname}Password"] ) ) {
			$user->mCookiePassword = $HTTP_COOKIE_VARS["{$wgDBname}Password"];
			$wsUserPassword = $user->addSalt( $user->mCookiePassword );
			$passwordCorrect = $wsUserPassword == $user->mPassword;
		} else {
			return new User(); # Can't log in from session
		}

		if ( ( $sName == $user->mName ) && $passwordCorrect ) {
			if($makenew) {
				if($wgMemc->set( $key, $user ))
					wfDebug( "User::loadFromSession() successfully saved user\n" );
				else
					wfDebug( "User::loadFromSession() unable to save to memcached\n" );
			}
			$user->spreadBlock();
			return $user;
		}
		return new User(); # Can't log in from session
	}

	function loadFromDatabase()
	{
		if ( $this->mDataLoaded ) { return; }
		# check in separate table if there are changes to the talk page
		$this->mNewtalk=0; # reset talk page status
		if($this->mId) {
			$sql = "SELECT 1 FROM user_newtalk WHERE user_id={$this->mId}";
			$res = wfQuery ($sql, DB_READ, "User::loadFromDatabase" );

			if (wfNumRows($res)>0) {
				$this->mNewtalk= 1;
			}
			wfFreeResult( $res );
		} else {
			global $wgDBname, $wgMemc;
			$key = "$wgDBname:newtalk:ip:{$this->mName}";
			$newtalk = $wgMemc->get( $key );
			if($newtalk === false) {
				$sql = "SELECT 1 FROM user_newtalk WHERE user_ip='{$this->mName}'";
				$res = wfQuery ($sql, DB_READ, "User::loadFromDatabase" );

				$this->mNewtalk = (wfNumRows($res)>0) ? 1 : 0;
				wfFreeResult( $res );

				$wgMemc->set( $key, $this->mNewtalk, time() ); // + 1800 );
			} else {
				$this->mNewtalk = $newtalk ? 1 : 0;
			}
		}
		if(!$this->mId) {
			$this->mDataLoaded = true;
			return;
		} # the following stuff is for non-anonymous users only

		$sql = "SELECT user_name,user_password,user_newpassword,user_email," .
		  "user_options,user_rights,user_touched FROM user WHERE user_id=" .
		  "{$this->mId}";
		$res = wfQuery( $sql, DB_READ, "User::loadFromDatabase" );

		if ( wfNumRows( $res ) > 0 ) {
			$s = wfFetchObject( $res );
			$this->mName = $s->user_name;
			$this->mEmail = $s->user_email;
			$this->mPassword = $s->user_password;
			$this->mNewpassword = $s->user_newpassword;
			$this->decodeOptions( $s->user_options );
			$this->mRights = explode( ",", strtolower( $s->user_rights ) );
			$this->mTouched = $s->user_touched;
		}

		wfFreeResult( $res );
		$this->mDataLoaded = true;
	}

	function getID() { return $this->mId; }
	function setID( $v ) {
		$this->mId = $v;
		$this->mDataLoaded = false;
	}

	function getName() {
		$this->loadFromDatabase();
		return $this->mName;
	}

	function setName( $str )
	{
		$this->loadFromDatabase();
		$this->mName = $str;
	}

	function getNewtalk()
	{
		$this->loadFromDatabase();
		return ( 0 != $this->mNewtalk );
	}

	function setNewtalk( $val )
	{
		$this->loadFromDatabase();
		$this->mNewtalk = $val;
		$this->invalidateCache();
	}

	function invalidateCache() {
		$this->loadFromDatabase();
		$this->mTouched = wfTimestampNow();
		# Don't forget to save the options after this or
		# it won't take effect!
	}

	function validateCache( $timestamp ) {
		$this->loadFromDatabase();
		return ($timestamp >= $this->mTouched);
	}

	function getPassword()
	{
		$this->loadFromDatabase();
		return $this->mPassword;
	}

	function getNewpassword()
	{
		$this->loadFromDatabase();
		return $this->mNewpassword;
	}

	function addSalt( $p )
	{
		global $wgPasswordSalt;
		if($wgPasswordSalt)
			return md5( "{$this->mId}-{$p}" );
		else
			return $p;
	}

	function encryptPassword( $p )
	{
		return $this->addSalt( md5( $p ) );
	}

	function setPassword( $str )
	{
		$this->loadFromDatabase();
		$this->setCookiePassword( $str );
		$this->mPassword = $this->encryptPassword( $str );
		$this->mNewpassword = "";
	}

	function setCookiePassword( $str )
	{
		$this->loadFromDatabase();
		$this->mCookiePassword = md5( $str );
	}

	function setNewpassword( $str )
	{
		$this->loadFromDatabase();
		$this->mNewpassword = $this->encryptPassword( $str );
	}

	function getEmail()
	{
		$this->loadFromDatabase();
		return $this->mEmail;
	}

	function setEmail( $str )
	{
		$this->loadFromDatabase();
		$this->mEmail = $str;
	}

	function getOption( $oname )
	{
		$this->loadFromDatabase();
		if ( array_key_exists( $oname, $this->mOptions ) ) {
			return $this->mOptions[$oname];
		} else {
			return "";
		}
	}

	function setOption( $oname, $val )
	{
		$this->loadFromDatabase();
		$this->mOptions[$oname] = $val;
		$this->invalidateCache();
	}

	function getRights()
	{
		$this->loadFromDatabase();
		return $this->mRights;
	}

	function addRight( $rname )
	{
		$this->loadFromDatabase();
		array_push( $this->mRights, $rname );
		$this->invalidateCache();
	}

	function isSysop()
	{
		$this->loadFromDatabase();
		if ( 0 == $this->mId ) { return false; }

		return in_array( "sysop", $this->mRights );
	}

	function isDeveloper()
	{
		$this->loadFromDatabase();
		if ( 0 == $this->mId ) { return false; }

		return in_array( "developer", $this->mRights );
	}

	function isBot()
	{
		$this->loadFromDatabase();
		if ( 0 == $this->mId ) { return false; }

		return in_array( "bot", $this->mRights );
	}

	function &getSkin()
	{
		if ( ! isset( $this->mSkin ) ) {
			$skinNames = Skin::getSkinNames();
			$s = $this->getOption( "skin" );
			if ( "" == $s ) { $s = 0; }

			if ( $s >= count( $skinNames ) ) { $sn = "SkinStandard"; }
			else $sn = "Skin" . $skinNames[$s];
			$this->mSkin = new $sn;
		}
		return $this->mSkin;
	}

	function isWatched( $title ) {
		$wl = WatchedItem::fromUserTitle( $this, $title );
		return $wl->isWatched();
	}
	
	function addWatch( $title ) {
		$wl = WatchedItem::fromUserTitle( $this, $title );
		$wl->addWatch();
		$this->invalidateCache();
	}
	
	function removeWatch( $title ) {
		$wl = WatchedItem::fromUserTitle( $this, $title );
		$wl->removeWatch();
		$this->invalidateCache();
	}
	
	/* private */ function encodeOptions()
	{
		$a = array();
		foreach ( $this->mOptions as $oname => $oval ) {
			array_push( $a, "{$oname}={$oval}" );
		}
		$s = implode( "\n", $a );
		return wfStrencode( $s );
	}

	/* private */ function decodeOptions( $str )
	{
		$a = explode( "\n", $str );
		foreach ( $a as $s ) {
			if ( preg_match( "/^(.[^=]*)=(.*)$/", $s, $m ) ) {
				$this->mOptions[$m[1]] = $m[2];
			}
		}
	}

	function setCookies()
	{
		global $wsUserID, $wsUserName, $wsUserPassword;
		global $wgCookieExpiration, $wgCookiePath, $wgCookieDomain, $wgDBname;
		if ( 0 == $this->mId ) return;
		$this->loadFromDatabase();
		$exp = time() + $wgCookieExpiration;

		$wsUserID = $this->mId;
		setcookie( "{$wgDBname}UserID", $this->mId, $exp, $wgCookiePath, $wgCookieDomain );

		$wsUserName = $this->mName;
		setcookie( "{$wgDBname}UserName", $this->mName, $exp, $wgCookiePath, $wgCookieDomain );

		$wsUserPassword = $this->mPassword;
		if ( 1 == $this->getOption( "rememberpassword" ) ) {
			setcookie( "{$wgDBname}Password", $this->mCookiePassword, $exp, $wgCookiePath, $wgCookieDomain );
		} else {
			setcookie( "{$wgDBname}Password", "", time() - 3600 );
		}
	}

	function logout()
	{
		global $wsUserID, $wgCookiePath, $wgCookieDomain, $wgDBname;
		$this->mId = 0;

		$wsUserID = 0;

		setcookie( "{$wgDBname}UserID", "", time() - 3600, $wgCookiePath, $wgCookieDomain );
		setcookie( "{$wgDBname}Password", "", time() - 3600, $wgCookiePath, $wgCookieDomain );
	}

	function saveSettings()
	{
		global $wgMemc, $wgDBname;

		if ( ! $this->mNewtalk ) {
			if( $this->mId ) {
				$sql="DELETE FROM user_newtalk WHERE user_id={$this->mId}";
				wfQuery ($sql,"User::saveSettings");
			} else {
				$sql="DELETE FROM user_newtalk WHERE user_ip='{$this->mName}'";
				wfQuery ($sql,"User::saveSettings");
				$wgMemc->delete( "$wgDBname:newtalk:ip:{$this->mName}" );
			}
		}
		if ( 0 == $this->mId ) { return; }

		$sql = "UPDATE user SET " .
		  "user_name= '" . wfStrencode( $this->mName ) . "', " .
		  "user_password= '" . wfStrencode( $this->mPassword ) . "', " .
		  "user_newpassword= '" . wfStrencode( $this->mNewpassword ) . "', " .
		  "user_email= '" . wfStrencode( $this->mEmail ) . "', " .
		  "user_options= '" . $this->encodeOptions() . "', " .
		  "user_rights= '" . wfStrencode( implode( ",", $this->mRights ) ) . "', " 
.
		  "user_touched= '" . wfStrencode( $this->mTouched ) .
		  "' WHERE user_id={$this->mId}";
		wfQuery( $sql, "User::saveSettings" );
		$wgMemc->delete( "$wgDBname:user:id:$this->mId" );
	}

	# Checks if a user with the given name exists
	#
	function idForName()
	{
		$gotid = 0;
		$s = trim( $this->mName );
		if ( 0 == strcmp( "", $s ) ) return 0;

		$sql = "SELECT user_id FROM user WHERE user_name='" .
		  wfStrencode( $s ) . "'";
		$res = wfQuery( $sql, "User::idForName" );
		if ( 0 == wfNumRows( $res ) ) { return 0; }

		$s = wfFetchObject( $res );
		if ( "" == $s ) return 0;

		$gotid = $s->user_id;
		wfFreeResult( $res );
		return $gotid;
	}

	function addToDatabase()
	{
		$sql = "INSERT INTO user (user_name,user_password,user_newpassword," .
		  "user_email, user_rights, user_options) " .
		  " VALUES ('" . wfStrencode( $this->mName ) . "', '" .
		  wfStrencode( $this->mPassword ) . "', '" .
		  wfStrencode( $this->mNewpassword ) . "', '" .
		  wfStrencode( $this->mEmail ) . "', '" .
		  wfStrencode( implode( ",", $this->mRights ) ) . "', '" .
		  $this->encodeOptions() . "')";
		wfQuery( $sql, "User::addToDatabase" );
		$this->mId = $this->idForName();
	}
	
	function spreadBlock()
	{
		# If the (non-anonymous) user is blocked, this function will block any IP address
		# that they successfully log on from.
		$fname = "User::spreadBlock";
		
		wfDebug( "User:spreadBlock()\n" );
		if ( $this->mId == 0 ) {
			return;
		}
		
		$userblock = Block::newFromDB( "", $this->mId );
		if ( !$userblock->isValid() ) {
			return;
		}
		
		# Check if this IP address is already blocked
		$addr = getenv( "REMOTE_ADDR" );
		$ipblock = Block::newFromDB( $addr );
		if ( $ipblock->isValid() ) {
			# Just update the timestamp
			$ipblock->updateTimestamp();
			return;
		}
		
		# Make a new block object with the desired properties
		wfDebug( "Autoblocking {$this->mUserName}@{$addr}\n" );
		$ipblock->mAddress = $addr;
		$ipblock->mUser = 0;
		$ipblock->mBy = $userblock->mBy;
		$ipblock->mReason = str_replace( "$1", $this->getName(), wfMsg( "autoblocker" ) );
		$ipblock->mReason = str_replace( "$2", $userblock->mReason, $ipblock->mReason );
		$ipblock->mTimestamp = wfTimestampNow();
		$ipblock->mAuto = 1;

		# Insert it
		$ipblock->insert();
	
	}
}

?>
