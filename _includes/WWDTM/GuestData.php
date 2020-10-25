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
	
	class Guest {
	public $Appearances;
	public $AllAppearances;
	public $AppearancesList = array();
	}
	
	class GuestData {
		/* Declare Database Access Settings */
		private $dbUri;
		private $dbOption;
		private $dbConnection;
		
		/* Declare Arrays For Common Data */
		public $guests = array();
		
		function __construct() {
			$this->dbUri = DB_TYPE . '://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_NAME;
			$this->dbOption = array('portability' => MDB2_PORTABILITY_ALL);
			$this->dbConnection = MDB2::singleton($this->dbUri, $this->dbOption);
			if (pear::isError($this->dbConnection)) {
				die($this->dbConnection->getMessage());
			}
			
			$this->buildGuestCache();
		}
		
		private function buildGuestCache() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select guestid, guest, guestslug from ww_guests order by guestid asc');
			
			while ($row = $res->fetchRow()) {
				$guestID = $row[0];
				$this->guests[$guestID] = array('name' => $row[1], 'slug' => $row[2]);
			}
		}
		
		public function getGuestIDs() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select guestid from ww_guests where guestid != ' . SHOW_GUESTID_NONE . ' order by guest asc');
			$data = array();
			
			while ($row = $res->fetchRow()) {
				$data[] = $row[0];
			}
			return $data;
		}
		
		public function guestInfoFromSlug($guestSlug) {
			$this->dbConnection = MDB2::singleton();
			$sth = $this->dbConnection->prepare('select guestid, guest from ww_guests where guestslug = ?', array('text'));
			$res = $sth->execute($guestSlug);
			$row = $res->fetchRow();

			if (count($row) == 0) {
				return null;
			} else {
				return array('id' => $row[0], 'name' => $row[1]);
			}
		}
	
		public function getGuestData($guestID) {
			$guest = new Guest();
			
			$this->dbConnection = MDB2::singleton();
			$guestID = $this->dbConnection->quote($guestID, 'integer');
			$query = "select count(gm.guestid) from ww_showguestmap gm join ww_shows s on gm.showid = s.showid where gm.guestid = $guestID and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$guest->Appearances = $row[0];
			
			$query = "select count(guestid) from ww_showguestmap where guestid = $guestID";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$guest->AllAppearances = $row[0];
			
			$query = "select s.showdate as showdate, gm.guestscore as score, gm.exception as exception, s.bestof as bestof, s.repeatshowid as repeatshowid from ww_showguestmap gm join ww_shows s on gm.showid = s.showid where gm.guestid = $guestID order by s.showdate asc";
			$res = $this->dbConnection->query($query);
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$data = array();
				$data['showdate'] = $row['showdate'];
				$data['score'] = $row['score'];
				$data['exception'] = $row['exception'];
				$data['bestof'] = $row['bestof'];
				$data['repeatshowid'] = $row['repeatshowid'];
				$guest->AppearancesList[] = $data;
			}
			return $guest;
		}
	}
}
