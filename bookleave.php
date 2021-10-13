<?php 
require "routines.php";

function bookLeave($id, $from, $to, $type, $conn)
{
    $bookLeave = "insert into staalbur_RNSAttendacne.emp_leave 
        (emp_id, leavefrom, leaveto, leavetype) VALUES (" . $id . ", '" . $from . "', '" . $to . "', '" . $type . "')";
    $conn->query($bookLeave);
    $dtDate = new DateTime($_POST["currentMonth"]);
    $stringDate = $dtDate->format('F Y');
    $register = buildRegister($id, $stringDate);
    $message = "Leave booked from " . $from . " to " . $to;
    $output = json_encode(array("message"=>$message,"register" => $register),JSON_UNESCAPED_SLASHES);
    return $output;
}
if (isset($_POST["fromDate"])) {
    $fromDate = new DateTime($_POST['fromDate']);
    $toDate = new DateTime($_POST['toDate']);
    $t = $_POST['leaveType'];
    $conn = makeContact();
    echo bookLeave(intval($_POST['UserID']), $fromDate->format('Y-m-d'), $toDate->format('Y-m-d'), $_POST['leaveType'], $conn);
}
if (isset($_POST["action"])){
    $action = $_POST["action"];
    $id = $_POST["id"];
    $UserID = $_POST["UserID"];
    $currentMonth = $_POST["currentMonth"];
    $output = array();
    if($action == "open"){
        $sql = "SELECT * FROM emp_leave where id = " . $id;
        $data = getData($sql)[1];
        while($row = $data->fetch_assoc()){
             $output["leaveFrom"] = $row["leavefrom"];
            $output["leaveTo"] = $row["leaveto"];
            $output["leaveType"] = $row["leavetype"];
        }
        echo json_encode($output);
    }
    if($action == "delete"){
        $sql = "delete from emp_leave where id = " . $id;
        $data = getData($sql)[0];
        $register = buildRegister($UserID, $currentMonth);
        $output["message"] = "Leave Cancelled";
        $output["register"] = $register;
        echo json_encode($output,JSON_UNESCAPED_SLASHES);
    }
    if($action == "update"){
        $leaveFrom = $_POST["leaveFrom"];
        $leaveTo = $_POST["leaveTo"];
        $leaveType = $_POST["leaveType"];
        $sql = "update emp_leave set leavefrom = '". $leaveFrom ."', leaveto = '". $leaveTo ."', leavetype = '". $leaveType ."' where id = " . $id;
        $data = getData($sql)[0];
        $register = buildRegister($UserID, $currentMonth);
        $output["message"] = "Leave Saved";
        $output["register"] = $register;
        echo json_encode($output,JSON_UNESCAPED_SLASHES);
    }
    
}
 
