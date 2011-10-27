<?php

function checkUser($user, $package_name)
{
    global $dbh;
    $add = ($pacid) ? 'AND p.id = ' . $dbh->quoteSmart($pacid) : '';
    // It's a lead or user of the package
    $query = "SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = ? AND
                 p.id = m.package $add AND
                 (m.role IN ('lead', 'developer'))";
    $res = $dbh->getOne($query, array($user));
    if ($res !== null) {
        return true;
    }

    // Try to see if the user is an admin
    $res = user::isAdmin($user);
    return ($res === true);
}


function valid_package($file, $auth_user)
{
    include_once 'phar:///home/pierre/pyrus.phar/PEAR2_Pyrus-2.0.0a3/php/PEAR2/Autoload.php';

    $has_error = false;

    try {
        $package = new PEAR2\Pyrus\Package(PECL_UPLOAD_DIR . '/' . $file->name);
    } catch (Exception $e) {
        $file->error[] = 'Invalid package.xml: ' . $e->getMessage();
        $has_error = true;
    }

    if (!$auth_user->isAdmin()) {
        if (!user::maintains($auth_user->handle, $package_name, "lead")) {
            $has_error = true;#
            $file->errors[] = 'Only leads and admins are allowed to release packages.';
        }
    }

    if ($package->channel != 'pecl.php.net' || version_compare($package->attribs['version'], '2.0', '<')) {
        $file->errors[] = 'Your package uses package.xml 1.0. With the release of PEAR 1.4.0 stable,
         PECL packages require package.xml 2.0 and channel name "pecl.php.net"';
        $has_error = true;
    }

    if ($package->type != 'extsrc') {
        $file->errors[] = 'Release type ' . $info->type . ' is not ' .
                    'supported at pecl.php.net, only Extension releases are supported.  ' .
                    'pear.php.net supports php packages';
        $has_error = true;
    }

    if (!$has_error) {
        global $dbh;
        $exists = $dbh->getOne('SELECT 1 from packages WHERE name=' . $dbh->quote($package->name));
        if (!$exists) {
            $has_error = true;
            $file->errors[] = 'The package ' . $package->name . ' does not exist. Please create it prior to release.';
        }
        
    }
    if (version_compare($package->version['release'], '0.1.0', '<') || version_compare($package->version['api'], '0.1.0', '<')) {
        $file->errors[] = 'The version (release or API) cannot be lower than 0.1.0. The z in x.y.z format means patch(es) release.';
        $has_error = true;
    }

    if (!$has_error) {
        $pkg = new stdClass;
        $pkg->name = $package->name;
        $pkg->version_release = $package->version['release'];
        $pkg->version_api = $package->version['api'];
        $pkg->description = $package->description;
        $pkg->notes = $package->notes;
        $pkg->summary = $package->summary;
        $file->package = $pkg;
    }
    return !$has_error;
}

$json = filter_input(INPUT_GET, 'json', FILTER_VALIDATE_BOOLEAN);
$files = array();

if ($json) {
    $has_upload = count($_FILES) > 0;
    if ($has_upload) {
        foreach ($_FILES as $upload) {
            if (!is_uploaded_file($upload['tmp_name'])) {
                header('HTTP/1.1 403 Bad request');
                exit("Error in the uploaded data or in the upload request.");
            }

            $file = new stdClass();
            $file->name = $upload['name'];
            $file->size = (int)$upload['size'];
            $file->type = $upload['type'];
            $file->package = NULL;
            $file->name_md5 = md5($file->name);
            $dir = PECL_UPLOAD_DIR . '/' . $auth_user->handle . '/';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $file_path =  $dir . $file->name;
            if (!move_uploaded_file($upload['tmp_name'], $file_path)) {
                $file->error = 'Cannot move uploaded file.';
            }
            file_put_contents($dir . '/' . basename($file->name)  . '.json', json_encode($file));

            $files[] = $file;
        }
    }
} else {
    $has_upload = count($_FILES) > 0 && isset($_FILES['files']);
    if ($has_upload) {
        foreach ($_FILES['files']['tmp_name'] as $k => $tmp_name) {
            if (!is_uploaded_file($tmp_name)) {
                header('HTTP/1.1 400 Bad request');
                exit("Error in the uploaded data or in the upload request.");
            }

            $file = new stdClass();
            $file->name = $_FILES['files']['name'][$k];
            $file->size = (int)$_FILES['files']['size'][$k];
            $file->type = $_FILES['files']['type'][$k];
            $file->package = NULL;

            $file_path = PECL_UPLOAD_DIR . '/' . $file->name;
            if (!move_uploaded_file($tmp_name, $file_path)) {
                $file->error = 'Cannot move uploaded file.';
            }

            if (!valid_package($file)) {
            }

            $files[] = $file;
        }
    }
}

foreach ($files as $file) {
    if (valid_package($file, $auth_user)) {
        $dir = PECL_UPLOAD_DIR . '/' . $auth_user->handle . '/';
        file_put_contents($dir . '/' . basename($file->name)  . '.json', json_encode($file));
    }
}
header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');

if (!$has_upload) {
    header('HTTP/1.1 403 Bad request');
    exit();
}


echo json_encode($files);
