<?php
$db = parse_ini_file("tangle.ini");
$server = $db['host'];
$user = $db['user'];
$db_name = $db['name'];
$pwd = $db['pass'];
$connect = mysqli_connect("$host", "$user", "$pwd", "$db_name");
function up($connect) {
  $id = $_POST["id"];
  $sql = "UPDATE text_content SET count = count+1 WHERE id = '$id';";
  $result = mysqli_query($connect, $sql);
}

function login($connect){
    $user = mysqli_real_escape_string($connect, $_POST['username']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);
    $query = "SELECT id FROM users WHERE uname = '$user' AND password = '$password';";
    //echo $query;
    $q_result = mysqli_query($db_server, $query);
    if ($q_result->connect_errno) {
        echo "incorrect";
    } else {
        $array = mysqli_fetch_array($q_result, MYSQLI_ASSOC);
        $id = $array['id'];
        $_SESSION['id'] = $id;
        echo "hey there $user! </br> welcome to this site";
    }
}
function checksession($connect){
    if(!empty($_SESSION['id'])){
      $id = $_SESSION['id'];
      $query = "SELECT uname FROM users WHERE id = '$id';"; // AND password = '$password';";
      //echo $query;
      $q_result = mysqli_query($db_server, $query);
      if ($q_result->connect_errno) {
          echo "incorrect";
      } else {
          $array = mysqli_fetch_array($q_result, MYSQLI_ASSOC);
          $user = $array['uname'];

          echo "hey there $user! </br> welcome to this site";
      }
    } else {
      echo "error";
    }
}
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
function get_top_group($connect){
  $user_id = $_GET['user_id'];
  $query3 = "SELECT group_id FROM user_group_ref WHERE user_id= '$user_id' AND active = 'y' LIMIT 1;";
  $result3 = mysqli_query($connect, $query3);
  $row2 = mysqli_fetch_array($result3);
  $group_id = $row2['group_id'];
  echo $group_id;
}
function joingroup($connect){
  $userid = $_POST['userid'];
  $groupcode = mysqli_real_escape_string($connect, $_POST['groupcode']);
  $check_query_active = "SELECT group_id FROM groups WHERE group_code = '$groupcode';";
  $q_result = mysqli_query($connect, $check_query_active);

  $result = mysqli_fetch_array($q_result, MYSQLI_ASSOC);
  if(empty($result['group_id'])) {
      return "empty";
  } else {
    $group_id = $result['group_id'];
    $query_2 = "INSERT INTO user_group_ref (group_id, user_id, admin, active) VALUES ($group_id, $userid, 'y', 'y');";
    $result3 = mysqli_query($connect, $query_2);
  }
}
function gen_group_code($connect){
    $code = "";
    for($x = 0; $x < 6; $x++) {
      $try_again = chr(64+rand(0,26));
      //echo $try_again;
      $code .= $try_again;
    }
    $check_if_code_is_used_query = "SELECT COUNT(*) FROM groups WHERE group_code = '$code';";

    $get_code_result = mysqli_query($db_server, $check_if_code_is_used_query);
    if ($get_code_result->connect_errno) {
            echo "Failed to connect to database: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }  else {
        $row = mysqli_fetch_array($get_code_result, MYSQLI_ASSOC);
        mysqli_free_result($get_code_result);
        $code_count = $row['COUNT(*)'];
        while ($code_count === '1') {
            //return code, to be posted to db
            for($x = 0; $x < 6; $x++) {
              $try_again = chr(64+rand(0,26));
              $code .= $try_again;
            }
            $check_if_code_is_used_query = "SELECT COUNT(*) FROM groups WHERE group_code = '$code';";

            $get_code_result = mysqli_query($db_server, $check_if_code_is_used_query);
            if ($get_code_result->connect_errno) {
                echo "Failed to connect to database: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            } else {
              $row = mysqli_fetch_array($get_code_result, MYSQLI_ASSOC);
              mysqli_free_result($get_code_result);
              $code_count = $row['COUNT(*)'];
            }
        }
        return $code;
    }
}
function newgroup($connect){
  $userid = $_POST['userid'];
  $group_name = $_POST['group_name'];
  $group_code = gen_group_code($connect);
  $query = "INSERT INTO groups (group_name, group_code) VALUES ('$group_name', '$group_code');";
  $result3 = mysqli_query($connect, $query);
  $group_id = mysqli_insert_id($connect);
  $query_2 = "INSERT INTO user_group_ref (group_id, user_id, admin, active) VALUES ($group_id, $userid, 'y', 'y');";
  $result3 = mysqli_query($connect, $query_2);
  echo $group_id;
}
function get_top_group_name($connect){
  $group_id = $_GET['group_id'];
  $query3 = "SELECT group_name FROM groups WHERE group_id = '$group_id';";
  $result3 = mysqli_query($connect, $query3);
  $row2 = mysqli_fetch_array($result3);
  $group_id = $row2['group_name'];
  echo $group_id;
}
function get_groups($connect){
  $user_id = $_GET['user_id'];
  $query3 = "SELECT group_id FROM user_group_ref WHERE user_id= '$user_id' AND active = 'y';";
  $result3 = mysqli_query($connect, $query3);
  $output .= '<div id="userpanel">';
  while($row2 = mysqli_fetch_array($result3)) {
    $group_id = $row2['group_id'];
    $userget = "SELECT group_name FROM groups WHERE group_id = '$group_id';";
    $userresult = mysqli_fetch_array(mysqli_query($connect, $userget));
    $groupname = $userresult['group_name'];
    $output .= "<a href='javascript:void(0);' id='groupname' onclick='groupswitch(\"$group_id\")'>$groupname</a></br>";
  }
  $output .="<div>
    <a href='javascript:void(0);' id='joingroup' onclick='joingroup()'>join a new group!</a></br>
    <a href='javascript:void(0);' id='creategroup' onclick='newgroup()'>create one</a>
    </div>
    </div>
  ";
  echo $output;
}
function select($connect) {
  $user_id = $_GET['user_id'];
  $group_id = $_GET['group_id'];
  $output .= '
        <div class="table-responsive" id="tabbble">
             <table class="table table-bordered" id="stufftable">
                  ';
    $output .= '<tr><td class="text">
      <div style="float: left; width: 90%;">
      <input type="text" id="text_add" contenteditable="true" placeholder="text here!"/>
      <img id="blah" src="#" alt="" />
      <div style="float: right; width: 10%;">
      <button type="button" name="btn_add" id="btn_add" class="btn btn-primary">
        post!
      </button>


         <input type="file" onchange="readURL(this);" id="imgs" name="img" />
      <button type="button" name="btn_add" id="btn_img" onclick="chooseFile();" class="btn btn-primary">
        <i class="fa fa-upload"></i>

      </button>

      </div>
      </div>
      </td>
      </tr>';
    $idnum = $group_id;

    $query = "SELECT posts.* FROM posts WHERE group_id = '$group_id' ORDER BY post_datetime DESC;";
    $result = mysqli_query($connect, $query);

    $rowcount = mysqli_num_rows($result);
    $x = 1;
    $idnum = 0;
    while($row = mysqli_fetch_array($result)){

      $userget = "SELECT username FROM users WHERE user_id ='".$row['user_id']."';";
      $userresult = mysqli_fetch_array(mysqli_query($connect, $userget));
      $username = $userresult['username'];

      $content = $row['post_text_content'];
      $output .= '
        <tr>
        <td class="text" data-id1="'.$row["id"].'">
              <span style="color: #efefef; font-size:75%; float:left;"<b>'.$username.':</b></span> </br>
        <span style="float: left">'.$content.'</span>
        </br>
         <span style="color: grey"><i>'.$time_since.' </span> </i>
        </td>

         </tr>
    ';
      $x ++;




   }

   $output .= '</table>
        </div>';
   echo $output;
}
function delete($connect) {
  $sql = "DELETE FROM text_content WHERE id = '".$_POST["id"]."'";
  if(mysqli_query($connect, $sql)){
      echo 'Data Deleted';
  }
}
function edit($connect) {
  $id = $_POST["id"];
   $text = mysqli_real_escape_string($connect, $_POST["text"]);
   $sql = "UPDATE text_content SET 'text' = '$text' WHERE id='".$id."'";
   if(mysqli_query($connect, $sql))
   {
        echo 'Data Updated';
   }
}
function get_trending($connect){
    $query = "SELECT COUNT(id), hashtag FROM tag_ref WHERE time > DATE_SUB(CURDATE(), INTERVAL 1 WEEK) GROUP BY hashtag ORDER BY COUNT(id) DESC LIMIT 10;";
    $result = mysqli_query($connect, $query);
    $trending = "";
    if(mysqli_num_rows($result) > 0){
      $rowcount = mysqli_num_rows($result);
      while($row = mysqli_fetch_array($result)){
        $tag = $row["hashtag"];
        $trending = $trending . "<a href='javascript:void(0);' id='linktag' onclick='linktag(\"$tag\")'> #$tag </a> </br>";
      }
    }
    echo $trending;

}
function image_upload($connect){
  $user_id = $_GET['user_id'];
  $group_id = $_GET['group_id'];
  $sourcePath = $_FILES['file']['tmp_name'];       // Storing source path of the file in a variable
  $targetPath = "uploads/".$_FILES['file']['name']; // Target path where file is to be stored
  move_uploaded_file($sourcePath,$targetPath) ;
  echo $_FILES['file']['name'];
}
function l0gin($connect){
  $username = mysqli_real_escape_string($connect, $_POST['username']);
  $password = mysqli_real_escape_string($connect, $_POST['password']);
  $query = "SELECT user_id FROM users WHERE username = '$username' and password = '$password';";
  $result = mysqli_fetch_array(mysqli_query($connect, $query));
  $results = $result['user_id'];
  echo $results;
}
function reg($connect){
  $username = mysqli_real_escape_string($connect, $_POST['username']);
  $password = mysqli_real_escape_string($connect, $_POST['password']);
  $query = "INSERT INTO users (username, password) VALUES ('$username', '$password');";
  $result = mysqli_query($connect, $query);
  $id = mysqli_insert_id($connect);
  echo $id;
}
function group_create($connect){
  $user_id = $_GET['user_id'];
  $group_name = $_POST['group_name'];
  $query1 = "INSERT INTO groups (group_name) VALUES ('$group_name');";
  $result = mysqli_query($connect, $query1);
  $group_id = mysqli_insert_id($connect);
  $query2 = "INSERT INTO user_group_ref (group_id, user_id, admin, active) VALUES ('$group_id', '$user_id', 'y', 'y');";
  $result = mysqli_query($connect, $query2);
}
function insert($connect){
  $val = mysqli_real_escape_string($connect, $_POST['text']);
   /* Match hashtags */
  $userid = mysqli_real_escape_string($connect, $_POST['userid']);
  $group_id = mysqli_real_escape_string($connect, $_POST['groupid']);
   $sql = "INSERT INTO posts(group_id, user_id, post_datetime, post_type, post_text_content) VALUES('$group_id', '$userid', NOW(), 'text', '$val');";
   if(mysqli_query($connect, $sql))
   {
        echo 'Data Inserted';
   }
   echo $sql;
 }




$method = $_GET['method'];
if (!empty($method)){
  $method($connect);
} else {
  echo "hi there";
}
?>
