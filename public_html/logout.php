<?php

if (isset($showmsg)) {
    $delay = 3;
    Header("Refresh: $delay; url=\"$PHP_SELF\"");
    response_header("Logging Out...");
//	$ua = $HTTP_USER_AGENT;
	$logoutmsg = "Authorization failed. Retry?";
    report_error("Press 'Cancel' when presented a new login box or ".
		 "one saying '$logoutmsg'<br />");
    response_footer();
} else {
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"PEAR user\"");
    Header("Refresh: 1; url=\"./\"");
    auth_reject(PEAR_AUTH_REALM, "Logging out");
}

?>
