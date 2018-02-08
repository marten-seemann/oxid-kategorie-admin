<?php
require("../inc/includes.inc.php");

function printCategories($cat_id = "") {
  global $cats;
  $maxlength = 24;
  if(empty($cat_id)) $root = false;
  else $root = true;
  if($root) $sub_cats = $cats->getSubCategories($cat_id);
  else $sub_cats = $cats->getRootCategories();

  if(count($sub_cats)==0) return array();
  $data = array();
  // var_dump($sub_cats);
  foreach($sub_cats as $cat) {
    $classes = array();
    if($cats->isCategoryHidden($cat)) $classes[] = "category_hidden";
    if(!$cats->isCategoryActive($cat)) $classes[] = "category_inactive";
    // shorten long category names
    $shortened = false;
    $name_long = $cats->getCategoryName($cat);
    if(strlen($name_long) > ($maxlength+4)) {
      $name = mb_substr($name_long, 0, $maxlength,'UTF-8')."...";
    }
    else {
      $name = $name_long;
      $name_long = "";
    }
    if(strlen($name) == 0) $name = " "; // workaround to fix a bug in jstree occuring with empty category names and enabled dnd plugin
    $data[] = array(
        "attr" => array(
          "id" => "node_{$cat}",
          "class" => implode(" ", $classes)
          ),
        "data" => array(
          "title" => $name
          ),
        "metadata" => array(
          "name_long" => $name_long,
          "sort" => $cats->getCategorySort($cat)
          ),
        "children" => printCategories($cat)
      );
  }
  return $data;
}


$timer = new Timer();
$timer->start("categories");
$cats = new CategoryHandler(true);

// display oxroot
$categories = array(
  "attr" => array(
    "id" => "node_root"
    ),
  "data" => array(
    "title" => $lang->category_oxroot
    ),
  "metadata" => array(
    "sort" => ""
    ),
  "children" => printCategories()
  );
// var_dump($categories);
// var_dump(printCategories());
$timer->stop("categories");
$timer->start("formatting");
// var_dump($categories);
echo json_encode($categories);
$timer->stop("formatting");
// var_dump($timer->getAll());
?>
