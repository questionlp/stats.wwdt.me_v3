<?php
namespace WWDTM {
	/* Require PEAR::DB */
	require_once 'MDB2.php';
	require_once 'Config.php';
	
	use \MDB2 as MDB2;
	use \PEAR as PEAR;
	use \DateTime as DateTime;
	
	class Host {
	public $Appearances;
	public $AllAppearances;
	public $AppearancesList = array();
	}
	
	class HostData {
		/* Declare Database Access Settings */
		private $dbUri;
		private $dbOption;
		private $dbConnection;
		
		/* Declare Arrays For Common Data */
		public $hosts = array();
		
		function __construct() {
			$this->dbUri = DB_TYPE . '://' . DB_USERNAME . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_NAME;
			$this->dbOption = array('portability' => MDB2_PORTABILITY_ALL);
			$this->dbConnection = MDB2::singleton($this->dbUri, $this->dbOption);
			if (pear::isError($this->dbConnection)) {
				die($this->dbConnection->getMessage());
			}
			
			$this->buildHostCache();
		}
		
		private function buildHostCache() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select hostid, host, hostslug from ww_hosts order by hostid asc');
			
			while ($row = $res->fetchRow()) {
				$hostID = $row[0];
				$this->hosts[$hostID] = array('name' => $row[1], 'slug' => $row[2]);
			}
		}
		
		public function getHostIDs() {
			$this->dbConnection = MDB2::singleton();
			$res = $this->dbConnection->query('select hostid from ww_hosts where hostid != ' . SHOW_HOSTID_TBD . ' order by host asc');
			$data = array();
			
			while ($row = $res->fetchRow()) {
				$data[] = $row[0];
			}
			return $data;
		}
		
		public function hostInfoFromSlug($hostSlug) {
			$this->dbConnection = MDB2::singleton();
			$sth = $this->dbConnection->prepare('select hostid, host from ww_hosts where hostslug = ?', array('text'));
			$res = $sth->execute($hostSlug);
			$row = $res->fetchRow();

			if (count($row) == 0) {
				return null;
			} else {
				return array('id' => $row[0], 'name' => $row[1]);
			}
		}

		public function getHostData($hostID) {
			$host = new Host();
			
			$this->dbConnection = MDB2::singleton();
			$hostID = $this->dbConnection->quote($hostID, 'integer');
			$query = "select count(hm.hostid) from ww_showhostmap hm join ww_shows s on hm.showid = s.showid where hm.hostid = $hostID and s.bestof = 0 and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$host->BaseAppearances = $row[0];

			$query = "select count(hm.hostid) from ww_showhostmap hm join ww_shows s on s.showid = hm.showid where hm.hostid = $hostID and s.repeatshowid is null";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$host->AppearancesWithBestOfs = $row[0];

			$query = "select count(hostid) from ww_showhostmap where hostid = $hostID";
			$res = $this->dbConnection->query($query);
			$row = $res->fetchRow();
			$host->AllAppearances = $row[0];
			
			$query = "select s.showdate as showdate, s.bestof as bestof, s.repeatshowid as repeatshowid from ww_showhostmap hm join ww_shows s on hm.showid = s.showid where hm.hostid = $hostID order by s.showdate asc";
			$res = $this->dbConnection->query($query);
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$data = array();
				$data['showdate'] = $row['showdate'];
				$data['bestof'] = $row['bestof'];
				$data['repeatshowid'] = $row['repeatshowid'];
				$host->AppearancesList[] = $data;
			}
			
			return $host;
		}
	}
}
