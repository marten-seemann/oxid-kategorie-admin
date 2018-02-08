<?php
require("../inc/includes.inc.php");

$catHandler = new CategoryHandler();

if(isset($_POST['cat'])) $cat = $_POST['cat'];
$mode = $_POST['mode'];
if($mode == "move") { // handles all category movements (including reorders)
  // diefalse();
  $target = $_POST['target'];
  if(strlen($target) == 0) diefalse();
  $order = $_POST['order'];

  $res = array();

  if($target != $catHandler->getParentCategory($cat)) { // move the category to another parent category. is not needed when just reordering a category
    if($target == "root") $target = "oxrootid";
    if($catHandler->setParentCategory($cat, $target)) $res['move'] = "true";
    else {
      $res['move'] = "false";
      diefalse();
    }
  }
  // now deal with the correct category order
  if($config['dynamic_sorting']) {
    $reorder = $catHandler->reorder($order);
    if($reorder === false) $res['order'] = false;
    else $res['order'] = $reorder;
  }
  echo json_encode($res);
}
else if($mode == "add") {
  $title = $_POST['name'];
  if($cat == "root") $cat = "oxrootid";
  $res = $catHandler->newCategory($cat, $title);
  if($res === false) diefalse();
  else {
    $res = array(
      "id" => $res["id"],
      "sort" => $res["sort"],
      "active" => $res["active"],
      "hidden" => $res["hidden"]
      );
    echo json_encode($res);
  }
}
else if($mode == "delete") {
  if($catHandler->deleteCategory($cat)) echo json_encode("true");
  else diefalse();
}
else diefalse();


function diefalse() {
  die(json_encode("false"));
}
?>