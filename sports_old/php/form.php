<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_id = filter_var($_POST['reg_id']);
    $dis = filter_var($_POST['dis']);
    $divi = filter_var($_POST['divi']);
    $club_name = filter_var($_POST['club_name']);
    $village = filter_var($_POST['village']);
    $c_name = filter_var($_POST['c_name']);
    $sec_name = filter_var($_POST['sec_name']);
    $voli = filter_var($_POST['voli']);
    $net = filter_var($_POST['net']);
    $nb = filter_var($_POST['nb']);
    $foot = filter_var($_POST['foot']);
    $teni = filter_var($_POST['teni']);
    $bat = filter_var($_POST['bat']);
    $wik = filter_var($_POST['wik']);
    $eq = filter_var($_POST['eq']);
    $reg_date = filter_var($_POST['reg_date']);
    

    $stmt = $conn->prepare("INSERT INTO club_register (reg_id, district, division, village, club_name, chair_name, sec_name, volleyball, net, nb, football, tenis, bat, wicket, equipments, reg_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssssssssssssss", $reg_id, $dis, $divi, $village,$club_name, $c_name, $sec_name, $voli, $net, $nb, $foot, $teni, $bat, $wik, $eq, $reg_date);

    if ($stmt->execute()) {
        header("Location: /sports/form.html?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
