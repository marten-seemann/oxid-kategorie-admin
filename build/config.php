<?php
// specify the language to use
$config['language'] = 'de'; // possibilities: de, en

// specify the path where your OXID shop installation is located
$config['oxid_basedir'] = '../';

// specify the path where your installation of the Category Master is located
$config['category_master_dir'] = '../kategorie-master/';

// should this tool handle the sorting?
// if set to "true", then the OXSORT values will be set automatically to ensure the correct order of your categories
// if set to "false", you have to set the OXSORT value manually in order to change the order of the categories
$config['dynamic_sorting'] = true;

// show the OXID of the category in category details
$config['details_show_oxid'] = false;

