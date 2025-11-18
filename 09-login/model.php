<?php
namespace Page\Model;

require_once "./model.php";
require_once "./view.php";
require_once "./controller.php";

use Page\Fetch\Fetch;

enum LoginState {
    case PENDING;
    case SUCCESS;
    case FAILURE;
}

class User {
    public bool $validUser;

    public function __construct() {
        session_start();
        $this->validUser = $this->isLoggedIn();
    }

    // NOTE: Future Flora, if you decide to adapt this code for Moonshine,
    // PLEASE remember to salt the passwords in the database... last thing
    // we want is for actual users of an actual web application to have
    // their data stolen because we didn't do our due diligence. >_>
    public function login(array $credentials): bool {
        $account = new Fetch(
            "SELECT event_user_name, event_user_password
                FROM wdv341_event_user
                WHERE event_user_name = :user",
        [
            ":user" => $credentials["user"]
        ]);
        
        
        if ($account->next() instanceof PDOException) return false;
        if (
            $account->current["event_user_password"]
            != $credentials["pass"]
        ) return false;

        $_SESSION["validUser"] = true;
        $this->validUser = true;
        return true;
    }

    public function logout() {
        $this->validUser = false;

        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION["validUser"])
            && $_SESSION["validUser"] == true;
    }
}
?>