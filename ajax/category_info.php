<?php
require("../inc/includes.inc.php");
$cat = $_GET['cat'];

$fieldlist = array("OXID", "OXTITLE", "OXSORT", "OXHIDDEN", "OXACTIVE", "OXSHOPID", "OXDESC", "OXEXTLINK", "OXVAT", "OXSKIPDISCOUNTS", "OXPRICEFROM", "OXPRICETO", "OXLONGDESC", "OXICON", "OXTHUMB", "OXTEMPLATE");

// check if the column OXPROMOICON is available
// this is only the case in >= OXID 4.5.0
$result = $db->query("SHOW COLUMNS FROM oxcategories LIKE 'OXPROMOICON'");
if($result->num_rows > 0) $fieldlist[] = "OXPROMOICON";
$result = $db->query("SELECT ".implode(",", $fieldlist)." FROM oxcategories WHERE OXID='".$db->validate($cat)."'");if($result->num_rows == 0) die("false");
if($result->num_rows == 0) die("false");
$data = $result->fetch_object();
?>

<form id="cat_details" class="form-horizontal">
  <input type="hidden" id="cat_id" name="cat_id" value="<?php echo inputEscape($cat); ?>">

  <ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active"><a href="#main" data-toggle="tab"><?php echo $lang->category_tab_main; ?></a></li>
    <li><a href="#description" data-toggle="tab"><?php echo $lang->category_tab_description; ?></a></li>
    <li><a href="#pictures" data-toggle="tab"><?php echo $lang->category_tab_pictures; ?></a></li>
    <!-- <li><a href="#seo" data-toggle="tab"><?php echo $lang->category_tab_seo; ?></a></li> -->
    <li><a href="#articles" data-toggle="tab"><?php echo $lang->category_tab_articles; ?></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade active in" id="main">
      <div class="col-sm-11 col-md-9">
        <?php
        if(isset($config['details_show_oxid']) AND $config['details_show_oxid']) {
        ?>
          <div class="form-group">
            <label class="control-label col-sm-3" ><?php echo $lang->category_oxid; ?></label>
            <div class="col-sm-9"><div class="only-text"><?php echo $data->OXID; ?></div></div>
          </div>
        <?php
        }
        ?>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxtitle"><?php echo $lang->category_oxtitle; ?></label>
          <div class="col-sm-9"><?php echo getInput("oxtitle", true); ?></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxsort"><?php echo $lang->category_oxsort; ?></label>
          <div class="col-sm-9"><?php echo getInput("oxsort", true, "input-mini", "text"); ?></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxactive"><?php echo $lang->category_oxactive; ?></label>
          <div class="col-sm-9"><input type="checkbox" id="input_oxactive" name="oxactive" <?php if($data->OXACTIVE) echo "checked=\"checked\""; ?>></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxhidden"><?php echo $lang->category_oxhidden; ?></label>
          <div class="col-sm-9"><input type="checkbox" id="input_oxhidden" name="oxhidden" <?php if($data->OXHIDDEN) echo "checked=\"checked\""; ?>></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxdesc"><?php echo $lang->category_oxdesc; ?></label>
          <div class="col-sm-9"><?php echo getInput("oxdesc", false); ?></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxextlink"><?php echo $lang->category_oxextlink; ?></label>
          <div class="col-sm-9"><?php echo getInput("oxextlink", false, "", "url"); ?></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxvat"><?php echo $lang->category_oxvat; ?></label>
          <div class="col-sm-9"><?php echo getInput("oxvat", false, "input-mini"); ?></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxskipdiscounts"><?php echo $lang->category_oxskipdiscounts; ?></label>
          <div class="col-sm-9"><input type="checkbox" id="input_oxskipdiscounts" name="oxskipdiscounts" <?php if($data->OXSKIPDISCOUNTS) echo "checked=\"checked\""; ?>></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3" for="input_oxtemplate"><?php echo $lang->category_oxtemplate; ?></label>
          <div class="col-sm-9"><?php echo getInput("oxtemplate", false, "input-medium"); ?></div>
        </div>
        <div class="form-group">
          <div class="form-inline">
            <label class="control-label col-sm-3" for="input_oxpricefrom"><?php echo $lang->category_oxpricefromto; ?></label>
            <div class="col-sm-9"><?php echo $lang->category_oxpricefrom." ".getInput("oxpricefrom", false, "input-mini")." ".$lang->category_oxpriceto." ".getInput("oxpriceto", false, "input-mini"); ?></div>
          </div>
        </div>
        <?php printFormActions(); ?>
      </div>
    </div>
    <div class="tab-pane fade" id="description">
      <div class="form-group">
        <label class="control-label col-sm-3" for="input_oxlongdesc"><?php echo $lang->category_oxlongdesc; ?></label>
        <div class="col-sm-9">
          <textarea name="oxlongdesc" id="input_oxlongdesc" class="form-control" style="width: 80%" rows="13"><?php echo $data->OXLONGDESC; ?></textarea>
          <!-- <div id="input_oxlongdesc"></div> -->
        </div>
      </div>
      <?php printFormActions(); ?>
    </div>
    <div class="tab-pane fade" id="pictures">
      <?php
      if(isset($data->OXPROMOICON)) { // the picture upload tab is only available for >= OXID 4.5.0 (below this version the pictures where saved differently in the file system)
        printPictureUpload("thumb");
        printPictureUpload("icon");
        printPictureUpload("promo_icon");
      }
      else echo "<div class=\"alert alert-error\">{$lang->picture_error_old_oxid}</div>";
      ?>
    </div>
    <!-- <div class="tab-pane" id="seo">seo...</div> -->
    <div class="tab-pane fade" id="articles">
      <?php
        $printed = false;
        if(isset($config['category_master_dir'])) {
          $cm_path = realpath(dirname(__FILE__)."/../".$config['category_master_dir']);
          if(file_exists($cm_path) AND is_dir($cm_path)) {
            echo "<p>{$lang->link_category_master_category_desc}</p>";
            $link = $config['category_master_dir']."?only_cat=".urlencode($cat);
            echo "<a href=\"$link\" class=\"btn btn-primary\" target=\"_blank\"><i class=\"icon-external-link\"></i> {$lang->link_category_master_category}</a>";
            $printed = true;
          }
        }
        if(!$printed) echo $lang->link_category_master_buy;
      ?>
    </div>
  </div>
</form>





<?php
function inputEscape($string) {
  return htmlspecialchars($string);
}

function printFormActions() {
  global $lang;
  echo "
  <div class=\"form-actions\">
    <button type=\"submit\" class=\"btn btn-primary\"><i class=\"icon-save\"></i> {$lang->category_details_submit}</button>
    <button type=\"reset\" class=\"btn btn-danger\"><i class=\"icon-remove\"></i> {$lang->category_details_reset}</button>
  </div>";
}

function printPictureUpload($mode) {
  global $lang;
  global $config;
  global $data;

  $oxidpath = "out/pictures/master/category/";
  $path = $config['oxid_basedir'].$oxidpath;
  switch($mode) {
    case "promo_icon": $field = "OXPROMOICON"; break;
    case "icon": $field = "OXICON"; break;
    case "thumb": $field = "OXTHUMB"; break;
  }
  $imgpath = $mode."/".$data->$field;
  if(file_exists($path.$imgpath) AND is_file($path.$imgpath)) $has_img = true;
  else $has_img = false;

  $key = "picture_legend_{$mode}";
  echo "
  <legend>{$lang->$key}</legend>";
  if($mode == "thumb") echo "<div class=\"alert\">{$lang->picture_thumb_lang_notice}</div>";
  echo "
   <div id=\"file-uploader-{$mode}\" class=\"fileuploader\">
    <div class=\"image\">";
    if($has_img) echo "<img src='".$config['oxid_basedir_orig'].$oxidpath.$imgpath."'>";
    echo "</div>
    <div class=\"upload-controls\">
     <div class=\"upload-infos\">
       <dl>
         <dt>{$lang->picture_filename}</dt><dd class=\"picture-filename\">{$data->$field}</dd>
         <dt>{$lang->picture_imagesize}</dt><dd><span class=\"picture-imagesize-width\">";
         if($has_img) {
            $imagesize = getimagesize($path.$imgpath);
            echo $imagesize[0];
         }
         echo "</span> x <span class=\"picture-imagesize-height\">";
         if($has_img) echo $imagesize[1];
         echo "</span></dd>
       </dl>
     </div>
      <div class=\"upload-add\"></div>
      <div class=\"upload-delete\"><button class=\"btn btn-danger\" type=\"button\"><i class=\"icon-trash\"></i> {$lang->picture_delete_button}</button></div>
    </div>
  </div>";
}


// language: bool
function getInput($name, $required = false, $classes = "", $type = "text") {
  global $lang;
  global $data;

  $value = $data->{strtoupper($name)};
  $html = "<input type=\"$type\" id=\"input_$name\" name=\"$name\" class=\"form-control $classes\" value=\"".inputEscape($value)."\"";
  if($required) $html .= " required";
  $html .= ">";
  return $html;
}
?>
