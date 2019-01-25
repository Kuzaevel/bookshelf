$("#login_user").on("submit", function(e){
    e.preventDefault();
    show_event($('#event_code').val());
});

$("#login_manager").on("submit",function(e){
   e.preventDefault();

   $.ajax({
        url: 'route.php',
        type: 'post',
        data: {'action': 'get-token'},
        error: function (xml, status, err) {
            console.log(err);
        },
        cache: true,
        success: function (data) {
            if (data['success']===true){

                var md5code = null;
                var salt = null;

                salt = md5($('#pwd').val());
                md5code = md5(data['code']+salt);

                $.ajax({
                        url: 'route.php',
                        type: 'post',
                        data: {'action': 'login-manager',
                                'login': $('#user').val(),
                                'c': data['code'],
                                'm': md5code
                            },
                        error: function (xml, status, err) {
                            console.log(err);
                        },
                        cache: true,
                        success: function (subdata) {
                            if (subdata['success'] === true) {
                                show_index('active');
                            } else {
                                $('#pwd').notify(subdata['error'],{
                                    autoHide: false,
                                    className: 'error'
                                });
                            }
                        }
                });
            } else {
                $('#pwd').notify(subdata['error'],{
                    autoHide: false,
                    className: 'error'
                });
            }
        }
    });
});

function init_script() {
   $('.leave_event').on('click',function(e){
        e.preventDefault();
        $.ajax({
            url: 'route.php',
            type: 'post',
                data: {'action': 'leave-event'},
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

    $('.logout').on('click',function(e){
        e.preventDefault();
        $.ajax({
            url: 'route.php',
            type: 'post',
                data: {'action': 'logout'},
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

    $('.admin_pages').on('click',function(e){
        e.preventDefault();
        show_index($(this).data('page'));
    });

    $('.delete_event').on('click',function(e){
        e.preventDefault();
        var page = $(this).data('page');
        if (!confirm('Вы уверены что хотите удалить мероприятие?')) {
            return;
        }
        $.ajax({
            url: 'meetings_app/route.php',
            type: 'post',
                data: {'action': 'delete_event',
                        event: $(this).data('event')
                    },
            error: function (xml, status, err) {
                console.log(err);
            },
            cache: true,
            success: function (data) {
                if(data['success'] == true) {
                    show_index(page);
                    $.notify('Мероприятие удалено',{
                            className: 'success'
                        }
                    );
                } else {
                    $.notify(data['error']);
                }
            }
        });
    });
}

function show_index(page) {
    $.ajax({
        url: 'index_page.php',
        type: 'post',
        data: {'page': page},
        error: function (xml, status, err) {
            console.log(err);
        },
        cache: true,
        success: function (data) {
            if (data['success'] === true){
                $('#container_meeting').html(data['html']);
                init_script();
            } else {
                $('#event_code').notify(data['error'],{
                    autoHide: false,
                    className: 'error'
                });
            }
        }
    });
}

function show_event(event_code) {
    $.ajax({
        url: 'route.php',
        type: 'post',
        data: {'action': 'login-user'
                , 'event': event_code
            },
        error: function (xml, status, err) {
            console.log(err);
        },
        cache: true,
        success: function (data) {
            var sid = null;

            var ec = new evercookie({
                phpuri: 'evercookie/php'
            });

            setTimeout(function () {
                ec.get("nm_sid", function(value) {
                    sid = value;
                    if (sid == undefined || sid == null) {
                        get_uniq_id();
                    }
                });
            },5000);

            if (data['success']){
                $('#container_meeting').html(data['html']);
                show();
            } else {
                $('#event_code').notify(data['error'],{
                    autoHide: false,
                    className: 'error'
                });
            }
        }
    });
}

function get_uniq_id(){
    var ec = new evercookie({
        phpuri: 'evercookie/php'
    });
    $.ajax({
            url: 'route.php',
            type: 'post',
            data: {'action': 'get-uniq-id'},
            error: function (xml, status, err) {
                console.log(err);
                return undefined;
            },
            cache: true,
            success: function (data) {
                if (data['success'] === true) {
                    ec.set("nm_sid", data['uniq_id']);
                    return data['uniq_id'];
                } else {
                    return undefined;
                }
            }
    });
}

function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}

function deleteCookie(name) {
    setCookie(name, "", {
        expires: -1
    });
}

function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}

$(document).ready(function(){
   init_script();
});