<?php
ini_set('display_errors',1);

session_start();

require_once 'functions.php';
if(isset($_COOKIE['logged_in'],$_COOKIE['hash']) && $_COOKIE['logged_in'] == 'yes' && check_cookie($_COOKIE['hash'])){
        redirect_to('index.php');
}
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 'yes'){
        redirect_to('index.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Login Page</title>
        <link rel="icon" href="files/images/tab-icon.png">
        <link rel="stylesheet" href="files/css/font-awesome.min.css">
        <link rel="stylesheet" href="files/css/bootstrap.min.css">
        <script src="files/js/jquery-3.1.1.js"></script>
        <link rel="stylesheet" href="files/css/style.css">
</head>
<body>
        <div class="container">
                <div class="row">
                        <div class="wrapper log-wrapper col-xs-push-1 col-xs-10 col-sm-push-3 col-sm-6 col-md-push-4 col-md-4
                        col-lg-3">
                        <form action="" method="post" id="login-form">
                                <span id="login-img"><i class="fa fa-user-circle"></i><h1>ورود</h1></span>
                                <div class="clear"></div>
                                <div class="input-div" id="user-div">
                                        <span id="icon-holder-user">
                                                <i class="fa fa-user"></i>
                                        </span>
                                        <input type="text" name="logusername" placeholder="نام کاربری " id="username-input">
                                </div><div class="clear"></div>
                                <div class="input-div" id="pass-div">
                                        <span id="icon-holder-pass">
                                                <i class="fa fa-lock"></i>
                                        </span>
                                        <input type="password" name="logpassword" placeholder="رمز عبور " id="password-input">
                                </div><div class="clear"></div>
                                <div class="remember">
                                        <input type="checkbox" name="rememberme" id="remember-me" value="rememberme">
                                        <label for="remember-me">مرا به خاطر بسپار</label>
                                </div><div class="clear"></div>
                                <button type="submit" id="sub-btn">ورود</button>
                                <div class="loading">
                                        <div class="obj"></div>
                                        <div class="obj"></div>
                                        <div class="obj"></div>
                                        <div class="obj"></div>
                                        <div class="obj"></div>
                                        <div class="obj"></div>
                                        <div class="obj"></div>
                                        <div class="obj"></div>
                                </div>
                        </form><div class="clear"></div>
                        <div class="err-log">

                                <i class="fa fa-exclamation-circle"></i>نام کاربری یا رمز عبور اشتباه است.</div>

                                <div class="line login-line">
                                        <div class="inner-line"></div>
                                </div>
                                <a href="register.php" id="register">ثبت نام</a>
                        </div>
                </div>
        </div>
        <script src="files/js/bootstrap.min.js"></script>
        <script type="text/javascript">
        $(document).ready(()=>{
                $('#login-form input').focus((e)=>{
                        let id = $(e.target).attr('id');
                        if(id == 'username-input'){
                                $('#icon-holder-user').css('top','-25px');
                                $('#user-div').addClass('opacity-fill');
                        }else if(id == 'password-input'){
                                $('#icon-holder-pass').css('top','-25px');
                                $('#pass-div').addClass('opacity-fill');
                        }
                });
                $('#login-form input').focusout((e)=>{
                        let id = $(e.target).attr('id');
                        if(id == 'username-input'){
                                $('#icon-holder-user').css('top','0');
                                $('#user-div').removeClass('opacity-fill');
                        }else if(id == 'password-input'){
                                $('#icon-holder-pass').css('top','0');
                                $('#pass-div').removeClass('opacity-fill');
                        }
                });
                // codes for ajax request
                $('#login-form').submit((e)=>{
                        e.preventDefault();
                        let username = $('#username-input').val();
                        let password = $('#password-input').val();
                        let remember = $('#remember-me').prop('checked');
                        $('.err-log').css('visibility','hidden');
                        $.ajax({
                                url:'auth.php',
                                type:'POST',
                                data:{
                                        logusername:username,
                                        logpassword:password,
                                        logremember:remember
                                },
                                beforeSend:()=>{
                                        $('.loading').css('visibility','visible');
                                },
                                success:(responce)=>{
                                        $('.loading').css('visibility','hidden');
                                        if(responce == 'OK'){
                                                window.location = 'index.php';
                                        }else if(responce == 'ERR_USER_PASS'){
                                                $('.err-log').css('visibility','visible');
                                        }
                                },
                                error:(err)=>{
                                        alert("Error : ".err);
                                }
                        });
                });
        });
        </script>
</body>
</html>
