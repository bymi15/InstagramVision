<?php
error_reporting(E_ALL & ~E_WARNING);
require_once "path.php";

require_once "lib/nxs-api/nxs-api.php";
require_once "lib/nxs-api/nxs-http.php";
require_once "lib/inc/nxs-functions.php";

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

          if (!empty($result) && is_array($result) && intval($result['isPosted'])===1){
            //set the URL of the post
            $data = ["err" => false, "value" => $result['postURL']];
          }else{
            $data = ["err" => true, "value" => 'An error has occured. Please try again.'];
          }

        }else{
          $data = ["err" => true, "value" => "Error! Could not login. Please make sure to enter the correct login details."];
        }
        //delete the image from the server
        /*
        if(isset($_POST['upload_name']) && !empty($_POST['upload_name'])){
          unlink(ABSPATH . 'uploads/' . $_POST['upload_name']);
        }
        */

        //deletes the image from the Imgur server
        if(isset($_POST['delete_hash']) && !empty($_POST['delete_hash'])){
          $client_id = 'b43ca153dcfa0ec';
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image/' . $_POST['delete_hash']);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
          curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

          $result = curl_exec($ch);

          curl_close($ch);
        }

        //return a json
        echo json_encode($data);

      //upload image to file server
      }else if(isset($_FILES['upload'])){
        $file_size = $_FILES['upload']['size'];
        $file_tmp = $_FILES['upload']['tmp_name'];
        $file_ext = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);

        $allowed =  array('gif', 'GIF', 'png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'bmp', 'BMP');

        //restrict file size
        if($file_size > 50000000) {
          $data = ["err" => true, "value" => 'File size cannot exceed 50 MB'];
        //sanitize file extension
        }elseif(!in_array($file_ext, $allowed)){
          $data = ["err" => true, "value" => 'Sorry! That file type is not allowed.'];
        }else{
          //upload file to server
          $handle = fopen($file_tmp, "r");
          $image = fread($handle, filesize($file_tmp));
          $client_id = 'b43ca153dcfa0ec';

          $ch = curl_init();

          curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image');
          curl_setopt($ch, CURLOPT_POST, TRUE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $client_id ));
          curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'image' => base64_encode($image) ));

          $reply = curl_exec($ch);

          //error
          if(!$reply){
            $data = ["err" => true, "value" => curl_error($ch)];
          }else{
            $reply = json_decode($reply);
            $data = ["err" => false, "value" => $reply->data->link, "value2" => $reply->data->deletehash];
          }

          curl_close($ch);

          /*$timestamp = microtime(true);
          $file_name = str_replace(".", "", $timestamp) . rand(1000, 9999) . '.' . $file_ext;
          $file_path = ROOTPATH . 'uploads/' . $file_name;
          $upload_path = ABSPATH . 'uploads/' . $file_name;

          if(move_uploaded_file($file_tmp, $upload_path)) {
            //change permissions to allow read/write
            chmod($upload_path, 0777);

            $data = ["err" => false, "value" => $file_path, "value2" => $file_name, "value3" => $img_data];
          }else{
            $data = ["err" => true, "value" => 'Error! Failed to upload file to server.'];
          }*/
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
