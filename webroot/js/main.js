// Opening and closing edit mode for comments
$(document).on('click','.messagebox .msgaction a.edit',function(){
    
    var container = $(this).closest('.messagebox');
    var contentBox = container.find('.msg-content');
    
    var text = contentBox.find('span').text();
    
    var path = container.find('.msgaction').data('edit-url');
    
    var editForm = $('<form></form>');
    
    editForm.attr('method', 'post');
    editForm.attr('action', path);
    
    var textarea = $('<textarea name="content">' + text + '</textarea>');
    
    container.addClass('edit-mode');
    editForm.append(textarea);
    contentBox.append(editForm);
    
    return false;       
});

$(document).on('click','.messagebox .msgaction a.cancel',function(){

    var container = $(this).closest('.messagebox');
    var contentBox = container.find('.msg-content');

    contentBox.find('form').remove();
    container.removeClass('edit-mode');

    return false;    
});


// Send the edit form an clicking save link
$(document).on('click','.messagebox .msgaction a.save',function(){
    
    $(this).closest('.messagebox').find('form').submit();
    
    return false;
});


$(document).on('onmousemove', 'body',function(){
    $('.message').delay(2500).fadeOut(1000);
});

//These two functions are for previewing the uploaded image.
$(document).on('change','input.preview',function(){
    readURL(this);
});
function readURL(input) {

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('.portrait img').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function selectNavButton(clicked) {
    var id = '#'+clicked.toLowerCase()+'Btn';

    const loc = $(location).attr('pathname');

    if (loc === '/mmt-5/projects') {
        id = '#homeBtn';
    } else if (loc.substring(0, loc.lastIndexOf('/')+1) === '/mmt-5/projects/view/') {
        id = '#projectViewBtn';
    } else if (loc === '/mmt-5/users/add') {
        id = '#usersBtn';
    }
    else if (loc === '/mmt-5/projects/add') {
        id = '#addBtn';
    }
    else if (loc === '/mmt-5/metrictypes') {
        id = '#metricsBtn';
    }
    else if (loc === '/mmt-5/worktypes') {
        id = '#workTypeBtn';
    }
    else if (loc === '/mmt-5/notes') {
        id = '#notesBtn';
    }


    $('.navtop ul a').removeClass('selectedLink');

    $(id+ ' a').addClass('selectedLink');
}
