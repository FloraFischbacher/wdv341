<?php
namespace Page\Controller;

require_once "./model.php";
require_once "./view.php";
require_once "./controller.php";

use Page\Model\LoginState;
use Page\Model\User;
use Page\View\Events;
use Page\View\Login;
use Page\View\Main;
use Page\View\View;

class Controller {
    private User $user;

    public function __construct() {
        $this->user = new User();
        $loginState = LoginState::PENDING;

        if (!empty($_POST["username"]) || !empty($_POST["password"])) {
            $loginState = $this->attemptLogin();
        }

        if (!empty($_POST["logout"])) {
            $this->user->logout();
        }

        if ($this->user->isLoggedIn()) {
            $this->display(new Main(new Events()));
        } else {
            $this->display(new Main(new Login($loginState)));
        }
    }

    public function attemptLogin(): LoginState {
        $result = LoginState::PENDING;

        $data = [
            "user" => !empty($_POST["username"])
                ? htmlspecialchars($_POST["username"]) : null,
            "pass" => !empty($_POST["username"])
                ? htmlspecialchars($_POST["password"]) : null
        ];

        if (!empty($data["user"])) {
            $result = LoginState::FAILURE;

            if (!empty($data["pass"]) && $this->user->login($data)) {
                $result = LoginState::SUCCESS;
            }
        }

        return $result;
    }

    public function display(View $view) {
        echo $view->render();
    }
}
?>