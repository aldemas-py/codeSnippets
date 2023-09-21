<?php
require("core.inc.php");

session_start();
if (isset($_POST['submit'])) {
    $user = mysqli_real_escape_string($mysqli, $_POST['username']);
    $password = mysqli_real_escape_string($mysqli, $_POST['password']);

    header('Content-Type: application/json');
    if ($user === '') {
        print json_encode(array('message' => 'Name cannot be empty', 'code' => 0));
        exit();
    }
    if ($password === '') {
        print json_encode(array('message' => 'Password cannot be empty', 'code' => 0));
        exit();
    }
    $result = mysqli_query($mysqli, "SELECT * FROM users WHERE username = '" . $user . "' and password = '" . $password . "'");
    if (!empty($result)) {
        if ($row = mysqli_fetch_array($result)) {
            $_SESSION['user_id'] = $row['uid'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_mobile'] = $row['mobile'];
            header("Location: dashboard.php");
        }
    } else {
        header("Location: login.php");
        $error_message = "Incorrect Email or Password!!!";
    }
}

if (isset($_SESSION['user_id']) != "") {
    header("Location: dashboard.php");
}
