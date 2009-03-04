<?php

class GlobalBlocking {
	static function getUserPermissionsErrors( &$title, &$user, $action, &$result ) {
		global $wgApplyGlobalBlocks;
		if ($action == 'read' || !$wgApplyGlobalBlocks) {
			return true;
		}
		$ip = wfGetIp();
		$blockError = self::getUserBlockErrors( $user, $ip );
		if( !empty($blockError) ) {
			$result[] = $blockError;
			return false;
		}
		return true;
	}
	
	static function isBlockedGlobally( &$user, $ip, &$blocked ) {
		$blockError = self::getUserBlockErrors( $user, $ip );
		if( $blockError ) {
			$blocked = true;
			return false;
		}
		return true;
	}
		
	static function getUserBlockErrors( $user, $ip ) {
		$dbr = GlobalBlocking::getGlobalBlockingSlave();
		
		$hex_ip = IP::toHex( $ip );
		$ip_pattern = substr( $hex_ip, 0, 4 ) . '%'; // Don't bother checking blocks out of this /16.
	
		$conds = array( 
			'gb_range_end>='.$dbr->addQuotes($hex_ip), // This block in the given range.
			'gb_range_start<='.$dbr->addQuotes($hex_ip),
			'gb_range_start like ' . $dbr->addQuotes( $ip_pattern ),
			'gb_expiry>'.$dbr->addQuotes($dbr->timestamp(wfTimestampNow())) 
		);
	
		if ( !$user->isAnon() )
			$conds['gb_anon_only'] = 0;
	
		// Get the block
		if ($block = $dbr->selectRow( 'globalblocks', '*', $conds, __METHOD__ )) {
		
			// Check for local whitelisting
			if (GlobalBlocking::getWhitelistInfo( $block->gb_id ) ) {
				// Block has been whitelisted.
				return array();
			}
			
			if ( $user->isAllowed( 'ipblock-exempt' ) ) {
				// User is exempt from IP blocks.
				return array();
			}

			$expiry = Block::formatExpiry( $block->gb_expiry );
	
			wfLoadExtensionMessages( 'GlobalBlocking' );
			
			$display_wiki = self::getWikiName( $block->gb_by_wiki );
			$user_display = self::maybeLinkUserpage( $block->gb_by_wiki, $block->gb_by );
			
			return array('globalblocking-blocked', $user_display, $display_wiki, $block->gb_reason, $expiry);
		}
		return array();
	}
	
	static function getGlobalBlockingMaster() {
		global $wgGlobalBlockingDatabase;
		return wfGetDB( DB_MASTER, 'globalblocking', $wgGlobalBlockingDatabase );
	}
	
	static function getGlobalBlockingSlave() {
		global $wgGlobalBlockingDatabase;
		return wfGetDB( DB_SLAVE, 'globalblocking', $wgGlobalBlockingDatabase );
	}
	
	static function getGlobalBlockId( $ip ) {
		$dbr = GlobalBlocking::getGlobalBlockingSlave();
	
		if (!($row = $dbr->selectRow( 'globalblocks', 'gb_id', array( 'gb_address' => $ip ), __METHOD__ )))
			return 0;
	
		return $row->gb_id;
	}
	
	static function purgeExpired() {
		// This is expensive. It involves opening a connection to a new master,
		// and doing a write query. We should only do it when a connection to the master
		// is already open (currently, when a global block is made).
		$dbw = GlobalBlocking::getGlobalBlockingMaster();
		
		// Stand-alone transaction.
		$dbw->begin();
		$dbw->delete( 'globalblocks', array('gb_expiry<'.$dbw->addQuotes($dbw->timestamp())), __METHOD__ );
		$dbw->commit();
		
		// Purge the global_block_whitelist table.
		// We can't be perfect about this without an expensive check on the master
		// for every single global block. However, we can be clever about it and store
		// the expiry of global blocks in the global_block_whitelist table.
		// That way, most blocks will fall out of the table naturally when they expire.
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		$dbw->delete( 'global_block_whitelist', array( 'gbw_expiry<'.$dbw->addQuotes($dbw->timestamp())), __METHOD__ );
		$dbw->commit();
	}
	
	static function getWhitelistInfo( $id = null, $address = null ) {
		$conds = array();
		if ($id != null) {
			$conds = array( 'gbw_id' => $id );
		} elseif ($address != null) {
			$conds = array( 'gbw_address' => $address );
		} else {
			//WTF?
			throw new MWException( "Neither Block IP nor Block ID given for retrieving whitelist status" );
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		$row = $dbr->selectRow( 'global_block_whitelist', array( 'gbw_by', 'gbw_reason' ), $conds, __METHOD__ );
		
		if ($row == false) {
			// Not whitelisted.
			return false;
		} else {
			// Block has been whitelisted
			return array( 'user' => $row->gbw_by, 'reason' => $row->gbw_reason );
		}
	}
	
	static function getWhitelistInfoByIP( $block_ip ) {
		return self::getWhitelistInfo( null, $block_ip );
	}
	
	static function getWikiName( $wiki_id ) {
		if (class_exists('WikiMap')) {
			// We can give more info than just the wiki id!
			$wiki = WikiMap::getWiki( $wiki_id );
				
			if ($wiki) {
				return $wiki->getDisplayName();
			}
		}
		
		return $wiki_id;
	}
	
	static function maybeLinkUserpage( $wiki_id, $user ) {
		if (class_exists( 'WikiMap')) {
			$wiki = WikiMap::getWiki( $wiki_id );
			
			if ($wiki) {
				return "[".$wiki->getUrl( "User:$user" )." $user]";
			}
		}
		return $user;
	}
	
	static function insertBlock( $address, $reason, $expiry, $options = array() ) {
		global $wgUser;
		$errors = array();
		
		## Purge expired blocks.
		GlobalBlocking::purgeExpired();

		## Validate input
		$ip = IP::sanitizeIP( $address );
		
		$anonOnly = in_array( 'anon-only', $options );
		$modify = in_array( 'modify', $options );

		if (!IP::isIPAddress($ip)) {
			// Invalid IP address.
			$errors[] = array( 'globalblocking-block-ipinvalid', $ip );
		}
		
		if ( false === $expiry ) {
			$errors[] = array( 'globalblocking-block-expiryinvalid', $expiry );
		}
		
		$existingBlock = GlobalBlocking::getGlobalBlockId($ip);
		if ( !$modify && $existingBlock ) {
			$errors[] = array( 'globalblocking-block-alreadyblocked', $ip );
		}
	
		// Check for too-big ranges.
		list( $range_start, $range_end ) = IP::parseRange( $ip );
		
		if (substr( $range_start, 0, 4 ) != substr( $range_end, 0, 4 )) {
			// Range crosses a /16 boundary.
			$errors[] = array( 'globalblocking-block-bigrange', $ip );
		}
		
		// Normalise the range
		if ($range_start != $range_end) {
			$ip = Block::normaliseRange( $ip );
		}
		
		if (count($errors)>0)
			return $errors;

		// We're a-ok.
		$dbw = GlobalBlocking::getGlobalBlockingMaster();
		
		// Delete the old block, if applicable
		
		if ($modify) {
			$dbw->delete( 'globalblocks', array( 'gb_id' => $existingBlock ), __METHOD__ );
		}

		$row = array();
		$row['gb_address'] = $ip;
		$row['gb_by'] = $wgUser->getName();
		$row['gb_by_wiki'] = wfWikiId();
		$row['gb_reason'] = $reason;
		$row['gb_timestamp'] = $dbw->timestamp(wfTimestampNow());
		$row['gb_anon_only'] = $anonOnly;
		$row['gb_expiry'] = Block::encodeExpiry($expiry, $dbw);
		list( $row['gb_range_start'], $row['gb_range_end'] ) = array( $range_start, $range_end );

		$dbw->insert( 'globalblocks', $row, __METHOD__ );
		
		return array();
	}
	
	static function block( $address, $reason, $expiry, $options = array() ) {
		global $wgContLang;
		
		$expiry = Block::parseExpiryInput( $expiry );
		$errors = self::insertBlock( $address, $reason, $expiry, $options );
		
		if ( count($errors) > 0 )
			return $errors;
	
		$anonOnly = in_array( 'anon-only', $options );
		$modify = in_array( 'modify', $options );

		// Log it.
		$logAction = $modify ? 'modify' : 'gblock2';
		$flags = array();
		
		if ($anonOnly)
			$flags[] = wfMsgForContent( 'globalblocking-list-anononly' );
		
		if ( $expiry != 'infinity' ) {
			$displayExpiry = $wgContLang->timeanddate( $expiry );
			$flags[] = wfMsgForContent( 'globalblocking-logentry-expiry', $displayExpiry );
		} else {
			$flags[] = wfMsgForContent( 'globalblocking-logentry-noexpiry' );
		}
		
		$info = implode( ', ', $flags );

		$page = new LogPage( 'gblblock' );
		$page->addEntry( $logAction,
			Title::makeTitleSafe( NS_USER, $address ),
			$reason,
			array($info, $address)
		);

		return array();
	}
}