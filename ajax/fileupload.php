<?php
require("../inc/includes.inc.php");
require("../inc/fileupload.class.php");

$catHandler = new CategoryHandler();

// handle delete
if(isset($_POST['action']) AND $_POST['action'] == 'delete') {
  $mode = $_POST['role'];
  $cat_id = $_POST['cat_id'];
  if($catHandler->deletePicture($cat_id, $mode)) echo "true";
  else echo "false";
  die;
}

// handle uploads
$mode = $_GET['role'];
$cat_id = $_GET['cat_id'];
$filename = $_GET['qqfile'];

$allowedExtensions = array("png", "jpg", "jpeg", "gif", "bmp", "tiff");
$sizeLimit = 10*1024*1024;
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

// Call handleUpload() with the name of the folder, relative to PHP's getcwd()
$path = $config['oxid_basedir']."tmp/";
$result = $uploader->handleUpload($path, true);

$picpaths = $catHandler->savePicture($cat_id, $path.$filename, $_GET['role']);
if($picpaths === false) {
  echo json_encode(array("path" => "false"));
  die();
}
$imagesize = getimagesize($picpaths["filesystem"]);

$result = array(
  "path" => $picpaths["url"],
  "filename" => pathinfo($picpaths["url"], PATHINFO_BASENAME),
  "imagesize" => array(
    "width" => $imagesize[0],
    "height" => $imagesize[1]
    )
  );

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
?>