<?php
require('tests/RunSeleniumTests.php');
require('tests/TestsAutoLoader.php');
require('tests/jasmine/spec_makers/makeJqueryMsgSpec.php');
require('tests/parser/parserTest.inc');
require('tests/parser/parserTestsParserHook.php');
require('tests/parserTests.php');
require('tests/phpunit/MediaWikiLangTestCase.php');
require('tests/phpunit/MediaWikiPHPUnitCommand.php');
require('tests/phpunit/MediaWikiTestCase.php');
require('tests/phpunit/StructureTest.php');
require('tests/phpunit/bootstrap.php');
require('tests/phpunit/data/xmp/1.result.php');
require('tests/phpunit/data/xmp/2.result.php');
require('tests/phpunit/data/xmp/3-invalid.result.php');
require('tests/phpunit/data/xmp/3.result.php');
require('tests/phpunit/data/xmp/4.result.php');
require('tests/phpunit/data/xmp/5.result.php');
require('tests/phpunit/data/xmp/6.result.php');
require('tests/phpunit/data/xmp/7.result.php');
require('tests/phpunit/data/xmp/bag-for-seq.result.php');
require('tests/phpunit/data/xmp/flash.result.php');
require('tests/phpunit/data/xmp/gps.result.php');
require('tests/phpunit/data/xmp/invalid-child-not-struct.result.php');
require('tests/phpunit/data/xmp/no-namespace.result.php');
require('tests/phpunit/data/xmp/no-recognized-props.result.php');
require('tests/phpunit/data/xmp/utf16BE.result.php');
require('tests/phpunit/data/xmp/utf16LE.result.php');
require('tests/phpunit/data/xmp/utf32BE.result.php');
require('tests/phpunit/data/xmp/utf32LE.result.php');
require('tests/phpunit/data/xmp/xmpExt.result.php');
require('tests/phpunit/docs/ExportDemoTest.php');
require('tests/phpunit/includes/ArticleTablesTest.php');
require('tests/phpunit/includes/ArticleTest.php');
require('tests/phpunit/includes/BlockTest.php');
require('tests/phpunit/includes/CdbTest.php');
require('tests/phpunit/includes/DiffHistoryBlobTest.php');
require('tests/phpunit/includes/EditPageTest.php');
require('tests/phpunit/includes/ExternalStoreTest.php');
require('tests/phpunit/includes/ExtraParserTest.php');
require('tests/phpunit/includes/FauxResponseTest.php');
require('tests/phpunit/includes/FormOptionsInitializationTest.php');
require('tests/phpunit/includes/FormOptionsTest.php');
require('tests/phpunit/includes/GlobalFunctions/GlobalTest.php');
require('tests/phpunit/includes/GlobalFunctions/GlobalWithDBTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfAssembleUrlTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfBCP47Test.php');
require('tests/phpunit/includes/GlobalFunctions/wfBaseNameTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfExpandUrlTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfGetCallerTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfRemoveDotSegmentsTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfShorthandToIntegerTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfTimestampTest.php');
require('tests/phpunit/includes/GlobalFunctions/wfUrlencodeTest.php');
require('tests/phpunit/includes/HooksTest.php');
require('tests/phpunit/includes/HtmlTest.php');
require('tests/phpunit/includes/HttpTest.php');
require('tests/phpunit/includes/IPTest.php');
require('tests/phpunit/includes/JsonTest.php');
require('tests/phpunit/includes/LanguageConverterTest.php');
require('tests/phpunit/includes/LicensesTest.php');
require('tests/phpunit/includes/LinksUpdateTest.php');
require('tests/phpunit/includes/LocalFileTest.php');
require('tests/phpunit/includes/LocalisationCacheTest.php');
require('tests/phpunit/includes/MWFunctionTest.php');
require('tests/phpunit/includes/MWNamespaceTest.php');
require('tests/phpunit/includes/MessageTest.php');
require('tests/phpunit/includes/ParserOptionsTest.php');
require('tests/phpunit/includes/PathRouterTest.php');
require('tests/phpunit/includes/PreferencesTest.php');
require('tests/phpunit/includes/Providers.php');
require('tests/phpunit/includes/RecentChangeTest.php');
require('tests/phpunit/includes/ResourceLoaderTest.php');
require('tests/phpunit/includes/RevisionStorageTest.php');
require('tests/phpunit/includes/RevisionTest.php');
require('tests/phpunit/includes/SampleTest.php');
require('tests/phpunit/includes/SanitizerTest.php');
require('tests/phpunit/includes/SanitizerValidateEmailTest.php');
require('tests/phpunit/includes/SeleniumConfigurationTest.php');
require('tests/phpunit/includes/SiteConfigurationTest.php');
require('tests/phpunit/includes/TemplateCategoriesTest.php');
require('tests/phpunit/includes/TestUser.php');
require('tests/phpunit/includes/TimeAdjustTest.php');
require('tests/phpunit/includes/TimestampTest.php');
require('tests/phpunit/includes/TitleMethodsTest.php');
require('tests/phpunit/includes/TitlePermissionTest.php');
require('tests/phpunit/includes/TitleTest.php');
require('tests/phpunit/includes/UserTest.php');
require('tests/phpunit/includes/WebRequestTest.php');
require('tests/phpunit/includes/WikiPageTest.php');
require('tests/phpunit/includes/XmlJsTest.php');
require('tests/phpunit/includes/XmlSelectTest.php');
require('tests/phpunit/includes/XmlTest.php');
require('tests/phpunit/includes/ZipDirectoryReaderTest.php');
require('tests/phpunit/includes/api/ApiBlockTest.php');
require('tests/phpunit/includes/api/ApiEditPageTest.php');
require('tests/phpunit/includes/api/ApiOptionsTest.php');
require('tests/phpunit/includes/api/ApiPurgeTest.php');
require('tests/phpunit/includes/api/ApiQueryTest.php');
require('tests/phpunit/includes/api/ApiTest.php');
require('tests/phpunit/includes/api/ApiTestCase.php');
require('tests/phpunit/includes/api/ApiTestCaseUpload.php');
require('tests/phpunit/includes/api/ApiUploadTest.php');
require('tests/phpunit/includes/api/ApiWatchTest.php');
require('tests/phpunit/includes/api/PrefixUniquenessTest.php');
require('tests/phpunit/includes/api/RandomImageGenerator.php');
require('tests/phpunit/includes/api/format/ApiFormatPhpTest.php');
require('tests/phpunit/includes/api/format/ApiFormatTestBase.php');
require('tests/phpunit/includes/api/generateRandomImages.php');
require('tests/phpunit/includes/cache/GenderCacheTest.php');
require('tests/phpunit/includes/cache/ProcessCacheLRUTest.php');
require('tests/phpunit/includes/db/DatabaseSQLTest.php');
require('tests/phpunit/includes/db/DatabaseSqliteTest.php');
require('tests/phpunit/includes/db/DatabaseTest.php');
require('tests/phpunit/includes/db/ORMRowTest.php');
require('tests/phpunit/includes/db/TestORMRowTest.php');
require('tests/phpunit/includes/debug/MWDebugTest.php');
require('tests/phpunit/includes/filerepo/FileBackendTest.php');
require('tests/phpunit/includes/filerepo/FileRepoTest.php');
require('tests/phpunit/includes/filerepo/StoreBatchTest.php');
require('tests/phpunit/includes/installer/InstallDocFormatterTest.php');
require('tests/phpunit/includes/json/ServicesJsonTest.php');
require('tests/phpunit/includes/libs/CSSJanusTest.php');
require('tests/phpunit/includes/libs/CSSMinTest.php');
require('tests/phpunit/includes/libs/GenericArrayObjectTest.php');
require('tests/phpunit/includes/libs/IEUrlExtensionTest.php');
require('tests/phpunit/includes/libs/JavaScriptMinifierTest.php');
require('tests/phpunit/includes/media/BitmapMetadataHandlerTest.php');
require('tests/phpunit/includes/media/BitmapScalingTest.php');
require('tests/phpunit/includes/media/ExifBitmapTest.php');
require('tests/phpunit/includes/media/ExifRotationTest.php');
require('tests/phpunit/includes/media/ExifTest.php');
require('tests/phpunit/includes/media/FormatMetadataTest.php');
require('tests/phpunit/includes/media/GIFMetadataExtractorTest.php');
require('tests/phpunit/includes/media/GIFTest.php');
require('tests/phpunit/includes/media/IPTCTest.php');
require('tests/phpunit/includes/media/JpegMetadataExtractorTest.php');
require('tests/phpunit/includes/media/JpegTest.php');
require('tests/phpunit/includes/media/MediaHandlerTest.php');
require('tests/phpunit/includes/media/PNGMetadataExtractorTest.php');
require('tests/phpunit/includes/media/PNGTest.php');
require('tests/phpunit/includes/media/SVGMetadataExtractorTest.php');
require('tests/phpunit/includes/media/TiffTest.php');
require('tests/phpunit/includes/media/XMPTest.php');
require('tests/phpunit/includes/media/XMPValidateTest.php');
require('tests/phpunit/includes/mobile/DeviceDetectionTest.php');
require('tests/phpunit/includes/normal/CleanUpTest.php');
require('tests/phpunit/includes/parser/MagicVariableTest.php');
require('tests/phpunit/includes/parser/MediaWikiParserTest.php');
require('tests/phpunit/includes/parser/NewParserTest.php');
require('tests/phpunit/includes/parser/ParserMethodsTest.php');
require('tests/phpunit/includes/parser/ParserPreloadTest.php');
require('tests/phpunit/includes/parser/PreprocessorTest.php');
require('tests/phpunit/includes/parser/TagHooksTest.php');
require('tests/phpunit/includes/search/SearchEngineTest.php');
require('tests/phpunit/includes/search/SearchUpdateTest.php');
require('tests/phpunit/includes/specials/QueryAllSpecialPagesTest.php');
require('tests/phpunit/includes/specials/SpecialRecentchangesTest.php');
require('tests/phpunit/includes/specials/SpecialSearchTest.php');
require('tests/phpunit/includes/upload/UploadFromUrlTest.php');
require('tests/phpunit/includes/upload/UploadStashTest.php');
require('tests/phpunit/includes/upload/UploadTest.php');
require('tests/phpunit/languages/LanguageAmTest.php');
require('tests/phpunit/languages/LanguageArTest.php');
require('tests/phpunit/languages/LanguageBeTest.php');
require('tests/phpunit/languages/LanguageBe_taraskTest.php');
require('tests/phpunit/languages/LanguageBhTest.php');
require('tests/phpunit/languages/LanguageBsTest.php');
require('tests/phpunit/languages/LanguageCsTest.php');
require('tests/phpunit/languages/LanguageCuTest.php');
require('tests/phpunit/languages/LanguageCyTest.php');
require('tests/phpunit/languages/LanguageDsbTest.php');
require('tests/phpunit/languages/LanguageFrTest.php');
require('tests/phpunit/languages/LanguageGaTest.php');
require('tests/phpunit/languages/LanguageGdTest.php');
require('tests/phpunit/languages/LanguageGvTest.php');
require('tests/phpunit/languages/LanguageHeTest.php');
require('tests/phpunit/languages/LanguageHiTest.php');
require('tests/phpunit/languages/LanguageHrTest.php');
require('tests/phpunit/languages/LanguageHsbTest.php');
require('tests/phpunit/languages/LanguageHuTest.php');
require('tests/phpunit/languages/LanguageHyTest.php');
require('tests/phpunit/languages/LanguageKshTest.php');
require('tests/phpunit/languages/LanguageLnTest.php');
require('tests/phpunit/languages/LanguageLtTest.php');
require('tests/phpunit/languages/LanguageLvTest.php');
require('tests/phpunit/languages/LanguageMgTest.php');
require('tests/phpunit/languages/LanguageMkTest.php');
require('tests/phpunit/languages/LanguageMlTest.php');
require('tests/phpunit/languages/LanguageMoTest.php');
require('tests/phpunit/languages/LanguageMtTest.php');
require('tests/phpunit/languages/LanguageNlTest.php');
require('tests/phpunit/languages/LanguageNsoTest.php');
require('tests/phpunit/languages/LanguagePlTest.php');
require('tests/phpunit/languages/LanguageRoTest.php');
require('tests/phpunit/languages/LanguageRuTest.php');
require('tests/phpunit/languages/LanguageSeTest.php');
require('tests/phpunit/languages/LanguageSgsTest.php');
require('tests/phpunit/languages/LanguageShTest.php');
require('tests/phpunit/languages/LanguageSkTest.php');
require('tests/phpunit/languages/LanguageSlTest.php');
require('tests/phpunit/languages/LanguageSmaTest.php');
require('tests/phpunit/languages/LanguageSrTest.php');
require('tests/phpunit/languages/LanguageTest.php');
require('tests/phpunit/languages/LanguageTiTest.php');
require('tests/phpunit/languages/LanguageTlTest.php');
require('tests/phpunit/languages/LanguageTrTest.php');
require('tests/phpunit/languages/LanguageUkTest.php');
require('tests/phpunit/languages/LanguageUzTest.php');
require('tests/phpunit/languages/LanguageWaTest.php');
require('tests/phpunit/languages/utils/CLDRPluralRuleEvaluatorTest.php');
require('tests/phpunit/maintenance/DumpTestCase.php');
require('tests/phpunit/maintenance/MaintenanceTest.php');
require('tests/phpunit/maintenance/backupPrefetchTest.php');
require('tests/phpunit/maintenance/backupTextPassTest.php');
require('tests/phpunit/maintenance/backup_LogTest.php');
require('tests/phpunit/maintenance/backup_PageTest.php');
require('tests/phpunit/maintenance/fetchTextTest.php');
require('tests/phpunit/maintenance/getSlaveServerTest.php');
require('tests/phpunit/phpunit.php');
require('tests/phpunit/skins/SideBarTest.php');
require('tests/phpunit/suites/ExtensionsTestSuite.php');
require('tests/phpunit/suites/UploadFromUrlTestSuite.php');
require('tests/qunit/QUnitTestResources.php');
require('tests/qunit/data/load.mock.php');
require('tests/qunit/data/styleTest.css.php');
require('tests/selenium/Selenium.php');
require('tests/selenium/SeleniumConfig.php');
require('tests/selenium/SeleniumLoader.php');
require('tests/selenium/SeleniumServerManager.php');
require('tests/selenium/SeleniumTestCase.php');
require('tests/selenium/SeleniumTestConsoleLogger.php');
require('tests/selenium/SeleniumTestConstants.php');
require('tests/selenium/SeleniumTestHTMLLogger.php');
require('tests/selenium/SeleniumTestListener.php');
require('tests/selenium/SeleniumTestSuite.php');
require('tests/selenium/installer/MediaWikiButtonsAvailabilityTestCase.php');
require('tests/selenium/installer/MediaWikiDifferentDatabaseAccountTestCase.php');
require('tests/selenium/installer/MediaWikiDifferntDatabasePrefixTestCase.php');
require('tests/selenium/installer/MediaWikiErrorsConnectToDatabasePageTestCase.php');
require('tests/selenium/installer/MediaWikiErrorsNamepageTestCase.php');
require('tests/selenium/installer/MediaWikiHelpFieldHintTestCase.php');
require('tests/selenium/installer/MediaWikiInstallationCommonFunction.php');
require('tests/selenium/installer/MediaWikiInstallationConfig.php');
require('tests/selenium/installer/MediaWikiInstallationMessage.php');
require('tests/selenium/installer/MediaWikiInstallationVariables.php');
require('tests/selenium/installer/MediaWikiInstallerTestSuite.php');
require('tests/selenium/installer/MediaWikiMySQLDataBaseTestCase.php');
require('tests/selenium/installer/MediaWikiMySQLiteDataBaseTestCase.php');
require('tests/selenium/installer/MediaWikiOnAlreadyInstalledTestCase.php');
require('tests/selenium/installer/MediaWikiRestartInstallationTestCase.php');
require('tests/selenium/installer/MediaWikiRightFrameworkLinksTestCase.php');
require('tests/selenium/installer/MediaWikiUpgradeExistingDatabaseTestCase.php');
require('tests/selenium/installer/MediaWikiUserInterfaceTestCase.php');
require('tests/selenium/suites/AddContentToNewPageTestCase.php');
require('tests/selenium/suites/AddNewPageTestCase.php');
require('tests/selenium/suites/CreateAccountTestCase.php');
require('tests/selenium/suites/DeletePageAdminTestCase.php');
require('tests/selenium/suites/EmailPasswordTestCase.php');
require('tests/selenium/suites/MediaWikiEditorConfig.php');
require('tests/selenium/suites/MediaWikiEditorTestSuite.php');
require('tests/selenium/suites/MediaWikiExtraTestSuite.php');
require('tests/selenium/suites/MediawikiCoreSmokeTestCase.php');
require('tests/selenium/suites/MediawikiCoreSmokeTestSuite.php');
require('tests/selenium/suites/MovePageTestCase.php');
require('tests/selenium/suites/MyContributionsTestCase.php');
require('tests/selenium/suites/MyWatchListTestCase.php');
require('tests/selenium/suites/PageDeleteTestSuite.php');
require('tests/selenium/suites/PageSearchTestCase.php');
require('tests/selenium/suites/PreviewPageTestCase.php');
require('tests/selenium/suites/SavePageTestCase.php');
require('tests/selenium/suites/SimpleSeleniumConfig.php');
require('tests/selenium/suites/SimpleSeleniumTestCase.php');
require('tests/selenium/suites/SimpleSeleniumTestSuite.php');
require('tests/selenium/suites/UserPreferencesTestCase.php');
require('tests/testHelpers.inc');