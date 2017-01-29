<?php
?>

<html>
  <head>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <script src="js/jquery-3.1.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/app.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="page-header">
      <h1>Instagram Vision</h1>
      </div>

      <form id="form" method="post" action="" onsubmit="return false;" class="form-horizontal">

        <div class="form-group">
          <div id="status_text" class="col-lg-3 col-lg-offset-5 label label-info"></div>
          <img id="spinner" src="img/ajax-loader.gif" style="display:none; padding-left: 5px;"/>
          <div id="alert_message"></div>
        </div>

        <div class="form-group">
          <label class="col-lg-2 control-label" for="email">Email:</label>
          <div class="col-lg-10">
            <input class="form-control" type="email" id="email" name="email"/>
          </div>
        </div>

        <div class="form-group">
          <label class="col-lg-2 control-label" for="pass">Password:</label>
          <div class="col-lg-10">
            <input class="form-control" type="password" id="pass" name="pass"/>
          </div>
        </div>

        <div class="form-group">
          <label class="col-lg-2 control-label" for="msg">Caption:</label>
          <div class="col-lg-10">
            <textarea class="form-control" rows="3" id="msg" name="msg"></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-lg-2 control-label" for="img_url">Image URL:</label>
          <div class="col-lg-6">
            <input class="form-control" type="text" id="img_url" name="img_url" readonly/>
          </div>
          <div class="col-lg-2">
            <label class="btn btn-success">
              Choose Image<input id="file" type="file" name="upload" class="hidden" onchange="chooseImage(this);">
            </label>
          </div>
          <div class="col-lg-2">
            <button id="btnGenerate" class="btn btn-primary" onclick="analyseImage($('#img_url').val());">
              Generate Hash Tags
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="col-lg-2 control-label" for="img_format">Image Format:</label>
          <div class="col-lg-10"><!--
            <input class="form-control" type="text" id="img_format" name="img_format"/> -->
            <div class="radio">
              <label>
                <input type="radio" name="img_format" value="E" checked>
                Extended
              </label>
            </div>
            <div class="radio">
              <label>
                <input type="radio" name="img_format" value="C">
                Cropped
              </label>
            </div>
            <div class="radio">
              <label>
                <input type="radio" name="img_format" value="U">
                Untouched
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <input id="btnUpload" class="btn btn-primary" type="submit" value="Post Image" onclick="uploadImage();"/>
          </div>
        </div>
      </form>
    </div>
  </body>
</html>
