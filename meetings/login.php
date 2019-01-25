<?php session_start(); ?>
<?php include_once "head.php"?>

<link rel="stylesheet" href="./css/style.css?v=<?=time?>"/>

<div id="container_meeting" class="container_meeting container col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="login_meeting col-lg-4 col-md-4 col-sm-6 col-xs-12 col-lg-offset-4 col-md-offset-4 col-sm-offset-3">
        <div class="meeting_block meeting_user col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <form id="login_user">
                <label class="col-lg-12 col-md-12 col-sm-12 col-xs-12">Я участник мероприятия</label>
                <input type="text" id="event_code" name="event_code" class="form-control col-lg-6 col-md-6 col-sm-12 col-xs-12" placeholder="Код мероприятия" autocomplete="off">
                <input type="submit" class="form-control col-lg-6 col-md-6 col-sm-12 col-xs-12 btn-primary" value="Присоединиться">
            </form>
        </div>
        <div class="meeting_block meeting_manager col-lg-12 col-md-12 col-sm-12 col-xs-12" style="">
            <form id="login_manager">
                <label class="col-lg-12 col-md-12 col-sm-12 col-xs-12">Я организатор мероприятий</label>
                <input type="text" id="user" name="user" class="form-control col-lg-6 col-md-6 col-sm-12 col-xs-12" placeholder="Имя пользователя" autocomplete="off">
                <input type="password" id="pwd" name="password" class="form-control col-lg-6 col-md-6 col-sm-12 col-xs-12 password" placeholder="Пароль" autocomplete="new-password">
                <input type="submit" class="form-control col-lg-6 col-md-6 col-sm-12 col-xs-12 col-lg-offset-6 col-md-offset-6 btn-primary" value="Войти">
            </form>
        </div>
    </div>
</div>

<script src="./assets/js/jquery.min.js" type="text/javascript"></script>
<script src="./js/notify.js" type="text/javascript"></script>
<script src="./js/md5.min.js" type="text/javascript"></script>
<script src="./js/swfobject-2.2.min.js" type="text/javascript"></script>
<script src="./evercookie/js/evercookie.js" type="text/javascript"></script>
<script src="./js/script.js?v=<?=time();?>" type="text/javascript"></script>
<script src="./js/questions.js?v=<?=time();?>" type="text/javascript"></script>

<?php
if(isset($_SESSION['user'])) {
    echo '
        <script type="text/javascript">
            show_index("active");
        </script>
        ';
//} else if(isset($_COOKIE['nm_sid']) && (int)$_COOKIE['nm_sid'] != null && isset($_COOKIE['nm_ev_id'])){
} else if (isset($_SESSION['nm_ev_id'])) {
    echo '
        <script type="text/javascript">
            show_event('.$_SESSION['nm_ev_id'].');
        </script>
        ';
}

if (isset($_GET['event_id'])) {
    $event_id = (int)$_GET['event_id'];
    echo '
        <script type="text/javascript">
            show_event('.$event_id.');
        </script>
        ';
}