<?php
//error_reporting(E_ALL);
//ini_set('display_errors','On');

//***DEFAULT SETTINGS***
ini_set('date.timezone','Asia/Tokyo');
$pagetitle = 'チラシの裏';
$dbfile = './data/chirashi.db';

//**DEFINE DATABASE SCHEMA**
$dsn = 'sqlite:'.$dbfile;
$createsql = "CREATE TABLE memo ("
             ."id INTEGER PRIMARY KEY AUTOINCREMENT,"
             ."name TEXT NOT NULL UNIQUE,"
             ."content TEXT,"
             ."registdate TEXT,"
             ."status TEXT,"
             ."lastupdate TEXT"
             .")";
$insertsql = "INSERT INTO memo (name, content, registdate, status, lastupdate) VALUES (?, ?, datetime('now', 'localtime'), 'ACTIVE', datetime('now', 'localtime'))";
$updatesql = "UPDATE memo SET name = ?, content = ?, status = ?, lastupdate = datetime('now', 'localtime') WHERE id = ?";
$deletesql = "DELETE FROM memo WHERE id = ?";

//**INITIALIZE DATABASE**
if(!is_file($dbfile)) {
  if(!touch($dbfile)) {
    die("can't make dbfile! check write permission of 'data' directory.");
  }
}
if(filesize($dbfile) > 0) {
  $createsql = '';
}
try {
  $dbh = new PDO($dsn);
  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  if(!empty($createsql)) { //if table not exists
    $dbh->exec($createsql);
  }
} catch (PDOException $e) {
  die("PDO error 001: ".$e->getMessage());
}

//**MAIN**
$textid = '';
$textname = '';
$content = '';
if(!empty($_REQUEST['textid'])) {
  $textid = $_REQUEST['textid'];
  $res = $dbh->query('SELECT * FROM memo WHERE id = '.$textid);
  $item = $res->fetch();
  $textname = $item['name'];
  $content = $item['content'];
  $status = $item['status'];
  $registdate = $item['registdate'];
  $lastupdate = $item['lastupdate'];
}

if(isset($_REQUEST['save'])) {
  $textname = $_REQUEST['textname'];
  $content = $_REQUEST['content'];
  if(empty($textid)) {
    try {
      $sth = $dbh->prepare($insertsql);
      $sth->execute(array($textname,$content));
    } catch (PDOException $e) {
      die("PDO error 002: ".$e->getMessage());
    }
    $res = $dbh->query("SELECT * FROM memo WHERE name = '$textname'");
    $item = $res->fetch();
    $textid = $item['id'];
    $status = $item['status'];
    $registdate = $item['registdate'];
    $lastupdate = $item['lastupdate'];
  } else {
    try {
      $sth = $dbh->prepare($updatesql);
      $sth->execute(array($textname,$content,$status,$textid));
    } catch (PDOException $e) {
      die("PDO error 003: ".$e->getMessage());
    }
  }
} elseif(isset($_REQUEST['delete'])) {
  try {
    $sth = $dbh->prepare($deletesql);
    $sth->execute(array($textid));
  } catch (PDOException $e) {
    die("PDO error 004: ".$e->getMessage());
  }
  $textid = '';
  $textname = '';
  $content = '';
  $status = '';
  $registdate = '';
  $lastupdate = '';
}

$tablehtml = '';
$even = 'odd';
foreach($dbh->query('SELECT * FROM memo') as $item) {
  if($even === 'even') {
    $even = 'odd';
  } else {
    $even = 'even';
  }
  $tablehtml .= '<tr id="'.$item['id'].'" class="'.$even.'">';
  $tablehtml .= '<td>'.$item['id'].'</td>';
  $tablehtml .= '<td class="textname"><a href="'.$_SERVER['SCRIPT_NAME'].'?textid='.$item['id'].'">'.$item['name'].'</a></td>';
  //$tablehtml .= '<td class="status">'.$item['status'].'</td>';
  $tablehtml .= '<td class="date">'.$item['lastupdate'].'</td>';
  $tablehtml .= '<td class="action">';
  $tablehtml .= ' <a href="'.$_SERVER['SCRIPT_NAME'].'?delete=delete&textid='.$item['id'].'">DELETE</a>';
  $tablehtml .= ' <a href="./'.$item['id'].'.html" target="_blank">MarkDown</a>';
  $tablehtml .= '</td>';
  $tablehtml .= '</tr>';
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <link rel="stylesheet" type="text/css" href="./chirashi.css">
    <title><?php echo $pagetitle; ?></title>
  </head>
  <body>
   <div data-role="page">
    <div class="title" data-role="header">
     <?php echo $pagetitle; ?>
    </div><!-- /header -->

    <div data-role="content">
     <div class="form">
      <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
<?php
if(!empty($textid)) {
  echo 'ID: '.$textid;
} else {
  echo 'NEW TEXT ';
}
?>
       Title: <input type="input" name="textname" value="<?php echo $textname; ?>" size="60" />
       <input type="submit" name="save" value="save" />
<?php if(isset($status) && ($status === 'ACTIVE')) { ?>
       <input type="submit" name="delete" value="delete" />
<?php } // switch by $status ?>
       <br />
       <textarea id="content" rows="20" cols="80" name="content"><?php echo $content; ?></textarea>
       <br />
       <input type="hidden" name="textid" value="<?php echo $textid; ?>" />
      </form>
     </div>
     <div class="table">
      <table class="border">
       <tr class="odd"><th>ID</th><th>Title</th><th>Last Update</th><th>Action</th></tr>
<?php echo $tablehtml; ?>
       <tr class="odd"><td></td><td></td><td></td><td><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">CREATE</a></td></tr>
      </table>
     </div>
    </div><!-- /content -->
     
    <div class="footer" data-role="footer">
     php-chirashi (C) ozzozz
    </div><!-- /footer -->
   </div><!-- /page -->
  </body>
</html>
