<?php

use Jaybizzle\CrawlerDetect\CrawlerDetect;

// we don't need all of WordPress here, just the basics.  so, we'll define
// SHORTINIT to true which limits the amount of WP core that is included when
// we then require wp-load.php.  when that's complete, we'll have access to
// the WP database object which is what we're really going for.

define('SHORTINIT', true);
require 'deps/vendor/autoload.php';
require 'web/wp-load.php';
global $wpdb;

if (($file = ($_GET['file'] ?? null)) === null) {
    header("HTTP/1.0 404 Not Found");
    die('<h1>File Not Found</h1>');
}

die($file);


// now, we don't want to count bots.  HTTP_USER_AGENT sniffing isn't the best
// way to prevent, so we also have a robots.txt which disallows access to this
// file specifically.  but, we also have the CrawlerDetect object that we can
// use here, too.  for that, we need our autoloader.

$crawlerDetector = new CrawlerDetect();
if (!$crawlerDetector->isCrawler()) {



}

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=" . basename($_GET['file']));
header("Content-Length: ".filesize($path));
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: binary");
ob_clean();
ob_end_flush();
readfile($path);
