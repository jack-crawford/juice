<?php
$db = parse_ini_file("juice.ini");
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
function create($connect){
    $user = mysqli_real_escape_string($connect, $_POST['username']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);

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
function select($connect) {
  if (empty($_GET['tag']) || $_GET['tag'] == "all" || $_GET['tag'] == "undefined") {
      $tag = " all";
      $sql = "SELECT * FROM text_content WHERE locked = 'n' ORDER BY id";
  } else {
    $tag = $_GET['tag'];
    $sql = "SELECT * FROM text_content WHERE hashtags LIKE '% $tag,%' ORDER BY id";

  }
  $result = mysqli_query($connect, $sql);
  $output .= '
        <div class="table-responsive" id="tabbble">
             <table class="table table-bordered" id="stufftable">
                  ';
  if(mysqli_num_rows($result) > 0){
    $rowcount = mysqli_num_rows($result);
    $x = 1;
    while($row = mysqli_fetch_array($result)){
      $userget = "SELECT uname FROM users WHERE id ='".$row['userid']."';";
      $userresult = mysqli_fetch_array(mysqli_query($connect, $userget));
      $username = $userresult['uname'];
      if ($row['locked'] == "y") {
        $tags = explode(', ', $row['hashtags']);
        if ($tag == $tags[1]) {
            $count = $row['count'];
            $time_since = $row["timestamp"];
            $content = $row["text"];
            $taglength = strlen($tag);
            if(substr($content, 0, 10) == '<a href="j' && $tag != " all") {
              $content = substr($content, strpos($content, "/a>")+3);
            }

            if ($x == $rowcount) {
              $output .= '
                 <tfoot id="bottomrow">
                      <td class="text" data-id1="'.$row["id"].'">
                      <span style="color: #efefef; font-size:75%; float:left;"<b>'.$username.':</b></span> </br>
                      <span style="float: left">'.$content.'</span>
                      <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                      '.$count.' </span> </br>
                      <span style="color: grey"><i>'.$time_since.' </span> </i>
                     </td>

                 </tfoot>
            ';
            } else {
              $output .= '
                <tr>
                <td class="text" data-id1="'.$row["id"].'">
                      <span style="color: #efefef; font-size:75%; float:left;"<b>'.$username.':</b></span> </br>
                <span style="float: left">'.$content.'</span>
                 <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                 '.$count.' </span> </br>
                 <span style="color: grey"><i>'.$time_since.' </span> </i>
                </td>

                 </tr>
            ';
          }
        }
      } else {
        $count = $row['count'];
            $time_since = $row["timestamp"];
            $content = $row["text"];
            $taglength = strlen($tag);
            if(substr($content, 0, 10) == '<a href="j' && $tag != " all") {
              $content = substr($content, strpos($content, "/a>")+3);
            }
            if ($x == $rowcount) {
              $output .= '
                 <tfoot id="bottomrow">
                      <td class="text" data-id1="'.$row["id"].'">
                      <span style="color: #efefef; font-size:75%; float:left;"<b>'.$username.':</b></span> </br>
                      <span style="float: left">'.$content . $tags[1].'</span>
                      <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                      '.$count.' </span> </br>
                      <span style="color: grey"><i>'.$time_since.' </span> </i>
                     </td>

                 </tfoot>
            ';
            } else {
              $output .= '
                 <tr>
                 <td class="text" data-id1="'.$row["id"].'">
                      <span style="color: #efefef; font-size:75%; float:left;"<b>'.$username.':</b></span> </br>
                 <span style="float: left">'.$content.'</span>
                 <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                 '.$count.' </span> </br>
                 <span style="color: grey"><i>'.$time_since.' </span> </i>
                </td>

                 </tr>
            ';
          }
      }

      $x ++;
    }
    $output .= '

    ';
   }
   else {
        $output .= '<tr>
                            <td colspan="4">Data not Found</td>
                       </tr>';
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
function l0gin($connect){
  $username = mysqli_real_escape_string($connect, $_POST['username']);
  $password = mysqli_real_escape_string($connect, $_POST['password']);
  $query = "SELECT id FROM users WHERE uname = '$username' and password = '$password';";
  $result = mysqli_fetch_array(mysqli_query($connect, $query));
  $results = $result['id'];
  echo $results;
}
function reg($connect){
  $username = mysqli_real_escape_string($connect, $_POST['username']);
  $password = mysqli_real_escape_string($connect, $_POST['password']);
  $query = "INSERT INTO users (uname, password) VALUES ('$username', '$password');";
  $result = mysqli_query($connect, $query);
  $id = mysqli_insert_id($connect);
  echo $id;
}
function insert($connect){
  $val = mysqli_real_escape_string($connect, $_POST['text']);
   /* Match hashtags */
  $userid = mysqli_real_escape_string($connect, $_POST['userid']);
  preg_match_all('/#(\w+)/', $val, $matches);
  preg_match_all('/%(\w+)/', $val, $quiettags);
  if(!empty($quiettags[1])) {
    $hashtags = "subtle, ";
    $lockval = 'y';
  } else {
    $hashtags = "all, ";
    $lockval = 'n';
  }
  /* Add all matches to array */
   foreach ($quiettags[1] as $quiettag) {
      $hashtags .= $quiettag . ", ";
      $sql = "INSERT INTO tag_ref(hashtag, locked) VALUES('$quiettag', 'y');";
      if(mysqli_query($connect, $sql)){
        echo "succ";
      }
   }
   foreach ($matches[1] as $match) {
     $hashtags .= $match . ", ";
     $sql = "INSERT INTO tag_ref(hashtag, locked) VALUES('$match', 'n');";
     if(mysqli_query($connect, $sql)){
       echo "ess";

     }
   }
   $val = strip_tags($val, '<p><a><b><i><u>');
   $val = preg_replace("/#(\\w+)/", "<a href=\'javascript:void(0);\' id=\"inline_tag\" onclick=\'linktag(\"$1\")\'>#$1</a>", $val);
   $val = preg_replace("/%(\\w+)/", "<a href=\'javascript:void(0);\' id=\"inline_tag\" onclick=\'linktag(\"$1\")\'>%$1</a>", $val);

   $sql = "INSERT INTO text_content(text, count, hashtags, locked, userid) VALUES('$val', 0, '$hashtags', '$lockval', '$userid');";
   if(mysqli_query($connect, $sql))
   {
        echo 'Data Inserted';
   }
   echo $sql;
 }

function gethashtags($text) {
  preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $text, $matchedHashtags);
  $hashtag = '';
  // For each hashtag, strip all characters but alpha numeric
  if(!empty($matchedHashtags[0])) {
    foreach($matchedHashtags[0] as $match) {
        $hashtag .= preg_replace("/[^a-z0-9]+/i", "", $match).',';
    }
  }
  return rtrim($hashtag, ',');
}


$method = $_GET['method'];
if (!empty($method)){
  $method($connect);
} else {
  echo "hi there";
}
?>
