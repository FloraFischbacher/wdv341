<?php
require "/var/sens/db.php";

function selectEvents() {
    global $db;

    $stmt = $db->prepare("SELECT * FROM events");
    $stmt->execute();
    return $stmt;
}

function displayTable() {
    global $connected;

    if (!$connected) {
        return "Could not connect to DB!";
    }

    $match = selectEvents();

    if ($match->rowCount() != 0) $match = $match->fetchAll();
    else return "Entry not found!";

    $table = [
        "head" => <<<EOH
        <table>
            <thead>
                <tr>
                    <th scope="col">Event ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Description</th>
                    <th scope="col">Presenter</th>
                    <th scope="col">Event Date</th>
                    <th scope="col">Event Time</th>
                    <th scope="col">Date Added</th>
                    <th scope="col">Last Updated</th>
                </tr>
            </thead>
            <tbody>
        EOH,
        "body" => "",
        "tail" => "    </tbody>\n</table>",
    ];

    foreach ($match as $row) {
        $table["body"] .= <<<EOB
                <tr>
                    <th scope="row">{$row["event_id"]}</th>
                    <td>{$row["event_name"]}</td>
                    <td>{$row["event_description"]}</td>
                    <td>{$row["event_presenter"]}</td>
                    <td>{$row["event_date"]}</td>
                    <td>{$row["event_time"]}</td>
                    <td>{$row["event_date_inserted"]}</td>
                    <td>{$row["event_date_updated"]}</td>
                </tr>
        EOB;
    }

    return $table["head"] . $table["body"] . $table["tail"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>7-1: SELECT (all rows)</title>
</head>
<body>
    <header>
        <h1>7-1: <code>SELECT</code> statements (all rows)</h1>
    </header>
    
    <main>
        <?= displayTable() ?>
    </main>

    <footer>

    </footer>
</body>
</html>