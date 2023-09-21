<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <link rel="stylesheet" href="style.css">
</head>

<body">
    <div class="body" id="body">
        <div class="login">
            <nav class="navbar" id="myNavbar"> </nav>
            <div class="content">
                <form action="process.php" method="post" id="loginForm" name="loginForm">
                    <div id="myform">
                        <input type="text" placeholder="User name" name="username" id="username" />
                        <br>

                        <input type="password" placeholder="password" name="password" id="password" />
                        <br>

                        <input type="submit" name="submit" id="submit" />
                        <div id="status"></div>
                    </div>
                </form>
                <div class="footer" id="footer"></div>
            </div>
        </div>
    </div>
    </body>

</html>