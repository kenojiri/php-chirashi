<?php
//error_reporting(E_ALL);
//ini_set('display_errors','On');

include_once "./markdown.php";

//***SETTINGS***
$pagetitle = 'チラシの裏';
$dbfile = './data/chirashi.db';
$dsn = 'sqlite:'.$dbfile;

//**OPEN DATABASE**
try {
  $dbh = new PDO($dsn);
  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
  die("PDO error 001: ".$e->getMessage());
}

//**MAIN**
if(!empty($_REQUEST['textid'])) {
  $textid = $_REQUEST['textid'];
  $res = $dbh->query('SELECT * FROM memo WHERE id = '.$textid);
  $item = $res->fetch();
  $textname = $item['name'];
  $mdtext = $item['content'];
  $status = $item['status'];
  $registdate = $item['registdate'];
  $lastupdate = $item['lastupdate'];
  $pagetitle = $item['name'].' - '.$pagetitle;
} else {
  header('Location: ./');
  exit;
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <link rel="stylesheet" type="text/css" href="./md.css">
    <title><?php echo $pagetitle; ?></title>
  </head>
  <body>
   <div data-role="page">
    <div class="title" data-role="header">
     <?php echo $textname ?>
    </div><!-- /header -->

    <div data-role="content" style="border: outset; text-align: left; margin: 0 100px;">
<?php
echo Markdown($mdtext);
?>
    </div><!-- /content -->
     
    <div class="footer" data-role="footer">
     php-chirashi (C) ozzozz
    </div><!-- /footer -->
   </div><!-- /page -->
  </body>
</html>
