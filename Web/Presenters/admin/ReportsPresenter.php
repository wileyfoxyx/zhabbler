<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class ReportsPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        if(isset($_COOKIE['zhabbler_session'])){
            $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
            $user = (new User())->get_user_by_token($session->sessionToken);
            if($user->admin != 1){
                header("Location: /");
                die;
            }
            if(isset($_GET['report_delete'])){
                $GLOBALS['db']->query("DELETE FROM reports WHERE reportID = ?", (int)$_GET['report_delete']);
                header("Location: /admin/reports");
                die;
            }
            $reports = $GLOBALS['db']->fetchAll("SELECT * FROM reports LEFT JOIN users ON userID = reportTo ORDER BY reportID DESC");
            $params += ["user" => $user, "reports" => $reports];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/admin/reports.latte", $params);
        }else{
            header("Location: /login");
            die;
        }
    }
}
(new ReportsPresenter())->load();