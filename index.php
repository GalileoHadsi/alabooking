<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
  $username = $_SESSION['username'];
  $room = $_POST['room'];
  $reason = $_POST['reason'];
  $timeFrom = date('H:i:s',strtotime($_POST['timefrom']));
  $timeTo = date('H:i:s',strtotime($_POST['timeto']));
  $date = date('Y-m-d', strtotime($_POST['date']));
  $combinedDateFrom = date('Y-m-d H:i:s', strtotime("$date $timeFrom"));
  $combinedDateTo = date('Y-m-d H:i:s', strtotime("$date $timeTo"));
  // Make sure the submitted registration values are not empty.
  if (empty($_POST['room']) || empty($_POST['reason']) || empty($_POST['timefrom']) || empty($_POST['timeto']) || empty($_POST['date'])) {
  	// One or more values are empty.
  	echo('Please complete the registration form');
  }
  else{
    if ( $combinedDateTo < $combinedDateFrom){
      echo nl2br("Let's be a little sensical now, shall we?\n Check your timing well");
    } else{
        /* Check whether starting time is less than 0700h and ending time is greater than 2130h */
        if($timeFrom < "07:00:00" OR $timeTo > "21:30:00"){
          echo ("You can not be allowed in a room in that time frame");
        }else{
              // sort rooms by room then by date. Then if time to is less than time from of new selection, then book the room
                  $doublebooking = "SELECT * FROM `bookings` where room='$room' AND useDate='$date'";
                  $availability = $link->query($doublebooking);
                  if ($availability->num_rows > 0){
                    echo 'Found a culprit';
                    while($row = $availability->fetch_assoc()) {
                      if($row['dateTo'] < $combinedDateFrom){
                        echo 'Room already booked';
                        header("location: index.php");
                      }
                      else{
                        $sql = "INSERT INTO `bookings` (student, room, reason, dateFrom, dateTo, useDate) VALUES ('$username', '$room', '$reason', '$combinedDateFrom', '$combinedDateTo', '$date')";

                        if (mysqli_query($link, $sql)) {
                            echo "New record created successfully";
                            header("location: index.php");
                        } else {
                            echo "Error: " . $sql . "<br>" . mysqli_error($link);
                        }
                      }
                    }
                  }
                  $sql = "INSERT INTO `bookings` (student, room, reason, dateFrom, dateTo, useDate) VALUES ('$username', '$room', '$reason', '$combinedDateFrom', '$combinedDateTo', '$date')";

                  if (mysqli_query($link, $sql)) {
                      echo "New record created successfully";
                      header("location: index.php");
                  } else {
                      echo "Error: " . $sql . "<br>" . mysqli_error($link);
                  }

                  mysqli_close($link);
        }
      }
  }
}
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
  <title>Booking Software</title>
</head>
<body>

  <div class="navbar-fixed">
    <nav class="nav-wrapper" style="color: #ad2727;">
      <div class="container">
        <a href="#" class="brand-logo">Booking</a>
        <a href="#" class="sidenav-trigger" data-target="mobile-links">
          <i class="material-icons">menu</i>
        </a>
        <ul class="right hide-on-med-and-down">
          <li><a href="">Home</a></li>
          <li><a href="">About</a></li>
          <li><a href="">Contact</a></li>
          <li><a href="logout.php" class="btn white black-text">Logout</a></li>
        </ul>
      </div>
    </nav>
  </div>

  <ul class="sidenav" id="mobile-links">
    <li><a href="">Home</a></li>
    <li><a href="">About</a></li>
    <li><a href="">Contact</a></li>
    <li><a href="logout.php" class="btn red black-text">Logout</a></li>
  </ul>

  <div class="container">
    <br />

    <div class="row">
      <div class="col s12 m8 offset-m2">
        <div class="card blue darken-1 z-depth-3">
          <div class="card-content white-text">
            <span class="card-title"><?php echo htmlspecialchars($_SESSION["username"]); ?> Upcoming Bookings</span>
            <?php
              $username = $_SESSION['username'];
              //$room = "SELECT room FROM `bookings` WHERE student='$username' ORDER BY dateFrom";
              $room = "SELECT * FROM `bookings` where student='$username' ORDER BY dateFrom";
              $result = $link->query($room);

              if ($result->num_rows > 0) {
                  echo "<table class=\"responsive-table centered\">";
                  echo "<tr>";
                  echo "<th>Name</th>";
                  echo "<th>Date From</th>";
                  echo "<th>Date To</th></tr>";
                   while($row = $result->fetch_assoc()) {
                   Print "<tr>";
                   Print "<td>".$row['room'] . "</td> ";
                   Print "<td>".$row['dateFrom'] . " </td>";
                   Print "<td>".$row['dateTo'] . " </td></tr>";
                   }
                   Print "</table>";

              } else {
                  echo "0 results";
              }
            ?>
          </div>
          <!--
          <div class="card-action">
            <a href="#">This is a link</a>
            <a href="#">This is a link</a>
          </div>
        -->
        </div>
      </div>
      <br>
    </div>
    <div class="row">
      <div class="col s6">
        <div class="card small">
          <div class="card-image waves-effect waves-block waves-light">
            <img class="activator" src="img/Excellence.JPG" >
          </div>
          <div class="card-content">
            <span class="card-title activator grey-text text-darken-4">Make booking<i class="material-icons right">more_vert</i></span>
          </div>
          <div class="card-reveal">
            <span class="card-title grey-text text-darken-4">Make a new booking<i class="material-icons right">close</i></span>
            <p>Use the button below to book any room of your choice.<p>
            <br />
            <a class="waves-effect waves-light btn orange modal-trigger" href="#terms"><i class="material-icons left">add_circle_outline</i>Book a room</a>
          </div>
        </div>
      </div>
      <div class="col s6">
        <div class="card small">
          <div class="card-image waves-effect waves-block waves-light">
            <img class="activator responsive-img circle" src="img/Diversity.JPG">
          </div>
          <div class="card-content">
            <span class="card-title activator grey-text text-darken-4">Availabe rooms<i class="material-icons right">more_vert</i></span>
          </div>
          <div class="card-reveal">
            <span class="card-title grey-text text-darken-4">Available rooms<i class="material-icons right">close</i></span>
            <p>Use the button below to book any room of your choice.<p>
            <br />
            <a class="waves-effect waves-light btn orange modal-trigger" href="#terms"><i class="material-icons left">add_circle_outline</i>Book a room</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="terms" class="modal" style="width: 70%">
    <div class="modal-content">
      <h4>Book A Room</h4>
      <div class="col s12 l5 offset-l2">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
          <div class="row">
            <div class="input-field col s12 m6">
              <i class="material-icons prefix">home</i>
              <select class="icons" id="room" name="room">
                <optgroup label="LCs">
                  <option value="LC1">LC 1</option>
                  <option value="LC2">LC 2</option>
                </optgroup>
                <optgroup label="Value Rooms">
                  <option value="Curiosity">Curiosity</option>
                  <option value="Excellence" data-icon="img/Excellence.JPG">Excellence</option>
                  <option value="Compassion" data-icon="img/Compassion.JPG">Compassion</option>
                  <option value="Humility" data-icon="img/Humility.JPG">Humility</option>
                  <option value="Diversity" data-icon="img/Diversity.JPG">Diversity</option>
                </optgroup>
                <!--
                <optgroup label="Value Rooms">
                  <option value="3">Option 3</option>
                  <option value="4">Option 4</option>
                </optgroup>
                <optgroup label="Value Rooms">
                  <option value="3">Option 3</option>
                  <option value="4">Option 4</option>
                </optgroup>
                <optgroup label="Value Rooms">
                  <option value="3">Option 3</option>
                  <option value="4">Option 4</option>
                </optgroup>
              -->
              </select>
            </div>
            <div class="input-field col s12 m6">
              <i class="material-icons prefix">message</i>
              <textarea name="reason" id="message" class="materialize-textarea" data-length="50"></textarea>
              <label for="message">Valid Reason</label>
            </div>
          </div>
          <div class="row">
            <div class="input-field col s12 m6">
              <i class="material-icons prefix">alarm</i>
              <input type="text" name="timefrom" id="timefrom" class="timepicker">
              <label for="timefrom">Used From:</label>
            </div>
            <div class="input-field col s12 m6">
              <i class="material-icons prefix">schedule</i>
              <input type="text" name="timeto" id="timeto" class="timepicker">
              <label for="timeto">Used To:</label>
            </div>
          </div>

          <div class="row">
            <div class="input-field col s12 m6 offset-m3">
              <i class="material-icons prefix">date_range</i>
              <input type="text" name="date" id="date" class="datepicker">
              <label for="date">Pick a date</label>
            </div>
          </div>
          <center><input type="submit" class="btn modal-close" value="Book a Room"></center>
        </form>
      </div>
    </div>
  </div>

  <footer class="page-footer">
    <div class="container">
      <div class="row">
        <div class="col l6 s12">
          <ul>
            <li><a class="grey-text text-lighten-3" href="#!">Allxs</a></li>
            <li><a class="grey-text text-lighten-3" href="#!">Canvas</a></li>
          </ul>
        </div>
        <div class="col l4 offset-l2 s12">
          <ul>
            <li><a class="grey-text text-lighten-3" href="#!">Kampasi</a></li>
            <li><a class="grey-text text-lighten-3" href="#!">Back to top</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-copyright">
      <div class="container">
      © 2020 Copyright
      <a class="grey-text text-lighten-4 right" href="#!">ELEMENT Co.</a>
      </div>
    </div>
</footer>
  <!-- Compiled and minified JavaScript -->
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
  <script>
    $(document).ready(function(){
      $('.sidenav').sidenav();
      $('.modal').modal();
      $('select').formSelect();
      $('textarea#message').characterCounter();
      $('.timepicker').timepicker();
      $('.datepicker').datepicker({
        disableWeekends: false,
        yearRange: 1
      });
    });
  </script>
</body>
</html>
