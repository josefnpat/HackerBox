<?php

error_reporting(-1);

DEFINE('BOX_NAME',"HackerBox");
DEFINE('WHITELIST',"zip love jpg gif png");
DEFINE('LIFESPAN',60*60*24);

$upload_errors = array();
$upload_errors[UPLOAD_ERR_OK]="File uploaded.";
$upload_errors[UPLOAD_ERR_INI_SIZE]="The uploaded file exceeds the upload_max_filesize directive in `php.ini`.";
$upload_errors[UPLOAD_ERR_FORM_SIZE]="The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
$upload_errors[UPLOAD_ERR_PARTIAL]="The uploaded file was only partially uploaded.";
$upload_errors[UPLOAD_ERR_NO_FILE]="No file was uploaded.";
$upload_errors[UPLOAD_ERR_NO_TMP_DIR]="Missing a temporary folder.";
$upload_errors[UPLOAD_ERR_CANT_WRITE]="Failed to write file to disk";
$upload_errors[UPLOAD_ERR_EXTENSION]="File upload stopped by extension";

function human_filesize($bytes, $decimals = 1) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

function return_bytes($val) {
  $val = trim($val);
  $last = strtolower($val[strlen($val)-1]);
  switch($last) {
  case 'g':
    $val *= 1024;
  case 'm':
    $val *= 1024;
  case 'k':
    $val *= 1024;
  }
  return $val;
}

function arraytoul($arr,$func=NULL) {
  if(count($arr) > 0){
    $s = "<ul>\n";
    $div = false;
    foreach($arr as $ele){
      $s.="<li class='".($div?"even":"odd")."'>".($func?$func($ele):$ele)."</li>\n";
      $div = !$div;
    }
    return $s."</ul>\n";
  } else {
    return false;
  }
}

$max = return_bytes(ini_get('upload_max_filesize'));

$msgs = array();

if( isset($_FILES) ){

  if( array_key_exists('f',$_FILES) ){

    $fail = false;
    $ext = pathinfo($_FILES['f']['name'], PATHINFO_EXTENSION);
    $valid_exts = explode(" ",WHITELIST);
    if(!in_array(strtolower($ext),$valid_exts)){
      $msgs[] = "Extension not in whitelist: ".WHITELIST;
      $fail = true;
    }
    if( $_FILES['f']['size'] > $max ){
      $msgs[] = "File size maximum reached: ".human_filesize($max,0);
      $fail = true;
    }
    if( $_FILES['f']['error'] > 0){
      $msgs[] = $upload_errors[$_FILES['f']['error']];
      $fail = true;
    }
    if( file_exists('uploads/'.$_FILES['f']['name']) ){
      $msgs[] = "File with same name already exists.";
      $fail = true;
    }
    if(!$fail){
      $success = move_uploaded_file($_FILES['f']["tmp_name"],'uploads/'.$_FILES['f']['name']);
      if($success){
        $msgs[] = $upload_errors[UPLOAD_ERR_OK];
      } else {
        $msgs[] = "Failed to move file to uploads/.";
      }
    }

  } else {
    if(empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
      $msgs[] = "File size maximum reached: ".human_filesize($max,0);
    }

  }

}

?><!DOCTYPE html>
  <head>
    <title><?php echo BOX_NAME; ?></title>
    <meta charset="utf-8" />
    <link href="style.css" rel="stylesheet" type="text/css">
  </head>
  <body>
    <div class="wrapper">
      <div class="left">
<pre><?php include("logo.ascii"); ?></pre>
        <h1><?php echo BOX_NAME; ?></h1>
<?php echo arraytoul($msgs); ?>
        <form enctype="multipart/form-data" action="index.php" method="POST">
          <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max; ?>" />
          <input name="f" type="file" /><br />
          <input type="submit"/>
        </form>
      </div> <!-- left -->
      <div class="right">
<?php

chdir("uploads");
$ups = glob("*");

$upsrender = arraytoul($ups,function($up){
  return "<a href='uploads/$up'>$up</a> <small>[".human_filesize(filesize($up)).",".(time()-filemtime($up))."s]</small>";
});
if($upsrender === NULL){
  echo "<p>No files have been uploaded yet.";
} else {
  echo $upsrender;
}
?>
      </div> <!-- right -->
    </div> <!-- wrapper -->
  </body>
</html>

