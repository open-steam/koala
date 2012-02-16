<?php

require_once( "../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();
$feeds = array(
	"http://131.234.154.23/locomotion/public/services/feeds/forum_public.php?id=187277",
	"http://131.234.154.23/locomotion/public/services/feeds/forum_public.php?id=191082"
);

$user->set_attribute( "USER_RSS_FEEDS", $feeds );
$items=lms_rss::get_merged_items($feeds);


print_r( $user->get_attribute( "USER_RSS_FEEDS" ) );

?>
