<?php response_header("Error 404"); ?>

<h2>Error 404 - document not found</h2>

<p>The requested document <i><?php echo $REQUEST_URI; ?></i> was not
found on this server.</p>

<p>If you think that this error message is caused by an error in the
configuration of the server, please contact
<?php echo make_mailto_link("pear-webmaster@php.net"); ?>.

<?php response_footer(); ?>
