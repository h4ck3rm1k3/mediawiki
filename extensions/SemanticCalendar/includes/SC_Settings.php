<?php

###
# This is the path to your installation of Semantic Calendar as
# seen from the web. Change it if required ($wgScriptPath is the
# path to the base directory of your wiki). No final slash.
##
$scgScriptPath = $wgScriptPath . '/extensions/SemanticCalendar';
##

###
# This is the path to your installation of Semantic Calendar as
# seen on your local filesystem. Used against some PHP file path
# issues.
##
$scgIP = $IP . '/extensions/SemanticCalendar';
##


// PHP fails to find relative includes at some level of inclusion:
//$pathfix = $IP . $scgScriptPath;

// load global functions
require_once('SC_GlobalFunctions.php');
