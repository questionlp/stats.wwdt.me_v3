<?php
/* Legacy File -- No Longer Used */

require_once __DIR__ . '/../_includes/WWDTM/GraphData.php';

$graphs = new \WWDTM\GraphData();
$graphs->generatePanelistGraphs();
$graphs->generateLargePanelistGraphs();
?>
