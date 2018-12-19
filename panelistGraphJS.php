<?php
/* Set namespace and pull in required WWDTM objects */
namespace WWDTM;

require_once __DIR__ . '/_includes/WWDTM/GraphJSONData.php';
require_once __DIR__ . '/_includes/WWDTM/PanelistData.php';
require_once __DIR__ . '/_includes/WWDTM/Config.php';

$panelistData = new PanelistData();
$graphJSONData = new GraphJSONData();

header('Content-type: text/javascript');

if (isset($_GET['pnlid']) && is_numeric($_GET['pnlid'])) {
	$panelistID = (int) ($_GET['pnlid']);
	
	if ($panelistData->validatePanelistID($panelistID)) {
?>
AmCharts.ready(function() {
    AmCharts.theme = AmCharts.themes.wwdtm;
    var spreadData = AmCharts.loadJSON('<?php print GRAPH_LOAD_JSON_URL; ?>?type=spread&pnlid=<?php print $panelistID; ?>');
    var trendData = AmCharts.loadJSON('<?php print GRAPH_LOAD_JSON_URL; ?>?type=trend&pnlid=<?php print $panelistID; ?>');
    
    var spreadChart = new AmCharts.AmSerialChart();
    var spreadGraph = new AmCharts.AmGraph();
    var spreadCategoryAxis = spreadChart.categoryAxis;
    var spreadValueAxis = new AmCharts.ValueAxis();
    var spreadChartCursor = new AmCharts.ChartCursor();
	    
    spreadCategoryAxis.autoGridCount = false;
    spreadCategoryAxis.gridCount = 20;
    spreadCategoryAxis.title = 'Score';
    spreadValueAxis.title = '# Times Scored';
    spreadChartCursor.enabled = false;
	
    spreadChart.addValueAxis(spreadValueAxis);
    spreadChart.addChartCursor(spreadChartCursor);
    spreadChart.pathToImages = '<?php print GRAPH_PATH_TO_IMAGES; ?>';
    spreadChart.dataProvider = spreadData;
    spreadChart.categoryField = 'score';
    spreadChart.addTitle("Scoring Breakdown for <?php print $panelistData->panelists[$panelistID]; ?>");

    spreadGraph.labelText = '[[value]]';
    spreadGraph.type = 'column';
    spreadGraph.valueField = 'count';
    spreadGraph.showBalloon = false;
    spreadGraph.gridAboveGraphs = true;
    spreadGraph.lineAlpha = 0.2;
    spreadGraph.fillAlphas = 0.8;
    spreadChart.addGraph(spreadGraph);

    var trendChart = new AmCharts.AmSerialChart();
    var trendGraph = new AmCharts.AmGraph();
    var trendCategoryAxis = trendChart.categoryAxis;
    var trendValueAxis = new AmCharts.ValueAxis();
    var trendChartScrollbar = new AmCharts.ChartScrollbar();
    var trendChartCursor = new AmCharts.ChartCursor();

    trendCategoryAxis.labelsEnabled = false;
    trendCategoryAxis.tickLength = 0;
    trendCategoryAxis.title = 'Appearance';
    trendValueAxis.title = 'Score';
    trendValueAxis.minimum = 0;

    trendChartScrollbar.autoGridCount = false;
    trendChartCursor.cursorPosition = 'mouse';
    trendChartCursor.categoryBalloonEnabled = false;

    trendChart.addValueAxis(trendValueAxis);
    trendChart.addChartScrollbar(trendChartScrollbar);
    trendChart.addChartCursor(trendChartCursor);
    trendChart.pathToImages = '<?php print GRAPH_PATH_TO_IMAGES; ?>';
    trendChart.dataProvider = trendData;
    trendChart.categoryField = 'showdate';
    trendChart.addTitle("Score Graph for <?php print $panelistData->panelists[$panelistID]; ?>");

    trendGraph.valueField = 'score';
    trendGraph.bullet = 'round';
    trendGraph.bulletSize = 1;
    trendGraph.bulletBorderColor = '#ffffff';
    trendGraph.bulletBorderThickness = 0;
    trendGraph.balloonText = '[[category]]: <b>[[value]]</b>';
    trendChart.addGraph(trendGraph);

    spreadChart.write('pnl<?php print $panelistID; ?>spread');
    trendChart.write('pnl<?php print $panelistID; ?>trend');
});
<?php
	} else {
		print '// Invalid panelist ID';
	}
}
