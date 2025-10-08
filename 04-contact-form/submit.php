<?php declare(strict_types=1);

// Wow, I did not anticipate how difficult it would be to self-host a mail
// server (at least, while avoiding making it so that sending emails to a major
// service providers is a *nightmare*). I will simply be using the Gmail server
// as directed here. @_@

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "vendor/autoload.php";

$mailer = new PHPMailer(true);

$data = [];
$errors = [];

function setupMailer(): bool
{
    global $errors;
    global $mailer;

    // I definitely don't want to leak my Gmail app password on a public
    // repository... >_>
    $mail_pw = getenv("MAIL_PW");

    try {
        $mailer->SMTPDebug = 0;
        $mailer->isSMTP();
        $mailer->Host = "smtp.gmail.com";
        $mailer->SMTPAuth = true;
        $mailer->Username = "fischbacher.flora.w@gmail.com";
        $mailer->Password = $mail_pw;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = 587;
    } catch (Exception $err) {
        error_log("error!");
        array_push(
            $errors,
            "Mailer could not be set up. Mailer Error: {$mailer->ErrorInfo}",
        );
    }

    return count($errors) == 0;
}

function sendMsgs(array $formData): bool
{
    global $errors;
    global $mailer;

    $now = new DateTime("now", new DateTimeZone("America/Chicago"));
    $contactDate = $now->format("m/d/Y");

    $formattedReason = $formData["reason"];

    if ($formattedReason == "sales") $formattedReason = "Promotions and collaborations";
    if ($formattedReason == "tech") $formattedReason = "Technical issues and security advisories";
    if ($formattedReason == "service") $formattedReason = "Service-related complaints";
    if ($formattedReason == "other") $formattedReason = "Other";

    try {
        if ($formData["confirm"] == true) {
            $mailer->setFrom(
                "fischbacher.flora.w@gmail.com",
                "Flora Fischbacher"
            );
            $mailer->addAddress(
                $formData["email"],
                $formData["name"]
            );

            $mailer->Subject = "Contact request received";
            $mailer->Body = <<<EOM
            <table style="width: 100%; background-color: #f1c255; font-family: sans-serif; font-size: 14pt; border: 0; margin: 0 auto; padding: 3em">
                <td style="background-color: #FFF; color: #000; border: 0; border-radius: 20px; padding: 1em 2em;">
                    <p>Thank you for submitting a contact request! We will reach out based on the following provided information:</p>

                    <ul>
                        <li>Full name: {$formData["name"]}</li>
                        <li>Email address: {$formData["email"]}</li>
                        <li>Contact date: $contactDate</li>
                        <li>Contact reason: $formattedReason</li>
                    </ul>
            EOM;

            $mailer->AltBody = <<<EOM
            Thank you for submitting a contact request! We will reach out based on the following provided information:

            - Full name: {$formData["name"]}
            - Email address: {$formData["email"]}
            - Contact date: $contactDate
            - Contact reason: $formattedReason
            EOM;

            if (array_key_exists("comments", $formData)) {
                $mailer->AltBody .= <<<EOM

                With regards to the following comments:
                "{$formData["comments"]}"
                EOM;

                $mailer->Body .= <<<EOM
                <p>With regards to the following comments:</p>
                <blockquote>"{$formData["comments"]}"</blockquote>
                EOM;
            }

            $mailer->Body .= "</table>";
            $mailer->send();
        }

        // Because we don't want to send out our internal emails! :p
        $mailer->clearAllRecipients();

        $mailer->setFrom(
            "fischbacher.flora.w@gmail.com",
            "Flora Fischbacher"
        );
        $mailer->addAddress(
            "fischbacher.flora.w@gmail.com",
            "Flora Fischbacher"
        );

        $mailer->Subject = "New contact request from \"{$formData["name"]}\"";

        $mailer->AltBody = <<<EOM
        - Full name: {$formData["name"]}
        - Email address: {$formData["email"]}
        - Contact date: $contactDate
        - Contact reason: $formattedReason
        EOM;

        $mailer->Body = <<<EOM
        <table>
            <tr>
                <th>Full name</th>
                <td>{$formData["name"]}</td>
            </tr>
            <tr>
                <th>Email address</th>
                <td>{$formData["email"]}</td>
            </tr>
            <tr>
                <th>Contact date</th>
                <td>$contactDate</td>
            </tr>
            <tr>
                <th>Contact reason</th>
                <td>$formattedReason</td>
            </tr>
        EOM;

        if (array_key_exists("comments", $formData)) {
            $mailer->AltBody .= "\n- Additional comments: {$formData["comments"]}";
            $mailer->Body .= <<<EOM
                <tr>
                    <th>Additional comments</th>
                    <td>{$formData["comments"]}</td>
                </tr>
            EOM;
        }

        $mailer->Body .= "</table>";
        $mailer->send();
    } catch (Exception $err) {
        array_push(
            $errors,
            "Mailer could not be set up. Mailer Error: {$mailer->ErrorInfo}",
        );
    }

    return count($errors) == 0;
}

function validateForm(): bool
{
    global $errors;
    global $data;

    $name = $_POST["name"];
    $email = $_POST["email"];
    $confirm = $_POST["confirm"] ?? false;
    $reason = $_POST["reason"];
    $comments = $_POST["comments"];

    if (empty($name)) {
        array_push($errors, "No name was provided.");
    }
    if (empty($email)) {
        array_push($errors, "No email address was provided.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "The email address provided was not valid.");
    }
    if (empty($reason)) {
        array_push($errors, "No reason for contact was provided.");
    }

    $data["name"] = htmlspecialchars($name);
    $data["email"] = htmlspecialchars($email);
    $data["confirm"] = $confirm != false ? true : false;
    $data["reason"] = htmlspecialchars($reason);

    if ($comments != null && !empty($comments)) {
        $data["comments"] = htmlspecialchars($comments);
    }

    return count($errors) == 0;
}

function processForm(): void
{
    global $data;

    if (!setupMailer()) {
        return;
    }
    if (!validateForm()) {
        return;
    }
    if (!sendMsgs($data)) {
        return;
    }
}

function generateResponse(bool $successful): string
{
    global $errors;

    if ($successful) {
        return "<p>Thank you for submitting a contact request. We will reach out to you with a response as soon as possible.</p>"; // :)
    } else {
        $message = "<p>The following errors were found in your submission:</p><ul>";
        foreach ($errors as $err) {
            $message .= "<li>" . $err . "</li>";
        }
        $message .= "</ul>";
        return $message;
    }
}

processForm();

$succeeded = count($errors) == 0;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PROJECT: Contact Form with Email</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <header>
        <h1>Form <?= $succeeded ? "submitted" : "submission failed" ?></h1>
    </header>
    <main>
        <?= generateResponse($succeeded) ?>
    </main>
    <footer>

    </footer>
  </body>
</html>
