<?php response_header("Error 404"); ?>

<h2>Error 404 - document not found</h2>

<p>The requested document <i><?php echo $REQUEST_URI; ?></i> was not
found in the PEAR manual.</p>

<p>Please go to the <?php print_link("/manual/", "Table of Contents"); ?> 
and try to find the desired chapter there.</p>

<?php response_footer(); ?>
