<?php
namespace WWDTM {
	/* Require WWDTM Files */
	require_once __DIR__ . '/../GuestData.php';
	require_once __DIR__ . '/../Functions.php';
	
	use \DateTime as DateTime;
	
	class Render_Guest {
		private $WWDTM_GuestData;
		
		function __construct() {
			$this->WWDTM_GuestData = new GuestData();
		}
		
		public function guest($guestID) {
			$Functions = new Functions();
			$guest = $this->WWDTM_GuestData->getGuestData($guestID);
			$guestInfo = $this->WWDTM_GuestData->guests[$guestID];
			$guestName = $guestInfo['name'];
			$guestSlug = $guestInfo['slug'];
			$guestDataURL = '/guests/' . $guestSlug;
			
			print "<div class=\"grw\"><div class=\"gn\">\n";
			print "<a href=\"$guestDataURL\"><h3>$guestName</h3></a>";
			if (defined('DEVMODE') && DEVMODE == 1) {
				print 'Internal ID: ' . $guestID;
			}
			print "</div>\n";
			
			print "<div class=\"gri\">\n";
			print "<table><tr><td class=\"gc1\">Appearances: $guest->Appearances<br>";
			print "Appearances with Repeats/Best Ofs: $guest->AllAppearances</td></tr>\n";
			print '<tr><td><div>List of appearances:</div><ul class="apl">';
			
			$count = count($guest->AppearancesList);
			for ($i = 0; $i < $count; $i++) {
				$showDate = $guest->AppearancesList[$i]['showdate'];
				$showDateURL = $Functions->dateFormatToURL($showDate);
				$score = $guest->AppearancesList[$i]['score'];
				$exception = $guest->AppearancesList[$i]['exception'];
				$bestOf = $guest->AppearancesList[$i]['bestof'];
				$repeatShowID = $guest->AppearancesList[$i]['repeatshowid'];
					
				if (!is_null($score)) {
					if ($exception == 1) {
						print "<li><strong><a href=\"/shows/$showDateURL\">$showDate: $score *</a></strong>";
					} else if ($score >= 2) {
						print "<li><strong><a href=\"/shows/$showDateURL\">$showDate: $score</a></strong>";
					} else {
						print "<li><a href=\"/shows/$showDateURL\">$showDate: $score</a>";
					} 
				} else {
					print "<li><a href=\"/shows/$showDateURL\">$showDate</a>";
				}
				
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
		
		public function guests() {
			$guestIDs = $this->WWDTM_GuestData->getGuestIDs();
			$count = count($guestIDs);
			for ($i = 0; $i < $count; $i++) {
				$this->guest($guestIDs[$i]);
			}
		}
	}
}
