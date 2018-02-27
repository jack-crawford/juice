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
                      <td class="text" data-id1="'.$row["id"].'"><span style="float: left">'.$content.'</span>
                      <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                      '.$count.' </span> </br>
                      <span style="color: grey">'.$time_since.' </span>
                     </td>

                 </tfoot>
            ';
            } else {
              $output .= '
                 <tr>
                 <td class="text" data-id1="'.$row["id"].'"><span style="float: left">'.$content.'</span>
                 <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                 '.$count.' </span> </br>
                 <span style="color: grey">'.$time_since.' </span>
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
                      <td class="text" data-id1="'.$row["id"].'"><span style="float: left">'.$content . $tags[1].'</span>
                      <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                      '.$count.' </span> </br>
                      <span style="color: grey">'.$time_since.' </span>
                     </td>

                 </tfoot>
            ';
            } else {
              $output .= '
                 <tr>
                 <td class="text" data-id1="'.$row["id"].'"><span style="float: left">'.$content.'</span>
                 <span style="float: right"><button type="button" id="button_up" name="up_btn" data-id3="'.$row["id"].'" class="btn btn-xs btn-primary btn_delete"><i class="fa fa-arrow-up"></i></button>
                 '.$count.' </span> </br>
                 <span style="color: grey">'.$time_since.' </span>
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
function insert($connect){
  $val = mysqli_real_escape_string($connect, $_POST['text']);
   /* Match hashtags */
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
   }
   foreach ($matches[1] as $match) {
     $hashtags .= $match . ", ";
   }

   $val = preg_replace("/#(\\w+)/", "<a href=\"juice.html?tag=$1\" id=\"inline_tag\">#$1</a>", $val);
   $val = preg_replace("/%(\\w+)/", "<a href=\"juice.html?tag=$1\" id=\"inline_tag\">%$1</a>", $val);

   $sql = "INSERT INTO text_content(text, count, hashtags, locked) VALUES('$val', 0, '$hashtags', '$lockval');";
   if(mysqli_query($connect, $sql))
   {
        echo 'Data Inserted';
   }
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
