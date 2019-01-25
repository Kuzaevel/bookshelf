var sort      = 'date'; //'popular'; // TODO   получать из куки. Если в куках не определено,
// TODO   то записать в куки по умолчанию сортировку по дате
var isSpeaker = 0;
var isMonitor = 0;

function isEmpty(str) {
    if (str != null && typeof str !== "undefined") {
        str = str.trim();
    }
    if(str) { return false }
    return true
}

function show() {
    $("#addQuestion").submit.disabled = 1;
    $("#addQuestion").find('input').attr('disabled', 'disabled');
    $("#addQuestion").find('textarea').attr('disabled', 'disabled');

    $("#addQuestion").submit(function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#sendQuestion').blur();
        return false;
    });

    init();

    if (isEmpty(getCookie('nm_sid'))) {
        var wait = setInterval(function () {
            if (!isEmpty(getCookie('nm_sid'))) {
                initQuestionForm();
                initUserLikes();
                initPlusLikeButton();
                $('.score__btn').css('cursor', 'pointer');
                clearInterval(wait);
            }
        }, 1000);
    } else {
        initQuestionForm();
        $('.score__btn').css('cursor', 'pointer');
    }
    setInterval('init()', 10000);
}

function initUserLikes() {
    var arrayUserLikes = [];

    $.ajax({
        url: 'meetings_app/questions.php',
        type: 'post',
        data: {'action': 'getUserLikes'},
        error: function (xml, status, err) {
            console.log(err);
        },
        cache: true,
        success: function (data) {
            arrayUserLikes = data['questionsIDs'];

            $('.score__btn').each(function() {
                if (arrayUserLikes.includes($(this).data('id'))) {
                    if(!$(this).hasClass('score__btn-active')) {
                        $(this).addClass('score__btn-active');
                    }
                } else {
                    if($(this).hasClass('score__btn-active')) {
                        $(this).removeClass('score__btn-active');
                    }
                }
            });
        }
    });

}

function initQuestionForm() {
    $("#addQuestion").find('input').removeAttr('disabled');
    $("#addQuestion").find('textarea').removeAttr('disabled');

    $("#addQuestion").on("submit", function(e){
        e.preventDefault();
        e.stopPropagation();
        var form = $(this);
        var formData = form.serializeArray();
        if( formData.length>0){
            formData.forEach(function(e){
                if(e.name == "description" ) {
                    if (!isEmpty(e.value)) {
                        $.ajax({
                            url: 'meetings_app/questions.php',
                            type: 'post',
                            data: {
                                "action": "addQuestion",
                                "formData": formData
                            },
                            error: function (xml, status, err) {
                                console.log(err);
                            },
                            cache: true,
                            success: function (data) {
                                form[0].reset();
                                $('#sendQuestion').blur();
                                init();
                            }
                        });
                    }
                }
            })
        }
    });
}

function initPlusLikeButton() {
    $('.score__btn--plus').click(function () {
        var idQuest = $(this).data('id');
        var buttonLike = $(this);

        $.ajax({
            url: 'meetings_app/questions.php',
            type: 'post',
            data: {'action': 'addLike'
                , 'idQuestion': idQuest
            },
            error: function (xml, status, err) {
                console.log(err);
            },
            cache: true,
            success: function (data) {
                $('#' + 'l-count-' + idQuest).html(data['html']);
                initUserLikes();
            }

        });
    });
}

function init() {
    $.ajax({
        url: 'meetings_app/questions.php',
        type: 'post',
        data: {
            "action": "getQuestions",
            "isSpeaker": isSpeaker,
            "sort": sort,
            "isMonitor": isMonitor
        },
        error: function (xml, status, err) {
            console.log(err);
        },
        cache: true,
        success: function (data) {

            $('#meeting_questions').html(data['html']);

            if (!isEmpty(getCookie('nm_sid'))) {
                initUserLikes();
                initPlusLikeButton();
                $('.score__btn').css('cursor', 'pointer');
            } else {
                $('.score__btn').css('cursor', 'not-allowed');
            }

            $('.sort_button').each(function () {
                if ($(this).data('sort') == sort) {
                    $(this).addClass('sort_active');
                }
            });

            $('.sort_button-base').click(function () {
                sort = $(this).data('sort'); //TODO также записывать в куки
                init();
            });

            $('.sort_button-likes').click(function () {
                var $wrapper = $('.question-list');
                $wrapper.find('.question-item').sort(function (a, b) {
                    return +b.dataset.likes - +a.dataset.likes;
                }).appendTo($wrapper);
            });

            $('.sort_button-date').click(function () {
                $('.question-item').each(function () {
                });
                var $wrapper = $('.question-list');
                $wrapper.find('.question-item').sort(function (a, b) {
                    return +a.dataset.date - +b.dataset.date;
                }).appendTo($wrapper);
            });

            $('.question-item__eye').click(function () {
                var icon = $(this).find('.glyphicon');
                var idQuest = $(this).data('id');

                if (icon.hasClass('glyphicon-eye-open')) {
                    icon.removeClass('glyphicon-eye-open');
                    icon.addClass('glyphicon-eye-close');
                    isHidden = 1;
                } else if (icon.hasClass('glyphicon-eye-close')) {
                    icon.removeClass('glyphicon-eye-close');
                    icon.addClass('glyphicon-eye-open');
                    isHidden = 0;
                }

                $.post('/meetings_app/questions.php', {
                    "action": "hideQuestion",
                    "idQuestion": idQuest,
                    "isHidden": isHidden
                }, function () {
                    init();
                });

            });

            $('.leave_event').on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'meetings_app/route.php',
                    type: 'post',
                    data: {
                        'action': 'leave-event'
                    },
                    error: function (xml, status, err) {
                        console.log(err);
                    },
                    cache: true,
                    success: function (data) {
                        var url = window.location.toString();
                        if (url.indexOf("?") > 0) {
                            url = url.substring(0, url.indexOf("?"));
                            window.location.replace(url);
                        } else {
                            location.reload();
                        }
                    }
                });
            });
        }
    });
}
