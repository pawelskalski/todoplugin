//Get numeric id from element
function getThisId(element) {
    var id = element.attr("id").toString();
    var id = id.match(/\w+-(\d+)-\w+/)[1];
    return id;
}


//Send ajax request and build new DOM elements from ajax response
function createNewToDo(checkbox, message) {
    if (message.length > 0) {
        if (checkbox) {
            checkbox = 'true'
        } else {
            checkbox = 'false'
        }
        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_new_todo',
                message: message,
                checkbox: checkbox
            },
            success: function (response) {
            }
        }).done(function (response, status, jqXHR) {
            $("#message").val('');
            var newestTodo = response.data[response.data.length - 1];
            var newestTodo_ID = newestTodo["ID"];
            var newestTodo_message = newestTodo["message"];
            if (newestTodo["status"] === "true") {
                var newestTodo_status = "checked";
            } else {
                var newestTodo_status = "";
            }
            var newTodoBody = "" +
                "<div class=\"pskalski-todo-task-wrapper\">\n" +
                "<form class=\"row\" id=\"item-" + newestTodo_ID + "\">\n" +
                "<div class=\"position-relative col-2 p-0\">\n" +
                "<label class=\"b-contain center-horizontaly-verticaly position-relative col-2 p-0\">\n" +
                "<input type=\"checkbox\" onclick=updateToDoStatus(" + newestTodo_ID + "," + newestTodo_status + ") class=\"center-horizontaly-verticaly\" id=\"item-" + newestTodo_ID + "-status \"" + newestTodo_status + ">\n" +
                "<div class=\"b-input center-horizontaly-verticaly\"></div>\n" +
                "</label>\n" +
                "</div>\n" +
                "<div class=\"col-10 p-0\">\n" +
                "<input type=\"hidden\" id=\"item-" + newestTodo_ID + "\" value=\"" + newestTodo_ID + "\">\n" +
                "<textarea  type=\"text\" id=\"item-" + newestTodo_ID + "-message\">" + newestTodo_message + "</textarea>\n" +
                "<button class=\"delete_button\" id=\"delete-" + newestTodo_ID + "-item\">x</button>\n" +
                "</div>\n" +
                "</form>\n" +
                "</div>";
            $("#pskalskiWrapper > div:nth-child(1)").after(newTodoBody);
            $('form').each(function () {
                $(this).find('textarea').keypress(function (e) {
                    if (e.which === 10 || e.which === 13) {
                        e.preventDefault();
                        try {
                            var id = getThisId($(this));
                            var message = $(this).val();
                            updateToDoMessage(id, message);
                        } catch (e) {
                            //Ignore
                        }
                    }
                });
                $(this).find('textarea').on('focus', function() {
                    var id = getThisId($(this));
                    var delete_button = "#delete-"+id+"-item";
                    $(delete_button).fadeIn();
                });
                $(this).find('textarea').blur(function (e) {
                    e.preventDefault();
                    try {
                        var id = getThisId($(this));
                        var message = $(this).val();
                        updateToDoMessage(id, message)
                        var id = getThisId($(this));
                        var delete_button = "#delete-"+id+"-item";
                        $(delete_button).fadeOut();
                    } catch (e) {
                        //Ignore
                    }
                });
            });
            $('.delete_button').click(function (e) {
                e.preventDefault();
                var id = getThisId($(this));
                deleteToDo(id);
            });
        });
    } else {
        alert("Task cant be empty, write somethink")
    }
}


//Send ajax request to update task status
function updateToDoStatus(id, status) {
    $.ajax({
        url: ajax_object.ajaxurl,
        type: 'POST',
        data: {
            action: 'update_status_todo',
            id: id,
            status: status,
        }
    })
}


//Send ajax request to delete task, and delete DOM element
function deleteToDo(id) {
    $.ajax({
        url: ajax_object.ajaxurl,
        type: 'POST',
        data: {
            action: 'delete_todo',
            id: id,
        }
    }).done(function (response, status, jqXHR) {
        $("#item-" + id).remove();
    })
}


//Send ajax response to update task message
function updateToDoMessage(id, message) {
    $.ajax({
        url: ajax_object.ajaxurl,
        type: 'POST',
        data: {
            action: 'update_message_todo',
            id: id,
            message: message,
        }
    })
}


//All event listeners are here
$(document).ready(function ($) {

    $("#submit_button").click(function (e) {
        e.preventDefault();
        var checkbox = $('#checkbox').prop('checked');
        var message = $("#message").val();
        createNewToDo(checkbox, message);
    });

    $("#message").keypress(function (e) {
        if (e.which === 10 || e.which === 13) {
            e.preventDefault();
            var checkbox = $('#checkbox').prop('checked');
            var message = $("#message").val();
            createNewToDo(checkbox, message)
        }
    });

    $('input[type=checkbox]').change(function () {
        try {
            var id = getThisId($(this));
            var status = $(this).prop("checked");
            updateToDoStatus(id, status);
        } catch (e) {
            //ignore
        }
    });

    $('form').each(function () {
        $(this).find('textarea').keypress(function (e) {
            if (e.which === 10 || e.which === 13) {
                e.preventDefault();
                try {
                    console.log($(this));
                    var id = getThisId($(this));
                    var message = $(this).val();
                    updateToDoMessage(id, message);
                } catch (e) {
                    //Ignore
                }
            }
        });
        $(this).find('textarea').on('focus', function() {
            var id = getThisId($(this));
            var delete_button = "#delete-"+id+"-item";
            $(delete_button).fadeIn();
        });
        $(this).find('textarea').blur(function (e) {
            e.preventDefault();
            try {
                var id = getThisId($(this));
                var message = $(this).val();
                updateToDoMessage(id, message)
                var id = getThisId($(this));
                var delete_button = "#delete-"+id+"-item";
                $(delete_button).fadeOut();
            } catch (e) {
                //Ignore
            }
        });
    });
    $('.delete_button').click(function (e) {
        e.preventDefault();
        var id = getThisId($(this));
        deleteToDo(id);
    });

});

