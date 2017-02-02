<html>
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/rangeslider.css">
  <script src="js/jquery-3.1.1.min.js"></script>
  <script src="https://npmcdn.com/tether@1.2.4/dist/js/tether.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/rangeslider.min.js"></script>
  <script src="js/underscore-min.js"></script>

  <script src="js/app.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="page-header">
      <h1>Instagram Vision</h1>
      </div>

      <form id="form" method="post" action="" onsubmit="return false;" class="form-horizontal">

      <?php
        //if user is logged in
        session_start();
        if(isset($_COOKIE['advSet'], $_SESSION['token'], $_SESSION['email'])){
          echo'
          <div class="form-group">
            <div id="status_text" class="col-lg-3 col-lg-offset-5 label label-info"></div>
            <img id="spinner" src="img/ajax-loader.gif" style="display:none; padding-left: 5px;"/>
            <div id="alert_message"></div>
          </div>

          <div class="form-group">
            <label class="col-lg-2 control-label"></label>
            <h4 class="col-lg-8">You are logged in as: <strong>' . $_SESSION["email"] . '</strong></h4>
            <div class="col-lg-2">
                <button id="btnLogout" class="col-lg-12 btn btn-info" onclick="logout();">
                    Logout
                </button>
            </div>
          </div>

          <div class="form-group">
            <label class="col-lg-2 control-label" for="img_url">Image URL:</label>
            <div class="col-lg-8">
              <input class="form-control" type="text" id="img_url" name="img_url" readonly/>
            </div>
            <div class="col-lg-2">
              <label class="btn btn-warning">
                Choose Image<input id="file" type="file" name="upload" class="hidden" onchange="chooseImage(this);">
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="col-lg-2 control-label" for="msg">Caption:</label>
            <div class="col-lg-10">
              <textarea class="form-control" rows="2" id="msg" name="msg"></textarea>
            </div>
          </div>

          <div class="form-group">
            <label class="col-lg-2 control-label" for="msg">Hash Tags:</label>
            <div class="col-lg-8">
              <textarea class="form-control" rows="2" id="hash_tags" name="hash_tags"></textarea>
            </div>
            <div class="col-lg-2">
              <button id="btnGenerate" class="btn btn-primary" onclick="analyseImage($(\'#img_url\').val());">
                Generate Hash Tags
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="col-lg-2 control-label" for="msg">Extra Hash Tags:</label>
            <div class="col-lg-10">
              <textarea class="form-control" rows="2" id="extra_hash_tags" name="extra_hash_tags"></textarea>
              <div class="checkbox">
                <label>
                  <input type="checkbox" id="include_hash_tags_1"> Include most popular hashtags
                </label>
                &nbsp
                <label>
                  <input type="checkbox" id="include_hash_tags_2"> Include nature tags
                </label>
                &nbsp
                <label>
                  <input type="checkbox" id="include_hash_tags_3"> Include animal tags
                </label>
                &nbsp
                <label>
                  <input type="checkbox" id="include_hash_tags_4"> Include food tags
                </label>
                &nbsp
                <label>
                  <input type="checkbox" id="include_hash_tags_5"> Include popular korean tags
                </label>
              </div>
            </div>
          </div>

          <br>
          <div class="form-group">
            <label class="col-lg-2 control-label" for="msg">Tag Confidence:</label>
            <div class="col-lg-10">
              <input id="tag_slider" type="range" min="0" max="99" step="1" value="0"/>
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
              <input id="btnUpload" class="btn btn-success col-lg-12" type="submit" value="Post Image" onclick="uploadImage();"/>
            </div>
          </div>
          ';
        }else{
          echo'
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
            <label class="col-lg-2 control-label"></label>
            <div class="col-lg-10">
              <button id="btnLogin" class="col-lg-12 btn btn-primary" onclick="login();">
                  Login
              </button>
            </div>
          </div>

          ';
        }
        ?>
      </form>
    </div>
  </body>
</html>
