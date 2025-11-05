<?php
require "/var/sens/db.php";

function select() {
    global $db;

    $stmt = $db->prepare("SELECT event_id, event_name, event_description, event_presenter, event_date, event_time, event_date_inserted, event_date_updated FROM events WHERE event_id = 2");
    $stmt->execute();
    return $stmt;
}

function fillArray() {
    global $connected;

    if (!$connected) {
        return "Could not connect to DB!";
    }

    $match = select();

    if ($match->rowCount() != 0) return $match->fetch(PDO::FETCH_ASSOC);
    else return "Entry not found!";
}

class Event {
    public int $id;
    public string $name;
    public string $desc;
    public string $presenter;
    public DateTimeImmutable $date;
    public DateTimeImmutable $inserted;
    public DateTimeImmutable $updated;

    public function __construct(
        int $id,
        string $name, string $desc, string $presenter,
        string $date, string $time, string $inserted, string $updated
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->desc = $desc;
        $this->presenter = $presenter;

        $this->date = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $date . " " . $time);
        $this->inserted = new DateTimeImmutable($inserted);
        $this->updated = new DateTimeImmutable($updated);
    }
}

function instanceEvent(array | string $eventData): Event | string {
    if (is_array($eventData)) {
        return new Event(
            $eventData["event_id"], 
            $eventData["event_name"], 
            $eventData["event_description"], 
            $eventData["event_presenter"], 
            $eventData["event_date"], 
            $eventData["event_time"],
            $eventData["event_date_inserted"],
            $eventData["event_date_updated"]
        );
    } else return $eventData;
}

function getJSON(Event | string $data): string {
    if ($data instanceof Event) {
        return json_encode($data, JSON_PRETTY_PRINT);
    } else {
        return $data;
    }
}

function run() {
    return getJSON(instanceEvent(fillArray()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>8-1: PHP JSON Object</title>
</head>
<body>
    <h1>Creating a JSON object with PHP</h1>
    <pre><?= run(); ?></pre>
</body>
</html>