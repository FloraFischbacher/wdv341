<?php
$valid = false;
$result;

$failHeader = "<h1>Contact request invalid</h1>";
$failBody = "<p>The following problems were found in your contact request submission:</p>";
$failList = "<ul>";
$failButton = "<a class=\"return-btn\" href=\"./index.html\">Return to form</a>";

$successHeader = "<h1>Contact request submitted</h1>";
$successBody = "<p>Dear ";

$firstName = $_POST["first_name"];
$lastName = $_POST["last_name"];
$emailAddress = $_POST["email_addr"];
$schoolName = $_POST["school_name"];
$major = $_POST["major"];
$gradeLevel = $_POST["grade_level"] ?? "";
$contactMethods = $_POST["contact_methods"] ?? "";
$comments = $_POST["comments"];

if (empty($firstName)) {
	$failList .= "<li>A <strong>first name</strong> was not provided (required field).</li>";
}

if (empty($emailAddress)) {
	$failList .= "<li>A <strong>email address</strong> was not provided (required field).</li>";
} else {
	if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
		$failList .= "<li>The email address \"<strong>" . $emailAddress . "</strong>\" is not valid.";
	}
}

if (empty($schoolName)) {
	$failList .= "<li>A <strong>school name</strong> was not provided (required field).</li>";
}

if ($major == "") {
	$failList .= "<li>A <strong>major</strong> was not provided (required field).</li>";
} else {
	try {
		$major = match($major) {
			"cis" => "Computer Information Systems",
			"gd" => "Graphic Design",
			"wdv" => "Web Development",
		};
	} catch (UnhandledMatchError $err) {
		$failList .= "<li>The <strong>major</strong> provided was not an option present on the form.</li>";
	}
}

if ($gradeLevel == "") {
	$failList .= "<li>A <strong>grade level</strong> was not provided (required field).</li>";
} else {
	try {
		$output = match($gradeLevel) {
			"high_school" => "high school student",
			"freshman" => "freshman",
			"sophomore" => "sophomore",
		};

		$gradeLevel = $output;
	} catch (UnhandledMatchError $err) {
		$failList .= "<li>The <strong>grade level</strong> provided was not an option present on the form.</li>";
	}
}

if ($contactMethods == "") {
	$failList .= "<li>A <strong>contact method</strong> was not provided (required field).</li>";
} else {
	if (!is_array($contactMethods)) {
		$contactMethods = [$contactMethods];
	}

	if ($contactMethods != "") {
		$output = [];
		foreach ($contactMethods as $method) {
			try {
				array_push($output, match($method) {
					"program_info" => "Information about DMACC's programs",
					"advisor" => "Steps to contact a program advisor",
				});
			} catch (UnhandledMatchError $err) {
				$failList .= "<li>A <strong>contact method</strong> provided was not an option present on the form.</li>";
			}
		}
		$contactMethods = $output;
	}
}


if ($failList == "<ul>") {
	$valid = true;
}
$failList .= "</ul>";

if ($valid) {
	$successBody .= htmlspecialchars($firstName) . ",</p>";
	$successBody .= "<p>Thank you for your interest in DMACC.</p>";
	$successBody .= "<p>We have you listed as a " . htmlspecialchars($gradeLevel) . " starting this fall.</p>";
	$successBody .= "<p>You have declared <em>" . htmlspecialchars($major) . "</em> as your major.</p>";
	$successBody .= "<p>Based on your responses we will provide the following information in our confirmation email to you at <strong>" . htmlspecialchars($emailAddress) . "</strong>:</p><ul>";

	foreach ($contactMethods as $method) {
		$successBody .= "<li>" . htmlspecialchars($method) . "</li>";
	}

	$successBody .= "</ul>";

	if ($comments != "") {
		$successBody .= "<p>You have shared the following comments which we will review:</p>";
		$successBody .= "<blockquote><p>" . htmlspecialchars($comments) . "</p></blockquote>";
	}

	$result = $successHeader . $successBody;
} else {
	$result = $failHeader . $failBody . $failList . $failButton;
}
?>

<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>4-1: HTML Form Processor</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');

* {
	margin: 0;
}

html {
	background-color: #003EA8;
	font-size: 14pt;
}

body {
	font-family: 'Lato', sans-serif;
	min-height: 100vh;
	display: flex;
	align-items: center;
}

main {
	width:600px;
	margin: 5em auto;
	background-color: #FFF;
	padding: 1em;
	border-radius: 10px;
	box-shadow: 0px 15px 25px 0px rgba(0, 0, 0, 0.33);
}

h1 {
	margin-bottom: 0.5rem;
	padding: 2rem;
	font-size: 28pt;
	font-weight: 800;
	text-align:center;
}

p:not(p:last-of-type), p:has(+ :not(p)), ul {
	margin-bottom: 1rem;
}

ul {
	line-height: 1.4;
}

blockquote {
	border-left: 5px solid rgba(0, 0, 0, 0.3);
	background: rgba(0, 0, 0, 0.1);
	padding: 1em;
}

.return-btn {
	display: block;
	color: #FFF;
	background-color: #003EA8;
	padding: 1em;
	border-radius: 500px;
	font-weight: 600;
	margin: 1em auto;
	margin-top: 2em;
	max-width: 200px;
	text-decoration: none;
	text-align: center;
}

.return-btn:hover {
	background-color: #04307d;
	text-decoration: underline;
}

</style>
</head>
<html>
<body>
	<main>
		<?= $result ?>
	</main>
</body>
</html>
