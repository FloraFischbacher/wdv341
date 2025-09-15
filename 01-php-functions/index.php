<?php

function format_mdy_date($unix_timestamp) {
    $date = new DateTimeImmutable("@" . $unix_timestamp);
    $formatted = $date->format("m/d/Y");
    return $formatted;
}

function format_dmy_date($unix_timestamp) {
    $date = new DateTimeImmutable("@" . $unix_timestamp);
    $formatted = $date->format("d/m/Y");
    return $formatted;
}

function act_on_string($input) {
    // Assignment wasn't clear whether it wants the length before or after
    // trimming the string, so I'll do both just to be safe!
    $len_before = strlen($input);
    $trimmed = trim($input);
    $len_after = strlen($trimmed);
    $lower = strtolower($trimmed);

    // If I'm already converting the string to lowercase, I don't need to check
    // for an uppercase form of DMACC, I can just use the all-lowercase string!
    $has_dmacc = str_contains($lower, "dmacc");
    // (Side note: it's kind of adorable how the names for the arguments of
    // str_contains() are called "haystack" and "needle"! They're just really
    // good names for that. :))

    return [
        "len_before" => $len_before,
        "trimmed" => $trimmed,
        "len_after" => $len_after,
        "lower" => $lower,
        "has_dmacc" => $has_dmacc
    ];
}

// Fun fact: I had to look up if the other two sections of US-based phone
// numbers had specific names (which they do, actually: a "central office code"
// and a "line number"). In the process of researching that question I ended up
// learning a lot about how phone systems work internationally after falling
// down a bit of a rabbit hole. It turns out that the conventions for phone
// numbers *greatly* depends on the country, and so a lot of the assumptions I
// wanted to make about the length and format that the phone number appears in
// for the end-user is greatly limited by the specific country that the phone
// number belongs to.
//
// Because of this, I unfortunately decided to forego supporting telephone
// *country codes* (i.e +1 for the US, Canada, and other North American
// territories, +52 for Mexico, +61 for Australia, etc.) for the sake of
// simplicity (as much as I wish that I didn't have to! ^-^').
//
// If I am expected to support this, however, I have no qualms with
// resubmitting the assignment to support that. I would have to use something
// like an adaptation of Google's libphonenumber for PHP[^1], but that would
// also introduce PHP package management and that might be too much for a
// five-point assignment. It's up to you, though (as I don't know what you have
// planned for this course, and don't want to overstep). I don't mind learning
// more in my own time, regardless. :)
//
// [^1]: https://github.com/giggsey/libphonenumber-for-php
function format_phone_num($number_str) {
    $processed = trim($number_str);

    if (strlen($processed) != 10) {
        // It's up to the consumer of the function to handle this properly
        // into an resulting error message.
        //
        // I know that PHP has exceptions, but I don't believe they've been
        // taught yet so I will refrain from using them here.
        return false;
    }

    $area_code = substr($processed, 0, 3);
    $office_code = substr($processed, 3, 3);
    $line_num = substr($processed, 6, 4);

    $result = "+1 (" . $area_code . ") " . $office_code . "-" . $line_num;
    return $result;
}

function format_currency($amount) {
    $formatter = new NumberFormatter("en_US", NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($amount, "USD");
}

$curiosity_landing = 1344230277; // August 6, 2012; 05:17:57 UTC!
$result_string = act_on_string("  Hello, DMACC! :)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>3-1: PHP Functions</title>
</head>
<body>
    <h1>3-1: PHP Functions</h1>
    <h2>Flora Fischbacher</h2>
    <ol>
        <li>Curiosity rover land date (MM/DD/YYYY): <?= format_mdy_date($curiosity_landing); ?></li>
        <li>Curiosity rover land date (DD/MM/YYYY): <?= format_dmy_date($curiosity_landing); ?></li>
        <li><em>String manipulation work:</em></li>
        <ul>
            <li>Length before trimming: <?= $result_string["len_before"]; ?></li>
            <li>Length after trimming: <?= $result_string["len_after"]; ?></li>
            <li>Trimmed string: "<?= $result_string["trimmed"]; ?>"</li>
            <li>Lowercase version: "<?= $result_string["lower"]; ?>"</li>
            <li>Has DMACC: <?= $result_string["has_dmacc"] ? "Yes!" : "No"; ?></li>
        </ul>
        <li>Formatted phone number: <?= format_phone_num("1234567890"); ?></li>
        <li>Formatted currency (US): <?php echo format_currency(123456); ?></li>
    </ol>
</body>
</html>
