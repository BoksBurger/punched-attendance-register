<?php
require "routines.php";
if (isset($_POST["loadAttendance"])) {
        echo loadAttendance($_POST["date"], $_POST["emp_id"], false);
}
if (isset($_POST["saveAttendance"])) {
        $emp_id = $_POST["emp_id"];
        $punch = 'in';
        $date = trim($_POST["date"]);
        $time = trim($_POST["startTime"]);
        $time_id = trim($_POST["startTime_id"]);
        saveAttendance($time_id,$emp_id,$date,$time,$punch);
        $punch = 'lunchon';
        $time = trim($_POST["lunchStart"]);
        $time_id = trim($_POST["lunchStart_id"]);
        saveAttendance($time_id,$emp_id,$date,$time,$punch);
        $punch = 'lunchoff';
        $time = trim($_POST["lunchEnd"]);
        $time_id = trim($_POST["lunchEnd_id"]);
        saveAttendance($time_id,$emp_id,$date,$time,$punch);
        $punch = 'out';
        $time = trim($_POST["stopTime"]);
        $time_id = trim($_POST["stopTime_id"]);
        saveAttendance($time_id,$emp_id,$date,$time,$punch);
        echo loadAttendance($date,$emp_id, true) ;
}
function loadAttendance($date,$emp_id, $isUpdated){
        $in = "";
        $in_id = "";
        $lunchon = "";
        $lunchon_id = "";
        $lunchoff = "";
        $lunchoff_id = "";
        $out = "";
        $out_id = "";
        $sqlString = "select * from staalbur_RNSAttendacne.attendance where emp_id = " . $emp_id . " and date_format (date_time, '%d %M %Y') = '" . $date . "'";
        $data = getData($sqlString)[1];
        while ($row = $data->fetch_assoc()) {
                switch ($row["emp_punched"]) {
                        case 'in':
                                {
                                        $dtIn = new DateTime($row["date_time"]);
                                        $in = $dtIn->format('H:i:s');
                                        $in_id = $row["id"];
                                        break;
                                }
                        case 'lunchon':
                                {
                                        $dtLunchon = new DateTime($row["date_time"]);
                                        $lunchon = $dtLunchon->format('H:i:s');
                                        $lunchon_id = $row["id"];
                                        break;
                                }
                        case 'lunchoff':
                                {
                                        $dtLunchoff = new DateTime($row["date_time"]);
                                        $lunchoff = $dtLunchoff->format('H:i:s');
                                        $lunchoff_id = $row["id"];
                                        break;
                                }
                        case 'out':
                                {
                                        $dtOut = new DateTime($row["date_time"]);
                                        $out = $dtOut->format('H:i:s');
                                        $out_id = $row["id"];
                                        break;
                                }
                }
        }
        $conn = makeContact();
        $dtDate = new DateTime($date);
        $stringDate = $dtDate->format('F Y');
        $register = null;
        if ($isUpdated){
                if(isset($_POST["skipRender"])){
                        //Not doing anything here. Just Chilling.
                } else{
                        $register = buildRegister($emp_id, $stringDate);     
                }
        }
        $output = json_encode(array("id"=>$emp_id,"date"=>$date,"start"=>$in,"start_id"=>$in_id,"on"=>$lunchon,"on_id"=>$lunchon_id,"off"=>$lunchoff,"off_id"=>$lunchoff_id,"end"=>$out,"end_id"=>$out_id,"register" => $register),JSON_UNESCAPED_SLASHES);
        return $output;
};
function formatDate($date, $time)
{
        $dt = new DateTime($date);
        $arrTime = explode(":", $time);
        $dt = $dt->setTime(intval($arrTime[0]), intval($arrTime[1]), intval($arrTime[2]));
        return $dt->format('Y-m-d H:i:s');
}
function buildSql($action, $emp_id, $punch, $timestamp, $record_id)
{
        $sql = "";
        switch ($action) {
                case 'insert':
                        {
                                $sql = "insert into staalbur_RNSAttendacne.attendance (emp_id,emp_punched,date_time) values (" . $emp_id . ",'" . $punch . "','" . $timestamp . "')";
                                break;
                        }
                case 'update':
                        {
                                $sql = "update staalbur_RNSAttendacne.attendance set date_time = '" . $timestamp . "' where id = " .  $record_id;
                                break;
                        }
                case 'delete':
                        {
                                $sql = "delete from staalbur_RNSAttendacne.attendance where id = " .  $record_id;
                                break;
                        }
        }
        return $sql;
}
function saveAttendance($id, $emp, $date, $time, $punch)
{
        if ($id != "") {
                if ($time != "") {
                        //update
                        $sql = buildSql("update", $emp, $punch, formatDate($date, $time), $id);
                        getData($sql);
                } else {
                        //delete
                        $sql = buildSql("delete", null, null, null, $id);
                        getData($sql);
                }
        } else if ($time != "") {
                //insert
                $sql = buildSql("insert", $emp, $punch, formatDate($date, $time), null);
                getData($sql);
        }
};
 