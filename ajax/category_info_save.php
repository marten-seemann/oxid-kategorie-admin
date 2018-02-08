<?php
require("../inc/includes.inc.php");
$catHandler = new CategoryHandler();

$cat = $_POST['cat_id'];
if(!$catHandler->categoryExists($cat)) die("false");

function checkboxValue($key) {
  if(isset($_POST[$key]) AND $_POST[$key]) return "1";
  else return "0";
}

// in some database fields, it is important if the value is NULL or 0, e.g. for the VAT
// so make sure the correct value gets written: if the user posted an empty string, write NULL, else write the string
function getValueOrNull($value) {
  global $db;
  if(strlen($value) == 0) return "NULL";
  else return "'".$db->validate($value)."'";
}

$db->query("UPDATE oxcategories SET
  OXTITLE='".$db->validate($_POST['oxtitle'])."',
  OXACTIVE='".checkboxValue('oxactive')."',
  OXHIDDEN='".checkboxValue('oxhidden')."',
  OXDESC='".$db->validate($_POST['oxdesc'])."',
  OXLONGDESC='".$db->validate($_POST['oxlongdesc'])."',
  OXSORT='".$db->validate($_POST['oxsort'])."',
  OXEXTLINK='".$db->validate($_POST['oxextlink'])."',
  OXVAT=".getValueOrNull($_POST['oxvat']).",
  OXPRICEFROM=".getValueOrNull($_POST['oxpricefrom']).",
  OXPRICETO=".getValueOrNull($_POST['oxpriceto']).",
  OXSKIPDISCOUNTS='".checkboxValue('oxskipdiscounts')."',
  OXTEMPLATE='".$db->validate($_POST['oxtemplate'])."'
  WHERE OXID='".$db->validate($cat)."'");

$res = array();
$res['cat_id'] = $cat;
$res['oxactive'] = intval(checkboxValue('oxactive'));
$res['oxhidden'] = intval(checkboxValue('oxhidden'));
$res["sort"] = $_POST['oxsort'];

echo json_encode($res);


?>