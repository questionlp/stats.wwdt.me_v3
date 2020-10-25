<?php
# Copyright (c) 2007-2020 Linh Pham
# wwdt.me_v3 is relased under the terms of the Apache License 2.0

namespace WWDTM {
	/* Require PEAR::DB */
	require_once 'MDB2.php';
	require_once 'Config.php';
	
	use \MDB2 as MDB2;
	use \PEAR as PEAR;
	use \DateTime as DateTime;
	
	class Scorekeeper {
	public $Appearances;
	public $AllAppearances;
	public $AppearancesList = array();
	}
	
	class ScorekeeperData {
		/* Declare Database Access Settings */
		private $dbUri;
		private $dbOption;
		private $dbConnection;
		
		/* Declare Arrays For Common Data */
		public $scorekeepers = array();
		
		function __construct() {
			$this->dbUri = DB_TYPE . '://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_NAME;
			$this->dbOption = array('portability' => MDB2_PORTABILITY_ALL);
			$this->dbConnection = MDB2::singleton($this->dbUri, $this->dbOption);
			if (pear::isError($this->dbConnection)) {
				die($this->dbConnection->getMessage());
			}
			
			$this->buildScorekeeperCache();
		}
		
		private function buildScorekeeperCache() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select scorekeeperid, scorekeeper, scorekeeperslug from ww_scorekeepers order by scorekeeperid asc');
			
			while ($row = $res->fetchRow()) {
				$scorekeeperID = $row[0];
				$this->scorekeepers[$scorekeeperID] = array('name' => $row[1], 'slug' => $row[2]);
			}
		}
		
		public function getScorekeeperIDs() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select scorekeeperid from ww_scorekeepers where scorekeeperid != ' . SHOW_SCOREKEEPERID_TBD . ' order by scorekeeper asc');
			$data = array();
			
			while ($row = $res->fetchRow()) {
				$data[] = $row[0];
			}
			return $data;
		}
		
		public function scorekeeperInfoFromSlug($scorekeeperSlug) {
			$this->dbConnection = MDB2::singleton();
			$sth = $this->dbConnection->prepare('select scorekeeperid, scorekeeper from ww_scorekeepers where scorekeeperslug = ?', array('text'));
			$res = $sth->execute($scorekeeperSlug);
			$row = $res->fetchRow();

			if (count($row) == 0) {
				return null;
			} else {
				return array('id' => $row[0], 'name' => $row[1]);
			}
		}
		
		public function getScorekeeperData($scorekeeperID) {
			$scorekeeper = new Scorekeeper();
			
			$this->dbConnection = MDB2::singleton();
			$hostID = $this->dbConnection->quote($scorekeeperID, 'integer');
			$query = "select count(sm.scorekeeperid) from ww_showskmap sm join ww_shows s on sm.showid = s.showid where sm.scorekeeperid = $scorekeeperID and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$scorekeeper->BaseAppearances = $row[0];
			

			$query = "select count(sm.scorekeeperid) from ww_showskmap sm join ww_shows s on s.showid = sm.showid where sm.scorekeeperid = $scorekeeperID and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$scorekeeper->AppearancesWithBestOfs = $row[0];

			$query = "select count(scorekeeperid) from ww_showskmap where scorekeeperid = $scorekeeperID";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$scorekeeper->AllAppearances = $row[0];
			
			$query = "select s.showdate as showdate, s.bestof as bestof, s.repeatshowid as repeatshowid from ww_showskmap sm join ww_shows s on sm.showid = s.showid where sm.scorekeeperid = $scorekeeperID order by s.showdate asc";
			$res = $this->dbConnection->query($query);
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$data = array();
				$data['showdate'] = $row['showdate'];
				$data['bestof'] = $row['bestof'];
				$data['repeatshowid'] = $row['repeatshowid'];
				$scorekeeper->AppearancesList[] = $data;
			}			
			return $scorekeeper;
		}
	}
}
