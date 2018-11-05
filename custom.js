$( document ).ready(function() {


    loadComments();

// Send comment client logic
$("#comment_box").submit(function(e) {
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');
    console.log(url);

    $.ajax({
        type: "POST",
        url: url,
        data: form.serialize(),
        success: function(data)
        {
            loadComments();
        }
    });
});

$(document).on("click", "#saveButtonAnswer", function (e) {
    $(".answer_box").submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function(data)
            {
                loadComments();
            }
        });
    });
});

$(document).on("click", ".reply", function (e) {
    e.preventDefault();
    var commentId = $(this).attr('href');
    $("#post-comment"+commentId).toggle();
});

// Send comment client logic
function loadComments () {
    $(".tettt").remove();
    var url = '/articles/history/8/true'; // hardcode for now (only one category)

    $.ajax({
        type: "GET",
        url: url,
        success: function(data)
        {
            $('.testt').html(data.html);
        }
    });
}
});
