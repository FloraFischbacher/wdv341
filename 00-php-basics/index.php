<!DOCTYPE html>
<?php
    $yourName = "Flora Fischbacher";
    $number1 = 5;
    $number2 = 3;
    $total = $number1 + $number2;

    $array = array("PHP", "HTML", "JavaScript");
    $len = count($array, 0);

    $js_array = "let array = [";

    foreach (range(0, $len - 1) as $idx) {
        if (!($idx == $len - 1)) {
            $js_array = $js_array . '"' . $array[$idx] . '", ';
        } else {
            $js_array = $js_array . '"' . $array[$idx] . '"';
        }
    }

    $js_array = $js_array . "];";
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>2-1: PHP Basics</title>
</head>
<body>
    <h1>2-1: PHP Basics</h1>
    <h2><?php echo $yourName; ?></h2>

    <ul>
        <li>Number one: <?php echo $number1; ?></li>
        <li>Number two: <?php echo $number2; ?></li>
        <li>Total of both: <?php echo $total; ?></li>
    </ul>

    <p>Here's the languages that went into this page:</p>

    <ul id="languages"></ul>

    <script>
        let lang_ul = document.getElementById("languages");
        <?php echo $js_array; ?>

        for (let elem of array) {
            // document.write() is deprecated, so let's do this the right way! :)

            let node = document.createElement("li");
            let text = document.createTextNode(elem);
            node.appendChild(text);
            lang_ul.appendChild(node);
        }
    </script>
</body>
</html>
