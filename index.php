<?php
include("cacheable.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <title>LFK Manifest</title>
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="jquery-ui-1.8.23.custom/css/ui-lightness/jquery-ui-1.8.23.custom.css" rel="stylesheet">
    <style type="text/css">
      .waiting-circles{ padding: 0; display: inline-block; 
          position: relative; width: 60px; height: 60px;}
      .waiting-circles-element{ margin: 0 2px 0 0; background-color: #e4e4e4; 
          border: solid 1px #f4f4f4;
          width: 10px; height: 10px; display: inline-block; 
          -moz-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px;}
      .waiting-circles-play-0{ background-color: #9ec45f; }
      .waiting-circles-play-1{ background-color: #aed46f; }
      .waiting-circles-play-2{ background-color: #bee47f; }
      hr {
        margin: 0;
      }
      .slidesjs-next {
        float: right;
      }
      .slidesjs-next, .slidesjs-previous {
        color: #cccccc;
      }
    </style>
    <script src="jquery-ui-1.8.23.custom/js/jquery-1.8.0.min.js"></script>
    <script src="jquery-ui-1.8.23.custom/js/jquery-ui-1.8.23.custom.min.js"></script>
    <script src="https://www.google.com/jsapi"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="slidesjs/jquery.slides.min.js"></script>
    <script src="jquery-waiting.js"></script>
    <script src="manifest.js"></script>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-static-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#" id="time">--:--</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
            <?php if($_GET['page'] == "overview") { ?>
              <li class="active">
            <?php } else { ?>
              <li class="inactive">
            <?php } ?>
                <a href="overview">Översikt</a></li>
              </li>
            <?php if($_GET['page'] == "wish") { ?>
              <li class="active">
            <?php } else { ?>
              <li class="inactive">
            <?php } ?>
                <a href="wish">Liftkö</a></li>
              </li>
              <li>
                <a href="https://holfuy.com/en/weather/761" target="_blank">V&auml;derstation</a>
              </li>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container">
      <?php
      
        switch($_GET['page']) {
          case "wish":
            include("front-wish.php");
            break;
          case "overview":
            include("front-overview.php");
            break;
          default:
            include("front-overview.php");
            break;
        }
      ?>
      <hr>

      <footer>
        <p>&copy; Linköpings Fallskärmsklubb <?php echo date("Y"); ?></p>
      </footer>

    </div> <!-- /container -->
  </body>
</html>
