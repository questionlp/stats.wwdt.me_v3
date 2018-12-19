<?php
namespace WWDTM {
	/* Require WWDTM Files */
	require_once __DIR__ . '/../PanelistData.php';
	require_once __DIR__ . '/../Functions.php';
	include_once __DIR__ . '/../Panelist.js.php';
	
	use \DateTime as DateTime;
	
	class Render_Panelist {
		private $WWDTM_PanelistData;
		
		function __construct() {
			$this->WWDTM_PanelistData = new PanelistData();
		}
		
		public function panelist($panelistID, $expanded = false) {
			$Functions = new Functions();
			$panelist = $this->WWDTM_PanelistData->getPanelistData($panelistID);
			$panelistInfo = $this->WWDTM_PanelistData->panelists[$panelistID];
			$panelistName = $panelistInfo['name'];
			$panelistSlug = $panelistInfo['slug'];
			$panelistDataURL = '/panelists/' . $panelistSlug;
			
			$firstAppURL = '/shows/' . $Functions->dateFormatToUrl($panelist->FirstAppearance);
			$latestAppURL = '/shows/' . $Functions->dateFormatToUrl($panelist->LatestAppearance);
			$latestWinURL = '/shows/' . $Functions->dateFormatToUrl($panelist->LatestFirstPlace);
			$latestWinTiedURL = '/shows/' . $Functions->dateFormatToUrl($panelist->LatestFirstPlaceTied);
			
			print "<div class=\"prw\"><div class=\"pn\">\n";
			if ($panelistID == SHOW_PANELISTID_LUKE_BURBANK) {
				print "<h3><a href=\"$panelistDataURL\" title=\"Luuuuuuke\">$panelistName</a></h3>";
				if (defined('DEVMODE') && DEVMODE == 1) {
					print 'Internal ID: ' . $panelistID;
				}
			} else {
				print "<h3><a href=\"$panelistDataURL\">$panelistName</a></h3>";
				if (defined('DEVMODE') && DEVMODE == 1) {
					print 'Internal ID: ' . $panelistID;
				}
			}
			
			print "</div>\n";
			print "<div class=\"pri\">\n";
			print '<table><tr>';
			print "<td class=\"pc1\">Appearances: $panelist->Appearances<br>";
			print "Appearances incl. R/B: $panelist->AllAppearances<br>";
			print "Appearances incl. Score: $panelist->AppearancesWithScores<br>";
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
				print "$panelist->CorrectBluffs<br>";
			} else {
				print '<em>(N/A)</em><br>';
			}

			print '<br>';
			print 'Please note that, due to not having a complete set of data recorded for all of ';
			print 'the Bluff the Listener segments, the data presented here should not be considered ';
			print 'final or complete.';
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
				PanelistJS($panelistID, htmlentities($panelistName, ENT_COMPAT));
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
	}
}
