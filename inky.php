<?php
$db = parse_ini_file("inky.ini");
$server = $db['host'];
$user = $db['user'];
$db_name = $db['name'];
$pwd = $db['pass'];
$connect = mysqli_connect("$host", "$user", "$pwd", "$db_name");

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
  $user_id = $_GET['user_id'];
  $sql = "SELECT * FROM work WHERE user_id = $user_id ORDER BY id;";

  $result = mysqli_query($connect, $sql);

  $output .= '
        <div class="table-responsive" id="tabbble">
             <table class="table table-bordered" id="stufftable">
                  ';
  if(mysqli_num_rows($result) > 0){
    $rowcount = mysqli_num_rows($result);
    $x = 1;
    $row = mysqli_fetch_array($result);
    while($row){
      $userget = "SELECT uname FROM users WHERE id ='".$row['user_id']."';";
      $userresult = mysqli_fetch_array(mysqli_query($connect, $userget));
      $username = $userresult['uname'];
      $content = $row['content'];
      if ($x == $rowcount) {
        $output .= '
          <tr>
          <td class="text" data-id1="'.$row["id"].'">
          <span style="color: white; font-size:75%; float:left;"<b>'.$username.':</b></span> </br>
          <span style="float: left" id="your_work" data-id1="'.$row["id"].'" contenteditable="true">'.$content.'</span>
         </td>

           </tr>
           <tfoot id="bottomrow">
                <td class="text" style="text-align: center;">
                  <button type="button" name="btn_new_entry" id="btn_new_entry" class="btn btn-info">
                    new
                  </button>
                </td>

           </tfoot>
      ';
      } else {
          $output .= '
            <tr>
            <td class="text" data-id1="'.$row["id"].'">
            <span style="color: white; font-size:75%; float:left;"<b>'.$username.':</b></span> </br>
            <span style="float: left" id="your_work" data-id1="'.$row["id"].'" contenteditable="true">'.$content.'</span>
           </td>

             </tr>
        ';

      }

      $row = mysqli_fetch_array($result);
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
   $id = $_POST["post_id"];
   $text = mysqli_real_escape_string($connect, $_POST["text"]);
   $sql = "UPDATE work SET content = '$text' WHERE id='".$id."'";
   if(mysqli_query($connect, $sql))
   {
        echo "Data Updated + $sql";
   }
}
function newpost($connect) {
   $id = $_POST["userid"];
   $starter = "INSERT INTO work (user_id, content) VALUES ($id, 'hey there! edit this!');";
   $result = mysqli_query($connect, $starter);
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
  $query = "SELECT id FROM users WHERE uname = '$username' and pwd = '$password';";
  $result = mysqli_fetch_array(mysqli_query($connect, $query));
  $results = $result['id'];
  echo $results;
}
function reg($connect){
  $username = mysqli_real_escape_string($connect, $_POST['username']);
  $password = mysqli_real_escape_string($connect, $_POST['password']);
  $query = "INSERT INTO users (uname, pwd) VALUES ('$username', '$password');";
  $result = mysqli_query($connect, $query);
  $id = mysqli_insert_id($connect);
  $starter = "INSERT INTO work (user_id, content) VALUES ($id, 'welcome to inky, edit this to start');";
  $result = mysqli_query($connect, $starter);
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
