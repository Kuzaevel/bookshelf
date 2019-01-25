<?php
//цепляемся к сессии модикса, подгружаем объекты
define('MODX_API_MODE', true);
include_once '../index.php';

if(!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'no login']);
    die();
}

require_once("../manager/includes/config.inc.php");
require_once("./functions.php");

$mysqli = new mysqli( $database_server, $database_user, $database_password, str_replace("`", "", $dbase ) );

mysqli_set_charset($mysqli,"utf8");

require "./Mustache/Autoloader.php";

Mustache_Autoloader::register();
$loader = new Mustache_Loader_FilesystemLoader("templates",array('extension'=>'.html'));
$m = new Mustache_Engine;

if(mysqli_connect_errno()){
    echo mysqli_connect_error();
}

if($_SESSION['adm'] == '1' && isset($_SESSION['user'])) {

    switch ($_POST['page']) {
        case 'active':
        case 'archive':
            $sql = "SELECT ev.event_id "
                . ", ev.name "
                . ", ev.start_date"
                . ", ev.end_date"
                . ", COUNT(*) as quest_cnt "
                . ", q.id as quest_id "
                . "FROM meeting_events ev "
                . "LEFT JOIN meeting_questions q ON q.idEvent = ev.event_id "
                . ($_POST['page'] == 'active' ? "WHERE ev.is_completed = 0 " : "")
                . ($_POST['page'] == 'archive' ? "WHERE ev.is_completed = 1 " : "")
                . "GROUP BY ev.name, ev.start_date, ev.end_date "
                . "ORDER BY ev.event_id";

            $res = $mysqli->query($sql);
            if ($res) {
                $data['events'] = $res->fetch_all(MYSQLI_ASSOC);

                foreach ($data['events'] as $key => $event) {
                    $data['events'][$key]['start_date'] = (new DateTime($data['events'][$key]['start_date']))->format('d.m.Y');
                    $data['events'][$key]['end_date'] = (new DateTime($data['events'][$key]['end_date']))->format('d.m.Y');

                    $quest_cnt = $event['quest_id'] == null ? 0 : $event['quest_cnt'];
                    $data['events'][$key]['quest_cnt'] = $quest_cnt . ' ' . true_wordform($quest_cnt, 'вопрос','вопроса','вопросов');
                }
            }

            break;

        case 'manager':
             $sql = "SELECT id"
                . ", login "
                . ", name "
                . ", email "
                . ", is_admin "
                . "FROM meeting_users u "
                . "ORDER BY id";

            $res = $mysqli->query($sql);
            if ($res) {
                $data['users'] = $res->fetch_all(MYSQLI_ASSOC);

                foreach ($data['users'] as $key => $user) {
                    $data['user_role'] = $user['is_admin'] ? 'Администратор' : 'Организатор';
                }

            }
            break;

    }

    $tpl = $loader->load('admin_page');
    $data[$_POST['page']] = true;
    $data['page'] = $_POST['page'];

    json_response(['success' => true, 'html' => $m->render($tpl, $data)]);
} else {
?>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login_meeting">Здесь будет модерка</div>

<?php
}
?>
<script src="./meetings_app/js/script.js?v=8" type="text/javascript"></script>