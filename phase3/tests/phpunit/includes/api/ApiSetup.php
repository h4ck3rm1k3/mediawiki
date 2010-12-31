<?php

abstract class ApiTestSetup extends MediaWikiTestCase {
	protected $user;
	protected $sysopUser;
	protected static $apiUrl;

	function setUp() {
		global $wgServer, $wgContLang, $wgAuth, $wgMemc, $wgRequest;

		self::$apiUrl = $wgServer . wfScript( 'api' );

		$wgMemc = new FakeMemCachedClient;
		$wgContLang = Language::factory( 'en' );
		$wgAuth = new StubObject( 'wgAuth', 'AuthPlugin' );
		$wgRequest = new FauxRequest( array() );
		$this->setupUser();
	}

	protected function doApiRequest( $params, $data = null ) {
		$_SESSION = isset( $data[2] ) ? $data[2] : array();

		$req = new FauxRequest( $params, true, $_SESSION );
		$module = new ApiMain( $req, true );
		$module->execute();

		$data[0] = $module->getResultData();
		$data[1] = $req;
		$data[2] = $_SESSION;

		return $data;
	}

	function setupUser() {
		if ( $this->user == null || $this->sysopUser == null ) {
			$this->user = new UserWrapper( 'User for MediaWiki automated tests', User::randomPassword() );
			$this->sysopUser = new UserWrapper( 'Sysop for MediaWiki automated tests', User::randomPassword(), 'sysop' );
		}

		$GLOBALS['wgUser'] = $this->sysopUser->user;
	}
}

class UserWrapper {
	public $userName, $password, $user;

	public function __construct( $userName, $password, $group = '' ) {
		$this->userName = $userName;
		$this->password = $password;

		$this->user = User::newFromName( $this->userName );
		if ( !$this->user->getID() ) {
			$this->user = User::createNew( $this->userName, array(
				"email" => "test@example.com",
				"real_name" => "Test User" ) );
		}
		$this->user->setPassword( $this->password );

		if ( $group !== '' ) {
			$this->user->addGroup( $group );
		}
		$this->user->saveSettings();
	}
}

