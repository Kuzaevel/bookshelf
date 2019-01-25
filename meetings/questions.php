<?php
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

if(mysqli_connect_errno()){
    echo mysqli_connect_error();
}

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        header('HTTP/1.1 400 Bad Request');
        die('');

    case 'POST':

        if (!array_key_exists('action', $_POST)) {
            header('HTTP/1.1 400 Bad Request');
            die('');
        }

        $idEvent   = (int)$_SESSION['nm_ev_id']; // номер события
        $idUser =  $_COOKIE['nm_sid']; // получаем ид пользователя из куков или из локального хранилища

        switch ($_POST['action']) {
            case 'getQuestions':
                $isSpeaker = (int)$_POST['isSpeaker']; //TODO надо получать от пользователя
                $isMonitor = (int)$_POST['isMonitor']; //TODO надо получать от пользователя
                $sort      =  mysqli_real_escape_string($mysqli,$_POST['sort']); //TODO надо получать от пользователя

                //получаем вопросы в событии и данные по событию
                $sql = "SELECT q.*, COUNT(l.id) as countLikes, (SELECT count(id) from meeting_likes u where u.idUser = 40 and u.idQuestion = q.id) as userLikes,
                            e.name as e_name, e.description as e_description, e.start_date, e.end_date
                          FROM meeting_questions q
                        LEFT JOIN meeting_likes l
                          ON q.id = l.idQuestion
                            JOIN meeting_events e ON e.event_id = q.idEvent " .
                        ($isSpeaker!=1 ? "WHERE NOT(q.isHidden)" : "") . " AND q.idEvent = $idEvent" .
                        " GROUP BY q.id ORDER BY ". ($sort=="date" ? "q.date": "countLikes DESC") ;

                $res = $mysqli->query($sql);
                $data['questions'] = $res->fetch_all(1);
                $data['count'] = $res->num_rows. ' ' . true_wordform($res->num_rows, 'вопрос','вопроса','вопросов'); //склоняем вопросы

                //форматируем дату для отображения и сортировки
                foreach ($data['questions'] as $key=>$val ) { //$question
                    if($key == 0) {
                        // формируем данные по событию
                        $data['meeting'] = $data['questions']['$key'];//$question;
                        $sdate = new DateTime( $data['questions']['$key'] );//($question['start_date']);
                        $data['meeting']['start_date'] = $sdate->format('d.m.Y H:i');
                        $sdate = new DateTime( $data['questions']['$key']);//($question['end_date']);
                        $data['meeting']['end_date'] = $sdate->format('d.m.Y H:i');
                    }
                    $sdate = new DateTime($data['questions'][$key]['date']);
                    $data['questions'][$key]['date'] = $sdate->format('d.m.Y H:i');
                    $data['questions'][$key]['dateSort'] = $sdate->format('YmdHis');
                    $data['questions'][$key]['userLikes'] = ($data['questions'][$key]['userLikes']>0?1:0);
                }

                $data['isMonitor'] = $isMonitor;

                // отображаем различные шаблоны в зависимости от типа пользователя и вывода на проектор
                if($isSpeaker and $isMonitor) {
                    $tpl = $loader->load('questions');
                } else if ($isSpeaker and !$isMonitor) {
                    $tpl = $loader->load('questionsEdit');
                } else {
                    $tpl = $loader->load('questions');
                }

                json_response(['success' => true, 'html' => $m->render($tpl, $data)]);
                break;

            case 'addQuestion':
                $author =  mysqli_real_escape_string($mysqli,$_POST['formData'][0]['value']);
                $desc   =  mysqli_real_escape_string($mysqli,$_POST['formData'][1]['value']);
                $author = ($author == "" ? "Неизвестный" : $author);

                $sql = "INSERT INTO meeting_questions
                          (idEvent, author, user_id, description)
                        VALUES ($idEvent,'$author',$idUser,'$desc');";

                $mysqli->query($sql);
                break;

            case 'getUserLikes':
                $data['questionsIDs'] = [];
                $sql = "SELECT idQuestion FROM meeting_likes
                          WHERE idUser = $idUser; ";
                $res = $mysqli->query($sql);

                $array = $res->fetch_all(2);
                foreach ($array as $key=>$value) {
                    array_push($data['questionsIDs'],(int)implode(",", $value));
                }
                json_response(['success' => true, 'questionsIDs' => $data['questionsIDs'] ]);
                break;

            case 'addLike':
                $idQuestion = (int)$_POST['idQuestion'];

                $sql = "SELECT * FROM meeting_likes
                          WHERE idUser = $idUser AND idQuestion = $idQuestion";
                $res = $mysqli->query($sql);
                $userLikesCount = $res->num_rows;

                if ($userLikesCount > 0) {
                    //удаляем лайк
                    $sql = "DELETE FROM meeting_likes WHERE idUser = $idUser AND idQuestion = $idQuestion";
                    $res = $mysqli->query($sql);
                } else {
                    //записываем плюс лайк
                    $sql = "INSERT INTO meeting_likes
                          (idQuestion,idUser)
                        VALUES ($idQuestion,$idUser);";
                    $mysqli->query($sql);
                }

                $sql = "SELECT count(id) AS count
                          FROM meeting_likes
                        WHERE idQuestion = $idQuestion; ";

                $res = $mysqli->query($sql);
                $data = $res->fetch_array();
                json_response(['success' => true, 'html' => $data['count']]);
                break;

            case 'hideQuestion':
                $idQuestion = (int)$_POST['idQuestion'];
                $isHidden   = (int)$_POST['isHidden'];

                $sql = "UPDATE meeting_questions
                          SET isHidden=$isHidden
                        WHERE  id=$idQuestion";
                $mysqli->query($sql);
                json_response(['success' => true]);
                break;
        }
}
