<?php
//error_reporting(E_ALL);

// expected url vars: idpack
$idpack = (isset($idpack)) ? (int) $idpack : null;
if (empty($idpack)) {
    die ('No package selected');
}
// ** expected

$dbh = DB::Connect("mysql://pear:pear@localhost/pear");
if (DB::isError($dbh)) {
    die("DB::Factory failed: ".DB::errorMessage($dbh)."<BR>\n");
}
/*
$descriptions = array(
    "name" => "Package Name",
    "stablerelease" => "Latest Stable Release",
    "develrelease" => "Latest Development Release",
    "copyright" => "License",
    "summary" => "Package Description"
);
*/
$row = $dbh->getRow("SELECT * FROM packages WHERE id = $idpack",
                    DB_FETCHMODE_ASSOC);
if (!$row) {
    //die ('No package selected (db)');   // XXX Uncomment me!
}
$release  = '1.3';
$name     = 'Net_Ping';
$maturity = 'stable';
$summary  = 'This is the Pear Ping Class';
$authors  = '';
// foreach ($maintainers as $key => $values) {
$authors .= '<tr><td>$name &lt;<a href="mailto:$mail">$mail</a>> ($role)</td></tr>';
// }
$release_date  = '2000-01-34';
$release_notes = 'Bug fix release';
response_header("Package :: $name");
?>
<center><h2><?php echo "$name $release ($maturity)";?></h2></center>
<table border="1" cellspacing="3" cellpadding="3" height="48" width="100%">
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Summary</th>
    <td><?php echo $summary;?></td>
</tr>
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Authors</th>
    <td>
        <!-- Authors -->
        <table border="0" cellspacing="1" cellpadding="1" width="100%">
        <?php echo $authors;?>
        </table>
    </td>
    <tr>
    <th class="pack" bgcolor="#009933" width="20%">Release Date</th>
    <td><?php echo $release_date;?></td>
    </tr>
    <tr>
    <th class="pack" bgcolor="#009933" width="20%">Release Notes</th>
    <td><?php echo $release_notes;?></td>
</tr>
</table>
<br>
<table border="0" cellspacing="3" cellpadding="3" height="48"
width="100%" align="center">
<tr>
    <td width="33%" align="center">| View Source Code &amp;<br> Doc On-line |</td>
    <td width="33%" align="center">| View ChangeLog |</td>
    <td width="33%" align="center">| Download Now |</td>
</tr>
</table>
<br>
<!-- PAckage Dependencies -->
<table border="0" cellspacing="3" cellpadding="3" width="100%">
<tr>
    <th class="others" colspan="3" bgcolor="#DDDDDD">Net_Ping dependencies:</th>
</tr>
<tr>
    <td colspan="3">PHP > 4.0.5</td>
</tr>
<tr>
    <td>_Net_Foo-1.5_</td>
    <td>| Download |</td>
    <td>| View Source Code / Doc On-line |</td>
</tr>
<tr>
    <td colspan="3" align="center">&nbsp;<br>| Download All Packages |</td>
</tr>
</table>
<br>
<!-- Related Links -->
<table border="0" cellspacing="3" cellpadding="3" width="100%">
<tr>
    <th class="others" colspan="3" bgcolor="#DDDDDD">Related Links:</th>
</tr>
<tr>
    <td>_Home Page_</td>
    <td>Description</td>
</tr>
</table>
<br>
<!-- Other releases download -->
<table border="0" cellspacing="3" cellpadding="3" width="100%">
<tr>
    <th class="others" colspan="3" bgcolor="#DDDDDD">Other releases download:</th>
</tr>
<tr>
    <td>_Last CVS version_</td>
</tr>
</table>
<br>
<?php
response_footer();
?>
