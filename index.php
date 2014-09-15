<?php

  require_once ('config.php');
  require_once ('database.php');
  require_once ('ui.php');

  $secret = '';

  if ( $testdata or isset ($_GET['testdata']) ) {
    $_POST["latitude"] = 'here';
    $_POST["longitude"] = 'here';
    $_POST["accuracy"] = 'here';
    $_POST["secret"] = 'testname';
  }

  if ( isset ( $_POST['latitude'] )) {
    $lat = clean_input($_POST['latitude']);
    $lon = clean_input($_POST['longitude']);
    $acc = clean_input($_POST['accuracy']);
    $secret = clean_input($_POST['secret']);
    $battery = clean_input($_POST['battlevel']);
    $charging = clean_input($_POST['charging']);

    $ip = $_SERVER['REMOTE_ADDR'];

    if ( add_request ($lat, $lon, $acc, $secret, $ip, $battery, $charging) ) {
      print ("200 OK at ".date('Y-m-d H:i')." from $secret.");
    } else {
      print ("Error! Something wrong with setup or data");
    }

    exit (0);
  }

  # If not a request from app, go on:

  $sid = 0;
  if ( isset ($_GET['sid']))
    $sid = clean_input($_GET['sid']);

  $rid = 0;
  if ( isset ($_GET['rid']))
    $rid = clean_input($_GET['rid']);

  if ($verbose) {
    print 'Post:<br/>';
    print_r($_POST);
  }

  include ('html_header.html');

  print '<h1><a href="'. $_SERVER['PHP_SELF'] .'">BigBrotherGPS Map</a></h1>';

  $requests = list_requests ($sid, $rid);
  $devices = list_secrets ();

  show_map ($devices, $requests);
  show_requests ($requests);
  show_devices ($devices);

  show_log ( list_latest_requests() );

  include ('html_footer.html');
?>
