<?php
$startTime = microtime(true);
/* Pull in required packages */
require_once __DIR__.'/vendor/autoload.php';
$app = new Silex\Application();

/* Require: WWDTM */
require_once __DIR__ . '/_includes/WWDTM/ShowData.php';
require_once __DIR__ . '/_includes/WWDTM/PanelistData.php';
require_once __DIR__ . '/_includes/WWDTM/GuestData.php';
require_once __DIR__ . '/_includes/WWDTM/HostData.php';
require_once __DIR__ . '/_includes/WWDTM/ScorekeeperData.php';
require_once __DIR__ . '/_includes/WWDTM/Render/Page.php';
require_once __DIR__ . '/_includes/WWDTM/Functions.php';
require_once __DIR__ . '/_includes/WWDTM/Config.php';

/* Setup Render Page object */
$Render = new WWDTM\Render_Page();
$Functions = new WWDTM\Functions();

/* Handle application requests */
$app->get('/', function () use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Show.php';
	
	$desc = true;
	if (isset($_GET['asc'])) {
		$desc = false;
	}
	
	$Render_Show = new WWDTM\Render_Show();
	$Render->htmlStart(SITE_NAME);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Home.html');
	if ($desc) {
		print "<div id=\"sort\">Sort: <a href=\"/?asc\">Oldest First</a> | <strong>Newest First</strong></div>";
	} else {
		print "<div id=\"sort\">Sort: <strong>Oldest First</strong> | <a href=\"/\">Newest First</a></div>";		
	}
	$Render_Show->recent($desc);
	$Render->footer($startTime);
	return '';
});

$app->get('/about', function () use ($app, $Render, $startTime) {
	$title = 'About | ' . SITE_NAME;
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/About.html');
	$Render->footer($startTime);
	return '';
});

$app->get('/help', function () use ($app, $Render, $startTime) {
	$title = 'Help | ' . SITE_NAME;
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Help.html');
	$Render->footer($startTime);
	return '';
});

$app->get('/shows/all', function () use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Show.php';
	header('Cache-Control: max-age=' . CACHE_MAX_AGE);
	$ShowData = new WWDTM\ShowData();
	
	$desc = false;
	if (isset($_GET['desc'])) {
		$desc = true;
	}
	
	$showYears = $ShowData->getShowYears($desc);
	$title = 'All Shows | ' . SITE_NAME;
	$Render_Show = new WWDTM\Render_Show();
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Shows.html');
	if ($desc) {
		print "<div id=\"sort\">Sort: <a href=\"/shows/all\">Oldest First</a> | <strong>Newest First</strong></div>";
	} else {
		print "<div id=\"sort\">Sort: <strong>Oldest First</strong> | <a href=\"/shows/all?desc\">Newest First</a></div>";
	}
	
	$count = count($showYears);
	for ($i = 0; $i < $count; $i++) {
		$sYear = $showYears[$i];
		print "<h2 class=\"yh\">$sYear</h2>\n";
		$Render_Show->year($showYears[$i], $desc);
		print "<br>\n";
	}
	$Render->footer($startTime);
	return '';
});

$app->get('/shows/{year}', function ($year) use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Show.php';
	header('Cache-Control: max-age=' . CACHE_MAX_AGE);
	$ShowData = new WWDTM\ShowData();
	
	$year = intval($year);
	$desc = false;
	if (isset($_GET['desc'])) {
		$desc = true;
	}
	
	if (($year >= $ShowData->MinYear) && ($year <= $ShowData->MaxYear)) {
		$Render_Show = new WWDTM\Render_Show();
		$title = "Show Info: $year | " . SITE_NAME;
		$Render->htmlStart($title);
		$Render->navigationMenu();
		$Render->contentStart();
		$Render->blurb(__DIR__ . '/_content/ShowYear.html');
		$Render_Show->monthsOfYear($year);
		if ($desc) {
			print "<div id=\"sort\">Sort: <a href=\"/shows/$year\">Oldest First</a> | <strong>Newest First</strong></div>";
		} else {
			print "<div id=\"sort\">Sort: <strong>Oldest First</strong> | <a href=\"/shows/$year?desc\">Newest First</a></div>";
		}
		print "<h2 class=\"yh\">$year</h2>\n";
		$Render_Show->year($year, $desc);
		$Render->footer($startTime);
		return '';
	} else {
		return $app->redirect('/shows');
	}
});

$app->get('/shows/{year}/{month}', function ($year, $month) use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Show.php';
	$ShowData = new WWDTM\ShowData();
	
	$year = intval($year);
	$month = intval($month);
	$desc = false;
	if (isset($_GET['desc'])) {
		$desc = true;
	}
	
	if (($year >= $ShowData->MinYear && $year <= $ShowData->MaxYear) && ($month >= 1 && $month <= 12)) {
		$Render_Show = new WWDTM\Render_Show();
		$title = $Render->monthPageTitle($year, $month);
		$Render->htmlStart($title);
		$Render->navigationMenu();
		$Render->contentStart();
		$Render->blurb(__DIR__ . '/_content/ShowMonth.html');
		$Render->linkYearStats($year);
		if ($desc) {
			print "<div id=\"sort\">Sort: <a href=\"/shows/$year/$month\">Oldest First</a> | <strong>Newest First</strong></div>";
		} else {
			print "<div id=\"sort\">Sort: <strong>Oldest First</strong> | <a href=\"/shows/$year/$month?desc\">Newest First</a></div>";
		}
		$Render_Show->month($year, $month, $desc);
		$Render->footer($startTime);
		return '';
	} else {
		return $app->redirect('/shows');
	}
});

$app->get('/shows/{year}/{month}/{day}', function ($year, $month, $day) use ($app, $Render, $Functions, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Show.php';
	$ShowData = new WWDTM\ShowData();
	
	$year = intval($year);
	$month = intval($month);
	$day = intval($day);
	$showDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
	if ($Functions->validDate($showDate) && $ShowData->showExists($showDate)) {
		$Render_Show = new WWDTM\Render_Show();
		$title = "Show Info: $showDate | " . SITE_NAME;
		$showID = $ShowData->getShowID($showDate);
		$Render->htmlStart($title);
		$Render->navigationMenu();
		$Render->contentStart();
		$Render->blurb(__DIR__ . '/_content/ShowIndividual.html');
		$Render->linkYearAndMonthStats($year, $month);
		$Render_Show->show($showID);
		$Render->footer($startTime);
		return '';
	} else {
		return $app->redirect('/shows');
	}
});

$app->get('/panelists', function () use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Panelist.php';
	$PanelistData = new WWDTM\PanelistData();
	$Render_Panelist = new WWDTM\Render_Panelist();
	
	$title = 'Panelist Stats | ' . SITE_NAME;
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Panelist.html');
	$Render_Panelist->panelists();
	$Render->footer($startTime);
	return '';
});

$app->get('/panelists/{panelist}', function ($panelist) use ($app, $Render, $Functions, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Panelist.php';
	$PanelistData = new WWDTM\PanelistData();
	$Render_Panelist = new WWDTM\Render_Panelist();
	$panelistSlug = $Functions->slugify(strval($panelist));
	$panelistInfo = $PanelistData->panelistInfoFromSlug($panelistSlug); 

	if (!is_null($panelistInfo)) {
		if (strval($panelist) <> $panelistSlug) {
			return $app->redirect('/panelists/' . $panelistSlug, 301);
		}

		$title = 'Panelist Stats: ' . $panelistInfo['name'] . ' | ' . SITE_NAME;
		$Render->htmlStart($title);
		$Render->navigationMenu();
		$Render->contentStart();
		$Render->blurb(__DIR__ . '/_content/Panelist.js.html');
		$Render->blurb(__DIR__ . '/_content/Panelist.html');
		$Render_Panelist->panelist($panelistInfo['id'], true);
		$Render->footer($startTime);
		return '';
	} else {
		return $app->redirect('/panelists');
	}
});

$app->get('/guests', function () use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Guest.php';
	$GuestData = new WWDTM\GuestData();
	$Render_Guest = new WWDTM\Render_Guest();
	
	$title = 'Guest Stats | ' . SITE_NAME;
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Guest.html');
	$Render_Guest->guests();
	$Render->footer($startTime);
	return '';
});

$app->get('/guests/{guest}', function($guest) use ($app, $Render, $Functions, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Guest.php';
	$GuestData = new WWDTM\GuestData();
	$Render_Guest = new WWDTM\Render_Guest();
	$guestSlug = $Functions->slugify(strval($guest));
	$guestInfo = $GuestData->guestInfoFromSlug($guestSlug);
	$guestName = $guestInfo['name'];

	if ($guestName == 'Patrick Stewart') {
		$guestName = 'Sir Patrick Stewart';
	}

	if (!is_null($guestInfo)) {
		if (strval($guest) <> $guestSlug) {
			return $app->redirect('/guests/' . $guestSlug, 301);
		}

		$title = 'Guest Info: ' . $guestInfo['name'] . ' | ' . SITE_NAME;
		$Render->htmlStart($title);
		$Render->navigationMenu();
		$Render->contentStart();
		$Render->blurb(__DIR__ . '/_content/Guest.html');
		$Render_Guest->guest($guestInfo['id']);
		$Render->footer($startTime);
		return '';
	} else {
		return $app->redirect('/guests');
	}
});

$app->get('/hosts', function () use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Host.php';
	$HostData = new WWDTM\HostData();
	$Render_Host = new WWDTM\Render_Host();
	
	$title = 'Host Information | ' . SITE_NAME;
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Host.html');
	$Render_Host->hosts();
	$Render->footer($startTime);
	return '';
});

$app->get('/hosts/{host}', function($host) use ($app, $Render, $Functions, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Host.php';
	$HostData = new WWDTM\HostData();
	$Render_Host = new WWDTM\Render_Host();
	$hostSlug = $Functions->slugify(strval($host));
	$hostInfo = $HostData->hostInfoFromSlug($hostSlug);

	if (!is_null($hostInfo)) {
		if (strval($host) <> $hostSlug) {
			return $app->redirect('/hosts/' . $hostSlug, 301);
		}

		$title = 'Host Info: ' . $hostInfo['name'] . ' | ' . SITE_NAME;
		$Render->htmlStart($title);
		$Render->navigationMenu();
		$Render->contentStart();
		$Render->blurb(__DIR__ . '/_content/Host.html');
		$Render_Host->host($hostInfo['id']);
		$Render->footer($startTime);
		return '';
	} else {
		return $app->redirect('/hosts');
	}
});

$app->get('/scorekeepers', function () use ($app, $Render, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Scorekeeper.php';
	$ScorekeeperData = new WWDTM\ScorekeeperData();
	$Render_Scorekeeper = new WWDTM\Render_Scorekeeper();
	
	$title = 'Scorekeeper Information | ' . SITE_NAME;
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Scorekeeper.html');
	$Render_Scorekeeper->scorekeepers();
	$Render->footer($startTime);
	return '';
});

$app->get('/scorekeepers/{scorekeeper}', function($scorekeeper) use ($app, $Render, $Functions, $startTime) {
	require_once __DIR__ . '/_includes/WWDTM/Render/Scorekeeper.php';
	$ScorekeeperData = new WWDTM\ScorekeeperData();
	$Render_Scorekeeper = new WWDTM\Render_Scorekeeper();
	$scorekeeperSlug = $Functions->slugify(strval($scorekeeper));
	$scorekeeperInfo = $ScorekeeperData->scorekeeperInfoFromSlug($scorekeeperSlug);
	
	if (!is_null($scorekeeperInfo)) {
		if (strval($scorekeeper) <> $scorekeeperSlug) {
			return $app->redirect('/scorekeepers/' . $scorekeeperSlug, 301);
		}

		$title = 'Scorekeeper Info: ' . $scorekeeperInfo['name'] . ' | ' . SITE_NAME;
		$Render->htmlStart($title);
		$Render->navigationMenu();
		$Render->contentStart();
		$Render->blurb(__DIR__ . '/_content/Scorekeeper.html');
		$Render_Scorekeeper->scorekeeper($scorekeeperInfo['id']);
		$Render->footer($startTime);
		return '';
	} else {
		return $app->redirect('/scorekeepers');
	}
});

$app->get('/search', function() use ($app, $Render, $startTime) {
	$title = 'Search | ' . SITE_NAME;
	$Render->htmlStart($title);
	$Render->navigationMenu();
	$Render->contentStart();
	$Render->blurb(__DIR__ . '/_content/Search.html');
	$Render->footer($startTime);
	return '';
});

/* Handle URLs with trailing slash by redirecting to URLs with */
$app->get('/about/', function () use ($app) {
	return $app->redirect('/about');
});

$app->get('/help/', function () use ($app) {
	return $app->redirect('/help');
});

$app->get('/shows/{year}/', function ($year) use ($app) {
	return $app->redirect("/shows/$year");
});

$app->get('/shows/{year}/{month}/', function($year, $month) use ($app) {
	return $app->redirect("/shows/$year/$month");
});

$app->get('/shows/{year}/{month}/{day}/', function($year, $month, $day) use ($app) {
	return $app->redirect("/shows/$year/$month/$day");
});

$app->get('/panelists/', function () use ($app) {
	return $app->redirect('/panelists');
});

$app->get('/panelists/{panelist}/', function ($panelist) use ($app) {
	return $app->redirect("/panelists/$panelist");
});

$app->get('/guests/', function () use ($app) {
	return $app->redirect('/guests');
});

$app->get('/guests/{guest}/', function ($guest) use ($app) {
	return $app->redirect('/guests/$guest');
});

$app->get('/hosts/', function () use ($app) {
	return $app->redirect('/hosts');
});

$app->get('/hosts/{host}/', function ($host) use ($app) {
	return $app->redirect('/hosts/$host');
});

$app->get('/scorekeepers/', function () use ($app) {
	return $app->redirect('/scorekeepers');
});

$app->get('/scorekeepers/{scorekeeper}/', function ($scorekeeper) use ($app) {
	return $app->redirect('/scorekeepers/$scorekeeper');
});

$app->get('/search/', function () use ($app) {
	return $app->redirect('/search');
});

use Symfony\Component\HttpFoundation\Response;
use Symfony\Compoennt\HttpFoundation\Request;
/* Handle exceptions */
$app->error(function (\Exception $e, $code) use ($app, $Render, $startTime) {
//$app->error(function (\Exception $e, Request $request, $code) {
/*
	switch ($code) {
		case 404:
			header('HTTP/1.0 404 Not Found');
			
			$title = 'Page or Item Not Found | ' . SITE_NAME;
			$Render->htmlStart($title);
			$Render->navigationMenu();
			$Render->contentStart();
			$Render->blurb(__DIR__ . '/_content/404.html');
			$Render->footer($startTime);
			return '';
		default:
			$message = "Something failed spectularly.<br>$e<br>$code";
	}
	return new Response($message);
*/
	return $app->redirect('/');
});

$app->run();
