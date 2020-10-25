<?php
# Copyright (c) 2007-2020 Linh Pham
# wwdt.me_v3 is relased under the terms of the Apache License 2.0

namespace WWDTM {
	/* Require WWDTM Files */
	require_once __DIR__ . '/../ScorekeeperData.php';
	require_once __DIR__ . '/../Functions.php';
	
	use \DateTime as DateTime;
	
	class Render_Scorekeeper {
		private $WWDTM_ScorekeeperData;
		
		function __construct() {
			$this->WWDTM_ScorekeeperData = new ScorekeeperData();
		}
		
		public function scorekeeper($scorekeeperID) {
			$Functions = new Functions();
			$scorekeeper = $this->WWDTM_ScorekeeperData->getScorekeeperData($scorekeeperID);
			$scorekeeperInfo = $this->WWDTM_ScorekeeperData->scorekeepers[$scorekeeperID];
			$scorekeeperName = $scorekeeperInfo['name'];
			$scorekeeperSlug = $scorekeeperInfo['slug'];
			$scorekeeperDataURL = '/scorekeepers/' . $scorekeeperSlug;
			
			print "<div class=\"grw\"><div class=\"gn\">\n";
			print "<a href=\"$scorekeeperDataURL\"><h3>$scorekeeperName</h3></a>";
			if (defined('DEVMODE') && DEVMODE == 1) {
				print 'Internal ID: ' . $scorekeeperID;
			}
			print "</div>\n";
			
			print "<div class=\"gri\">\n";
			if (count($scorekeeper->AppearancesList) >= 120) {
				print '<table class="w1">';
			} else {
				print '<table>';
			}
			print "<tr><td class=\"gc1\">Appearances (sans Repeats/Best Ofs): $scorekeeper->BaseAppearances<br>";
			print "Appearances including Best Ofs: $scorekeeper->AppearancesWithBestOfs<br>";
			print "Appearances including Repeats/Best Ofs: $scorekeeper->AllAppearances</td></tr>\n";
			print '<tr><td><div>List of appearances:</div>';
			
			if (count($scorekeeper->AppearancesList) >= 120) {    
				print '<ul class="apl2">';
			} else {
				print '<ul class="apl">';
			}
			
			$count = count($scorekeeper->AppearancesList);
			for ($i = 0; $i < $count; $i++) {
				$showDate = $scorekeeper->AppearancesList[$i]['showdate'];
				$showDateURL = $Functions->dateFormatToUrl($showDate);
				$bestOf = $scorekeeper->AppearancesList[$i]['bestof'];
				$repeatShowID = $scorekeeper->AppearancesList[$i]['repeatshowid'];
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
		
		public function scorekeepers() {
			$scorekeeperIDs = $this->WWDTM_ScorekeeperData->getScorekeeperIDs();
			$count = count($scorekeeperIDs);
			for ($i = 0; $i < $count; $i++) {
				$this->scorekeeper($scorekeeperIDs[$i]);
			}
		}
	}
}
