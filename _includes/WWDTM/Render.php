<?php
namespace WWDTM {
	/* Require WWDTM Files */
	require_once 'ShowData.php';
	require_once 'PanelistData.php';
	require_once 'GuestData.php';
	require_once 'HostData.php';
	require_once 'ScorekeeperData.php';
	require_once 'Functions.php';
	
	use \DateTime as DateTime;
	
	class Render {
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
		
		public function htmlStart($title) {
			require __DIR__ . '/../Templates/Header.php';
		}
		
		public function contentStart() {
			print "<div id=\"content\">\n";
			print "<h1>Wait Wait... Don't Tell Me! Stats and Show Details</h1>\n";
			if (defined('DEVMODE') && DEVMODE == 1) {
				print "<p id=\"dvmsg\">This is a development version of the WWDTM Stats Page! Please visit <a href=\"https://wwdt.me\">wwdt.me</a> for the live version!</p>\n";
			}
		}
		
		public function blurb($includeFile, $year = null, $month = null) {
			print '<div class="blurb">';
			require $includeFile;
			print "</div>\n";
		}
		
		public function linkYearStats($year) {
			print "<div class=\"ymlw\"><div class=\"yml\"><a href=\"/shows/$year\">&lt; Back to $year</a></div></div>";
		}
		
		public function linkYearAndMonthStats($year, $month) {
			$monthName = date('F', mktime(0, 0, 0, $month, 10));
			print "<div class=\"ymlw\"><div class=\"yml\"><a href=\"/shows/$year\">&lt; Back to $year</a></div>";
			print "<div class=\"yml\"><a href=\"/shows/$year/$month\">&lt; Back to $monthName $year</a></div></div>";
		}
	
		public function footer($startTime) {
			require __DIR__ . '/../Templates/Footer.php';
		}
		
		public function monthPageTitle($year, $month) {
			$YMD = $year . '-' . sprintf('%02d', $month) . '-01';
			$date = DateTime::createFromFormat('Y-m-d', $YMD);
			$dateFmt = 'Show Info: ' . $date->format('F Y') . ' | ' . SITE_NAME;
			return $dateFmt;
		}
		
		public function navigationMenu() {
			$showYears = $this->WWDTM_Data->getShowYears('desc');
			print "<div id=\"nav\"><ul>\n";
			print '<li><a href="/">Home</a></li><li><a href="/help">Help</a></li><li><a href="/search">Search</a></li><li><a href="/about">About</a></li></ul>';
			print "\n<hr>\n";
			print '<ul><li><a href="https://blog.wwdt.me/">Blog</a></li><li><a href="https://blog.wwdt.me/contact-me/">Contact Me</a></li></ul>';
			print "\n<hr>\n";
			print '<ul><li><a href="/panelists">Panelists</a></li><li><a href="/guests">Guests</a></li><li><a href="/hosts">Hosts</a></li><li><a href="/scorekeepers">Scorekeepers</a></li></ul>';
			print "\n<hr>\n";
			print '<ul><li><a href="/shows/all">All Shows</a></li>';
			
			$count = count($showYears);
			for ($i = 0; $i < $count; $i++) {
				$year = trim($showYears[$i]);
				print "<li><a href=\"/shows/$year\">$year</a></li>\n";
			}
			
			print "</ul>\n";
			
			if (defined('DEVMODE') && DEVMODE == 0) {
				print "<hr>\n";
				print '<div id="ntw"><a href="https://twitter.com/share" class="twitter-share-button" data-related="questionlp" data-hashtags="WWDTM" data-dnt="true">Tweet</a>';
				print "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
				print "</div>\n";
				print '<div class="fb-like" data-href="http://wwdt.me/" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>';
				print '<div class="plus1"><div class="g-plusone" data-size="medium" data-href="http://wwdt.me/"></div></div>';
			}
			print "</div>\n";
		}
		
		public function show($showID) {
			$Functions = new Functions();
			$showData = $this->WWDTM_Data->getShowData($showID);
			$showDateURL = $Functions->dateFormatToUrl($showData->Date);
			$showDateNPRURL = str_replace('-', '', $showData->Date);
			
			$hostName = $this->WWDTM_HostData->hosts[$showData->Host];
			$isGuestHost = $showData->isGuestHost;
			$hostURL = '/hosts/' . rawurlencode($hostName);
			$scorekeeperName = $this->WWDTM_ScorekeeperData->scorekeepers[$showData->Scorekeeper];
			$isGuestScorekeeper = $showData->isGuestScorekeeper;
			$scorekeeperURL = '/scorekeepers/' . rawurlencode($scorekeeperName);
			$locationID = $showData->Location;
			$location = $this->WWDTM_Data->Locations[$locationID];
			$city = $location['city'];
			$state = $location['state'];
			$venue = $location['venue'];
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
			if ($showData->isGuestHost == false) {
				print "$hostName <a href=\"$hostURL\">&rarr;</a>";
			} else if ($showData->Host == SHOW_HOSTID_LUKE_BURBANK) {
				print "<span class=\"gh\" title=\"Luuuuuuke\">$hostName</span> <a href=\"$hostURL\">&rarr;</a>";
			} else {
				print "<span class=\"gh\">$hostName</span> <a href=\"$hostURL\">&rarr;</a>";
			}
			
			print '</td><td class="hs">';
			if ($showData->isGuestScorekeeper == false) {
				if ($showData->Scorekeeper == SHOW_SCOREKEEPERID_CARL_KASELL and defined('SHOW_SCOREKEEPER_DISPLAY_CARL_EMERITUS') and SHOW_SCOREKEEPER_DISPLAY_CARL_EMERITUS == 1) {
					print "$scorekeeperName, <span class=\"skem\" title=\"Scorekeeper Emeritus\">SE</span> <a href=\"$scorekeeperURL\">&rarr;</a>";
				} else {
					print "$scorekeeperName <a href=\"$scorekeeperURL\">&rarr;</a>";
				}
			} else {
				print "<span class=\"gsk\">$scorekeeperName</span> <a href=\"$scorekeeperURL\">&rarr;</a>";
			}
			
			print "</td></tr>\n";
			print '<tr><td>';
			if (count($showData->Panelists) == 0) {
					print '<div><em>(N/A)</em></div>';
			} else {
				foreach ($showData->Panelists as $panelist) {
					$panelistID = $panelist['panelistid'];
					$panelistName = $this->WWDTM_PanelistData->panelists[$panelistID];
					$panelistDisplayName = $panelistName;
					$panelistURL = '/panelists/' . rawurlencode($panelistName);
					$score = $panelist['score'];
					$start = $panelist['start'];
					$correct = $panelist['correct'];
					$rank = $panelist['rank'];
					
					if ($panelistID == SHOW_PANELISTID_LUKE_BURBANK) {
						$panelistDisplayName = "<span title=\"Luuuuuuke\">$panelistDisplayName</span>";
					} else {
						$panelistDisplayName = htmlentities($panelistDisplayName, ENT_COMPAT);
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
				if ($showData->Bluff['chosen'] == SHOW_PANELISTID_LUKE_BURBANK) {
					print '<span title="Luuuuuuke">' . $this->WWDTM_PanelistData->panelists[$showData->Bluff['chosen']] . '</span>';
				} else {
					print $this->WWDTM_PanelistData->panelists[$showData->Bluff['chosen']];
				}
			}
			
			print '<br>Correct: ';
			if (is_null($showData->Bluff['correct'])) {
				print '<em>(N/A)</em>';
			} else {
				if ($showData->Bluff['correct'] == SHOW_PANELISTID_LUKE_BURBANK) {
					print '<span title="Luuuuuuke">' . $this->WWDTM_PanelistData->panelists[$showData->Bluff['correct']] . '</span>';
				} else {
					print $this->WWDTM_PanelistData->panelists[$showData->Bluff['correct']];
				}
			}
			
			print '</td><td>';
			if (count($showData->Guests) == 0) {
				print '<div><em>(N/A)</em></div>';
			} else {
				foreach($showData->Guests as $guest) {
					$guestID = $guest['guestid'];
					$guestName = htmlentities($this->WWDTM_GuestData->guests[$guestID], ENT_COMPAT);
					$guestURL = '/guests/' . rawurlencode($guestName);
					$score = $guest['score'];
					$exception = $guest['exception'];
					
					if (is_null($score)) {
						print "<div>$guestName <a href=\"$guestURL\">&rarr;</a></div>";
					} else if ($exception) {
						print "<div class=\"gw\">$guestName: $score * <a href=\"$guestURL\">&rarr;</a></div>";
					} else if ($score >= 2) {
						print "<div class=\"gw\">$guestName: $score <a href=\"$guestURL\">&rarr;</a></div>";
					} else {
						print "<div>$guestName: $score <a href=\"$guestURL\">&rarr;</a></div>";
					}
				}
			}
			
			print "</td></tr>\n";
			print '<tr><td colspan="3" class="sd">';
			if (is_null($showData->Description)) {
				print '<em>(N/A)</em>';
			} else {
				$description = htmlentities($showData->Description, ENT_COMPAT);
				print nl2br($description, false);
			}
			
			print "</td></tr>\n";
			print '<tr><td colspan="3" class="sn">';
			if (is_null($showData->Notes)) {
				print '<em>(N/A)</em>';
			} else {
				print nl2br(htmlentities($showData->Notes, ENT_COMPAT), false);
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
		
		public function panelist($panelistID, $expanded = false) {
			$Functions = new Functions();
			$panelist = $this->WWDTM_PanelistData->getPanelistData($panelistID);
			$panelistName = $this->WWDTM_PanelistData->panelists[$panelistID];
			$pnlGraphName = $Functions->cleanPanelistName($panelistName);
			$panelistDataURL = '/panelists/' . rawurlencode($panelistName);
			
			$firstAppURL = '/shows/' . $Functions->dateFormatToUrl($panelist->FirstAppearance);
			$latestAppURL = '/shows/' . $Functions->dateFormatToUrl($panelist->LatestAppearance);
			$latestWinURL = '/shows/' . $Functions->dateFormatToUrl($panelist->LatestFirstPlace);
			$latestWinTiedURL = '/shows/' . $Functions->dateFormatToUrl($panelist->LatestFirstPlaceTied);
			
			print "<div class=\"prw\"><div class=\"pn\">\n";
			if ($panelistID == SHOW_PANELISTID_LUKE_BURBANK) {
				print "<h3><a href=\"$panelistDataURL\" title=\"Luuuuuuke\">$panelistName</a></h3>";
				if (defined('DEVMODE') && DEVMODE == 1) {
					print "Internal ID: $panelistID";
				}
			} else {
				print "<h3><a href=\"$panelistDataURL\">$panelistName</a></h3>";
				if (defined('DEVMODE') && DEVMODE == 1) {
					print "Internal ID: $panelistID";
				}
			}
			
			print "</div>\n";
			print "<div class=\"pri\">\n";
			print '<table><tr>';
			print "<td class=\"pc1\">Appearances: $panelist->Appearances<br>";
			print "Appearances with R/B: $panelist->AllAppearances<br>";
			print "Appearances with Score: $panelist->AppearancesWithScores<br>";
			print "First Appearence: <a href=\"$firstAppURL\">$panelist->FirstAppearance</a><br>";
			print "Latest Appearance: <a href=\"$latestAppURL\">$panelist->LatestAppearance</a></td>";
			
			if ($panelist->AppearancesWithScores > 0) {
				print "<td class=\"pc2\">1st Place: $panelist->FirstPlace ";
				print '(' . sprintf("%01.2f", 100 * ($panelist->FirstPlace / $panelist->AppearancesWithScores)) . "%)<br>";
				print "1st Place (Tied): $panelist->FirstPlaceTied ";
				print '(' . sprintf("%01.2f", 100 * ($panelist->FirstPlaceTied / $panelist->AppearancesWithScores)) . "%)<br>";
				print "2nd Place: $panelist->SecondPlace ";
				print '(' . sprintf("%01.2f", 100 * ($panelist->SecondPlace / $panelist->AppearancesWithScores)) . "%)<br>";
				print "2nd Place (Tied): $panelist->SecondPlaceTied ";
				print '(' . sprintf("%01.2f", 100 * ($panelist->SecondPlaceTied / $panelist->AppearancesWithScores)) . "%)<br>";
				print "3rd Place: $panelist->ThirdPlace ";
				print '(' . sprintf("%01.2f", 100 * ($panelist->ThirdPlace / $panelist->AppearancesWithScores)) . "%)</td>";
			} else {
				print '<td class="pc2">';
				print '<em>No Scoring Data Available For Placement Data</em>';
				print '</td>';
			}
			
			if ($panelist->AppearancesWithScores > 0) {
				print "<td class=\"pc3\">";
				print "Min / Max Scores: $panelist->LowestScore / $panelist->HighestScore<br>";
				print "Median: $panelist->MedianScore<br>";
				print "Mean: $panelist->MeanScore<br>";
				print "Standard Deviation: $panelist->StandardDeviation<br>";
				print "Sum of Scores: $panelist->SumScores";
				print "</td></tr>\n";
				print "<tr><td colspan=\"3\">Last time $panelistName has won outright: ";
				if (!is_null($panelist->LatestFirstPlace)) {
					print "<a href=\"$latestWinURL\">$panelist->LatestFirstPlace</a> ($panelist->GamesSinceFirstPlace show(s) since last appearance)<br>";
				} else {
					print '<em>(N/A)</em><br>';
				}
					
				print "Last time $panelistName has won outright or tied for first: ";
				if (!is_null($panelist->LatestFirstPlaceTied)) {
					print "<a href=\"$latestWinTiedURL\">$panelist->LatestFirstPlaceTied</a> ($panelist->GamesSinceFirstPlaceTied show(s) since last appearance)";
				} else {
					print '<em>(N/A)</em>';
				}
			} else {
				print '<td class="pc3">';
				print '<em>No Scoring Data Available For Calculating Statistics</em>';
			}
		
			print "</td></tr>\n<tr><td colspan=\"3\">";
			print "Number of times a listener has chosen $panelistName's bluff: ";
			if (!is_null($panelist->ChosenBluffs)) {
				print "$panelist->ChosenBluffs<br>";
			} else {
				print '<em>(N/A)</em><br>';
			}
		
			print "Number of times $panelistName had the true story: ";
			if (!is_null($panelist->CorrectBluffs)) {
				print "$panelist->CorrectBluffs";
			} else {
				print '<em>(N/A)</em>';
			}
		
			print "</td></tr>\n";
			if (!($expanded)) {
				if ($panelist->AppearancesWithScores > 0) {
					print '<tr><td colspan="3"><div>Panelist scoring graphs have been moved to ';
					print "<a href=\"$panelistDataURL\">$panelistName's statistics page.</a></div></td></tr>\n";
				}
			} else {
				if ($panelist->AppearancesWithScores > 0) {
					print '<tr><td colspan="3">';
					print "<div id=\"pnl$panelistID" . 'spread" style="width: 720px; height: 325px"></div>';
					print "<div id=\"pnl$panelistID" . 'trend" style="width: 720px; height: 375px"></div>';
					print "<script type=\"text/javascript\" src=\"/panelistGraphJS.php?pnlid=$panelistID\"></script>";
					print "</td></tr>\n";
				}
	
				print '<tr><td colspan="3">';
				print '<div>List of appearances:</div><ul class="apl">';
	
				$count = count($panelist->AppearancesList);
				for ($i = 0; $i < $count; $i++) {
					$data = $panelist->AppearancesList[$i];
					$showDate = $data['showdate'];
					$score = $data['score'];
					$rank = $data['rank'];
					$bestOf = $data['bestof'];
					$repeatShowID = $data['repeatshowid'];
					$showDateURL = $Functions->dateFormatToUrl($showDate);
					if (is_null($score)) {
						print "<li><a href=\"/shows/$showDateURL\">$showDate</a>";
					} else {
						if (!is_null($rank)) {
							$rankName = $this->WWDTM_PanelistData->rankNames[$rank];
							print "<li><a href=\"/shows/$showDateURL\">$showDate: $score ($rankName)</a>";
						} else {
							print "<li><a href=\"/shows/$showDateURL\" class=\"r$rank\">$showDate: $score</a>";
						}
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
			}
			
			print "</table></div></div>\n";
		}
		
		public function panelists() {
			$panelistIDs = $this->WWDTM_PanelistData->getPanelistIDs();
			$count = count($panelistIDs);
			for ($i = 0; $i < $count; $i++) {
				$this->panelist($panelistIDs[$i]);
			}
		}
		
		public function guest($guestID) {
			$Functions = new Functions();
			$guest = $this->WWDTM_GuestData->getGuestData($guestID);
			$guestName = $this->WWDTM_GuestData->guests[$guestID];
			$guestDataURL = '/guests/' . rawurlencode($guestName);
			
			print "<div class=\"grw\"><div class=\"gn\">\n";
			print "<a href=\"$guestDataURL\"><h3>$guestName</h3></a>";
			if (defined('DEVMODE') && DEVMODE == 1) {
				print "Internal ID: $guestID";
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
		
		public function host($hostID) {
			$Functions = new Functions();
			$host = $this->WWDTM_HostData->getHostData($hostID);
			$hostName = $this->WWDTM_HostData->hosts[$hostID];
			$hostDataURL = '/hosts/' . rawurlencode($hostName);
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
				print "Internal ID: $hostID";
			}
			print "</div>\n";
			
			print "<div class=\"gri\">\n";
			if ($count >= 120) {
				print '<table class="w1">';
			} else {
				print '<table>';
			}
			print "<tr><td class=\"gc1\">Appearances: $host->Appearances<br>";
			print "Appearances with Repeats/Best Ofs: $host->AllAppearances</td></tr>\n";
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
		
		public function scorekeeper($scorekeeperID) {
			$Functions = new Functions();
			$scorekeeper = $this->WWDTM_ScorekeeperData->getScorekeeperData($scorekeeperID);
			$scorekeeperName = $this->WWDTM_ScorekeeperData->scorekeepers[$scorekeeperID];
			$scorekeeperDataURL = '/scorekeepers/' . rawurlencode($scorekeeperName);
			
			print "<div class=\"grw\"><div class=\"gn\">\n";
			print "<a href=\"$scorekeeperDataURL\"><h3>$scorekeeperName</h3></a>";
			if (defined('DEVMODE') && DEVMODE == 1) {
				print "Internal ID: $scorekeeperID";
			}
			print "</div>\n";
			
			print "<div class=\"gri\">\n";
			if (count($scorekeeper->AppearancesList) >= 120) {
				print '<table class="w1">';
			} else {
				print '<table>';
			}
			print "<tr><td class=\"gc1\">Appearances: $scorekeeper->Appearances<br>";
			print "Appearances with Repeats/Best Ofs: $scorekeeper->AllAppearances</td></tr>\n";
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
