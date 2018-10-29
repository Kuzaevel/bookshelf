jQuery( document ).ready(function( $ ) {

init();

});

function init(){
    console.log('start init');

    $('.new-book').click(function (e) {
        e.preventDefault();
        var post_url = $(this).attr("href");
        $.get(post_url,function(response) {
            $(".restore-block").html(response).after(init());
        })
    });

    $('.btn-add-book').click(function (e) {
        e.preventDefault();
        var form_data = $('.form-add_boo').serialize();
        console.log(form_data);

        $.post('/add',form_data,function(response) {
            $(".restore-block").html(response).after(init());
        });
    })
}