<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("foursquare_users");
	loadlib("foursquare_venues");
	loadlib("foursquare_checkins");

	$fsq_id = get_int32("foursquare_id");
	$chk_id = get_str("checkin_id");

	if ((! $fsq_id) || (! $chk_id)){
		error_404();
	}

	$history_url = "user/{$fsq_id}/history/";
	login_ensure_loggedin($history_url);

	$fsq_user = foursquare_users_get_by_foursquare_id($fsq_id);

	if (! $fsq_user){
		error_404();
	}

	$owner = users_get_by_id($fsq_user['user_id']);
	$is_own = ($GLOBALS['cfg']['user']['id'] == $owner['id']) ? 1 : 0;

	# for now...

	if (! $is_own){
		error_403();
	}

	$checkin = privatesquare_checkins_get_by_id($owner, $chk_id);

	if (! $checkin){
		error_404();
	}

	$checkin['locality'] = reverse_geoplanet_get_by_woeid($checkin['locality'], 'locality');

	$status_map = privatesquare_checkins_status_map();
	$broadcast_map = foursquare_checkins_broadcast_map();

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("checkin", $checkin);

	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	$GLOBALS['smarty']->assign_by_ref("broadcast_map", $broadcast_map);

	$GLOBALS['smarty']->assign("is_own", $is_own);

	$checkin_crumb = crumb_generate("api", "privatesquare.venues.checkin");
	$GLOBALS['smarty']->assign("checkin_crumb", $checkin_crumb);

	$status_crumb = crumb_generate("api", "privatesquare.checkins.updateStatus");
	$GLOBALS['smarty']->assign("status_crumb", $status_crumb);

	$delete_crumb = crumb_generate("api", "privatesquare.checkins.delete");
	$GLOBALS['smarty']->assign("delete_crumb", $delete_crumb);

	$GLOBALS['smarty']->display("page_user_checkin.txt");
	exit();
?>
