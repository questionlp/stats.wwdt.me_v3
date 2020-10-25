<?php
# Copyright (c) 2007-2020 Linh Pham
# wwdt.me_v3 is relased under the terms of the Apache License 2.0

require_once 'MDB2.php';

function dbDateExists($showdate) {
	$dsn = [
		'phptype' => 'mysqli',
		'username' => '',
		'password' => '',
		'hostspec' => '',
		'database' => '',
	];

	$options = [
		'debug' => '2',
		'portability' => MDB2_PORTABILITY_ALL,
		'persistent' => false,
	];

	$dbConnection = MDB2::factory($dsn, $options);
	if (PEAR::iserror($dbConnection)) {
		die($dbConnection->getMessage());
	}

	$statement = $dbConnection->prepare('select showdate from ww_shows where showdate = ?');
	$result = $statement->execute($showdate);
	if (PEAR::isError($result)) {
		die($result->getMessage());
	}

	$data = $result->fetchRow();
	$result->free();
	$dbConnection->disconnect();

	if (is_null($data)) {
		return false;
	} else {
		return true;
	}
}

function validShowDate($showdate) {
	$showdate = trim($showdate);
	if (strlen($showdate) == 10) {
		$dateArray = explode('-', $showdate);
		if (count($dateArray) == 3) {
			$year = $dateArray[0];
			$month = $dateArray[1];
			$day = $dateArray[2];
			if ((strlen($year) == 4) and (strlen($month) == 2) and (strlen($day) == 2)) {
				$dateString = "$year-$month-$day";
				return dbDateExists($dateString);
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else if (strlen($showdate) == 8) {
		$year = substr($showdate, 0, 4);
		$month = substr($showdate, 4, 2);
		$day = substr($showdate, 6, 2);
		$dateString = "$year-$month-$day";
		return dbDateExists($dateString);
	} else {
		return false;
	}
}

function redirectStats() {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://wwdt.me/');
}

function redirectShow($showdate) {
	$dtShowDate = new DateTime($showdate);
	$wwNewFormatDate = new DateTime('2006-01-07');

	$currentURLTemplate = 'http://www.npr.org/programs/wait-wait-dont-tell-me/archive?date=';
	$legacyURLTemplate = 'http://www.npr.org/programs/waitwait/archrndwn';

	if ($dtShowDate >= $wwNewFormatDate) {
		$url = $currentURLTemplate . $dtShowDate->format('m-d-Y');
		header('Location: ' . $url);
	} else {
		$legacyShowDate = $dtShowDate->format('ymd');
		$legacyShowYear = $dtShowDate->format('Y');
		$legacyMonthName = strtolower($dtShowDate->format('M'));
		$url = "$legacyURLTemplate/$legacyShowYear/$legacyMonthName/$legacyShowDate.waitwait.html";
		header('Location: ' . $url);
	}
}

if (isset($_REQUEST['s'])) {
	if (empty($_REQUEST['s'])) {
		redirectStats();
	} else {
		if (validShowDate($_REQUEST['s'])) {
			redirectShow($_REQUEST['s']);
		} else {
			redirectStats();
		}
	}
} else {
	redirectStats();
}
?>
