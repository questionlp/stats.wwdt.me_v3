<?php
namespace WWDTM {
	/* Require PEAR::MDB2 */
	require_once 'MDB2.php';
	
	/* Require WWDTM Config File */
	require_once 'Config.php';
	
	/* Require WWDTM Panelist Data and Functions */
	require_once 'PanelistData.php';
	require_once 'Functions.php';
	
	use \MDB2 as MDB2;
	use \PEAR as PEAR;
	
	class GraphJSONData {
		/* Declare Database Access Settings */
		private $dbUri;
		private $dbOptions;
		private $dbConnection;
		
		private $WWDTM_PanelistData;
		private $WWDTM_Functions;
		
		/* Declare Private Properties */
		private $MinScore;
		private $MaxScore;
		
		function __construct() {
			$this->dbUri = DB_TYPE . '://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_NAME;
			$this->dbOption = array('portability' => MDB2_PORTABILITY_ALL);
			$this->dbConnection = MDB2::singleton($this->dbUri, $this->dbOption);
			if (pear::isError($this->dbConnection)) {
				$pError = array();
				$pError['error'] = $this->dbConnection->getMessage();
				return json_encode($pError, true);
			}
			
			$this->WWDTM_PanelistData = new PanelistData();
			$this->WWDTM_Functions = new Functions();
			$this->minMaxScores();
		}
		
		private function minMaxScores() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select * from v_ww_graphdata_minmaxscores');
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			$this->MinScore = $row['min'];
			$this->MaxScore = $row['max'];
		}
		
		public function generateScoreSpreadJSON($panelistID) {
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
				$spread[] = array("score"=>$i, "count"=>$score[$i]);
				
			}
			
			$jsonData = json_encode($spread, JSON_NUMERIC_CHECK);
			return $jsonData;
		}
		
		public function generateScoreTrendJSON($panelistID) {
			$jsonArray = array();
			$scores = array();
			$this->dbConnection = MDB2::singleton();
			$panelistID = $this->dbConnection->quote($panelistID, 'integer');
			$res = $this->dbConnection->query("select s.showdate as showdate, pm.panelistscore as score from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and s.bestof = 0 and s.repeatshowid is null and pm.panelistscore is not null order by s.showdate asc");
			
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$jsonArray[] = array("showdate"=>$row['showdate'], "score"=>$row['score']);
			}
			
			$jsonData = json_encode($jsonArray, JSON_NUMERIC_CHECK);
			return $jsonData;
		}
	}
}
