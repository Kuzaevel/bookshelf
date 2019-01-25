<?php
//цепляемся к сессии модикса, подгружаем объекты
//define('MODX_API_MODE', true);
//include_once '../index.php';
session_start();

require_once("../manager/includes/config.inc.php");
require_once("./functions.php");

$mysqli = new mysqli( $database_server, $database_user, $database_password, str_replace("`", "", $dbase ) );

mysqli_set_charset($mysqli,"utf8");

require "./Mustache/Autoloader.php";

Mustache_Autoloader::register();
$loader = new Mustache_Loader_FilesystemLoader("templates",array('extension'=>'.html'));
$m = new Mustache_Engine;
//die('test');
if(mysqli_connect_errno()){
    echo mysqli_connect_error();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
//        if (!array_key_exists('action', $_GET)) {
//            header('HTTP/1.1 400 Bad Request');
//            die('');
//        }
        header('HTTP/1.1 400 Bad Request');
        die('');

    case 'POST':
        if (!array_key_exists('action', $_POST)) {
            header('HTTP/1.1 400 Bad Request');
            die('');
        }

        switch ($_POST['action']) {
            case 'get-token':
                    json_response(['success' => true, 'code' => mt_rand(1,10000)]);
                break;

            case 'login-manager':
                try {
                    $login = mysqli_real_escape_string($mysqli, $_POST['login']);

                    $sql = "SELECT password, is_admin FROM meeting_users WHERE login = '$login'";

                    $res = $mysqli->query($sql);
                    if ($res) {
                        $arr = $res->fetch_array();
                    }
                    $pwd = $arr['password'];

                    if (md5($_POST['c'].$pwd) === $_POST['m']) {
                        $_SESSION['user'] = $login;
                        $_SESSION['adm'] = $arr['is_admin'];
                        json_response(['success' => true, 'error' => '']);
                    } else {
                        json_response(['success' => false, 'error' => 'Неверный пароль']);
                        //session_destroy();
                        //не будем пока грохать сессию совсем, некрасиво получается, она теперь общая с modx
                        $_SESSION['user'] = null;
                        $_SESSION['adm'] = null;
                    }

                } catch (Exception $ex) {
                    json_response(['success' => false, 'error' => $ex->getMessage()]);
                }
                break;

            case 'login-user':
                try {
                    $event_id = (int)$_POST['event'];
                    $sql = "SELECT * FROM meeting_events WHERE event_id = $event_id";

                    $res = $mysqli->query($sql);

                    if ($res && $res->num_rows != 0) {
                        $arr = $res->fetch_array();
                        $start_date = new DateTime($arr['start_date']);
                        $end_date = new DateTime($arr['end_date']);

                        $data['meeting'] = $arr;
                        $data['meeting']['start_date'] = $start_date->format('d.m.Y H:i');
                        $data['meeting']['end_date'] = $end_date->format('d.m.Y H:i');

                        $tpl = $loader->load('questions_main-container');
                        $html = $m->render($tpl, $data);

                        $_SESSION['nm_ev_id'] = $event_id;
                        json_response(['success'=>true, 'html'=>$html]);
                    } else {
                        json_response(['success'=>false, 'error'=>'Неверный код мероприятия']);
                    }
                } catch (Exception $ex) {
                    json_response(['success' => false, 'error' => $ex->getMessage()]);
                }
                break;

            case 'get-uniq-id':
                try {
                    $sql = "UPDATE meeting_config SET user_id = user_id + 1 WHERE config_id = 0";
                    $res = $mysqli->query($sql);
                    if ($res) {
                        $sql = "SELECT user_id FROM meeting_config WHERE config_id = 0";
                        $res = $mysqli->query($sql);
                        if ($res) {
                            $arr = $res->fetch_array();
                            json_response(['success' => true, 'uniq_id' => $arr['user_id']]);
                        } else {
                            json_response(['success' => false]);
                        }
                    } else {
                        json_response(['success' => false]);
                    }
                } catch (Exception $ex) {
                    json_response(['success' => false, 'error' => $ex->getMessage()]);
                }
                break;

            case 'logout':
                $_SESSION['user'] = null;
                $_SESSION['adm'] = null;

                break;

            case 'leave-event':
                $_SESSION['nm_ev_id'] = null;

                break;

            case 'delete_event':
                $event_id = (int)$_POST['event'];

                try {
                    $sql = "DELETE FROM meeting_events WHERE event_id = $event_id";
                    
                    $res = $mysqli->query($sql);
                    if ($res) {
                        json_response(['success' => true]);
                    } else {
                        json_response(['success' => false, 'error' => 'Не удалось удалить мероприятие']);
                    }
                } catch (Exception $ex) {
                    json_response(['success' => false, 'error' => $ex->getMessage()]);
                }

                break;
        }

    default:
        die('');
}