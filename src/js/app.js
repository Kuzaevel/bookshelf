jQuery( document ).ready(function( $ ) {
    init();
});

function init(){

    $('.new-book').click(function (e) {
        e.preventDefault();
        var post_url = $(this).attr("href");
        $.get(post_url,function(response) {
            $(".restore-block").html(response).after(init());
        })
    });

    $('.btn-add-book').click(function (e) {
        e.preventDefault();
        var form_data = $('.form-add_book').serialize();
        $.post('/add',form_data,function(response) {
            $(".restore-block").html(response).after(init());
        });
    });

    $('.btn-view_book').click(function (e) {
        e.preventDefault();
        var post_url = $(this).attr("data-link-atr");
        $.get(post_url,function(response) {
            $(".restore-block").html(response).after(init());
        });
    });

    $('.btn-edit_book').click(function (e) {
        e.preventDefault();
        var post_url = $(this).attr("data-link-atr");
        $.get(post_url,function(response) {
            $(".restore-block").html(response).after(init());
        });
    });

    $('.form-edit_book').submit(function (e) {
        e.preventDefault();
        var form_data = $(this).serialize();
        var post_url = $(this).attr("action");
        $.post(post_url,form_data,function(response) {
            $(".restore-block").html(response).after(init());
        });
    });

    $('.btn-delete_book').click(function (e) {
        e.preventDefault();
        var post_url = $(this).attr("data-link-atr");
        $.get(post_url,function(response) {
            $(".restore-block").html(response).after(init());
        });
    });

    $('.btn-back-to-table').click(function (e) {
        e.preventDefault();
        $.post('/',function(response) {
            $(".restore-block").html(response).after(init());
        });
    });
}