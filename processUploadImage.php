<?php
  error_reporting(E_ALL & ~E_WARNING);
  require_once "path.php";

  require_once "nxs-api/nxs-api.php";
  require_once "nxs-api/nxs-http.php";
  require_once "inc/nxs-functions.php";

  if($_POST){
      //post to instagram
      if(isset($_POST['msg'], $_POST['img_url'])){
        $email = $_POST['email'];
        $pass = $_POST['pass'];
        $msg = $_POST['msg'];
        $imgURL = $_POST['img_url'];
        $imgFormat = $_POST['img_format']; // 'E' (Extended) or 'C' (Cropped) or 'U' (Untouched)

        $nt = new nxsAPI_IG();
        $loginError = $nt->connect($email, $pass);

        $result = null;
        if(!$loginError){
          $result = $nt->post($msg, $imgURL, $imgFormat);
        }else{
          $data = ["err" => true, "value" => "Error! Could not login. Please make sure to enter the correct login details."];
        }

        if (!empty($result) && is_array($result) && intval($result['isPosted'])===1){
          //set the URL of the post
          $data = ["err" => false, "value" => $result['postURL']];
        }else{
          $data = ["err" => true, "value" => 'An error has occured. Please try again.'];
        }

        //delete the image from the server
        if(isset($_POST['upload_name']) && !empty($_POST['upload_name'])){
          unlink(ABSPATH . 'uploads/' . $_POST['upload_name']);
        }

        //return a json
        echo json_encode($data);

      //upload image to file server
      }else if(isset($_FILES['upload'])){
        $file_size = $_FILES['upload']['size'];
        $file_tmp = $_FILES['upload']['tmp_name'];
        $file_ext = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);

        $allowed =  array('gif', 'png', 'jpg', 'mp4');

        //restrict file size
        if($file_size > 50000000) {
          $data = ["err" => true, "value" => 'File size cannot exceed 50 MB'];
        //sanitize file extension
        }elseif(!in_array($file_ext, $allowed)){
          $data = ["err" => true, "value" => 'Sorry! That file type is not allowed.'];
        }else{
          //upload file to server
          $timestamp = microtime(true);
          $file_name = str_replace(".", "", $timestamp) . rand(1000, 9999) . '.' . $file_ext;
          $file_path = ROOTPATH . 'uploads/' . $file_name;
          $upload_path = ABSPATH . 'uploads/' . $file_name;
          if(move_uploaded_file($file_tmp, $upload_path)) {
            //set the temporary path
            $data = ["err" => false, "value" => $file_path, "value2" => $file_name];
          }else{
            $data = ["err" => true, "value" => 'Error! Failed to upload file to server.'];
          }
        }

        //return a json
        echo json_encode($data);
      }else{
        $data = ["err" => true, "value" => "Error! Invalid POST Request."];

        //return a json
        echo json_encode($data);
      }
  }else{
    $data = ["err" => true, "value" => "Error! Invalid Request."];

    //return a json
    echo json_encode($data);
  }
?>
