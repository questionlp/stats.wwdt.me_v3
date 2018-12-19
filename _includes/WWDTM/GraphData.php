<?php
namespace WWDTM {
	/* Require PEAR::MDB2 and PHPlot */
	require_once 'MDB2.php';
	require_once 'phplot.php';

	/* Require WWDTM Config File */
	require_once 'Config.php';

	/* Require WWDTM Panelist Data and Functions */
	require_once 'PanelistData.php';
	require_once 'Functions.php';

	use \MDB2 as MDB2;
	use \PEAR as PEAR;
	use \Image_Graph as Image_Graph;
	use \PHPlot as PHPlot;

	class GraphData {
		/* Declare Database Access Settings */
		private $dbUri;
		private $dbOption;
		private $dbConnection;

		private $WWDTM_PanelistData;
		private $WWDTM_Functions;

		/* Declare Private Properties */
		private $panelistScoreBreakdown;
		private $MinScore;
		private $MaxScore;

		function __construct() {
			$this->dbUri = DB_TYPE . '://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_NAME;
			$this->dbOption = array('portability' => MDB2_PORTABILITY_ALL);
			$this->dbConnection = MDB2::singleton($this->dbUri, $this->dbOption);
			if (pear::isError($this->dbConnection)) {
				die($this->dbConnection->getMessage());
			}

			$this->WWDTM_PanelistData = new PanelistData();
			$this->WWDTM_Functions = new Functions();
			$this->minMaxScores();
		}

		private function minMaxScores() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select * from v_ww_graphdata_minmaxscores;');
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			$this->MinScore = $row['min'];
			$this->MaxScore = $row['max'];
		}

		private function panelistPointSpread($panelistID) {
			/* Initialize point spread and score arrays */
			$spread = array();
			$score = array();
			for ($i = $this->MinScore; $i <= $this->MaxScore; $i++) {
				$score[$i] = 0;
			}

			$this->dbConnection = MDB2::singleton();
			$panelistID = $this->dbConnection->quote($panelistID, 'integer');
			$res = $this->dbConnection->query("select pm.panelistscore as score, count(pm.panelistscore) as count from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and s.bestof = 0 and s.repeatshowid is null group by pm.panelistscore order by pm.panelistscore asc");

			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$score[$row['score']] = $row['count'];
			}

			for ($i = $this->MinScore; $i <= $this->MaxScore; $i++) {
				$spread[] = array($i, $score[$i]);
			}
			return $spread;
		}

		private function panelistPointTrend($panelistID) {
			$scores = array();
			$this->dbConnection = MDB2::singleton();
			$panelistID = $this->dbConnection->quote($panelistID, 'integer');
			$res = $this->dbConnection->query("select pm.panelistscore as score from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and s.bestof = 0 and s.repeatshowid is null and pm.panelistscore is not null order by s.showdate asc");

			$appearance = 0;
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$scores[] = array('', $appearance, $row['score']);
				$appearance++;
			}
			return $scores;
		}

		private function maxPanelistScoreCount($panelistID) {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query("select max(count) from (select count(panelistscore) as count from ww_showpnlmap where panelistid = $panelistID group by panelistscore) as counts");
			$row = $res->fetchRow();
			return $row[0];
		}

		private function getPanelistsWithScore() {
			$panelists = array();
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query("select * from v_ww_graphdata_panelistswithscore;");

			while ($row = $res->fetchRow()) {
				$panelists[] = $row[0];
			}
			return $panelists;
		}

		private function graphPanelistPointSpread($panelistID, $isLarge = false) {
			if ($isLarge) {
				$graphHeight = LARGE_GRAPH_HEIGHT;
				$graphWidth = LARGE_GRAPH_WIDTH;
				$graphFontSize = GRAPH_FONT_SIZE + 2;
			} else {
				$graphHeight = GRAPH_HEIGHT;
				$graphWidth = GRAPH_WIDTH;
				$graphFontSize = GRAPH_FONT_SIZE;
			}

			$panelistName = $this->WWDTM_PanelistData->panelists[$panelistID];
			$panelistFileName = $this->WWDTM_Functions->cleanPanelistName($panelistName);
			$scoreData = $this->panelistPointSpread($panelistID);
			$maxScoreCount = $this->maxPanelistScoreCount($panelistID);
			$plot = new PHPlot($graphWidth, $graphHeight);
			$plot->SetFontTTF('title', GRAPH_FONT, $graphFontSize + 1);
			$plot->SetFontTTF('x_label', GRAPH_FONT, $graphFontSize);
			$plot->SetFontTTF('x_title', GRAPH_FONT, $graphFontSize);
			$plot->SetFontTTF('y_label', GRAPH_FONT, $graphFontSize);
			$plot->SetFontTTF('y_title', GRAPH_FONT, $graphFontSize);
			$plot->SetDataColors(GRAPH_BAR_COLOR);
			$plot->SetDataBorderColors(GRAPH_LINE_COLOR);
			$plot->SetImageBorderType('none');
			$plot->SetPlotBorderType('none');
			$plot->bar_extra_space = 0.1;
			$plot->SetPlotType('bars');
			$plot->SetDataValues($scoreData);
			$plot->SetShading(0);
			$plot->SetXTickLabelPos('none');
			$plot->SetXTickPos('none');
			$plot->SetYTickIncrement(ceil($maxScoreCount / 10.0));
			$plot->SetYDataLabelPos('plotin');
			$plot->SetTitle("Scoring Breakdown for $panelistName");
			$plot->SetXTitle('Score');
			$plot->SetYTitle('# Times Scored');
			$plot->SetFileFormat(GRAPH_FILE_TYPE);
			if ($isLarge) {
				$plot->SetOutputFile(GRAPH_OUTPUT_PATH . '/pnlspread/' . $panelistFileName . '.large.' . GRAPH_FILE_TYPE);
			} else {
				$plot->SetOutputFile(GRAPH_OUTPUT_PATH . '/pnlspread/' . $panelistFileName . '.' . GRAPH_FILE_TYPE);
			}
			$plot->SetIsInline(true);
			$plot->DrawGraph();
		}

		private function graphPanelistPointTrend($panelistID, $isLarge = false) {
			if ($isLarge) {
				$graphHeight = LARGE_GRAPH_HEIGHT;
				$graphWidth = LARGE_GRAPH_WIDTH;
				$graphFontSize = GRAPH_FONT_SIZE + 2;
			} else {
				$graphHeight = GRAPH_HEIGHT;
				$graphWidth = GRAPH_WIDTH;
				$graphFontSize = GRAPH_FONT_SIZE;
			}

			$panelistName = $this->WWDTM_PanelistData->panelists[$panelistID];
			$panelistFileName = $this->WWDTM_Functions->cleanPanelistName($panelistName);
			$scoreData = $this->panelistPointTrend($panelistID);
			$maxScoreCount = $this->maxPanelistScoreCount($panelistID);
			$plot = new PHPlot($graphWidth, $graphHeight);
			$plot->SetFontTTF('title', GRAPH_FONT, $graphFontSize + 1);
			$plot->SetFontTTF('x_label', GRAPH_FONT, $graphFontSize);
			$plot->SetFontTTF('x_title', GRAPH_FONT, $graphFontSize);
			$plot->SetFontTTF('y_label', GRAPH_FONT, $graphFontSize);
			$plot->SetFontTTF('y_title', GRAPH_FONT, $graphFontSize);
			$plot->SetDataColors(GRAPH_LINEGRAPH_COLOR);
			$plot->SetLineWidths(1);
			$plot->SetImageBorderType('none');
			$plot->SetPlotBorderType('none');
			$plot->SetPlotType('lines');
			$plot->SetDataType('data-data');
			if (count($scoreData) > 3) {
				$plot->SetPlotAreaWorld(0, 0, count($scoreData), $this->MaxScore);
			} else {
				$plot->SetPlotAreaWorld(0, 0, null, $this->MaxScore);
			}
			$plot->SetDataValues($scoreData);
			$plot->SetShading(0);
			$plot->SetXDataLabelPos('none');
			$plot->SetXTickLabelPos('none');
			$plot->SetXTickPos('none');
			$plot->SetXTickIncrement(ceil(count($scoreData) / 10.0));
			$plot->SetYTickIncrement(2);
			$plot->SetDrawXGrid(true);
			$plot->SetTitle("Score Graph for $panelistName");
			$plot->SetXTitle('Time');
			$plot->SetYTitle('Score');
			$plot->SetFileFormat(GRAPH_FILE_TYPE);
			if ($isLarge) {
				$plot->SetOutputFile(GRAPH_OUTPUT_PATH . '/pnltrend/' . $panelistFileName . '.large.' . GRAPH_FILE_TYPE);
			} else {
				$plot->SetOutputFile(GRAPH_OUTPUT_PATH . '/pnltrend/' . $panelistFileName . '.' . GRAPH_FILE_TYPE);
			}
			$plot->SetIsInline(true);
			$plot->DrawGraph();
		}


		public function generatePanelistGraphs($isLarge = false) {
			$panelists = $this->getPanelistsWithScore();
			for ($i = 0; $i < count($panelists); $i++) {
				$this->graphPanelistPointSpread($panelists[$i], $isLarge);
				$this->graphPanelistPointTrend($panelists[$i], $isLarge);
			}
		}

		public function generateLargePanelistGraphs() {
			$this->generatePanelistGraphs($isLarge = true);
		}

	}
}
