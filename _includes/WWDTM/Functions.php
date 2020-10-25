<?php
# Copyright (c) 2007-2020 Linh Pham
# wwdt.me_v3 is relased under the terms of the Apache License 2.0

namespace WWDTM {
	/* Require Slugify */
	require_once SITE_PATH.'/vendor/autoload.php';

	use \DateTime as DateTime;
	use \Cocur\Slugify\Slugify as Slugify;
	
	class Functions {
		private $slugify;

		function __construct() {
			$this->slugify = new Slugify();
		}

		public function validDate($showDate) {
				$format = 'Y-m-d';
				$date = DateTime::createFromFormat($format, $showDate);
				return $date && $date->format($format) == $showDate;
		}

		public function cleanPanelistName($panelistName) {
			$replace = array(' ' => '', '\'' => '', '.' => '', ',' => '');
			return strtr($panelistName, $replace);
		}
		
		public function slugify($originalString) {
			return $this->slugify->slugify($originalString);
		}	

		public function dateFormatToUrl($showDate) {
			return str_replace('-', '/', $showDate);
		}
		
		public function meanScore($scores) {
			$count = count($scores);
			if (!empty($scores)) {
				return round(array_sum($scores) / $count, 4);
			} else {
				return null;
			}
		}
		
		public function medianScore($scores) {
			$count = count($scores);
			$midValue = floor(($count - 1) / 2);
			if (empty($scores)) {
				return null;
			} else {
				if ($count == 1) {
					return $scores[0];
				} else {
					if ($count % 2) {
						return $scores[$midValue];
					} else {
						$low = $scores[$midValue];
						$high = $scores[$midValue + 1];
						return (($low + $high) / 2);
					}
				}
			}
		}
		
		public function standardDeviation($scores) {
			$count = count($scores);
			if ($count < 2) {
				return null;
			}
			
			$mean = $this->meanScore($scores);
			$sum = 0;
			foreach($scores as $score) {
				$sum += pow($score - $mean, 2);
			}
			
			$stdDev = sqrt((1 / ($count - 1)) * $sum);
			return round($stdDev, 4);
		}
	}
}
