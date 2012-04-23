<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

global $TYPO3_CONF_VARS;

$sysconf = (array) unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY]);

//User overload configuration
if (isset($sysconf['userconf_allowed']) && $sysconf['userconf_allowed']) {
    if(!is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php'])){
        $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php'] = array();
    }
    if(!is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'])){
        $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'] = array();
    }
    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'user_obladydebug_overloadConfiguration';
}

function user_obladydebug_overloadConfiguration($params, $pObj){
    
    //mail('adrien@oblady.fr', 'debug win', var_export($pObj, true));
    if($pObj->user['TSconfig']){
        $parseObj = t3lib_div::makeInstance('t3lib_TSparser_TSconfig');
        $res = $parseObj->parseTSconfig($pObj->user['TSconfig'], 'userTS');
        if(isset($res['TSconfig']['tx_obladydebug.'])){
            $e = tx_obladydebug_ErrorList::getInstance();
            $e->setConfiguration($res['TSconfig']['tx_obladydebug.']);
        }
    }
}


//Constants
define('E_USER_ALL',	E_USER_NOTICE | E_USER_WARNING | E_USER_ERROR);
define('E_NOTICE_ALL',	E_NOTICE | E_USER_NOTICE);
define('E_WARNING_ALL',
    E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING);
define('E_ERROR_ALL',
    E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
define('E_ALL_NOT_NOTICE',	E_ALL & ~E_NOTICE_ALL);
define('E_DEBUG',		0x10000000);
define('E_VERY_ALL',	E_ERROR_ALL | E_WARNING_ALL | E_NOTICE_ALL | E_DEBUG);

define('SYSTEM_LOG',	0);
define('SAPI_LOG',		4);
define('MAIL_LOG',		1);
define('FILE_LOG',		3);

//GLOBALS objects
global $error, $errorReporter, $errorList;

$error = $errorList = tx_obladydebug_ErrorList::getInstance($sysconf);
$errorReporter = $errorList->getReporter();

if (isset($sysconf['set_error_handler']) && $sysconf['set_error_handler']) {
    set_error_handler(array($errorList, 'trapError'));
}

if (isset($sysconf['set_exception_handler']) && $sysconf['set_exception_handler']) {
    set_exception_handler(array($errorList, 'trapException'));
}

?>