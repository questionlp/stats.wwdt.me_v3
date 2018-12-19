<?php
/* Set namespace and pull in required WWDTM objects */
namespace WWDTM;

require_once __DIR__ . '/_includes/WWDTM/GraphJSONData.php';
require_once __DIR__ . '/_includes/WWDTM/PanelistData.php';

$panelistData = new PanelistData();
$graphJSONData = new GraphJSONData();

if (isset($_GET['type']) && isset($_GET['pnlid']) && is_numeric($_GET['pnlid'])) {
	$type = strtolower(trim($_GET['type']));
	$panelistID = (int) ($_GET['pnlid']);
	
	if ($panelistData->validatePanelistID($panelistID) && $type == 'spread') {
		header('Content-type: application/json');
		print $graphJSONData->generateScoreSpreadJSON($panelistID);
	} elseif ($panelistData->validatePanelistID($panelistID) && $type == 'trend') {
		header('Content-type: application/json');
		print $graphJSONData->generateScoreTrendJSON($panelistID);
	} else {
		return;
	}
}