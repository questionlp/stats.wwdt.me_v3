<?php
namespace WWDTM {
	/* Require WWDTM Files */
	require_once __DIR__ . '/../ShowData.php';
	
	use \DateTime as DateTime;
	
	class Render_Page {
		private $WWDTM_ShowData;
		
		function __construct() {
			$this->WWDTM_ShowData = new ShowData();
		}
		
		public function htmlStart($title) {
			require __DIR__ . '/../../Templates/Header.php';
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
			require __DIR__ . '/../../Templates/Footer.php';
		}
		
		public function monthPageTitle($year, $month) {
			$YMD = $year . '-' . sprintf('%02d', $month) . '-01';
			$date = DateTime::createFromFormat('Y-m-d', $YMD);
			$dateFmt = 'Show Info: ' . $date->format('F Y') . ' | ' . SITE_NAME;
			return $dateFmt;
		}
		
		public function navigationMenu() {
			$showYears = $this->WWDTM_ShowData->getShowYears('desc');
			print "<div id=\"nav\"><ul>\n";
			print '<li><a href="/">Home</a></li><li><a href="/help">Help</a></li><li><a href="/search">Search</a></li><li><a href="/about">About</a></li></ul>';
			print "\n<hr>\n";
			print '<ul><li><a href="https://blog.wwdt.me/">Blog</a></li><li><a href="https://blog.wwdt.me/contact-me/">Contact Me</a></li>';
			print '<li><a href="http://mataglap.com/wwdtm/">Infographic</a></li></ul>';
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
				print '<div class="fb-like" data-href="https://wwdt.me/" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>';
				print '<div class="plus1"><div class="g-plusone" data-size="medium" data-href="https://wwdt.me/"></div></div>';
			}
			print "</div>\n";
		}
	}
}
