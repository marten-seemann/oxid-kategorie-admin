<?php
// this file loads the language which the user wants to have
// this desired language is either transmitted via GET or via COOKIE
// otherwise we take the value from the configuration file
if(strpos($_SERVER['SCRIPT_FILENAME'],"/ajax/") !== false) $path = "../";
else $path = "";
if(isset($_GET['lang'])) $lang_requested = $_GET['lang'];
else if(isset($_COOKIE['cat_language'])) $lang_requested = $_COOKIE['cat_language'];
else $lang_requested = $config['language']; // this line only exists to make sure that $lang_requested exists
// check if the requested language is available. if not, use the language provided in the configuration file
if(in_array($lang_requested, LanguageHelper::availableLanguages())) $lang = new LanguageHelper($lang_requested, $path);
else $lang = new LanguageHelper($config['language'], $path);

if(isset($_COOKIE['cat_oxid_language'])) {
  $oxid_lang = $_COOKIE['cat_oxid_language'];
  $lang->setOxidLanguage($oxid_lang);
}
?>
