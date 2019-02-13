<?php

require_once 'db_config.php';

try{
    $con = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8",'root','');
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo 'BM_ERROR_FUNC_FILE ::'.$e->getMessage();
}

function encrypt_pass($pass){
    $salt = 'X`Gg.):#@BM_';
    $res = md5(sha1($salt.$pass).md5($pass));
    return $res;
}

function get_cookie_info_by_user($user){
    $data = get_user_info($user);
    $id = $data['id'];
    $res = get_cookie_info($id);
    return $res;
}
function check_cookie($hash,$data=false){
    global $con;
    $stmt = $con->prepare("SELECT * FROM cookie_id WHERE cookie_hash = ?");
    $stmt->execute(array($hash));
    $count = $stmt->rowCount();
    $res = $stmt->fetchAll();
    if($data==true){
        return $res[0];
    }
    if($count > 0 ){    
        return true;
    }else{
        return false;
    }
}
function get_cookie_info($id){
    global $con;
    $stmt = $con->prepare("SELECT * FROM cookie_id WHERE id = ? ");
    $stmt->execute(array($id));
    $count = $stmt->rowCount();
    $res = $stmt->fetchAll();
    if($count > 0){
        return $res[0];
    }else{
        return false;
    }
}


function set_session($user, $path, $display, $ip,$id,$email){
    $_SESSION['logged_in'] = 'yes';
    $_SESSION['username'] = $user;
    $_SESSION['user_id'] = $id;
    $_SESSION['profilepic'] = $path;
    $_SESSION['displayname'] = $display;
    $_SESSION['ip'] = $ip;
    $_SESSION['email'] = $email;
}

function get_user_info($username,$id=null){
    global $con;
    if(isset($username)){
        $stmt = $con->prepare("SELECT * FROM users WHERE username=?");
        $stmt->execute(array($username));
    }else{
        $stmt = $con->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute(array($id));
    }
    $count = $stmt->rowCount();
    $res = $stmt->fetchAll(2);
    if($count > 0){
        return $res[0];
    }else{
        return false;
    }
}
function doLogin($user, $pass){
    global $con;
    $stmt = $con->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->execute(array(strtolower($user), encrypt_pass($pass)));
    $count = $stmt->rowCount();
    if($count > 0 ){
        return true;
    }else{
        return false;
    }
}


function get_real_ip(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
function get_user_by_username($username){
    global $con;
    $stmt = $con->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute(array($username));
    $count = $stmt->rowCount();
    if($count == 0 ){
        return true;
    }else{
        return false;
    }
}

function get_user_by_email($email){
    global $con ;
    $stmt = $con->prepare("SELECT * FROM users WHERE email = ? ;");
    $stmt->execute(array(strtolower($email)));
    $count = $stmt->rowCount();
    if($count == 0){
        return true;
    }else{
        return false;
    }
}

function create_cookie_hash($txt){
    $salt = '&ASFUH&!*@$^&';
    $res = sha1(md5($salt.$txt).sha1($txt));
    return $res;
}

function register_user($user, $pass, $email, $displayname){
    global $con;
    if(get_user_by_username($user)){
        if(get_user_by_email($email)){
            $path = "files/images/user.png";
            $stmt = $con->prepare("INSERT INTO users VALUES(null,?,?,?,?,?) ");
            $stmt->execute(array(strtolower($user), encrypt_pass($pass), $displayname, strtolower($email) ,$path));
            $count = $stmt->rowCount();    
            if($count > 0 ){
                $res = get_user_info($user);
                $id = $res['id'];
                $stmt2 = $con->prepare("INSERT INTO cookie_id VALUES(?,?)");
                $stmt2->execute(array($id, create_cookie_hash($user)));
                $count2 = $stmt2->rowCount();
                if($count2 > 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return 'ERR_DUP_EMAIL';
        }
    }else{
        return 'ERR_DUP_USERNAME';
    }
}


function add_profile_path_to_db($username, $path){
    global $con;
    $stmt = $con->prepare("UPDATE users SET profile_pic = ? WHERE username = ?");
    $stmt->execute(array($path, $username));
    $count = $stmt->rowCount();
    if($count == 0){
        return true;
    }else{
        return false;
    }
    
}
function redirect_to($add){
    header("Location:{$add}");
}

function logout(){
    unset($_SESSION['logged_in'],$_SESSION['username']);
    setcookie('logged_in','',1);
    setcookie('hash','',1);
    header('Location:login.php');
    return true;
}

function set_id_to_login_details($id){
    global $con;
    $stmt = $con->prepare("INSERT INTO login_details VALUES(null,?,null,'')");
    $stmt->execute(array($id));
    $count = $stmt->rowCount();
    if($count > 0 ){
        return true;
    }else{
        return false;
    }
}
function get_login_details($user_id){
    global $con;
    $stmt = $con->prepare("SELECT * FROM login_details WHERE user_id = ? ORDER BY login_details_id DESC LIMIT 1");
    $stmt->execute(array($user_id));
    $count = $stmt->rowCount();    
    if($count > 0){
        $res = $stmt->fetchAll();
        return $res[0];
    }else{
        return false;
    }
}   


function fetch_user_last_activity($user_id){
    global $con;
    $stmt = $con->prepare("SELECT * FROM login_details WHERE user_id = ? ORDER BY last_activity DESC LIMIT 1");
    $stmt->execute(array($user_id));
    $res = $stmt->fetchAll();
    $data = $res[0];
    return $data['last_activity'];

}



function fetch_chat_history($from_user_id, $to_user_id){
    global $con;
    $stmt = $con->prepare("SELECT * FROM chat_message WHERE 
    (from_user_id = ? AND to_user_id = ?)
    OR (from_user_id = ? AND to_user_id = ?) 
    ORDER BY timestamp ASC");
    $stmt->execute(array($from_user_id , $to_user_id , $to_user_id , $from_user_id));
    $res = $stmt->fetchAll();
    $output = '<ul class="list-unstyled">';

    foreach($res as $row){
        $time = $row['timestamp'];
        $time = explode(' ', $time);

        // DATE
        $date = $time[0];
        $date = explode('-',$date);


        // TIME
        $time = $time[1];
        $time = explode(':',$time);
        $hour = $time[0];
        $min = $time[1];


        if($row['from_user_id'] == $from_user_id){
            $tick1 = '';
            $tick2 = '';
            if($row['is_sent'] == '1'){
                $tick1 = 'fa fa-check';
            }
            if($row['is_seen'] == '1'){
                $tick2 = 'fa fa-check';
            }

            $output .= '
            <li class="message-you">
                <p>'.$row['chat_message'].'
                    <div class="message-time">
                        <small><em>'.$hour.':'.$min.'</em><i class="'.$tick1.'"></i><i class="'.$tick2.'" style="margin-right: -5px;"></i></small>
                    </div>
                </p>
            </li>';
        }else{
        // $user = get_user_info(null, $row['from_user_id']);
        // $user_name = '<b class="user-username">'.$user['username'].'</b>';

        $output .= '
        <li class="message-other">
            <p>'. $row['chat_message'].'
                <div class="message-time">
                    <small><em>'.$hour.':'.$min.'</em></small>
                </div>
            </p>
        </li>';
        }

    }
    $output .= '</ul>';
    return $output;
}

function fetch_unseen_chat($from_user_id , $to_user_id){
    global $con;
    $stmt = $con->prepare("SELECT * FROM chat_message WHERE from_user_id = ? AND to_user_id = ? AND status = ?");
    $stmt->execute(array($from_user_id , $to_user_id , '0'));
    $count = $stmt->rowCount();
    $output = '';
    if($count > 0){
        $output .= '<small class="unseen-chat" style="    position: absolute;
        background-color: red;
        padding: 3px 6px 2px 4px;
        border-radius: 10px;
        bottom: -8px;
        left: -8px;">'.$count.'</small>';
        return $output;
    }
    
}