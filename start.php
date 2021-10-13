<?php
session_start();
require "routines.php";
if (isset($_GET["id"])) {
    $_SESSION["UserID"] = $_GET["id"];
    $_SESSION["currentMonth"] = setMonth();
    header("Location: " . "http://$_SERVER[HTTP_HOST]");
    exit();
}
if (!isset($_SESSION["UserID"])) {
    die("Please rescan your QR code or tap your NFC enabled device.");
}

$conn = makeContact();
$link = "http://$_SERVER[HTTP_HOST]";
$UserID = $_SESSION["UserID"];

if (isset($_POST["month"])) {
    $_SESSION["currentMonth"] = $_POST["month"];
    $register = array();
    $register["register"] = buildRegister($_POST["UserID"], $_POST["month"]);
    echo json_encode($register, JSON_UNESCAPED_SLASHES);
}

if (isset($_POST["punch"])) {
    echo startPunching($_POST["UserID"], $_POST["punch"]);
}

//Greet employee...
function greetEmployee($UserID)
{
    $result = getData("SELECT * FROM employee where id = " . $UserID)[1];
    $data = array();
    if ($result->num_rows == 1) {
        while ($row = $result->fetch_assoc()) {
            $data['greeting'] = "<h2>Hello " . $row["emp_name"] . "</h2>";
            $data['date'] = "<strong>Date:</strong> " . date("l, d M Y") . "<br />";
            $data['time'] =  "<strong>Time:</strong> " . date("H:i:s") . "<br />";
        }
    } else {
        echo "Oops! Something went wrong.";
    }
    $data = json_encode($data, JSON_UNESCAPED_SLASHES);
    return $data;
}
//Start punching...
function startPunching($UserID, $punch)
{
    $toDay = new DateTime();
    $arrayStart = array();
    if ($toDay != 'Saturday' and $toDay != 'Sunday') {
        $punchedIn = getData("SELECT date(date_time) as dt, time(date_time) as tm, emp_punched FROM attendance WHERE emp_id = " . $UserID . " and date(date_time) = date(now()) order by date_time desc");
        if ($punchedIn[0] == 0) {
            punch($UserID, 'in');
            $arrayStart['message'] = 'You have punched in. Enjoy your day.';
            $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='lunchon' type='button'>Take lunch break</button><button class='btn btn-secondary' id='tjaila' type='button'>Punch out</button><button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>";
        } else {
            while ($row = $punchedIn[1]->fetch_assoc()) {
                switch ($row["emp_punched"]) {
                    case 'out':
                        $arrayStart['message'] = "Thank you for your service today.";
                        break 2;
                    case 'lunchoff':
                        $arrayStart['message'] = "Back to work.";
                        $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='tjaila' type='button'>Punch out</button><button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>";
                        break 2;
                    case 'lunchon':
                        $arrayStart['message'] = "Enjoy your break.";
                        $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='lunchoff' type='button'>End lunch break</button><button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>";
                        break 2;
                    case 'in':
                        $arrayStart['message'] = "You have punced in. Enjoy your day.";
                        $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='lunchon' type='button'>Take lunch break</button><button class='btn btn-secondary' id='tjaila' type='button'>Punch out</button><button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>";
                        break 2;
                }
            }
        }
    } else {
        $arrayStart['message'] = "<h2>It's weekend. You should be out having fun!</h2><br />";
    }

    if ($punch  == "lunchon") {
        punch($UserID, $punch);
        $arrayStart['message'] = "Enjoy your break.";
        $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='lunchoff' type='button'>End lunch break</button><button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>";
    }
    if ($punch  == "lunchoff") {
        punch($UserID, $punch);
        $arrayStart['message'] = "Back to work.";
        $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='tjaila' type='button'>Punch out</button><button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>";
    }
    if ($punch  == "tjaila") {
        punch($UserID, "out");
        $arrayStart['message'] = "Thank you for your service today.";
        $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>";
    }
    if(!isset($arrayStart['punchButtons'])){
        $arrayStart['message'] = "You are done for today.";
        $arrayStart['punchButtons'] = "<button class='btn btn-secondary' id='bookLeave' type='button'>Book Leave</button><button class='btn btn-secondary' id='btnColor' type='button'>Change Color</button><button class='btn btn-secondary' id='btnPrint' type='button'>Print</button>"; 
    }
    $arrayStart["register"] = buildRegister($UserID, $_SESSION["currentMonth"]);
    $arrayStart = json_encode(array("message" => $arrayStart['message'], "punchButtons" => $arrayStart['punchButtons'], "register" => $arrayStart['register']), JSON_UNESCAPED_SLASHES);
    return $arrayStart;
}
