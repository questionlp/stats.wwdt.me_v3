<?php
namespace WWDTM {
	/* Require WWDTM Files */
	require_once __DIR__ . '/../ShowData.php';
	require_once __DIR__ . '/../PanelistData.php';
	require_once __DIR__ . '/../GuestData.php';
	require_once __DIR__ . '/../HostData.php';
	require_once __DIR__ . '/../ScorekeeperData.php';
	require_once __DIR__ . '/../Functions.php';
	
	use \DateTime as DateTime;
	
	class Render_Show {
		private $WWDTM_Data;
		private $WWDTM_PanelistData;
		private $WWDTM_GuestData;
		private $WWDTM_HostData;
		private $WWDTM_ScorekeeperData;
		
		function __construct() {
			$this->WWDTM_Data = new ShowData();
			$this->WWDTM_PanelistData = new PanelistData();
			$this->WWDTM_GuestData = new GuestData();
			$this->WWDTM_HostData = new HostData();
			$this->WWDTM_ScorekeeperData = new ScorekeeperData();
		}
		
		public function show($showID) {
			$Functions = new Functions();
			$showData = $this->WWDTM_Data->getShowData($showID);
			$showDateURL = $Functions->dateFormatToUrl($showData->Date);
			$showDateNPRURL = str_replace('-', '', $showData->Date);
			
			$hostInfo = $this->WWDTM_HostData->hosts[$showData->Host];
			$hostName = $hostInfo['name'];
			$hostSlug = $hostInfo['slug'];
			$isGuestHost = $showData->isGuestHost;
			$hostURL = '/hosts/' . $hostSlug;
			$scorekeeperInfo = $this->WWDTM_ScorekeeperData->scorekeepers[$showData->Scorekeeper];
			$scorekeeperName = $scorekeeperInfo['name'];
			$scorekeeperSlug = $scorekeeperInfo['slug'];
			$isGuestScorekeeper = $showData->isGuestScorekeeper;
			$scorekeeperDescription = $showData->ScorekeeperDescription;
			$scorekeeperURL = '/scorekeepers/' . $scorekeeperSlug;
			$locationID = $showData->Location;
			$location = $this->WWDTM_Data->Locations[$locationID];
			$city = htmlentities($location['city'],  ENT_COMPAT);
			$state = htmlentities($location['state'],  ENT_COMPAT);
			$venue = htmlentities($location['venue'],  ENT_COMPAT);
			$bestOf = $showData->BestOf;
			$repeatShowID = $showData->RepeatShowID;
			
			print '<div class="srw"><div class="srd">';
			print "<h3><a href=\"/shows/$showDateURL\">$showData->Date</a></h3>\n";
			if (defined('DEVMODE') && DEVMODE == 1) {
				print "Internal ID: $showID<br>";
			}
			if ($bestOf) {
				print '<div class="bo">Best Of</div>';
			}
			if (!is_null($repeatShowID)) {
				$repeatShowDate = $this->WWDTM_Data->Shows[$repeatShowID];
				$repeatShowDateURL = '/shows/' . $Functions->dateFormatToUrl($repeatShowDate);
				print "<div class=\"rid\">Repeat of $repeatShowDate <a href=\"$repeatShowDateURL\">&rarr;</a></div>";
			}
			print "<div class=\"dln\"><a href=\"/s/$showDateNPRURL\">NPR &gt;</a></div></div>";
			print "<div class=\"sri\">\n";
			print '<table><tr><td class="hl">';
			
			if ($locationID == SHOW_LOCATIONID_CHICAGO_CHASE) {
				print "$city, $state ($venue)";
			} else if ($locationID == SHOW_LOCATIONID_CHICAGO_STUDIO) {
				print "<span class=\"als\">$city, $state ($venue)</span>";
			} else if ($locationID == SHOW_LOCATIONID_TBD) {
				print "<span class=\"al\">($city)</span>";
			} else {
				if (!is_null($venue)) {
					print "<span class=\"al\">$city, $state ($venue)</span>";
				} else {
					print "<span class=\"al\">$city, $state</span>";
				}
			}
			
			print '</td><td class="hh">';
			$hostDisplayName = htmlentities($hostName, ENT_COMPAT);
			if ($showData->isGuestHost == false) {
				print "$hostDisplayName <a href=\"$hostURL\">&rarr;</a>";
			} else if ($showData->Host == SHOW_HOSTID_LUKE_BURBANK) {
				print "<span class=\"gh\" title=\"Luuuuuuke\">$hostDisplayName</span> <a href=\"$hostURL\">&rarr;</a>";
			} else {
				print "<span class=\"gh\">$hostDisplayName</span> <a href=\"$hostURL\">&rarr;</a>";
			}
			
			print '</td><td class="hs">';
			$scorekeeperDisplayName = htmlentities($scorekeeperName, ENT_COMPAT);
			if ($showData->isGuestScorekeeper == false) {
				if ($showData->Scorekeeper == SHOW_SCOREKEEPERID_CARL_KASELL and defined('SHOW_SCOREKEEPER_DISPLAY_CARL_EMERITUS') and SHOW_SCOREKEEPER_DISPLAY_CARL_EMERITUS == 1) {
					print "$scorekeeperDisplayName, <span class=\"skem\" title=\"Scorekeeper Emeritus\">SE</span>&nbsp;<a href=\"$scorekeeperURL\">&rarr;</a>";
				} else {
					if ($showData->Scorekeeper == SHOW_SCOREKEEPERID_BILL_KURTIS and !is_null($scorekeeperDescription)) {
						print "&quot;$scorekeeperDescription&quot; $scorekeeperDisplayName&nbsp;<a href=\"$scorekeeperURL\">&rarr;</a>";
					} else {
						print "$scorekeeperDisplayName&nbsp;<a href=\"$scorekeeperURL\">&rarr;</a>";
					}
				}
			} else {
				//if ($showData->Scorekeeper == SHOW_SCOREKEEPERID_BILL_KURTIS and !is_null($scorekeeperDescription)) {
				if (!is_null($scorekeeperDescription)) {
					print "<span class=\"gsk\">&quot;$scorekeeperDescription&quot; $scorekeeperName</span> <a href=\"$scorekeeperURL\">&rarr;</a>";
				} else {
					print "<span class=\"gsk\">$scorekeeperName</span>&nbsp;<a href=\"$scorekeeperURL\">&rarr;</a>";
				}
			}
			
			print "</td></tr>\n";
			print '<tr><td>';
			if (count($showData->Panelists) == 0) {
					print '<div><em>(N/A)</em></div>';
			} else {
				foreach ($showData->Panelists as $panelist) {
					$panelistID = $panelist['panelistid'];
					$panelistInfo = $this->WWDTM_PanelistData->panelists[$panelistID];
					$panelistName = $panelistInfo['name'];
					$panelistSlug = $panelistInfo['slug'];
					$panelistDisplayName = htmlentities($panelistName,  ENT_COMPAT);
					$panelistURL = '/panelists/' . $panelistSlug;
					$score = $panelist['score'];
					$start = $panelist['start'];
					$correct = $panelist['correct'];
					$rank = $panelist['rank'];
					
					if ($panelistID == SHOW_PANELISTID_LUKE_BURBANK) {
						$panelistDisplayName = "<span title=\"Luuuuuuke\">$panelistDisplayName</span>";
					} else {
						$panelistDisplayName = $panelistDisplayName;
					}
					
					if ($panelistID == SHOW_PANELISTID_MULTIPLE) {
						print "<div>$panelistDisplayName</div>";
					} else if (is_null($score)) {
						print "<div>$panelistDisplayName <a href=\"$panelistURL\">&rarr;</a></div>";
					} else if (is_null($start) || is_null($correct)) {
						print "<div class=\"r$rank\">$panelistDisplayName: $score <a href=\"$panelistURL\">&rarr;</a></div>";
					} else {
						print "<div class=\"r$rank\">$panelistDisplayName: $score ($start/$correct) <a href=\"$panelistURL\">&rarr;</a></div>";
					}
				}
			}
			
			print '</td><td>Chosen: ';
			if (is_null($showData->Bluff['chosen'])) {
				print '<em>(N/A)</em>';
			} else {
				$panelistDisplayName = htmlentities($this->WWDTM_PanelistData->panelists[$showData->Bluff['chosen']]['name'], ENT_COMPAT);
				if ($showData->Bluff['chosen'] == SHOW_PANELISTID_LUKE_BURBANK) {
					print '<span title="Luuuuuuke">' . $panelistDisplayName . '</span>';
				} else {
					print $panelistDisplayName;
				}
			}
			
			print '<br>Correct: ';
			if (is_null($showData->Bluff['correct'])) {
				print '<em>(N/A)</em>';
			} else {
				$panelistDisplayName = htmlentities($this->WWDTM_PanelistData->panelists[$showData->Bluff['correct']]['name'], ENT_COMPAT);
				if ($showData->Bluff['correct'] == SHOW_PANELISTID_LUKE_BURBANK) {
					print '<span title="Luuuuuuke">' . $panelistDisplayName . '</span>';
				} else {
					print $panelistDisplayName;
				}
			}
			
			print '</td><td>';
			
			if (count($showData->Guests) == 0) {
				print '<div><em>(N/A)</em></div>';
			} else {
				foreach($showData->Guests as $guest) {
					$guestID = $guest['guestid'];
					$guestInfo = $this->WWDTM_GuestData->guests[$guestID];
					$guestName = $guestInfo['name'];
					$guestSlug = $guestInfo['slug'];
					$guestDisplayName = htmlentities($guestName, ENT_COMPAT);
					$guestURL = '/guests/' . $guestSlug;
					$score = $guest['score'];
					$exception = $guest['exception'];
					
					if ($guestID == SHOW_GUESTID_NONE) {
						print '<div><em>(None)</em></div>';
					}
					else if (is_null($score)) {
						print "<div>$guestDisplayName <a href=\"$guestURL\">&rarr;</a></div>";
					} else if ($exception) {
						print "<div class=\"gw\">$guestDisplayName: $score * <a href=\"$guestURL\">&rarr;</a></div>";
					} else if ($score >= 2) {
						print "<div class=\"gw\">$guestDisplayName: $score <a href=\"$guestURL\">&rarr;</a></div>";
					} else {
						print "<div>$guestDisplayName: $score <a href=\"$guestURL\">&rarr;</a></div>";
					}
				}
			}
			
			print "</td></tr>\n";
			print '<tr><td colspan="3" class="sd">';
			if (is_null($showData->Description)) {
				print '<em>(N/A)</em>';
			} else {
				$desc = htmlentities($showData->Description, ENT_COMPAT);
				print nl2br($desc, false);
			}
			
			print "</td></tr>\n";
			print '<tr><td colspan="3" class="sn">';
			if (is_null($showData->Notes)) {
				print '<em>(N/A)</em>';
			} else {
				$notes = htmlentities($showData->Notes, ENT_COMPAT);
				print nl2br($notes, false);
			}
			
			print "</td></tr>\n";
			print "</table>\n</div>\n</div>\n";
		}
		
		public function recent($desc = true) {
			$showIDs = $this->WWDTM_Data->getRecentShowIDs($desc);
			$count = count($showIDs);
			for ($i = 0; $i < $count; $i++) {
				$this->show($showIDs[$i]);
			}
		}
		
		public function month($year, $month, $desc = false) {
			$showIDs = $this->WWDTM_Data->getShowIDsByMonth($year, $month, $desc);
			$count = count($showIDs);
			for ($i = 0; $i < $count; $i++) {
				$this->show($showIDs[$i]);
			}
		}
		
		public function year($year, $desc = false) {
			$showIDs = $this->WWDTM_Data->getShowIDsByYear($year, $desc);
			$count = count($showIDs);
			for($i = 0; $i < $count; $i++) {
				$this->show($showIDs[$i]);
			}
		}
		
		public function monthsOfYear($year) {
			print "<div id=\"month\">To view $year shows by month, click the corresponding month's link below: <ul>";
			for ($i = 1; $i < 12; $i++) {
				$month = date('F', mktime(0, 0, 0, $i, 10));
				$num = sprintf('%02d', $i);
				print "<li><a href=\"/shows/$year/$num\">$month $year</a></li>";
			}
			print "<li><a href=\"/shows/$year/12\">December $year</a></li></ul></div>\n";
		}
	}
}
