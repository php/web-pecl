<?php /* vim: set noet ts=4 sw=4: : */
/**
 * Procedures for reporting bugs
 *
 * See pearweb/sql/bugs.sql for the table layout.
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2004 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain common includes
 */
require_once './include/prepend.inc';

/**
 * Get user's CVS password
 */
require_once './include/cvs-auth.inc';

error_reporting(E_ALL ^ E_NOTICE);
$errors              = array();
$ok_to_submit_report = false;

if (isset($_POST['save']) && isset($_POST['pw'])) {
    // non-developers don't have $user set
    setcookie('MAGIC_COOKIE', base64_encode(':' . $_POST['pw']),
              time() + 3600 * 24 * 12, '/', '.php.net');
}

if (isset($_POST['in'])) {
	 if (!($errors = incoming_details_are_valid($_POST['in'], 1))) {

        /*
         * When user submits a report, do a search and display
         * the results before allowing them to continue.
         */
        if (!$_POST['in']['did_luser_search']) {

            $_POST['in']['did_luser_search'] = 1;

            // search for a match using keywords from the subject
            $sdesc = rinse($_POST['in']['sdesc']);

            /*
             * If they are filing a feature request,
             * only look for similar features
             */
            $package_name = $_POST['in']['package_name'];
            if ($package_name == 'Feature/Change Request') {
                $where_clause = "WHERE package_name = '$package_name'";
            } else {
                $where_clause = "WHERE package_name != 'Feature/Change Request'";
            }

			list($sql_search, $ignored) = format_search_string($sdesc);

			$where_clause .= $sql_search;

			$query = "SELECT * from bugdb $where_clause LIMIT 5";

            $res =& $dbh->query($query);

            if ($res->numRows() == 0) {
                $ok_to_submit_report = 1;
            } else {
				response_header('Report - Confirm');
                # the lol
                echo "<style>"; include('./style.css'); echo "</style>";

?>
<p>
 Are you sure that you searched before you submitted your bug report? We
 found the following bugs that seem to be similar to yours; please check
 them before sumitting the report as they might contain the solution you
 are looking for.
</p>

<p>
 If you're sure that your report is a genuine bug that has not been reported
 before, you can scroll down and click the submit button to really enter the
 details into our database.
</p>

<div class="warnings">

<table class="lusersearch">
 <tr>
  <td><b>Description</b></td>
  <td><b>Possible Solution</b></td>
 </tr>
<?php

				while ($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {

                    $resolution =& $dbh->getOne('SELECT comment FROM' .
                            ' bugdb_comments where bug = ' .
                            $row['id'] . ' ORDER BY id DESC LIMIT 1');

                    if ($resolution) {
                        $resolution = htmlspecialchars($resolution);
                    }

                    $summary = $row['ldesc'];
                    if (strlen($summary) > 256) {
                        $summary = htmlspecialchars(substr(trim($summary),
                                                    0, 256)) . ' ...';
                    } else {
                        $summary = htmlspecialchars($summary);
                    }

                    $bug_url = "/bugs/bug.php?id=$row[id]&amp;edit=2";

                    echo " <tr>\n";
                    echo '  <td colspan="2"><a href="' . $bug_url . '">Bug #';
                    echo $row['id'] . ': ' . htmlspecialchars($row['sdesc']);
                    echo "</a></td>\n";
                    echo " </tr>\n";
                    echo " <tr>\n";
                    echo '  <td>' . $summary . "</td>\n";
                    echo '  <td>' . nl2br($resolution) . "</td>\n";
                    echo " </tr>\n";

				}

                echo "</table>\n";
                echo "</div>\n";
			}
		} else {
			/*
			 * we displayed the luser search and they said it really
			 * was not already submitted, so let's allow them to submit
			 */
			$ok_to_submit_report = true;
		}

		if ($ok_to_submit_report) {
            // Put all text areas together.
            $fdesc = "Description:\n------------\n" . $_POST['in']['ldesc'] . "\n\n";
            if (!empty($_POST['in']['repcode'])) {
                $fdesc .= "Reproduce code:\n---------------\n";
                $fdesc .= $_POST['in']['repcode'] . "\n\n";
            }
            if (!empty($_POST['in']['expres']) ||
                $_POST['in']['expres'] === '0')
            {
                $fdesc .= "Expected result:\n----------------\n";
                $fdesc .= $_POST['in']['expres'] . "\n\n";
            }
            if (!empty($_POST['in']['actres']) ||
                $_POST['in']['actres'] === '0')
            {
                $fdesc .= "Actual result:\n--------------\n";
                $fdesc .= $_POST['in']['actres'] . "\n";
            }

            $query = 'INSERT INTO bugdb (' .
                     ' package_name,' .
                     ' bug_type,' .
                     ' email,' .
                     ' sdesc,' .
                     ' ldesc,' .
                     ' php_version,' .
                     ' php_os,' .
                     ' status, ts1,' .
                     ' passwd' .
                     ') VALUES (' .
                     " '" . escapeSQL($_POST['in']['package_name']) . "'," .
                     " '" . escapeSQL($_POST['in']['bug_type']) . "'," .
                     " '" . escapeSQL($_POST['in']['email']) . "'," .
                     " '" . escapeSQL($_POST['in']['sdesc']) . "'," .
                     " '" . escapeSQL($fdesc) . "'," .
                     " '" . escapeSQL($_POST['in']['php_version']) . "'," .
                     " '" . escapeSQL($_POST['in']['php_os']) . "'," .
                     " 'Open', NOW(), " .
                     " '" . escapeSQL($_POST['in']['passwd']) . "')";

            $dbh->query($query);

/*
 * Need to move the insert ID determination to DB eventually...
 */
            $cid = mysql_insert_id();;

            $report  = '';
            $report .= 'From:             ' . spam_protect(rinse($_POST['in']['email']),
                                                           'text') . "\n";
            $report .= 'Operating system: ' . rinse($_POST['in']['php_os']) . "\n";
            $report .= 'PHP version:      ' . rinse($_POST['in']['php_version']) . "\n";
            $report .= 'Package:          ' . $_POST['in']['package_name'] . "\n";
            $report .= 'Bug Type:         ' . $_POST['in']['bug_type'] . "\n";
            $report .= 'Bug description:  ';

            $fdesc = rinse($fdesc);
            $sdesc = rinse($_POST['in']['sdesc']);

			$ascii_report = "$report$sdesc\n\n".wordwrap($fdesc);
			$ascii_report.= "\n-- \nEdit bug report at http://pecl.php.net/bugs/bug.php?id=$cid&edit=";

            list($mailto, $mailfrom) = get_package_mail(
                    $_POST['in']['package_name']);

            $email = rinse($_POST['in']['email']);
            $protected_email  = '"' . spam_protect($email, 'text') . '"';
            $protected_email .= '<' . $mailfrom . '>';

			// provide shortcut URLS for "quick bug fixes"
			/*
			$dev_extra = "";
			$maxkeysize = 0;
			foreach ($RESOLVE_REASONS as $v) {
				if (!$v['webonly']) {
					$actkeysize = strlen($v['desc']) + 1;
					$maxkeysize = (($maxkeysize < $actkeysize) ? $actkeysize : $maxkeysize);
				}
			}
			foreach ($RESOLVE_REASONS as $k => $v) {
				if (!$v['webonly'])
					$dev_extra .= str_pad($v['desc'] . ":", $maxkeysize) .
						" http://bugs.php.net/fix.php?id=$cid&r=$k\n";
			}
            */

			$mid = sprintf("bug-%d-%08x@pecl.php.net", $cid, time());

			// Set extra-headers
			$extra_headers = "From: $protected_email\n";
			$extra_headers.= "X-PECL-Bug: $cid\n";
            $extra_headers .= 'X-PECL-PHP-Type: '     . rinse($_POST['in']['bug_type']) . "\n";
            $extra_headers .= 'X-PECL-PHP-Version: '  . rinse($_POST['in']['php_version']) . "\n";
            $extra_headers .= 'X-PECL-PHP-Category: ' . rinse($_POST['in']['package_name']) . "\n";
            $extra_headers .= 'X-PECL-PHP-OS: '       . rinse($_POST['in']['php_os']) . "\n";
			$extra_headers.= "X-PECL-PHP-Status: Open\n";
			$extra_headers.= "Message-ID: <$mid>";

            $type = @$types[$_POST['in']['bug_type']];

            if (DEVBOX == false) {
                // mail to package developers
                @mail($mailto, "[$siteBig-BUG] $type #$cid [NEW]: $sdesc",
                      $ascii_report . "1\n-- \n$dev_extra", $extra_headers,
                      '-fpear-sys@php.net');
                // mail to reporter
                @mail($email, "[$siteBig-BUG] $type #$cid: $sdesc",
                      $ascii_report . "2\n",
                      "From: $siteBig Bug Database <$mailfrom>\n" .
                      "X-PHP-Bug: $cid\n" .
                      "Message-ID: <$mid>",
                      '-fpear-sys@php.net');
            }
            localRedirect('bug.php?id=' . $cid . '&thanks=4');
            exit;
        }
    } else {
        // had errors...
	    response_header('Report - Problems');
	}

}  // end of if input

if (!package_exists($_REQUEST['package'])) {
    $errors[] = 'Package &quot;' . $_REQUEST['package'] . '&quot; does not exist.';
    response_header("Report - Invalid bug type");
    display_bug_error($errors);
} else {
    if (!isset($_POST['in'])) {
        response_header('Report - New');
        show_bugs_menu($_REQUEST['package']);
?>

<p>
 Before you report a bug, make sure to search for similar bugs using the
 &quot;Bug List&quot; link. Also, read the instructions for
 <a target="top" href="http://bugs.php.net/how-to-report.php">how to report
 a bug that someone will want to help fix</a>.
</p>

<p>
 If you aren't sure that what you're about to report is a bug, you should
 ask for help using one of the means for support
 <a href="/support/">listed here</a>.
</p>

<p>
 <strong>Failure to follow these instructions may result in your bug
 simply being marked as &quot;bogus.&quot;</strong>
</p>

<p>
 <strong>If you feel this bug concerns a security issue, eg a buffer
 overflow, weak encryption, etc, then email
 <?php echo make_mailto_link('security@php.net?subject=%5BSECURITY%5D possible new bug%21', 'security'); ?>
 who will assess the situation.</strong>
</p>
<?php
    }

    display_bug_error($errors);
?>

<form method="post"
 action="<?php echo $_SERVER['PHP_SELF'] . '?package='
 . $_REQUEST['package']; ?>">
<table class="form-holder" cellspacing="1">
 <tr>
  <th class="form-label_left">
   Y<span class="accesskey">o</span>ur email address:
  </th>
  <td class="form-input">
   <input type="hidden" name="in[did_luser_search]"
    value="<?php echo $_POST['in']['did_luser_search'] ? 1 : 0; ?>" />
   <input type="text" size="20" maxlength="40" name="in[email]"
    value="<?php echo clean($_POST['in']['email']); ?>" accesskey="o" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   PHP version:
  </th>
  <td class="form-input">
   <select name="in[php_version]">
    <?php show_version_options($_POST['in']['php_version']); ?>
   </select>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Package affected:
  </th>
  <td class="form-input">

    <?php

    if (!empty($_REQUEST['package'])) {
        echo '<input type="hidden" name="in[package_name]" value="';
        echo $_REQUEST['package'] . '" />' . $_REQUEST['package'];
        if ($_REQUEST['package'] == 'Bug System') {
            echo '<p><strong>WARNING: You are saying the <em>package';
            echo ' affected</em> is the &quot;Bug System.&quot; This';
            echo ' category is <em>only</em> for telling us about problems';
            echo ' that the '.$siteBig.' website\'s bug user interface is having. If';
            echo ' your bug is about a '.$siteBig.' package or other aspect of the';
            echo ' website, please hit the back button and actually read that';
            echo ' page so you can properly categorize your bug.</strong></p>';
        }
    } else {
        echo '<select name="in[package_name]">' . "\n";
        show_types(null, 0, $_REQUEST['package']);
        echo '</select>';
    }

    ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Bug Type:
  </th>
  <td class="form-input">
   <select name="in[bug_type]">
    <?php show_type_options($_POST['in']['bug_type']); ?>
   </select>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Operating system:
  </th>
  <td class="form-input">
   <input type="text" size="20" maxlength="32" name="in[php_os]"
    value="<?php echo clean($_POST['in']['php_os']); ?>" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Summary:
  </th>
  <td class="form-input">
   <input type="text" size="40" maxlength="79" name="in[sdesc]"
    value="<?php echo clean($_POST['in']['sdesc']); ?>" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Password:
  </th>
  <td class="form-input">
   <input type="password" size="20" maxlength="20" name="in[passwd]"
    value="<?php echo clean($_POST['in']['passwd']); ?>" />
   <p class="cell_note">
    You may enter any password here, which will be stored for this bug report.
    This password allows you to come back and modify your submitted bug report
    at a later date.
   </p>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Note:
  </th>
  <td class="form-input">
   Please supply any information that may be helpful in fixing the bug:
   <ul>
    <li>The version number of the <?php echo $siteBig; ?> package or files you are using.</li>
    <li>A short script that reproduces the problem.</li>
    <li>The list of modules you compiled PHP with (your configure line).</li>
    <li>Any other information unique or specific to your setup.</li>
    <li>
     Any changes made in your php.ini compared to php.ini-dist
     (<strong>not</strong> your whole php.ini!)
    </li>
    <li>
     A <a href="http://bugs.php.net/bugs-generating-backtrace.php">gdb
     backtrace</a>.
    </li>
   </ul>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Description:
   <p class="cell_note">
    Put patches and code samples in the
    &quot;Reproduce code&quot; section, <strong>below</strong>.
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[ldesc]"
    wrap="physical"><?php echo clean($_POST['in']['ldesc']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Reproduce code:
   <p class="cell_note">
    Please <strong>do not</strong> post more than 20 lines of source code.
    If the code is longer than 20 lines, provide a URL to the source
    code that will reproduce the bug.
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[repcode]"
    wrap="no"><?php echo clean($_POST['in']['repcode']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Expected result:
   <p class="cell_note">
    What do you expect to happen or see when you run the code above?
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[expres]"
    wrap="physical"><?php echo clean($_POST['in']['expres']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Actual result:
   <p class="cell_note">
    This could be a
    <a href="http://bugs.php.net/bugs-generating-backtrace.php">backtrace</a>
    for example.
    Try to keep it as short as possible without leaving anything relevant out.
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[actres]"
    wrap="physical"><?php echo clean($_POST['in']['actres']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Submit:
  </th>
  <td class="form-input">
   <input type="submit" value="Send bug report" />
  </td>
 </tr>
</table>
</form>

    <?php
}

response_footer();

?>
