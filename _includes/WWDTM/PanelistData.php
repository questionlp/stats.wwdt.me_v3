<?php
namespace WWDTM {
	/* Require PEAR::DB */
	require_once 'MDB2.php';
	require_once 'Config.php';
	require_once 'Functions.php';
	
	use \MDB2 as MDB2;
	use \PEAR as PEAR;
	use \DateTime as DateTime;
	
	class Panelist {
		public $Appearances;
		public $AllAppearances;
		public $AppearancesWithScores;
		public $AppearancesList = array();
		public $FirstAppearance;
		public $LatestAppearance;
		
		public $FirstPlace = 0;
		public $FirstPlaceTied = 0;
		public $SecondPlace = 0;
		public $SecondPlaceTied = 0;
		public $ThirdPlace = 0;
		public $LatestFirstPlace;
		public $LatestFirstPlaceTied;
		public $GamesSinceFirstPlace;
		public $GamesSinceFirstPlaceTied;
		
		public $LowestScore;
		public $HighestScore;
		public $SumScores;
		public $MeanScore;
		public $MedianScore;
		public $StandardDeviation;
		
		public $ChosenBluffs;
		public $CorrectBluffs;
		}
		
		class PanelistData {
		/* Declare Database Access Settings */
		private $dbUri;
		private $dbOption;
		private $dbConnection;
		
		/* Declare Arrays For Common Data */
		public $panelists = array();
		public $rankNames = array('1' => '1st', '1t' => 'Tied 1st', '2' => '2nd', '2t' => 'Tied 2nd', '3' => '3rd');
		
		function __construct() {
			$this->dbUri = DB_TYPE . '://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_NAME;
			$this->dbOption = array('portability' => MDB2_PORTABILITY_ALL);
			$this->dbConnection = MDB2::singleton($this->dbUri, $this->dbOption);
			if (pear::isError($this->dbConnection)) {
				die($this->dbConnection->getMessage());
			}
			
			$this->buildPanelistCache();
		}
		
		private function buildPanelistCache() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select panelistid, panelist, panelistslug from ww_panelists order by panelistid asc');
			
			while ($row = $res->fetchRow()) {
				$panelistID = $row[0];
				$this->panelists[$panelistID] = array('name' => $row[1], 'slug' => $row[2]);
			}
		}
		
		public function validatePanelistID($panelistID) {
			if (is_int($panelistID) && array_key_exists($panelistID, $this->panelists) && ($panelistID <> SHOW_PANELISTID_MULTIPLE)) {
				return true;
			} else {
				return false;
			}
		}
		
		public function getPanelistIDs () {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select panelistid from ww_panelists where panelistid != ' . SHOW_PANELISTID_MULTIPLE . ' order by panelist asc');
			$data = array();
			
			while ($row = $res->fetchRow()) {
				$data[] = $row[0];
			}
			return $data;
		}
		
		public function panelistInfoFromSlug($panelistSlug) {
			$this->dbConnection = MDB2::singleton();
			$sth = $this->dbConnection->prepare('select panelistid, panelist from ww_panelists where panelistslug = ?', array('text'));
			$res = $sth->execute($panelistSlug);
			$row = $res->fetchRow();

			if (count($row) == 0) {
				return null;
			} else {
				return array('id' => $row[0], 'name' => $row[1]);
			}
		}		
		public function getPanelistData($panelistID) {
			$this->dbConnection = MDB2::singleton();
			$panelistID = $this->dbConnection->quote($panelistID, 'integer');
			$query = "select count(pm.panelistid) as count, min(s.showdate) as first, max(s.showdate) as latest from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$panelist = new Panelist();
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			$panelist->Appearances = $row['count'];
			$panelist->FirstAppearance = $row['first'];
			$panelist->LatestAppearance = $row['latest'];
			
			$query = "select count(panelistid) from ww_showpnlmap where panelistid = $panelistID";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->AllAppearances = $row[0];
			
			$query = "select count(pm.panelistid) from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and s.bestof = 0 and s.repeatshowid is null and pm.panelistscore is not null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->AppearancesWithScores = $row[0];
			
			$query = "select s.showdate as showdate, pm.panelistscore as score, pm.showpnlrank as rank, s.bestof as bestof, s.repeatshowid as repeatshowid from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID order by s.showdate asc";
			$res = $this->dbConnection->query($query);
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$data = array();
				$data['showdate'] = $row['showdate'];
				$data['score'] = $row['score'];
				$data['rank'] = $row['rank'];
				$data['bestof'] = $row['bestof'];
				$data['repeatshowid'] = $row['repeatshowid'];
				$panelist->AppearancesList[] = $data;
			}
			
			$query = "select pm.showpnlrank as rank, count(pm.showpnlrank) as count from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and s.bestof = 0 and s.repeatshowid is null group by pm.showpnlrank ";
			$res = $this->dbConnection->query($query);
			
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$rank = $row['rank'];
				$count = $row['count'];
				
				switch (strval($rank)) {
					case '1':
						$panelist->FirstPlace = $count;
						break;
					case '1t':
						$panelist->FirstPlaceTied = $count;
						break;
					case '2':
						$panelist->SecondPlace = $count;
						break;
					case '2t':
						$panelist->SecondPlaceTied = $count;
						break;
					case '3':
						$panelist->ThirdPlace = $count;
						break;
				}
			}
			
			$query = "select max(s.showdate) from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and pm.showpnlrank = '1' and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->LatestFirstPlace = $row[0];
			
			$query = "select max(s.showdate) from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and pm.showpnlrank in ('1', '1t') and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->LatestFirstPlaceTied = $row[0];

			$query = "select count(s.showid) from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and (s.showdate > '$panelist->LatestFirstPlace' and s.showdate <= CURDATE()) and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->GamesSinceFirstPlace = $row[0];

			$query = "select count(s.showid) from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and (s.showdate > '$panelist->LatestFirstPlaceTied' and s.showdate <= CURDATE()) and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->GamesSinceFirstPlaceTied = $row[0];
			
			$query = "select pm.panelistscore as score from ww_showpnlmap pm join ww_shows s on pm.showid = s.showid where pm.panelistid = $panelistID and pm.panelistscore is not null and s.bestof = 0 and s.repeatshowid is null order by pm.panelistscore asc";
			$res = $this->dbConnection->query($query);
			
			$scores = array();
			while ($row = $res->FetchRow()) {
				$scores[] = $row[0];
			}
			
			$Functions = new Functions();
			if (!empty($scores)) {
				$panelist->LowestScore = min($scores);
				$panelist->HighestScore = max($scores);
				$panelist->SumScores = array_sum($scores);
				$panelist->MeanScore = $Functions->meanScore($scores);
				$panelist->MedianScore = $Functions->medianScore($scores);
				$panelist->StandardDeviation = $Functions->standardDeviation($scores);
			} else {
				$panelist->LowestScore = null;
				$panelist->HighestScore = null;
				$panelist->MeanScore = null;
				$panelist->MedianScore = null;
				$panelist->StandardDeviation = null;
			}
			
			$query = "select count(b.chosenbluffpnlid) from ww_showbluffmap b join ww_shows s on s.showid = b.showid where s.repeatshowid is null and b.chosenbluffpnlid = $panelistID";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->ChosenBluffs = $row[0];
			
			$query = "select count(b.correctbluffpnlid) from ww_showbluffmap b join ww_shows s on s.showid = b.showid where s.repeatshowid is null and b.correctbluffpnlid = $panelistID";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$panelist->CorrectBluffs = $row[0];
			return $panelist;
		}
	}
}
