$(document).ready(function() {
	behavior();
	saveStuff();
	$("body").css("background-color", "#" + setRandomColor());
	$("#quote-block").css("background-color", "#" + setRandomColor());
});

function setRandomColor() {
	var randomColor = (function lol(m, s, c) {
		return s[m.floor(m.random() * s.length)] + (c && lol(m, s, c - 1));
	})(Math, "ABCDEEFF", 4);
	//console.log(randomColor);
	return randomColor;
}

function reflowBehavior() {
	var selection = $(
		"#btnPrint, #selectedMonth, #lunchon.btn, #lunchoff.btn, #tjaila.btn, #btnSave, #btnBookLeave"
	);
	selection.unbind("click");
	$("*").unbind("blur");
  saveStuff();
  behavior();
}

function behavior() {
	$("#btnPrint").on("click", function(e) {
		e.preventDefault();
		reflowBehavior();
		window.print();
	});
	$("#btnColor").on("click", function(e) {
		$("body").css("background-color", "#" + setRandomColor());
		$("#quote-block").css("background-color", "#" + setRandomColor());
		e.preventDefault();
	});
	$("#selectedMonth").MonthPicker({
		MonthFormat: "MM yy",
		ShowIcon: false
	});
	$("#startTime").timeEntry({
		show24Hours: true,
		showSeconds: true,
		useMouseWheel: true,
		defaultTime: "08:00:00",
		initialField: 1,
		tabToExit: true,
		spinnerImage: "/js/timeentry/spinnerBlue.png",
		spinnerBigImage: "/js/timeentry/spinnerBlueBig.png",
		spinnerBigSize: [40, 40, 16]
	});
	$("#lunchStart").timeEntry({
		show24Hours: true,
		showSeconds: true,
		useMouseWheel: true,
		defaultTime: "13:00:00",
		initialField: 1,
		tabToExit: true,
		spinnerImage: "/js/timeentry/spinnerBlue.png",
		spinnerBigImage: "/js/timeentry/spinnerBlueBig.png",
		spinnerBigSize: [40, 40, 16]
	});
	$("#lunchEnd").timeEntry({
		show24Hours: true,
		showSeconds: true,
		useMouseWheel: true,
		defaultTime: "14:00:00",
		initialField: 1,
		tabToExit: true,
		spinnerImage: "/js/timeentry/spinnerBlue.png",
		spinnerBigImage: "/js/timeentry/spinnerBlueBig.png",
		spinnerBigSize: [40, 40, 16]
	});
	$("#stopTime").timeEntry({
		show24Hours: true,
		showSeconds: true,
		useMouseWheel: true,
		defaultTime: "16:30:00",
		initialField: 1,
		tabToExit: true,
		spinnerImage: "/js/timeentry/spinnerBlue.png",
		spinnerBigImage: "/js/timeentry/spinnerBlueBig.png",
		spinnerBigSize: [40, 40, 16]
	});
	$("#selectedMonth").on("blur", function() {
		$("#wait").css("visibility", "unset");
		$.post("/start.php", {
			month: $("#selectedMonth").val(),
			UserID: $("#UserID").val()
		}).done(function(data) {
			var jData = JSON.parse(data);
			$("#register").html(jData.register);
			$("#wait").css("visibility", "hidden");
			reflowBehavior();
		});
	});
	$("#fromDate").datepicker({
		dateFormat: "dd MM yy"
	});
	$("#toDate").datepicker({
		dateFormat: "dd MM yy"
	});
	$("#bookLeave").on("click", function(e) {
		$("#btnSaveLeave").css("display", "none");
		$("#btnCancelLeave").css("display", "none");
		$("#bookingResult").css("display", "none");
		$("#btnBookLeave").css("display", "block");
		$("#book-Leave").modal("show");
		e.preventDefault();
	});
	$("#book-Leave").on("hidden.bs.modal", function() {
		$("#fromDate").val("");
		$("#toDate").val("");
		$("#leaveType").val("STAT");
	});
	$(".leaveHole").on("click", function(e) {
		$("#wait").css("visibility", "unset");
		$("#leaveID").val(this.id);
		$("#btnSaveLeave").css("display", "block");
		$("#btnCancelLeave").css("display", "block");
		$("#btnBookLeave").css("display", "none");
		$("#book-Leave").modal("show");
		$.post("/bookleave.php", {
			action: "open",
			id: this.id,
			UserID: $("#UserID").val(),
			currentMonth: $("#currentMonth").val()
		}).done(function(data) {
			var jData = JSON.parse(data);
			$("#fromDate").val(jData.leaveFrom);
			$("#toDate").val(jData.leaveTo);
			$("#leaveType").val(jData.leaveType);
			$("#wait").css("visibility", "hidden");
		});
	});

	$("#edit-Attendance").on("hidden.bs.modal", function() {
		$("#startTime").val("");
		$("#lunchStart").val("");
		$("#lunchEnd").val("");
		$("#stopTime").val("");
		$("#attendanceMessage").html("");
		$("#attendanceMessage").css("display", "none");
	});
	$(".grayHole, .blackHole, .punchHole").on("click", function(e) {
		$("#wait").css("visibility", "unset");
		$("#edit-Attendance-Title").html(this.id + " " + $("#selectedMonth").val());
		$.post("loadAttendance.php", {
			loadAttendance: "true",
			date: this.id + " " + $("#selectedMonth").val(),
			emp_id: $("#UserID").val()
		}).done(function(data) {
			var data = JSON.parse(data);
			$("#startTime").val(data.start);
			$("#startTime").attr("name", data.start_id);
			$("#lunchStart").val(data.on);
			$("#lunchStart").attr("name", data.on_id);
			$("#lunchEnd").val(data.off);
			$("#lunchEnd").attr("name", data.off_id);
			$("#stopTime").val(data.end);
			$("#stopTime").attr("name", data.end_id);
			$("#wait").css("visibility", "hidden");
		});
		$("#edit-Attendance").data("day", this.id);
		$("#edit-Attendance").modal("show");
	});
	$("#lunchon.btn").on("click", function() {
		$("#wait").css("visibility", "unset");
		$("#lunchon.btn").off("click");
		console.log("Punching: " + this.id);
		$.post("/start.php", {
			UserID: $("#UserID").val(),
			punch: this.id
		}).done(function(data) {
			var jData = JSON.parse(data);
			$("#register").html(jData.register);
			$("#punchButtons").html(jData.punchButtons);
			$("#attendanceSubHeader").html(jData.message);
			$("#wait").css("visibility", "hidden");
			reflowBehavior();
		});
	});
	$("#lunchoff.btn").on("click", function() {
		$("#wait").css("visibility", "unset");
		$("#lunchoff.btn").off("click");
		console.log("Punching: " + this.id);
		$.post("/start.php", {
			UserID: $("#UserID").val(),
			punch: this.id
		}).done(function(data) {
			var jData = JSON.parse(data);
			$("#register").html(jData.register);
			$("#punchButtons").html(jData.punchButtons);
			$("#attendanceSubHeader").html(jData.message);
			$("#wait").css("visibility", "hidden");
			reflowBehavior();
		});
	});
	$("#tjaila.btn").on("click", function() {
		$("#wait").css("visibility", "unset");
		$("#tjaila.btn").off("click");
		console.log("Punching: " + this.id);
		$.post("/start.php", {
			UserID: $("#UserID").val(),
			punch: this.id
		}).done(function(data) {
			var jData = JSON.parse(data);
			$("#register").html(jData.register);
			$("#punchButtons").html(jData.punchButtons);
			$("#attendanceSubHeader").html(jData.message);
			$("#wait").css("visibility", "hidden");
			reflowBehavior();
		});
	});
}

function saveStuff() {
	$("#btnSave").on("click", function() {
		$("#wait").css("visibility", "unset");
		$.post("loadAttendance.php", {
			saveAttendance: "true",
			emp_id: $("#UserID").val(),
			date: $("#edit-Attendance-Title").html(),
			startTime: $("#startTime").val(),
			startTime_id: $("#startTime").attr("name"),
			lunchStart: $("#lunchStart").val(),
			lunchStart_id: $("#lunchStart").attr("name"),
			lunchEnd: $("#lunchEnd").val(),
			lunchEnd_id: $("#lunchEnd").attr("name"),
			stopTime: $("#stopTime").val(),
      stopTime_id: $("#stopTime").attr("name"),
      skipRender: true
		}).done(function(data) {
			var data = JSON.parse(data);
			$("#startTime").val(data.start);
			$("#startTime").attr("name", data.start_id);
			$("#lunchStart").val(data.on);
			$("#lunchStart").attr("name", data.on_id);
			$("#lunchEnd").val(data.off);
			$("#lunchEnd").attr("name", data.off_id);
			$("#stopTime").val(data.end);
			$("#stopTime").attr("name", data.end_id);
			$.post("start.php", {
        UserID: $("#UserID").val(),
        punch: "in"
			}).done(function(data) {
        var data = JSON.parse(data);
        $("#register").html(data.register);
        $("#attendanceSubHeader").html(data.message);
        $("#punchButtons").html(data.punchButtons);
        $("#attendanceMessage").html("Attendance Saved");
        $("#attendanceMessage").css("display", "block");
        $("#wait").css("visibility", "hidden");
        reflowBehavior();
      });
		});
	});
	$("#btnClear").on("click", function() {
		$("#startTime").val("");
		$("#lunchStart").val("");
		$("#lunchEnd").val("");
		$("#stopTime").val("");
		$("#attendanceMessage").html("");
		$("#attendanceMessage").css("display", "none");
	});
	$("#btnBookLeave").click(function(e) {
		e.preventDefault();
		$("#wait").css("visibility", "unset");
		$.post("/bookleave.php", $("#bookForm").serialize()).done(function(data) {
			var data = JSON.parse(data);
			$("#fromDate").val("");
			$("#toDate").val("");
			$("#leaveType").val("");
			$("#bookingResult").html(data.message);
			$("#bookingResult").css("display", "block");
			$("#register").html(data.register);
			reflowBehavior();
			$("#wait").css("visibility", "hidden");
		});
	});
	$("#btnCancelLeave").on("click", function(e) {
		e.preventDefault();
		$("#wait").css("visibility", "unset");
		$.post("/bookleave.php", {
			action: "delete",
			id: $("#leaveID").val(),
			UserID: $("#UserID").val(),
			currentMonth: $("#currentMonth").val()
		}).done(function(data) {
			var jData = JSON.parse(data);
			$("#bookingResult").html(jData.message);
			$("#bookingResult").css("display", "block");
			$("#register").html(jData.register);
			$("#wait").css("visibility", "hidden");
			$("#btnSaveLeave").css("display", "none");
			$("#btnCancelLeave").css("display", "none");
			$("#btnBookLeave").css("display", "block");
			$("#fromDate").val("");
			$("#toDate").val("");
			$("#leaveType").val("STAT");
			reflowBehavior();
		});
	});
	$("#btnSaveLeave").on("click", function(e) {
		e.preventDefault();
		$("#wait").css("visibility", "unset");
		$.post("/bookleave.php", {
			action: "update",
			id: $("#leaveID").val(),
			UserID: $("#UserID").val(),
			currentMonth: $("#currentMonth").val(),
			leaveFrom: $("#fromDate").val(),
			leaveTo: $("#toDate").val(),
			leaveType: $("#leaveType").val()
		}).done(function(data) {
			var jData = JSON.parse(data);
			$("#bookingResult").html(jData.message);
			$("#bookingResult").css("display", "block");
			$("#register").html(jData.register);
			$("#wait").css("visibility", "hidden");
			console.log(jData);
			reflowBehavior();
		});
	});
	$("#bookForm > div > div.modal-footer > button.btn.btn-secondary").on(
		"click",
		function() {
			$("#bookingResult").html("");
			$("#bookingResult").css("display", "none");
		}
	);
}
