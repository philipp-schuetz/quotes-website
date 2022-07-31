<?php
function check_auth_token($conn, $authtoken) {
    $sql = "SELECT * FROM users WHERE token = ?;";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    } else {
        mysqli_stmt_bind_param($stmt, "s", $authtoken);
        mysqli_stmt_execute($stmt);

        $resultData = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($resultData)) {
            return $row["userid"];
        } else {
            return false;
        }
    }
    mysqli_stmt_close($stmt);
}

// 1 = get, 2 = create/edit, 3 = delete
function get_permissions($conn, $userid) {
    $sql = "SELECT permission_level FROM users WHERE userid = ?;";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    } else {
        mysqli_stmt_bind_param($stmt, "i", $userid);
        mysqli_stmt_execute($stmt);

        $resultData = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($resultData)) {
            return $row["permission_level"];
        } else {
            return false;
        }
    }
    mysqli_stmt_close($stmt);
}

function create_quote($conn, $userid, $content) {
    $timestamp = time();

    $sql = "INSERT INTO quotes (userid, unix_timestamp, content) VALUES (?, ?, ?)";

    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    } else {
        mysqli_stmt_bind_param($stmt, "iis", $userid, $timestamp, $content);
        mysqli_stmt_execute($stmt);
    }
    log_action($conn, $userid, 1, NULL, $content);
    return true;
    mysqli_stmt_close($stmt);
}

function get_quote($conn, $quoteid, $count, $search) {
    if ($search != NULL) {
        $param = "%{$search}%";
        $sql = "SELECT quotes.quoteid, quotes.unix_timestamp, users.username userid, quotes.content FROM quotes JOIN users ON quotes.userid = users.userid WHERE content LIKE ? AND quoteid <= ? ORDER BY quoteid DESC";
        
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            return false;
        } else {
            mysqli_stmt_bind_param($stmt, "si", $param, $quoteid);
            mysqli_stmt_execute($stmt);
    
            $result = mysqli_stmt_get_result($stmt);
    
            if (mysqli_num_rows($result) > 0) {
                return $result;
            } else {
                return false;
            }
        }
        mysqli_stmt_close($stmt);
   
    } else {
        $sql = "SELECT quotes.quoteid, quotes.unix_timestamp, users.username userid, quotes.content FROM quotes JOIN users ON quotes.userid = users.userid WHERE quoteid <= ? ORDER BY quoteid DESC LIMIT ?";
        
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            return false;
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $quoteid, $count);
            mysqli_stmt_execute($stmt);
    
            $result = mysqli_stmt_get_result($stmt);
    
            if (mysqli_num_rows($result) > 0) {
                return $result;
            } else {
                return false;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

function update_quote($conn, $userid, $quoteid, $content) {
    $pre_update_content = get_pre_edit_quote($conn, $quoteid);

    $sql = "UPDATE quotes SET content = ? WHERE quoteid = ?";

    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    } else {
        mysqli_stmt_bind_param($stmt, "si", $content, $quoteid);
        mysqli_stmt_execute($stmt);
    }
    log_action($conn, $userid, 3, $pre_update_content, $content);
    return true;
    mysqli_stmt_close($stmt);
}

function delete_quote($conn, $userid, $quoteid) {
    $pre_delete_content = get_pre_edit_quote($conn, $quoteid);

    $sql = "DELETE FROM quotes WHERE quoteid = ?";

    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    } else {
        mysqli_stmt_bind_param($stmt, "i", $quoteid);
        mysqli_stmt_execute($stmt);
    }
    log_action($conn, $userid, 4, $pre_delete_content, NULL);
    return true;
    mysqli_stmt_close($stmt);
}

// actiontypes: 1 = create, 2 = read, 3 = update, 4 = delete
function log_action($conn, $userid, $action_type, $pre_action_content, $content) {
    $timestamp = time();
    $sql = "INSERT INTO logs (userid, action_type, pre_action_content, content, unix_timestamp) VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return;
    } else {
        mysqli_stmt_bind_param($stmt, "iissi", $userid, $action_type, $pre_action_content, $content, $timestamp);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
    return;
}

function get_pre_edit_quote($conn, $quoteid) {
        $sql = "SELECT * FROM quotes WHERE quoteid = ?";
        
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            return false;
        } else {
            mysqli_stmt_bind_param($stmt, "i", $quoteid);
            mysqli_stmt_execute($stmt);
    
            $resultData = mysqli_stmt_get_result($stmt);
    
            if ($row = mysqli_fetch_assoc($resultData)) {
                return $row["content"];
            } else {
                return false;
            }
        }
        mysqli_stmt_close($stmt);
}

function get_max_quoteid($conn) {
    $sql = "SELECT MAX(quoteid) FROM quotes";

    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row["MAX(quoteid)"];
    } else {
        return false;
    }
}

function uml_to_htmluml($string) {
    $uml = array("Ä", "ä", "Ö", "ö", "Ü", "ü", "ß", "Ñ", "ñ");
    $htmluml = array("&Auml;", "&auml;", "&Ouml;", "&ouml;", "&Uuml;", "&uuml;", "&szlig;", "&Ntilde;", "&ntilde;");

    $newstring = str_replace($uml, $htmluml, $string);
    return $newstring;
}