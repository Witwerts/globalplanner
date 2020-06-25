<!DOCTYPE html>
<html lang="en">
<head>
<title><?= (isset($this->title)) ? APP_NAME . ' - ' . $this->title : APP_NAME ?></title>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php

    foreach($this->_metaTags as $name => $desc){
        echo '<meta name="'.$name.'" content="'.$desc.'">';
    }

?>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<!-- Font Awesome Script -->
<script src="https://kit.fontawesome.com/c2bdb0bb37.js"></script>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

<?php

    // Load custom styles for a page if they exist
    foreach($this->_customStyles as $key => $value){
        echo '<link rel="stylesheet" type="text/css" href="'.URL.'public/css/'.$value.'">';
    }

    // Load custom header scripts if they exist
    foreach($this->_jsScriptsTop as $key => $value){
        echo '<script src="'.URL.'public/js/'.$value.'"></script>';
    }
    
?>

</head>

