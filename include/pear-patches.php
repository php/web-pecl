<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "PEAR.php";

/**
 * PEAR Patch Tracker
 *
 * @author Martin Jansen <mj@php.net>
 */
class patches {

    var $dbh;

    var $reject_reasons = array("Bogus", "Applied", "Outdated",
                                "Already fixed", "Won't fix");

    var $required = array("email" => "Please provide a email address", 
                          "title" => "Please provide a title for your patch", 
                          "description" => "Please provide a description of your patch", 
                          "package" => "No package has been specified",
                          "release" => "No release has been specified");

    /**
     * Construtor
     *
     * @param  object PEAR::DB instance
     * @return void
     */
    function patches(&$dbh) {
        $this->dbh = &$dbh;
    }

    /**
     * Check if all required fields contain something
     *
     * @access public
     * @param  array Associative array containing the user input
     * @param  array Array that will contain the error messages
     * @return boolean True or false
     */
    function required($data, &$errors) {
        foreach ($this->required as $field => $error) {
            if (!empty($data[$field])) {
                continue;
            }
            $errors[] = $error;
        }

        if (count($errors) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Add a new patch
     *
     * @access public
     * @param  array Information about the patch
     * @return mixed PEAR_Error instance or true
     */
    function add($filename, $package, $release, $email, $title, $description) {
        $id = $this->dbh->nextId("patches");
        $query = "INSERT INTO patches VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $result = $this->dbh->query($query, array($id, $package, $release,
                                                  $email, $filename, 
                                                  $title, $description
                                                  )
                                    );
        if (PEAR::isError($result)) {
            @unlink(PEAR_PATCHES . $filename);
            return PEAR::raiseError("Unable to save patch.");
        }

        return true;
    }

    /**
     * Create a unified diff against CVS
     *
     * @access public
     * @param  string Name of the file in CVS ("/pear/DB/DB.php")
     * @param  string Name of the new file
     * @return string Returns the unified diff
     */
    function diff($cvs_file, $patch_file) {
        $id = uniqid(time());

        $path = PEAR_CVS . "/" . dirname($cvs_file);
        $file = basename($cvs_file);

        @copy($patch_file, $path . "/" . $patch_file . ".new");
        exec("cd $path; cvs upd -dAP $file; diff -u $file $patch_file.new > /tmp/$id.diff; rm $patch_file.new");
        $diff = file_get_contents("/tmp/$id.diff");
        @unlink("/tmp/$id.diff");

        return $diff;
    }

    /**
     * Get patches for specific author
     *
     * @access public
     * @param  string Handle of the author
     * @return array  Array containing the patches
     */
    function getByAuthor($handle) {
        $query = "SELECT p.* FROM patches p, packages pa, maintains m" .
            " WHERE p.fk_package = pa.id AND pa.id = m.package" .
            " AND m.handle = '" . $handle . "'";

        return $this->dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    /**
     * Get patches for specific package
     *
     * @access public
     * @param  string Name of the package
     * @param  string Optional version of a release
     * @return array
     */
    function getByPackage($package, $release = null) {
        if ($release === null) {
            $query = "SELECT p.* FROM patches p, packages pa " .
                " WHERE p.fk_package = p.id AND " .
                " p.name '" . $package . "' ORDER BY added";
        } else {
            $query = "SELECT p.* FROM patches p, packages pa, releases r " .
                " WHERE p.fk_package = p.id AND p.id = r.package AND " .
                " p.name = '" . $package . "' AND r.version = '" . $release . "'" .
                " ORDER BY added";
        }

        return $this->dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    /**
     * Delete patch
     *
     * @access public
     * @param  int Patch id
     * @return mixed False oder DB_OK
     */
    function delete($id) {
        $query = "SELECT filename FROM patches WHERE id = '" . $id . "'";
        $filename = $this->dbh->getOne($query);
        if (@unlink(PEAR_PATCHES . $filename)) {
            $query = "DELETE FROM patches WHERE id = '" . $id . "'";
            return $this->dbh->query($query);
        }

        return false;
    }
}
?>