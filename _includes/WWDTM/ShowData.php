<?php
namespace WWDTM {
	/* Require PEAR::MDB2 */
	require_once 'MDB2.php';
	
	use \MDB2 as MDB2;
	use \PEAR as PEAR;
	use \DateTime as DateTime;
	use \DateInterval as DateInterval;
	
	class Show {
		public $Date;
		public $RepeatShowID;
		public $BestOf = false;
		public $Location;
		public $Host;
		public $isGuestHost = false;
		public $Scorekeeper;
		public $isGuestScorekeeper = false;
		public $ScorekeeperDescription;
		public $Panelists = array();
		public $Bluff;
		public $Guests = array();
		public $Description;
		public $Notes;
	}
	
	class ShowData {
		/* Declare Database Access Settings */
		private $dbUri;
		private $dbOption;
		private $dbConnection;
	   
		/* Declare Public Properties and Cached Data Arrays */
		public $Shows = array();
		public $Locations = array();
		public $MinYear;
		public $MaxYear;
	   
		function __construct() {
			$this->dbUri = DB_TYPE . '://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_NAME;
			$this->dbOption = array('portability' => MDB2_PORTABILITY_ALL);
			$this->dbConnection = MDB2::singleton($this->dbUri, $this->dbOption);
			if (pear::isError($this->dbConnection)) {
				die($this->dbConnection->getMessage());
			}
			
			$minMaxYears = $this->getMinMaxYears();
			$this->MinYear = $minMaxYears['min'];
			$this->MaxYear = $minMaxYears['max'];
			$this->buildShowsCache();
			$this->buildLocationsCache();
		}
	   
		private function getMinMaxYears() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select min(year(showdate)) as min, max(year(showdate)) as max from ww_shows');
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row;
		}
	   
		private function buildShowsCache() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select showid, showdate from ww_shows');
			while ($row = $res->fetchRow()) {
				$showID = $row[0];
				$this->Shows[$showID] = $row[1];
			}
		}
	   
		private function buildLocationsCache() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select locationid, city, state, venue from ww_locations');
			
			while ($row = $res->fetchRow()) {
				$location = array();
				$locationID = $row[0];
				$location['city'] = $row[1];
				$location['state'] = $row[2];
				$location['venue'] = $row[3];
				$this->Locations[$locationID] = $location;
			}
		}
		
		public function showExists($showDate) {
			return in_array($showDate, $this->Shows);
		}
	   
		public function getShowID($showDate) {
			return array_search($showDate, $this->Shows);
		}
	   
		public function getRecentShowIDs($desc = true) {
			$order = '';
			if ($desc) {
				$order = 'desc';
			} else {
				$order = 'asc';
			}
			
			$this->dbConnection = MDB2::singleton();
			$current = $this->dbConnection->quote(date('Y-m-d', strtotime('+7 days')));
			$past = $this->dbConnection->quote(date('Y-m-d', strtotime('-' . SHOW_RECENT_DAYS . ' days')));
			$query = "select showid from ww_shows where (showdate <= $current and showdate >= $past) order by showdate $order";
			$res = $this->dbConnection->query($query);
			$data = array();
			
			while ($row = $res->fetchRow()) {
				$data[] = $row[0];
			}
			return $data;
		}
	   
		public function getShowYears($desc = false) {
			$order = '';
			if ($desc) {
				$order = 'desc';
			} else {
				$order = 'asc';
			}
			
			$this->dbConnection = MDB2::singleton();
			$query = 'select distinct year(showdate) from ww_shows order by showdate ' . $order;
			$res = $this->dbConnection->query($query);
			
			$years = array();
			while ($row = $res->fetchRow()) {
				$years[] = $row[0];
			}
			return $years;
		}
	   
		public function getShowIDsByYear($year, $desc = false) {
			$order = '';
			if ($desc) {
				$order = 'desc';
			} else {
				$order = 'asc';
			}
			
			$this->dbConnection = MDB2::singleton();
			$year = $this->dbConnection->quote($year, 'integer');
			$query = "select showid from ww_shows where year(showdate) = $year order by showdate $order";
			$res = $this->dbConnection->query($query);
			$data = array();
			
			while ($row = $res->fetchRow()) {
				$data[] = $row[0];
			}
			return $data;
		}
	   
		public function getShowIDsByMonth($year, $month, $desc = false) {
			$order = '';
			if ($desc) {
				$order = 'desc';
			} else {
				$order = 'asc';
			}
			
			$this->dbConnection = MDB2::singleton();
			$year = $this->dbConnection->quote($year, 'integer');
			$month = $this->dbConnection->quote($month, 'integer');
			$query = "select showid from ww_shows where year(showdate) = $year and month(showdate) = $month order by showdate $order";
			$res = $this->dbConnection->query($query);
			$data = array();
			
			while ($row = $res->fetchRow()) {
				$data[] = $row[0];
			}
			return $data;
		}
	   
		public function getShowData($showID) {
			$show = new Show();
			$this->dbConnection = MDB2::singleton();
			$showID = $this->dbConnection->quote($showID, 'integer');
			$res = $this->dbConnection->query("select showdate, repeatshowid, bestof from ww_shows where showid = $showID");
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			$show->Date = $row['showdate'];
			$show->RepeatShowID = $row['repeatshowid'];
			$show->BestOf = $row['bestof'];
			
			$res = $this->dbConnection->query("select hostid, guest from ww_showhostmap where showid = $showID");
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			$show->Host = $row['hostid'];
			$show->isGuestHost = (boolean) $row['guest'];
			
			$res = $this->dbConnection->query("select scorekeeperid, guest, description from ww_showskmap where showid = $showID");
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			$show->Scorekeeper = $row['scorekeeperid'];
			$show->isGuestScorekeeper = (boolean) $row['guest'];
			$show->ScorekeeperDescription = $row['description'];
			
			$res = $this->dbConnection->query("select locationid from ww_showlocationmap where showid = $showID");
			$row = $res->fetchRow();
			$show->Location = $row[0];
			
			$res = $this->dbConnection->query("select panelistid, panelistlrndstart, panelistlrndcorrect, panelistscore, showpnlrank from ww_showpnlmap where showid = $showID order by panelistscore desc, showpnlmapid asc");
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$panelist = array();
				$panelist['panelistid'] = $row['panelistid'];
				$panelist['start'] = $row['panelistlrndstart'];
				$panelist['correct'] = $row['panelistlrndcorrect'];
				$panelist['score'] = $row['panelistscore'];
				$panelist['rank'] = $row['showpnlrank'];
				$show->Panelists[] = $panelist;
			}
			
			$res = $this->dbConnection->query("select chosenbluffpnlid as chosen, correctbluffpnlid as correct from ww_showbluffmap where showid = $showID");
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			$bluff = array();
			$bluff['chosen'] = $row['chosen'];
			$bluff['correct'] = $row['correct'];
			$show->Bluff = $bluff;
			
			$res = $this->dbConnection->query("select guestid, guestscore, exception from ww_showguestmap where showid = $showID order by showguestmapid asc");
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$guest = array();
				$guest['guestid'] = $row['guestid'];
				$guest['score'] = $row['guestscore'];
				$guest['exception'] = $row['exception'];
				$show->Guests[] = $guest;
			}
			
			$res = $this->dbConnection->query("select showdescription from ww_showdescriptions where showid = $showID");
			$row = $res->fetchRow();
			$show->Description = $row[0];
			
			$res = $this->dbConnection->query("select shownotes from ww_shownotes where showid = $showID");
			$row = $res->fetchRow();
			$show->Notes = $row[0];
			
			return $show;
		}
	}
}
