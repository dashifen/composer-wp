<?php

use Jaybizzle\CrawlerDetect\CrawlerDetect;

// we don't need all of WordPress here, just the basics.  so, we'll define
// SHORTINIT to true which limits the amount of WP core that is included when
// we then require wp-load.php and a few other WP core files to get access to
// the post meta manipulation functions as well as the media functions.

define('SHORTINIT', true);
require_once 'deps/vendor/autoload.php';
require_once 'web/wp-load.php';
require_once 'web/wp-includes/shortcodes.php';
require_once 'web/wp-includes/media.php';
require_once 'web/wp-includes/revision.php';
require_once 'web/wp-includes/class-wp-post.php';
require_once 'web/wp-includes/post.php';

// next, let's confirm that our file exists.  the $_GET['file'] parameter
// is the path to the file in the WP uploads folder.  that query string value
// should always be present, but in case it's not, we'll send a 404 if we run
// into problems.

if (($file = ($_GET['file'] ?? null)) === null) {
    header('HTTP/1.0 404 Not Found');
    die('<h1>File Not Found</h1>');
}

// both WP_CONTENT_DIR and $file contain the 'deps' folder for reasons.
// so the following concatenation will point to the actual file we want to
// access.  this builds us a path to it that we can use to check that the
// file exists, and we can 404 if it doesn't.

$path = realpath(trailingslashit(WP_CONTENT_DIR) . '../' . $file);
if (!file_exists($path)) {
    header('HTTP/1.0 404 Not Found');
    die('<h1>File Not Found</h1>');
}

// now, we don't want to count bots.  HTTP_USER_AGENT sniffing isn't the best
// way to prevent, so we also have a robots.txt which disallows access to this
// file specifically.  but, we also have the CrawlerDetect object that we can
// use here, too.  for that, we need our autoloader.

$crawlerDetector = new CrawlerDetect();
if (!$crawlerDetector->isCrawler()) {
    
    // we want the download link to be at memoriam.services but, for some
    // reason, they're linking to dashifen.com, the top-level site of the
    // network.  so, we'll check the latter if the former doesn't work ending
    // in a 400 Bad Request error if we can't find it at all.
    
    if (($postId = attachment_url_to_postid('https://memoriam.services/' . $file)) === 0) {
        if (($postId = attachment_url_to_postid('https://dashifen.com/' . $file)) === 0) {
            header('HTTP/1.0 400 Bad Request');
            die('<h1>Bad Request</h1>');
        }
    }
    
    // finally, we want to add one to this attachment post's meta.  first, we
    // select the current count.  but, it might be empty if this is the first
    // time that we've downloaded the file.  thus, the update_post_meta call
    // needs a ternary statement to determine the value we save.
    
    $x = get_post_meta($postId, '_download-count', true);
    update_post_meta($postId, '_download-count', (empty($x) ? 1 : $x+1));
}

// oh, and we should probably send them the file, too.

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=" . basename($file));
header("Content-Length: ".filesize($path));
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: binary");
ob_clean();
ob_end_flush();
readfile($path);
