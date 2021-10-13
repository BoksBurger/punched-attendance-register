<?php
 // $allowedIPs = ["192.168.0.55", "192.168.0.20", "192.168.0.16"];
// if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
//     echo $_SERVER['REMOTE_ADDR'] . " is not a valid IP<br />";
//     die("You may not punch in yet.");
// }
date_default_timezone_set("Africa/Johannesburg");
function makeContact()
{
    $servername = "50.87.249.167";
    $username = "staalbur_RNSAtt";
    $password = "4jnbErrw?-zV";
    $dbname = "staalbur_RNSAttendacne";
    // $servername = "localhost";
    // $username = "staalbur_RNSAtt";
    // $password = "4jnbErrw?-zV";
    // $dbname = "staalbur_rnsattendacne";
    $conn = new mysqli($servername, $username, $password, $dbname, null, null);
    $conn->query("SET time_zone = 'Africa/Johannesburg'");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
function setMonth()
{
    $dt = new DateTime();
    return $dt->format('F Y');
}
function getData($sqlString)
{
    $conn = makeContact();
    $data = $conn->query($sqlString);
    $rows = $conn->affected_rows;
    return [$rows,$data];
}

function punch($UserID, $punching)
{
    $sqlPunch = "INSERT INTO attendance(emp_id, emp_punched) VALUES (" . $UserID . ", '" . $punching . "')";
    getData($sqlPunch);
    return $punching;
}

function buildTotalString($i1, $i2)
{
    $output = $i1 . " Hrs ";
    if (round($i2) != 0) {
        $output = $output . $i2 . " min";
    }
    return $output;
}

function time_to_interval($time)
{
    $parts = explode(':', $time);
    return new DateInterval('PT' . $parts[0] . 'H' . $parts[1] . 'M' . $parts[2] . 'S');
}

function add_intervals($a, $b)
{
    $zerodate = new DateTime('0000-01-01 00:00:00');
    $dt = clone $zerodate;
    $dt->add($a);
    $dt->add($b);
    return $zerodate->diff($dt);
}
function zerodate()
{
    $zerodate = new DateTime('0000-01-01 00:00:00');
    return $zerodate;
}
function padZero($val)
{
    $value = strval($val);
    if ($val < 10) {
        $value = "0" . $val;
    }
    return $value;
}
function employeeFullName($UserID){
    $result = getData("SELECT * FROM employee where id = " . $UserID);
    $FullName = "";
    if ($result[0] == 1) {
        while ($row = $result[1]->fetch_assoc()) {
            $FullName = $row["emp_name"] . " " . $row["emp_surname"];
        }
    } else {
        $FullName = "___________________________";
    }
    return $FullName;
}
function weekDay($date){
    return $date->format('l');
}
function decodeLeave($leaveType){
    $output = "Undefined";
    switch ($leaveType){
        case "STAT":
            $output = "Normal Leave";
            break;
        case "FAM":
            $output = "Family Responsibility Leave";
            break;
        case "MAT":
            $output = "Maternity Leave";
            break;
        case "MOV":
            $output = "Moving House";
            break;
        case "REL":
            $output = "Religious Holiday";
            break;
        case "SCK":
            $output = "Sick Leave";
            break;
        case "NAT":
            $output = "Sport - National";
            break;
        case "PRV":
            $output = "Sport - Provincail";
            break;
        case "STY":
            $output = "Study Leave";
            break;
        case "UPD":
            $output = "Unpaid Leave";
            break;
        case "TRA":
            $output = "Travel Leave";
            break;
    }
    return $output;
}

require "render-register.php";
 