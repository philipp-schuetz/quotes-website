<?php
require_once "../includes/dbh.inc.php";
require_once "../includes/functions.inc.php";
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET["auth"])) {
    $authtoken = $_GET["auth"];
    $userid = check_auth_token($conn, $authtoken);
    if ($userid !== false) {
        $permission = get_permissions($conn, $userid);
        if (isset($_GET["op"])) {
            if ($_GET["op"] == "c" && $permission >= 2) {
                if (isset($_GET["content"])) {
                    if (strlen($_GET["content"]) <= 1024) {
                        $content = $_GET["content"];
                        if (isset($_GET["used_content"])) {
                            if ($_GET["used_content"] == $content) {
                                http_response_code(100);
                                echo '{"status_code":100,"msg":"A quote with this content was alredy created recently."}';
                            }
                        } else {
                            if (create_quote($conn, $userid, $content) === true) {
                                $_GET["used_content"] = $content;
                                http_response_code(201);
                                echo '{"status_code":201,"msg":"Quote was createt successfully."}';
                                if (isset($_GET["ref"])) {
                                    header("Location: ".$_GET["ref"]);
                                } else {
                                    header("Location: https://quotes.philippschuetz.de/");
                                }
                            } else {
                                http_response_code(500);
                                echo '{"status_code":500,"msg":"An error occurred while creating the quote."}';
                            }
                        }
                    } else {
                        http_response_code(400);
                        echo '{"status_code":400,"msg":"Quotes have a maximum length of 1024 characters."}';
                    }
                } else {
                    http_response_code(400);
                    echo '{"status_code":400,"msg":"Your request is missing a content string."}';
                }
            } elseif ($_GET["op"] == "r" && $permission >= 1) {
                if (isset($_GET["quoteid"])) {
                    if (intval($_GET["quoteid"]) != 0) {
                        if (isset($_GET["count"])) {
                            if ($_GET["count"] > 0) {
                                if (intval($_GET["count"]) != 0) {
                                    if (isset($_GET["search"])) {
                                        $search = $_GET["search"];
                                    } else {
                                        $search = NULL;
                                    }
                                    $quoteid = $_GET["quoteid"];
                                    $count = $_GET["count"];

                                    $result = get_quote($conn, $quoteid, $count, $search);
                                    if ($result !== false) {
                                        $result_arr = array(
                                            "statuscode"=>200,
                                            "data"=>array()
                                        );
                                        $i = 0;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $result_arr["data"][$i] = array("quoteid"=>$row["quoteid"], "username"=>$row["userid"], "unix_timestamp"=>$row["unix_timestamp"], "content"=>$row["content"]);
                                            $i = $i + 1;
                                        }
                                        echo json_encode($result_arr);
                                    } else {
                                        http_response_code(500);
                                        echo '{"status_code":500,"msg":"An error occurred while querying for quotes."}';
                                    }
                                } else {
                                    http_response_code(400);
                                    echo '{"status_code":400,"msg":"Number of quotes must be an integer."}';
                                }
                            } else {
                                http_response_code(400);
                                echo '{"status_code":400,"msg":"The minimum number of quotes is 1."}';
                            }
                        } else {
                            http_response_code(400);
                            echo '{"status_code":400,"msg":"Please specify the number of quotes you want to read."}';
                        }
                    } else {
                        http_response_code(400);
                        echo '{"status_code":400,"msg":"Quote id must be an integer."}';
                    }
                } else {
                    http_response_code(400);
                    echo '{"status_code":400,"msg":"Your request is missing a quote id."}';
                }
            } elseif ($_GET["op"] == "u" && $permission >= 2) {
                if (isset($_GET["quoteid"])) {
                    if (intval($_GET["quoteid"]) != 0) {
                        if (isset($_GET["content"])) {
                            if (strlen($_GET["content"]) <= 1024) {
                                $quoteid = $_GET["quoteid"];
                                $content = $_GET["content"];
                                if (update_quote($conn, $userid, $quoteid, $content) === true) {
                                    http_response_code(200);
                                    echo '{"status_code":200,"msg":"Quote was updated successfully."}';
                                    if (isset($_GET["ref"])) {
                                        header("Location: ".$_GET["ref"]);
                                    } else {
                                        header("Location: https://quotes.philippschuetz.de/");
                                    }
                                } else {
                                    http_response_code(500);
                                    echo '{"status_code":500,"msg":"An error occurred while updating the quote."}';
                                }
                            } else {
                                http_response_code(400);
                                echo '{"status_code":400,"msg":"Quotes have a maximum length of 1024 characters."}';
                            }
                        } else {
                            http_response_code(400);
                            echo '{"status_code":400,"msg":"Your request is missing a content string."}';
                        }
                    } else {
                        http_response_code(400);
                        echo '{"status_code":400,"msg":"Quote id must be an integer."}';
                    }
                } else {
                    http_response_code(400);
                    echo '{"status_code":400,"msg":"Your request is missing a quote id."}';
                }
            } elseif ($_GET["op"] == "d" && $permission >= 3) {
                if (isset($_GET["quoteid"])) {
                    if (intval($_GET["quoteid"]) != 0) {
                        $quoteid = $_GET["quoteid"];
                        if (delete_quote($conn, $userid, $quoteid) === true) {
                            http_response_code(200);
                            echo '{"status_code":200,"msg":"Quote deleted successfully."}';
                        } else {
                            http_response_code(500);
                            echo '{"status_code":500,"msg":"An error occurred while deleting the quote."}';
                        }
                    } else {
                        http_response_code(400);
                        echo '{"status_code":400,"msg":"Quote id must be an integer."}';
                    }
                } else {
                    http_response_code(400);
                    echo '{"status_code":400,"msg":"Your request is missing a quote id."}';
                }
            } elseif ($_GET["op"] == "mqid" && $permission >= 1) {
                $max_quoteid = get_max_quoteid($conn);
                if ($max_quoteid != false) {
                    http_response_code(200);
                    echo '{"status_code":200,"data":'.$max_quoteid.'}';
                } else {
                    http_response_code(500);
                    echo '{"status_code":500,"msg":"An error occurred."}';
                }
            } else {
                http_response_code(400);
                echo '{"status_code":400,"msg":"' . $_GET["op"] . ' is not a valid operation indicator or your permisson level is too low for this operation."}';
            }
        } else {
            http_response_code(400);
            echo '{"status_code":400,"msg":"Your request is missing an operation indicator."}';
        }
    } else {
        http_response_code(400);
        echo '{"status_code":400,"msg":"The provided authentication token is invalid."}';
    }
} else {
    http_response_code(400);
    echo '{"status_code":400,"msg":"Your request is missing an authentication token."}';
}