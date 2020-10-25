<?php
# Copyright (c) 2007-2020 Linh Pham
# wwdt.me_v3 is relased under the terms of the Apache License 2.0

define('VERSION', '3.4.0');
define('DEVMODE', 1);

/* Database Access */
define('DB_TYPE', 'mysqli');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_SERVER', '');
define('DB_NAME', '');

/* Site Information */
define('SITE_PATH', '/path/to/wwdt.me/');
define('SITE_NAME', "Wait Wait... Don't Tell Me! Stats and Show Details");
define('SITE_CSS_PATH', '/css/style.css');
define('CACHE_MAX_AGE', '3600');

/* Show Information */
define('SHOW_ROW_TEMPLATE_FILE', SITE_PATH . '/_includes/Templates/ShowRow.html.tmpl');
define('SHOW_RECENT_DAYS', 32);
define('SHOW_LOCATIONID_CHICAGO_STUDIO', 1);
define('SHOW_LOCATIONID_CHICAGO_CHASE', 2);
define('SHOW_LOCATIONID_TBD', 3);
define('SHOW_HOSTID_PETER_SAGAL', 1);
define('SHOW_HOSTID_LUKE_BURBANK', 2);
define('SHOW_HOSTID_TBD', 6);
define('SHOW_SCOREKEEPERID_CARL_KASELL', 1);
define('SHOW_SCOREKEEPERID_BILL_KURTIS', 11);
define('SHOW_SCOREKEEPERID_TBD', 8);
define('SHOW_SCOREKEEPER_DISPLAY_CARL_EMERITUS', 1);
define('SHOW_PANELISTID_LUKE_BURBANK', 14);
define('SHOW_PANELISTID_MULTIPLE', 17);
define('SHOW_GUESTID_NONE', 76);

/* Easter Egg */
define('EASTER_EGG_TAG1', '##_EE1_##');
define('EASTER_EGG_LINK1', 'http://vimeo.com/62793388');

/* Graph Settings */
define('GRAPH_FONT', SITE_PATH . '/_includes/Fonts/LiberationSans-Regular.ttf');
define('GRAPH_FONT_SIZE', 10);
define('GRAPH_WIDTH', 750);
define('GRAPH_HEIGHT', 320);
define('LARGE_GRAPH_WIDTH', 1600);
define('LARGE_GRAPH_HEIGHT', 665);
define('GRAPH_OUTPUT_PATH', SITE_PATH . '/graphs/');
define('LARGE_GRAPH_OUTPUT_PATH', SITE_PATH . '/large-graphs/');
define('GRAPH_BAR_COLOR', '#f15930');
define('GRAPH_LINE_COLOR', '#000000');
define('GRAPH_LINEGRAPH_COLOR', '#f15930');
define('GRAPH_BACKGROUND_COLOR', '#FFFFFF');
define('GRAPH_BORDER_COLOR', '#FFFFFF');
define('GRAPH_FILE_TYPE', 'png');

define('GRAPH_LOAD_JSON_URL', '/panelistJSONData.php');
define('GRAPH_PATH_TO_IMAGES', '/js/amcharts/images/');
?>
