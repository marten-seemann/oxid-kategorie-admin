<?php
require("inc/includes.inc.php");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getLanguageCode(); ?>">
<head>
  <meta charset="UTF-8">
  <title><?php echo $lang->page_title; ?></title>
  <link rel="stylesheet" href="assets/css/font-awesome.css">
  <link href="assets/bootstrap/css/bootstrap.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/jquery.fileuploader.css">
  <link rel="stylesheet" href="assets/css/jquery.noty.css">
  <link rel="stylesheet" href="assets/css/jquery.noty_theme_twitter.css">
  <link rel="stylesheet" href="assets/css/jquery.contextMenu.css">
  <link rel="stylesheet" href="assets/css/jquery.ddslick.css">
  <link rel="stylesheet" href="assets/css/styles.css">
  <script src="assets/js/jquery-1.8.2.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/js/jquery.typewatch.js"></script>
  <script src="assets/js/jquery.noty.js"></script>
  <script src="assets/js/jquery.cookie.js"></script>
  <script src="assets/js/jquery.media.js"></script>
  <script src="assets/js/jquery.fileuploader.js"></script>
  <script src="assets/js/jquery.form.js"></script>
  <script src="assets/js/jquery.idle-timer.js"></script>
  <script src="assets/js/noty_config.js"></script>
  <script src="assets/js/jquery.contextMenu.js"></script>
  <script src="assets/jstree/jquery.jstree.js"></script>
  <script src="assets/js/src/notification_handler.js"></script>
  <script src="assets/js/src/language_handler.js"></script>
  <script src="assets/js/src/category_tree.js"></script>
  <script src="assets/js/src/category_details.js"></script>
  <script src="assets/js/src/main.js"></script>
  <script src="assets/js/jquery.timer.js"></script>
  <script src="assets/js/jquery.ddslick.js"></script>
  <script src="assets/js/lang.js.php?lang=<?php echo $lang->getLanguageCode(); ?>"></script>
</head>
<body>
<script>
  document.config = { dynamic_sorting: <?php if($config['dynamic_sorting']) echo "true"; else echo "false"; ?> }
</script>

  <div class="container-fluid">
    <div class="row">
      <header>
        <div class="col-sm-12">
          <h1><?php echo $lang->page_header; ?><span id="help"><i class="icon-question-sign icon-small"></i></span></h1>
          <div id="header_functions_right">
            <div><a class="btn btn-default" id="refresh"><i class="icon-refresh"></i> <?php echo $lang->refresh_button; ?></a></div>
            <div id="cat_language_switcher_wrapper">
              <div><?php echo $lang->category_master_language; ?>:</div>
              <div id="cat_language_switcher" class="language_switcher">
                <form action="#">
                  <select>
                  <?php
                  $languages = LanguageHelper::availableLanguages();
                  foreach($languages as $key) {
                    echo "<option value='$key' data-imagesrc='assets/flags/".$lang->getFlagCode($key).".png' ";
                    if($key == $lang->getLanguageCode()) echo ' selected="selected"';
                    echo ">".$lang->getLanguageName($key)."</option>\n";
                  }
                  ?>
                  </select>
                </form>
              </div>
            </div>
            <div id="oxid_language_switcher_wrapper">
            <?php
            $oxid_languages = $lang->getOxidLanguages();
            if(count($oxid_languages) > 0 ) {
            echo "<div>{$lang->oxid_language}:</div>\n";
            echo "<div id=\"oxid_language_switcher\">\n";
            echo "<form action=\"#\">\n<select>\n";
            foreach($oxid_languages as $key) {
              echo "<option value='$key' data-imagesrc=\"assets/flags/".$lang->getFlagCode($key).".png\" ";
              if(isset($_COOKIE['cat_oxid_language']) AND $key == $_COOKIE['cat_oxid_language']) echo " selected=\"selected\"";
              echo ">".$lang->getLanguageName($key)."</option>\n";
            }
            echo "</select></form>\n";
            echo "</div>";
            }
            ?>
            </div>
          </div>
        </div>
      </header>
      <div class="modal fade" id="modal_help">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">x</button>
              <h4 class="modal-title"><?php echo $lang->help_modal_legend; ?></h4>
            </div>
            <div class="modal-body">
              <?php echo str_replace('%VERSION%', $version, $lang->help_modal_data); ?>
            </div>
            <div class="modal-footer">
              <a href="#" class="btn btn-default" data-dismiss="modal"><?php echo $lang->modal_close; ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-3">
        <section id="categories">
          <h2><?php echo $lang->category_header; ?><span id="category-info"><i class="icon-info-sign"></i></span></h2>
          <div class="modal fade" id="modal_categories">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">x</button>
                  <h4 class="modal-title"><?php echo $lang->category_modal_legend; ?></h4>
                </div>
                <div class="modal-body">
                  <?php echo $lang->category_modal_data; ?>
                </div>
                <div class="modal-footer">
                  <a href="#" class="btn btn-default" data-dismiss="modal"><?php echo $lang->modal_close; ?></a>
                </div>
              </div>
            </div>
          </div>

          <input type="text" id="tree_search" class="form-control" placeholder="<?php echo $lang->category_search; ?>" />
          <!-- <div id="tree_close_all"><input type="button" class="btn" value="<?php echo $lang->categories_close_all; ?>" /></div> -->
          <div id="category_tree">
          </div>
          <div id="search_hidden_cat_warning" class="alert alert-block">
            <a class="close" data-dismiss="alert" href="#">&times;</a>
            <h4 class="alert-heading"><?php echo $lang->categories_hidden_warning_heading; ?></h4>
            <?php echo $lang->categories_hidden_warning_text; ?>
          </div>
        </section>
      </div>
      <div class="col-sm-9">
       <section id="details">
          <?php if(file_exists("inc/demo_note.inc.php")) { require("inc/demo_note.inc.php"); } ?>
          <?php if(!isset($config['disable_backup_notice']) OR !$config['disable_backup_notice']) {
            echo "<div class=\"alert alert-warning alert-dismissable\">
             <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
               {$lang->backup_notice}
            </div>";
           }
          ?>
         <h2>Details</h2>
         <div id="details_main"></div>
       </section>
      </div>
    </div>
  </div>
  <?php if(file_exists("inc/tracker.inc.php")) { require("inc/tracker.inc.php"); } ?>
</body>
</html>

