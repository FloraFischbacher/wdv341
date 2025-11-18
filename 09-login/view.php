<?php
namespace Page\View;

require_once "./model.php";
require_once "./view.php";
require_once "./controller.php";

use Page\Fetch\Fetch;
use Page\Model\LoginState;

interface View {
    public function render(): string;
}

class Events implements View {
    public function render(): string {
        $events = new Fetch(
            "SELECT event_id, event_name, event_description,
                    event_presenter, event_date, event_time,
                    event_date_inserted, event_date_updated
            FROM wdv341_events"
        );

        $result = <<<EOH
        <button>Add new event</button>
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
                    <th scope="col">Operations</th>
                </tr>
            </thead>
            <tbody>
        EOH;

        while ($events->next() == true) {
            $result .= \sprintf(<<<EOH
                    <tr>
                        <th scope="row">%s</th>
                        <th scope="row">%s</th>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>
                            <button>Update</button>
                            <button>Delete</button>
                        </td>
                    </tr>
            EOH, $events->current["event_id"],
            $events->current["event_name"],
            $events->current["event_description"],
            $events->current["event_presenter"],
            $events->current["event_date"],
            $events->current["event_time"],
            $events->current["event_date_inserted"],
            $events->current["event_date_updated"]);
        }

        $result .= <<<EOH
        </table>
        <form action="./" method="post">
            <button type="submit" name="logout" value="logout">Log out</button>
        </form>
        EOH;

        return $result;
    }
}

class Login implements View {
    private LoginState $state;

    public function __construct(LoginState $state) {
        $this->state = $state;
    }

    public function render(): string {
        $invalid = $this->state == LoginState::FAILURE
            ? "style=\"border: 1px solid red; padding: 1em;\""
            : "";

        $msg = $this->state == LoginState::FAILURE
            ? "<p style=\"color: red;\">Username or password incorrect.</p>"
            : "";

        // NOTE: ${}-style string interpolation is deprecated as of PHP 8.2.
        return \sprintf(<<<EOH
        <form action="./" method="post">
            <section class="login" $invalid>
                {$this->renderField("Username", "text")}
                {$this->renderField("Password", "password")}
            </section>

            $msg

            <input type="submit" value="Log in">
        </form>
        EOH);
    }

    public function renderField(string $name, string $type): string {
        $lower = strtolower($name);

        return <<<EOH
        <article>
            <label for="$lower">$name</label><br>
            <input type="$type" name="$lower" id="$lower" required /><br>
        </article><br>
        EOH;
    }
}

class Main implements View {
    private View $content; 

    private const string HEADER = <<<EOH
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>10-2, 10-3: Login and logout</title>
    </head>
    <body>
        <h1>10-2, 10-3: Login/logout</h1>
    EOH;
    
    private const string FOOTER = <<<EOF
    </body>
    </html>
    EOF;

    public function __construct(View $within) {
        $this->content = $within;
    }

    public function render(): string {
        return Main::HEADER . $this->content->render() . Main::FOOTER;
    }
}
?>