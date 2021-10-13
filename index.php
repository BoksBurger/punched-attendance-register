<?php
require "start.php";
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>RNS Attendance Register</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="js/jquery-ui/themes/smoothness/jquery-ui.min.css">
    <link rel="stylesheet" href="js/monthpicker/MonthPicker.css">
    <link rel="stylesheet" href="js/timeentry/jquery.timeentry.css">
    <link rel="stylesheet" href="js/bxslider/jquery.bxslider.min.css">
    <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
</head>

<body>
    <div id="wait">
        <img src="img/spinner-blue-circle.gif" width="32" height="32">
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <h1>Attendance Register</h1>
                </div>
                <div class="print-header">
                    <h2>Employee: <?php echo employeeFullName($UserID); ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="greet-message" class="col-md-8">
                <div id="attendanceHeader" class="col-md-12">Greeting</div>
                <div id="attendanceSubHeader" class="col-md-12">Message</div>
            </div>
            <div id="quote-container" class="col-md-4">
                <div id="quote-block">
                    <div class="quote-header">
                        <h1>Quotes for a <?php echo weekDay(new DateTime()); ?></h1>
                    </div>
                    <div class="qSlider">

                    </div>
                    <span id="next-quote" style="display:block; cursor:pointer; width:100%; text-align:right;"></span>
                </div>
            </div>
            <script type="text/javascript">
                var qData = <?php require "quotes.php" ?>;
                var qHTML = "";
                if (qData.length > 0) {
                    for (i = 0; i < qData.length; i++) {
                        qHTML = qHTML + "<div class='quote-body'>";
                        qHTML = qHTML + "<p>" + qData[i].quote[0] + "</p>"
                        qHTML = qHTML + "<h2>" + qData[i].author[0] + "</h2>"
                        qHTML = qHTML + "</div>";
                    }
                    $(".qSlider").html(qHTML);
                }
            </script>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex flex-row-reverse">
                    <div id="punchButtons" class="btn-group btn-group-lg" role="group">
                    </div>
                </div>
                <div id="card-176703">
                    <div class="card">
                        <div class="card-header">
                            <a class="card-link" data-toggle="collapse" data-parent="#card-176703" href="#card-element-909567">Toggle Register Visibility</a>
                        </div>
                        <div id="card-element-909567" class="collapse show">
                            <div class="card-body">
                                <div id="monthPicker">
                                    <input class="" type="text" id="selectedMonth" value="" readonly="true">
                                </div>
                                <div id='register'></div>
                                <div id='signRegister'>
                                    <hr />
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td colspan="2" valign="top">I, <?php echo employeeFullName($UserID) ?>, hereby declare that all the information on this register is an acurate recording of attendance and time worked during the month of <?php echo $_SESSION["currentMonth"] ?>.</td>
                                        </tr>
                                        <tr>
                                            <td align="right" valign="bottom"><strong>Employee Signature:</strong></td>
                                            <td width="250" height="60" style="border-bottom:2px solid black">&nbsp;</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h5 class="card-header">
                        Summary
                    </h5>
                    <div class="card-body">
                        <p class="card-text">
                            Card content
                        </p>
                    </div>
                    <div class="card-footer">
                        Card footer
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <h5 class="card-header">
                        Office Use
                    </h5>
                    <div class="card-body">
                        <p class="card-text">
                            Card content
                        </p>
                    </div>
                    <div class="card-footer">
                        Card footer
                    </div>
                </div>
            </div>
        </div> -->
        <div class="row" id="footerTag">
            <div class="col-md-12">
                RNS Time &amp Attendance System 2019
            </div>
        </div>
    </div>
    <div id="book-Leave" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="bookForm" method="POST" autocomplete="off">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Book Leave</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Leave Start:<br />
                            <input class="form-control" type="text" id="fromDate" name="fromDate" value="">
                        </p>
                        <p>Leave End:<br />
                            <input class="form-control" type="text" id="toDate" name="toDate" value="">
                        </p>
                        <p>Type of leave:<br />
                            <select class="form-control" id="leaveType" name="leaveType">
                                <option value="STAT">Normal leave</option>
                                <option value="FAM">Family Responsibility Leave</option>
                                <option value="MAT">Maternity Leave</option>
                                <option value="MOV">Moving House</option>
                                <option value="REL">Religious Holiday</option>
                                <option value="SCK">Sick Leave</option>
                                <option value="NAT">Sport - National</option>
                                <option value="PRV">Sport - Provincail</option>
                                <option value="STY">Study Leave</option>
                                <option value="UPD">Unpaid Leave</option>
                                <option value="TRA">Travel Leave</option>
                            </select>
                        </p>
                        <input type="hidden" id="UserID" name="UserID" value='<?php echo $_SESSION["UserID"] ?>'>
                        <input type="hidden" id="currentMonth" name="currentMonth" value='<?php echo $_SESSION["currentMonth"] ?>'>
                        <input type="hidden" id="leaveID" name="leaveID" value="0">
                        <div id="bookingResult"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" id="btnBookLeave" class="btn btn-primary" value="Book Leave">Book Leave</button>
                        <button type="button" id="btnCancelLeave" class="btn btn-primary" value="Save">Cancel</button>
                        <button type="button" id="btnSaveLeave" class="btn btn-primary" value="Book Leave">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-Attendance" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="bookForm" method="POST" autocomplete="off">
                    <div id="edit-Attendance-Heading" class="modal-header">
                        <h5 id="edit-Attendance-Title" class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Start Time:<br />
                            <input class="form-control TimeEntry" type="text" id="startTime" name="startTime" value="">
                        </p>
                        <p>Lunch Start:<br />
                            <input class="form-control TimeEntry" type="text" id="lunchStart" name="lunchStart" value="">
                        </p>
                        <p>Lunch End:<br />
                            <input class="form-control TimeEntry" type="text" id="lunchEnd" name="lunchEnd" value="">
                        </p>
                        <p>Stop Time:<br />
                            <input class="form-control TimeEntry" type="text" id="stopTime" name="stopTime" value="">
                        </p>
                        <input type="hidden" id="UserID" name="UserID" value='<?php echo $_SESSION["UserID"] ?>'>
                        <div id="attendanceMessage"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-secondary" id="btnClear" value="Save">Clear</button>
                        <button type="button" class="btn btn-primary burger" id="btnSave" value="Save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#selectedMonth').val('<?php echo $_SESSION["currentMonth"] ?>');
            var dataGreeting = <?php echo greetEmployee($UserID) ?>;
            $('#attendanceHeader').html(dataGreeting.greeting + dataGreeting.date + dataGreeting.time);
            var dataMessage = <?php echo startPunching($UserID, 'in'); ?>;
            $('#attendanceSubHeader').html(dataMessage.message);
            $('#punchButtons').html(dataMessage.punchButtons);
            $('#register').html(dataMessage.register);
            $('.qSlider').bxSlider({
                pager: false,
                controls: true,
                adaptiveHeight: true,
                nextSelector: '#next-quote',
                nextText: 'Next quote...'

            });
        });
    </script>
    <script type="text/javascript" src="/js/bootstrap/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="/js/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/js/monthpicker/MonthPicker.js"></script>
    <script type="text/javascript" src="js/timeentry/jquery.mousewheel.min.js"></script>
    <script type="text/javascript" src="js/timeentry/jquery.plugin.js"></script>
    <script type="text/javascript" src="/js/timeentry/jquery.timeentry.min.js"></script>
    <script type="text/javascript" src="/js/bxslider/jquery.bxslider.min.js"></script>
    <script type="text/javascript" src="/js/attendance.js"></script>
</body>

</html> 