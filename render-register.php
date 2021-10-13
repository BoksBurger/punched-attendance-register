<?php
function leaveDays($UserID, $month){
    $sqlLeave = "select * from staalbur_RNSAttendacne.emp_leave where emp_id = " . $UserID;
    $leaveData = getData($sqlLeave)[1];
    $arrLeave = array();
    while ($row = $leaveData->fetch_assoc()){
        $leaveFrom = new DateTime($row["leavefrom"]);
        $leaveTo = new DateTime($row["leaveto"]);
        $leaveType = $row["leavetype"];
        $leaveDescription = decodeLeave($leaveType);
        $daysLeave = $leaveFrom->diff($leaveTo)->days;
        array_push($arrLeave, [$leaveFrom->format('d-m-Y'),$leaveType, $leaveDescription, $row["id"]]);
        for($i=0; $i < $daysLeave; $i++){
            $rangeDate = $leaveFrom->add(new DateInterval("P1D"));
            $range = clone($rangeDate);
            array_push($arrLeave, [$range->format('d-m-Y'),$leaveType, $leaveDescription, $row["id"]]);
        }
    }
    //Public Holidays 
    $sqlPublicHoliday = "SELECT * FROM public_holidays where date_format(date,'%M %Y') = '" . $month . "'";
    $publicHolidayData = getData($sqlPublicHoliday)[1];
    while($row = $publicHolidayData->fetch_assoc()){
        $holiday = new DateTime($row["date"]);
        array_push($arrLeave, [$holiday->format('d-m-Y'),"PUB", $row["holiday"], $row["id"]]);
    }
    function compare_dates($a, $b)
    {
        return strnatcmp($a[0], $b[0]);
    }
    usort($arrLeave, 'compare_dates');
    return $arrLeave;
}

function checkLeave($leaveDays, $runningDate, $dayNumber, $day, $isFuture, $registerHTML, $TotalDaysLeave, $TotalDaysAbcent){
    $data = array();
    $leaveCSS = "leaveHole hvr-fade";
    if($isFuture){
        $hasRow = false;
        foreach($leaveDays as &$arr){
            $leaveType = $arr[2];
            if(array_search($runningDate, $arr) > -1){
                if(!$hasRow){
                    if($arr[1]!="PUB"){
                        $TotalDaysLeave++;
                    } else {
                        $leaveCSS = "pubHole";
                    }
                    $registerHTML = $registerHTML . "<tr id='" . $arr[3] . "' class ='". $leaveCSS ."' ><td class='center-text'>" .  $dayNumber . "</td><td>" . $day . "</td><td class='center-text'>". $leaveType ."</td><td colspan='6'>&nbsp</td></tr>";
                    $hasRow = true;
                }
            }
        }
        if(!$hasRow){
            $registerHTML = $registerHTML . "<tr id='" . $dayNumber . "' class ='grayHole hvr-fade' ><td class='center-text'>" .  $dayNumber . "</td><td>" . $day . "</td><td colspan='7'></td></tr>";
            $hasRow = true;
        }
    } else {
        $hasRow = false;
        foreach($leaveDays as &$arr){
            $leaveType = $arr[2];
            if(array_search($runningDate, $arr) > -1){
                if(!$hasRow){
                    if($arr[1]!="PUB"){
                            $TotalDaysLeave++;
                    } else {
                        $leaveCSS = "pubHole";
                    }
                    $registerHTML = $registerHTML . "<tr id='" . $arr[3] . "' class ='".  $leaveCSS ." ' ><td class='center-text'>" .  $dayNumber . "</td><td>" . $day . "</td><td class='center-text'>". $leaveType ."</td><td colspan='6'>&nbsp</td></tr>";
                    $hasRow = true;
                }
            }
        }
        if(!$hasRow){
            $registerHTML = $registerHTML . "<tr id='" . $dayNumber . "' class ='blackHole hvr-fade' ><td class='center-text'>" .  $dayNumber . "</td><td>" . $day . "</td><td class='center-text'>AWOL</td><td colspan='6'>&nbsp</td></tr>";
            $TotalDaysAbcent++;
            $hasRow = true;
        }
    }
    $data["registerHTML"] = $registerHTML;
    $data["TotalDaysLeave"] = $TotalDaysLeave;
    $data["TotalDaysAbcent"] = $TotalDaysAbcent;
    return $data;
}

function buildRegister($UserID, $month){
    $conn = makeContact();
    $present = new DateTime();
    $present->setTime(0,0,0);
    $registerHTML = "";
    $sqlRegister = "select id, emp_id, emp_punched, date_format(date_time, '%d') as theDayNumber,
    date_format(date_time, '%W') as theDay,
    date_format(date_time, '%H:%i') as theTime, date_format(date_time, '%d-%m-%Y') as theDate, date_time
    from staalbur_RNSAttendacne.attendance
    where emp_id = " . $UserID . "
    and date_format(date_time, '%M %Y') = '" . $month . "'
    order by date_time asc;";
    $Register = $conn->query($sqlRegister);
    $registerHTML = $registerHTML . "<table cellspacing='0' cellpadding='2px' class='tableRegister'>
    <tr class='tableHead'><td class='center-text'>Date</td><td>Day</td><td class='center-text'>Time In</td><td class='center-text'>Lunch on</td><td class='center-text'>Lunch off</td><td class='center-text'>Time out</td><td class='center-text'>Lunch Taken</td><td class='center-text'>Time Worked</td><td class='center-text'>Hours Present</td></tr>";
    $fullmonth = new DateTime($month);
    $TotalDaysWorked = 0;
    $TotalDaysLeave = 0;
    $TotalDaysAbcent = 0;
    $TotalLunchTaken = new DateInterval('PT00H00M00S');
    $TotalHoursWorked = new DateInterval('PT00H00M00S');
    $TotalHoursPresent = new DateInterval('PT00H00M00S');
    $theDayNumber = 0;
    $leaveDays = leaveDays($UserID,$month);
    $row = mysqli_fetch_row($Register);
    for ($i = 0; $i < $fullmonth->format('t'); $i++) {
        $columnCounter = 0;
        $day = Date("l", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
        //$date = Date("d", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
        $theDayNumber++;
        $theDayNumber = padZero($theDayNumber);
        if ($day != 'Saturday' and $day != 'Sunday') {
            if (padZero($i + 1) != strval($row[3])) {
                for ($ii = $i + 1; $ii < intval($row[3]); $ii++) {
                    $day = Date("l", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
                    $dayNumber = Date("d", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
                    $runningDate = Date("d-m-Y", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
                    $currentDate = new DateTime($runningDate);
                    if ($day != 'Saturday' and $day != 'Sunday') {
                        $data = checkLeave($leaveDays, $runningDate, $dayNumber, $day, false,$registerHTML, $TotalDaysLeave, $TotalDaysAbcent);
                        if (isset($data["TotalDaysLeave"])){
                            $TotalDaysLeave = $data["TotalDaysLeave"];
                        }
                        if (isset($data["TotalDaysAbcent"])){
                            $TotalDaysAbcent = $data["TotalDaysAbcent"];
                        }
                        if (isset($data["registerHTML"])){
                            $registerHTML = $data["registerHTML"];
                        }
                    }
                    $i++;
                }
            }
            if (empty($row[0])) {
                $day = Date("l", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
                $dayNumber = Date("d", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
                $runningDate = Date("d-m-Y", mktime(0, 0, 0, $fullmonth->format('m'), $i + 1, $fullmonth->format('Y')));
                $currentDate = new DateTime($runningDate);
                $data = checkLeave($leaveDays, $runningDate, $dayNumber, $day, true,$registerHTML, $TotalDaysLeave, $TotalDaysAbcent);
                if (isset($data["TotalDaysLeave"])){
                    $TotalDaysLeave = $data["TotalDaysLeave"];
                }
                if (isset($data["TotalDaysAbcent"])){
                    $TotalDaysAbcent = $data["TotalDaysAbcent"];
                }
                if (isset($data["registerHTML"])){
                    $registerHTML = $data["registerHTML"];
                }
            }
            while ($row[3] == padZero($i + 1)) {
                switch ($row[2]) {
                    case 'in':
                        if (padZero($i + 1) == strval($row[3])) {
                            $TotalDaysWorked++;
                            $theDayNumber = $row[3];
                            if($present->format('d F Y') === $theDayNumber . " " . $month){
                                $hole = "punchHole hvr-fade";
                            }
                            else{
                                $hole = "grayHole hvr-fade";
                            }
                            $registerHTML = $registerHTML . "<tr id='". padZero(intval($row[3])) ."' class='". $hole ."'><td class='center-text'>" . $row[3] . "</td><td>" . $row[4] . "</td><td class='center-text'>" . $row[5] . "</td>";
                            $columnCounter = 1;
                            $takeLunch = false;
                            $StartTime = new DateTime($row[7]);
                            $LunchTimeOn = 0;
                            $LunchTimeOff = 0;
                            $StopTime = 0;
                            break 1;
                        } else {
                            break 1;
                        }
                    case 'lunchon':
                        {
                            $registerHTML = $registerHTML . "<td class='center-text'>" . $row[5] . "</td>";
                            $columnCounter = 2;
                            $takeLunch = true;
                            $LunchTimeOn = new DateTime($row[7]);
                            break 1;
                        }
                    case 'lunchoff':
                        {
                            $registerHTML = $registerHTML . "<td class='center-text'>" . $row[5] . "</td>";
                            $columnCounter = 3;
                            $LunchTimeOff = new DateTime($row[7]);
                            $LunchTaken = date_diff($LunchTimeOff, $LunchTimeOn, false);
                            break 1;
                        }
                    case 'out':
                        {

                            if (!$takeLunch) {
                                $registerHTML = $registerHTML . "<td class='center-text'>-</td><td class='center-text'>-</td><td class='center-text'>" . $row[5] . "</td>";
                                $columnCounter = 4;
                                $LunchTimeOn = zerodate();
                                $LunchTimeOff = zerodate();
                            } else {
                                $registerHTML = $registerHTML . "<td class='center-text'>" . $row[5] . "</td>";
                                $columnCounter = 4;
                            }
                            $StopTime = new DateTime($row[7]);
                            $LunchTaken = $LunchTimeOff->diff($LunchTimeOn);
                            $TimePresent = $StopTime->diff($StartTime);
                            $dtLunchBreak = new DateTime($LunchTaken->format('%h:%i'));
                            $dtTimePresent = new DateTime($TimePresent->format('%h:%i'));
                            $HoursWorked = $dtTimePresent->diff($dtLunchBreak);
                            $TotalLunchTaken = add_intervals($TotalLunchTaken, $LunchTaken);
                            $TotalHoursWorked = add_intervals($TotalHoursWorked, $HoursWorked);
                            $TotalHoursPresent = add_intervals($TotalHoursPresent, $TimePresent);
                            $registerHTML = $registerHTML . "<td class='center-text'>" . $LunchTaken->format('%h:%i') . "</td>";
                            $registerHTML = $registerHTML . "<td class='center-text'>" . $HoursWorked->format('%h:%i') . "</td>";
                            $registerHTML = $registerHTML . "<td class='center-text'>" . $TimePresent->format('%h:%i') . "</td></tr>";
                        }
                }
                
                $row = mysqli_fetch_row($Register);
                //Change today...
            }
            if($present->format('d F Y') === $theDayNumber . " " . $month){
                switch (strval($columnCounter)){ 
                    case "1":{
                        

                            $registerHTML = $registerHTML . "<td class='punchHole' colspan='".  strval(7 - $columnCounter) . "'></td> </tr>";
                            break;
                        }
                    case "2":{
                        

                            $registerHTML = $registerHTML . "<td class='punchHole' colspan='".  strval(7 - $columnCounter) . "'></td></tr>";
                            break;
                        }
                    case "3":{
                        

                            $registerHTML = $registerHTML . "<td class='punchHole' colspan='".  strval(7 - $columnCounter) . "'></td></tr>";
                            break;
                                }
                }    
            }    
        }    
    }    
    $TLT = (round($TotalLunchTaken->format('%d')) * 24) + round($TotalLunchTaken->format('%h'));
    $THW = (round($TotalHoursWorked->format('%d')) * 24) + round($TotalHoursWorked->format('%h'));
    $THP = (round($TotalHoursPresent->format('%d')) * 24) + round($TotalHoursPresent->format('%h'));
    $worked = "<td colspan='6'>Days worked: " . $TotalDaysWorked++;
    if($TotalDaysLeave > 0){
        $worked = $worked . " | Leave Taken: " . $TotalDaysLeave;
    }
    if($TotalDaysAbcent > 0){
        $worked = $worked . " | AWOL: " . $TotalDaysAbcent;
    }
    $registerHTML = $registerHTML . "<tr class='tableFooter'>" . $worked . " </td>
        <td class='center-text'>" . buildTotalString($TLT, $TotalLunchTaken->format('%i')) . "</td>
        <td class='center-text'>" . buildTotalString($THW, $TotalHoursWorked->format('%i')) . "</td>
        <td class='center-text'>" . buildTotalString($THP, $TotalHoursPresent->format('%i')) . "</td>
        </tr>";
    $registerHTML = $registerHTML . "</table>";
    return $registerHTML;
}
 