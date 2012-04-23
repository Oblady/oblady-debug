<?php

$extensionPath = t3lib_extMgm::extPath('oblady_debug');

//Load interfaces
require_once($extensionPath . 'Interfaces/Reporting.php');
require_once($extensionPath . 'Interfaces/Rendering.php');

//Return classes to load
$extensionClassesPath = $extensionPath . 'Classes/';
return array(
	'tx_obladydebug_div' => $extensionClassesPath . 'Div.php',
	'tx_obladydebug_erroriterator' => $extensionClassesPath . 'ErrorIterator.php',
	'tx_obladydebug_errorlist' => $extensionClassesPath . 'ErrorList.php',
	'tx_obladydebug_errorreporter' => $extensionClassesPath . 'ErrorReporter.php',
    
	'tx_obladydebug_reporting_base' => $extensionClassesPath . 'Reporting/Base.php',
	'tx_obladydebug_reporting_withdestination' => $extensionClassesPath . 'Reporting/WithDestination.php',
	'tx_obladydebug_reporting_stdout' => $extensionClassesPath . 'Reporting/Stdout.php',
	'tx_obladydebug_reporting_filelog' => $extensionClassesPath . 'Reporting/FileLog.php',
	'tx_obladydebug_reporting_maillog' => $extensionClassesPath . 'Reporting/MailLog.php',
	'tx_obladydebug_reporting_serverlog' => $extensionClassesPath . 'Reporting/ServerLog.php',
	'tx_obladydebug_reporting_systemelog' => $extensionClassesPath . 'Reporting/SystemeLog.php',
	'tx_obladydebug_reporting_browser' => $extensionClassesPath . 'Reporting/Browser.php',
	'tx_obladydebug_reporting_redirect' => $extensionClassesPath . 'Reporting/Redirect.php',
	'tx_obladydebug_reporting_popup' => $extensionClassesPath . 'Reporting/Popup.php',
	'tx_obladydebug_reporting_firebug' => $extensionClassesPath . 'Reporting/Firebug.php',
	'tx_obladydebug_reporting_firephp' => $extensionClassesPath . 'Reporting/FirePHP.php',
    
	'tx_obladydebug_rendering_plaintext' => $extensionClassesPath . 'Rendering/PlainText.php',
	'tx_obladydebug_rendering_html' => $extensionClassesPath . 'Rendering/HTML.php',
);

?>