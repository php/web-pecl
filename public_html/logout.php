<?php

Header("HTTP/1.0 401 Unauthorized");
Header("WWW-authenticate: basic realm=\"PEAR user\"");
Header("Refresh: 1; url=\"./\"");

auth_reject("PEAR user", "Logging out");

?>
