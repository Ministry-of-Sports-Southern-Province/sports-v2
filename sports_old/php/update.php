<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $reg_id = $_POST['reg_id'];
  $dis = $_POST['dis'];
  $divi = $_POST['divi'];
  $village = $_POST['village'];
  $club_name = $_POST['club_name'];
  $c_name = $_POST['c_name'];
  $sec_name = $_POST['sec_name'];
  $voli = $_POST['voli'];
  $net = $_POST['net'];
  $nb = $_POST['nb'];
  $foot = $_POST['foot'];
  $teni = $_POST['teni'];
  $bat = $_POST['bat'];
  $wik = $_POST['wik'];
  $eq = $_POST['eq'];
  $reg_date = $_POST['reg_date'];

  if (empty($reg_id) || empty($dis) || empty($divi) || empty($village) || empty($club_name) || empty($c_name) || empty($sec_name)) {
    echo "<script>
                alert('All fields are required!');
                window.history.back();
              </script>";
    exit;
  }

  $query = "UPDATE club_register SET district = ?, division = ?, village = ?, club_name = ?, chair_name = ?, sec_name = ?, volleyball = ?, net = ?, nb = ?, football = ?, tenis = ?, bat = ?, wicket = ?, equipments = ?,reg_date = ? WHERE reg_id = ?";
  $stmt = $conn->prepare($query);

  if ($stmt) {
    $stmt->bind_param("ssssssssssssssss", $dis, $divi, $village, $club_name, $c_name, $sec_name, $voli, $net, $nb, $foot, $teni, $bat, $wik, $eq, $reg_date, $reg_id);

    if ($stmt->execute()) {
      echo "<script>
                    alert('Record updated successfully!');
                    window.location.href = '/sports/edit.html?reg_id=$reg_id';
                  </script>";
    } else {
      echo "<script>
                    alert('Error updating record: " . $stmt->error . "');
                    window.history.back();
                  </script>";
    }

    $stmt->close();
  } else {
    echo "<script>
                alert('Error preparing the query: " . $conn->error . "');
                window.history.back();
              </script>";
  }

  $conn->close();
} else {
  echo "<script>
            alert('Invalid request method!');
            window.history.back();
          </script>";
}
