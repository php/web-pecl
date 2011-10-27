<?php

$release_name_md5 = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
$json = filter_input(INPUT_GET, 'json', FILTER_VALIDATE_BOOLEAN);
$cancel = filter_input(INPUT_GET, 'cancel', FILTER_VALIDATE_BOOLEAN);

if (!$release_name_md5) {
    $mode = 'list';
} else {
    $mode = 'release';
}

$upload_dir = PECL_UPLOAD_DIR . '/' . $auth_user->handle . '/';
$package_dir = PEAR_TARBALL_DIR;
$release_pending = glob('{' . $upload_dir . '*.tgz,'. $upload_dir. '*.zip}', GLOB_BRACE);
$release_json = glob($upload_dir . '*.json');

foreach ($release_json as $file) {
    $file = json_decode(file_get_contents($file));
    $files[$file->name] = $file;
    if ($mode == 'release' && $release_name_md5 == $file->name_md5) {
        $to_release = $file;
        break;
    }
}

if ($mode == 'release') {
    if ($cancel) {
        echo $file->name . " has been canceled!";
        unlink($upload_dir . $to_release->name);
    } else {
        echo $file->name . " has been released!";
        rename($upload_dir . $to_release->name, $package_dir);
    }
    unlink($upload_dir . $to_release->name . '.json');
    if ($json) {
        exit();
    }
}

$data = array(
    '$release_pending' => $release_pending,
    'files' => $files
);
if (!$json) {
    $page = new PeclPage();

} else {
    $page = new PeclPage(false);

}
$page->title = 'Confirm release(s)';
$page->setTemplate(PECL_TEMPLATE_DIR . '/developer/release-confirm.html');
$page->addData($data);
$page->render();
echo $page->html;