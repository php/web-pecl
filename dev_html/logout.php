<?php
session_destroy();
Header("HTTP/1.0 401 Unauthorized");
header('Location: ' . PECL_DEVELOPER_URL . '/');

