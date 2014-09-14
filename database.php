<?php

  function clean_input ($in) {
    return stripslashes ( $in );
  }

  function get_secret ($sname) {
    global $mysqli, $verbose;

    $query = 
      'SELECT * 
       FROM secrets
       WHERE sname LIKE "'.$sname.'"
       order by sid ASC
      ';

    if($verbose)
      print 'get_secret'.$query;

    $result = mysqli_query( $mysqli, $query );
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      if($verbose)
        print_r($row);
      return $row['sid'];
    }

    if ($verbose)
      print 'No hit. Insert and try again.';

    $query =
      'INSERT INTO secrets (sname)
       VALUES ("'.$sname.'")
      ';
    $result = mysqli_query( $mysqli, $query );

    return get_secret($sname);
  }

  function list_secrets ($sid = '', $sname = '') {
    global $mysqli, $verbose;
    $out = array();

    $query =
      'SELECT *
       FROM secrets
      ';

    if ($sname)
      $query .= '
        WHERE sname LIKE "'.$sname.'"
      ';
    else if ($sid)
      $query .= '
       WHERE sid = "'.$sid.'"
      ';

    $query .= '
       order by sname
    ';

    $result = mysqli_query( $mysqli, $query );
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      $out[ $row['sid'] ] = $row;

      if (1 > strlen($row['sname']))
        $out [ $row['sid'] ]['sname'] = '(No name)';
    }

    return $out;
  }

  function add_request (
    $lat = '', 
    $lon = '', 
    $acc = '', 
    $secret = '',
    $ip ) {
    global $mysqli, $verbose;

    $sid = get_secret ($secret);  

    $query = 
      "
      INSERT INTO
      requests (latitude, longitude, accuracy, sid, rip)
      values ('$lat', '$lon', '$acc', '$sid', '$ip')
      ";

    if($verbose)
      print 'get_secret'.$query;

    $result = mysqli_query( $mysqli, $query )
      or die('Err add_request!');

    return true;
  }

  function list_requests ($sid = '', $sname = '') {
    global $mysqli, $verbose;
    $out = array();

    if ($verbose)
      print 'list_requests';

    $query = "
      SELECT s . * , r . *
      FROM secrets s
      INNER JOIN requests r 
        ON r.rid = (
          SELECT rid
          FROM requests
          WHERE sid = s.sid
          ORDER BY rid DESC
          LIMIT 1
        )
      ";

    if ($sid)
      $query .= "
        WHERE s.sid = $sid
      ";

    if ($sname)
      $query .= "
        WHERE s.sname LIKE $sname
      ";

    $query .= "
      ORDER BY rdate DESC
    ";

    if ($verbose)
      print $query;

    $result = mysqli_query( $mysqli, $query ) or die('Err!');
    $i = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      $out[$i] = $row;
      $i++;
    }

    return $out;
  }

  function list_latest_requests ($secretid = '', $count = 10) {
    global $mysqli, $verbose;
    $out = array();

    if ($verbose)
      print 'list_latest_requests';

    $query = "
      SELECT r.*, s.sname
      FROM requests AS r
      LEFT JOIN secrets AS s
      ON r.sid = s.sid
      ";

    if ($secretid)
      $query .= "
        WHERE r.sid == $secretid
      ";

    $query .= "
      ORDER BY rdate DESC
      LIMIT $count
    ";

    if ($verbose)
      print $query;

    $result = mysqli_query( $mysqli, $query ) or die('Err!');
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      $out[ $row['rid'] ] = $row;

      if (1 > strlen($row['sname']))
        $out [ $row['rid'] ]['sname'] = '(No name)';
    }

    return $out;
  }

  function get_coordinates ($requests) {
    $out = array();

    foreach ($requests as $key => $r) {
      #print_r($r);
      $lat = floatval($r['latitude']);
      $lon = floatval($r['longitude']);

      #print 'lat'.$lat.'lon'.$lon;

      if ( ( 0 == $lat ) ||
        (0 == $lon ) )
        continue;
      $out[] = array ( floatval($r['latitude']), floatval($r['longitude']));
    }
    return $out;
  }

  # Connect to db mysqli("localhost", "user", "password", "database");
  $mysqli = new mysqli($host, $user, $password, $database);
  if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: 
      (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
  }

?>
