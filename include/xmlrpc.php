<?php // -*- C++ -*-

class XML_RPC {
    function serialize($val, $indent = null, $type = null) {
        if ($type === null) {
            $type = gettype($val);
            if ($type == "string") {
                if (ereg('^[0-9][0-9][0-9][0-9][0-1][0-9][0-3][0-9]T[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$', $val)) {
                    $type = "iso8601";
                } elseif (ereg('[\0-\31\128-\159]', $val)) {
                    $type = "base64";
                }
            }
        }
        switch ($type) {
            case "NULL":
            case "boolean":
                return XML_RPC::serializeBoolean((bool)$val, $indent);
            case "integer":
                return XML_RPC::serializeInteger($val, $indent);
            case "iso8601":
                return XML_RPC::serializeISO8601($val, $indent);
            case "string":
                return XML_RPC::serializeString($val, $indent);
            case "resource":
                return XML_RPC::serializeInteger((string)$val, $indent);
            case "double":
                return XML_RPC::serializeDouble($val, $indent);
            case "array":
                if (XML_RPC::isNumericArray($val)) {
                    return XML_RPC::serializeArray($val, $indent);
                }
                // fall through
            case "object":
                return XML_RPC::serializeAssoc((array)$val, $indent);
            case "base64":
            case "binary":
                return XML_RPC::serializeBinary($val, $indent);
        }
        return null;
    }

    // types: i4/int boolean string double dateTime.iso8601 base64

    function serializeInteger($val, $indent = null) {
        if ($indent !== null) {
            $pre = str_repeat(" ", $indent);
            $post = "\n";
        } else {
            $pre = $post = "";
        }
        return "$pre<value><i4>" . (int)$val .
            "</i4></value>$post";
    }

    function serializeBoolean($val, $indent = null) {
        if ($indent !== null) {
            $pre = str_repeat(" ", $indent);
            $post = "\n";
        } else {
            $pre = $post = "";
        }
        return "$pre<value><boolean>" . (bool)$val .
            "</boolean></value>$post";
    }

    function serializeString($val, $indent = null) {
        if ($indent !== null) {
            $pre = str_repeat(" ", $indent);
            $post = "\n";
        } else {
            $pre = $post = "";
        }
        return "$pre<value><string>" . htmlspecialchars($val) .
            "</string></value>$post";
    }

    function serializeDouble($val, $indent = null) {
        if ($indent !== null) {
            $pre = str_repeat(" ", $indent);
            $post = "\n";
        } else {
            $pre = $post = "";
        }
        return "$pre<value><double>" . (double)$val .
            "</double></value>$post";
    }

    function serializeISO8601($val, $indent = null) {
        if ($indent !== null) {
            $pre = str_repeat(" ", $indent);
            $post = "\n";
        } else {
            $pre = $post = "";
        }
        return "$pre<value><dateTime.iso8601>$val</dateTime.iso8601></value>$post";
        
    }
    
    function serializeBinary($val, $indent = null) {
        if ($indent !== null) {
            $pre = str_repeat(" ", $indent);
            $post = "\n";
        } else {
            $pre = $post = "";
        }
        return "$pre<value><base64>" . base64_encode($val) .
            "</base64></value>$post";
    }

    function serializeArray($val, $indent = null) {
        if (!XML_RPC::isNumericArray($val)) {
            return null;
        }
        if ($indent === null) {
            $i1 = $i2 = 0;
            $pre = $post = "";
        } else {
            $i1 = $indent + 1;
            $i2 = $indent + 2;
            $pre = str_repeat(" ", $indent);
            $pre1 = "$pre ";
            $pre2 = "$pre  ";
            $post = "\n";
        }
        $ret = "$pre<array>$post$pre1<data>$post";
        $lastindex = -1;
        while (list($k, $v) = each($val)) {
            if (!is_int($k)) {
                $t = (int)$k;
                settype($t, gettype($k));
                if ($t != $k) {
                    // this is an assoc, not an array
                    return XML_RPC::serializeAssoc($val, $indent);
                }
            }
            // if the array is numeric but has "holes", fill in the
            // holes with boolean false
            for ($t = $lastindex + 1; $t < $k; $t++) {
                $ret .= XML_RPC::serializeBoolean(0, $i2);
            }
            $ret .= XML_RPC::serialize($v, $i2);
        }
        $ret .= "$pre1</data>$post$pre</array>$post";
        return $ret;
    }

    function serializeAssoc($val, $indent = null) {
        if (!is_array($val)) {
            return null;
        }
        if ($indent === null) {
            $i1 = $i2 = null;
            $pre = $post = "";
        } else {
            $i1 = $indent + 1;
            $i2 = $indent + 2;
            $pre = str_repeat(" ", $indent);
            $pre1 = "$pre ";
            $pre2 = "$pre  ";
            $post = "\n";
        }
        $ret = "$pre<struct>$post";
        while (list($k, $v) = each($val)) {
            $ret .= "$pre1<member>$post$pre2<name>$k</name>$post";
            $ret .= XML_RPC::serialize($v, $i2);
            $ret .= "$pre1</member>$post";
        }
        $ret .= "$pre</struct>$post";
        return $ret;
    }

    function isNumericArray($val) {
        // test whether the value is an array or
        // a random index is an integer
        if (!is_array($val) || !is_int(key($val))) {
            return false;
        }
        return true;
    }
}

?>
