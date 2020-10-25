<?php
# Copyright (c) 2007-2020 Linh Pham
# wwdt.me_v3 is relased under the terms of the Apache License 2.0

namespace WWDTM {
	/* Require WWDTM Files */
	require_once __DIR__ . '/../HostData.php';
	require_once __DIR__ . '/../Functions.php';
	
	use \DateTime as DateTime;
	
	class Render_Host {
		private $WWDTM_HostData;
		
		function __construct() {
			$this->WWDTM_HostData = new HostData();
		}
		
		public function host($hostID) {
			$Functions = new Functions();
			$host = $this->WWDTM_HostData->getHostData($hostID);
			$hostInfo = $this->WWDTM_HostData->hosts[$hostID];
			$hostName = $hostInfo['name'];
			$hostSlug = $hostInfo['slug'];
			$hostDataURL = '/hosts/' . $hostSlug;
			$count = count($host->AppearancesList);
			
			print "<div class=\"grw\"><div class=\"gn\">\n";
			print "<a href=\"$hostDataURL\"><h3>";
			if ($hostID == SHOW_HOSTID_LUKE_BURBANK) {
				print "<span title=\"Luuuuuuke\">$hostName</span>";
			} else {
				print $hostName;
			}
			print '</h3></a>';
			if (defined('DEVMODE') && DEVMODE == 1) {
				print 'Internal ID: ' . $hostID;
			}
			print "</div>\n";
			
			print "<div class=\"gri\">\n";
			if ($count >= 120) {
				print '<table class="w1">';
			} else {
				print '<table>';
			}
			print "<tr><td class=\"gc1\">Appearances (sans Repeats/Best Ofs): $host->BaseAppearances<br>";
			print "Appearances including Best Ofs: $host->AppearancesWithBestOfs<br>";
			print "Appearances including Repeats/Best Ofs: $host->AllAppearances</td></tr>\n";
			print '<tr><td><div>List of appearances:</div>';
			
			if ($count >= 120) {    
				print '<ul class="apl2">';
			} else {
				print '<ul class="apl">';
			}
			
			for ($i = 0; $i < $count; $i++) {
				$showDate = $host->AppearancesList[$i]['showdate'];
				$bestOf = $host->AppearancesList[$i]['bestof'];
				$repeatShowID = $host->AppearancesList[$i]['repeatshowid'];
				$showDateURL = $Functions->dateFormatToUrl($showDate);
				
				print "<li><a href=\"/shows/$showDateURL\">$showDate</a>";
				if (!is_null($repeatShowID) && $bestOf) {
					print ' (R/B)';
				} else if (!is_null($repeatShowID)) {
					print ' (R)';
				} else if ($bestOf) {
					print ' (B)';
				}
				print '</li>';
			}

			print "</ul></td></tr>\n";
			print "</table></div></div>\n";
		}
		
		public function hosts() {
			$hostIDs = $this->WWDTM_HostData->getHostIDs();
			$count = count($hostIDs);
			for ($i = 0; $i < $count; $i++) {
				$this->host($hostIDs[$i]);
			}
		}
	}
}
