$(function() {
    submitComment();
    hashValue();
    onFocusValidation();
    $parentCommentId = '';
    replyComment();

    if ($('.tx_nscomments .approvedmessage').length) {
        $('html, body').stop().animate({
            scrollTop: ($('.tx_nscomments .approvedmessage').offset().top)
        }, 2000);
        setTimeout(function() {
            $('.tx_nscomments .approvedmessage').fadeOut("slow");
        }, 9000);
    }

    if ($('.tx_nscomments .approvecomment').length) {
        showpopup();
    }
    $(".tx_nscomments .approvecomment #cancel_button").click(function(){
        hidepopup();
    });
    $(".tx_nscomments .approvecomment #close_button").click(function(){
        hidepopup();
    });
});

// Show popup div
function showpopup()
{
    $(".tx_nscomments .approvecomment #popup_box").fadeToggle();
    $(".tx_nscomments .approvecomment #popup_box").css({"visibility":"visible","display":"block"});
}

// Hide popup div
function hidepopup()
{
    $(".tx_nscomments .approvecomment #popup_box").fadeToggle();
    $(".tx_nscomments .approvecomment #popup_box").css({"visibility":"hidden","display":"none"});
}

function replyComment() {
    $(document).on("click", '.comment-btn.reply', function(event) {
        var parentCommentId = $(this).parent().attr('id');
        $('#'+ parentCommentId + ' .comment-btn.reply').hide();
    });
}

// Scroll to paramlink
function hashValue() {
    // get hash value
    var hash = window.location.hash;
    // now scroll to element with that id
    if (hash != '') {
        $('html, body').stop().animate({
            scrollTop: ($(hash).offset().top)
        }, 2000);
    }
}

// Submit form using ajax
function submitComment() {

    // Submit comment
    $(document).on('submit', '.tx_nscomments #comment-form', function(event) {
        var captcha = $('.tx_nscomments #captcha').val();
        var ajaxURL = $(this).attr('action');
        var datatype = $('.tx_nscomments #dataType').val();
        var commentHTML = $('.active-comment-form').html();
        if (!event.isDefaultPrevented()) {
            if (validateField()) {
                $.ajax({
                    type: 'POST',
                    url: ajaxURL,
                    dataType: datatype,
                    cache:true,
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('.tx_nscomments #submit').attr('disabled', true);
                        $('.tx_nscomments #submit').css('cursor', 'default');
                    },
                    success: function(response) {
                        if ($('.tx_nscomments #approval').val() == 0) {
                            // GET HTML for comment list
                            $(".tx_nscomments #comments-list").load(location.href + " .tx_nscomments #comments-list>*", function(responseTxt, statusTxt, jqXHR) {
                               if(statusTxt == "success"){
                                    // Scroll to comment
                                    $.each(response, function(key, val) {
                                        if (val.parentId == '') {
                                            $('.tx_nscomments .thanksmsg').show();
                                            $('html, body').stop().animate({
                                                scrollTop: ($('.tx_nscomments .thanksmsg').offset().top)
                                            }, 2000);
                                            setTimeout(function() {
                                                $('.tx_nscomments .thanksmsg').fadeOut("slow");
                                            }, 7000);
                                        } else {

                                            $('.tx_nscomments .thanksmsg-' + val.parentId).show();
                                            $('html, body').stop().animate({
                                                scrollTop: ($('.tx_nscomments .thanksmsg-' + val.parentId).offset().top)
                                            }, 2000);
                                            setTimeout(function() {
                                                $('.tx_nscomments .thanksmsg-' + val.parentId).fadeOut("slow");
                                            }, 7000);
                                            $('.tx_nscomments #comments-' + val.parentId).fadeIn('slow');
                                            $('.tx_nscomments #parentId').val('');
                                        }
                                    });
                                }
                                if(statusTxt == "error"){
                                    alert("Error: " + jqXHR.status + " " + jqXHR.statusText);
                                }
                            });
                        } else {
                            try {
                                data = JSON.parse(response);
                                if(data.status == 'success') {
                                    $('.tx_nscomments .approve').show();
                                    $('html, body').stop().animate({
                                        scrollTop: ($('.tx_nscomments .approve').offset().top)
                                    }, 2000);
                                    setTimeout(function() {
                                        $('.tx_nscomments .approve').fadeOut("slow");
                                    }, 7000);
                                } else {
                                    commentError();
                                }
                            } catch(e) {
                                commentError();
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert(textStatus + " " + errorThrown);
                    },
                    complete: function(response) {
                        $('.tx_nscomments #comment-form')[0].reset();
                        var captcha = document.getElementById("captcha");
                        if(captcha){
                            refreshCaptcha();
                        }
                        $('.tx_nscomments #submit').attr('disabled', false);
                        $('.tx_nscomments #submit').css('cursor', 'pointer');
                        addForm();
                    },
                });
                event.preventDefault();
            } else {
                return false;
            }
        }
    });

    // Reply form
    $(document).on("click", '.reply', function(event) {
        var parentCommentId = $(this).parent().attr('id');
        var commentHTML = $('.active-comment-form').html();
        $('.active-comment-form .comment-form')[0].reset();
        $('.active-comment-form').html('');
        $('.active-comment-form').removeClass('active-comment-form');
        $(this).parent().parent().parent().parent().find('#reply-form-' + parentCommentId).append(commentHTML);
        $(this).parent().parent().parent().parent().find('#reply-form-' + parentCommentId).addClass('active-comment-form');
        $('#comment-form-close-btn').show();
        removeDefaultValidation();

        // Scroll to form position
        $('html, body').stop().animate({
            scrollTop: ($('.tx_nscomments #reply-form-' + parentCommentId).offset().top)
        }, 1000);

        // Set hidden parentId
        $('.tx_nscomments #parentId').val(parentCommentId);
        onFocusValidation();
    });

    // Close form
    $(document).on("click", ".tx_nscomments #comment-form-close-btn", function(event) {
        var parentCommentIdClose = $('#parentId').val();;
        $('#'+ parentCommentIdClose + ' .comment-btn.reply').show();
        addForm();
    });
}

// Open form on close button click
function addForm() {
    var commentHTML = $('.active-comment-form').html();
    $('.tx_nscomments .active-comment-form').html('');
    $('.tx_nscomments .active-comment-form').removeClass('active-comment-form');
    $('.tx_nscomments #form-comment-view').html(commentHTML);
    $('.tx_nscomments #form-comment-view').addClass('active-comment-form');
    $('.tx_nscomments #comment-form-close-btn').hide();
    $('.tx_nscomments #parentId').val('');
    removeDefaultValidation();
    onFocusValidation();
}

function commentError() {
    $('.tx_nscomments .approve-error').show();
    $('html, body').stop().animate({
        scrollTop: ($('.tx_nscomments .approve-error').offset().top)
    }, 2000);
    setTimeout(function() {
        $('.tx_nscomments .approve-error').fadeOut("slow");
    }, 7000);
}

// Custom Validation 
function validateField() {
    var flag = 1;
    var elementObj;
    var captcha = document.getElementById("captcha");
    var terms = document.getElementsByName('tx_nscomments_comment[newComment][terms]').length;

    if (!$('.tx_nscomments #name').val()) {
        $(".tx_nscomments #name").parent().addClass('has-error');
        $('.tx_nscomments #name_error').show();
        var flag = 0;
    } else {
        if (!validateName($('.tx_nscomments #name').val())) {
            $(".tx_nscomments #name_error_msg").show();
            $(".tx_nscomments #name_error").hide();
            $(".tx_nscomments #name").parent().addClass('has-error');
            var flag = 0;
        } else {
            $(".tx_nscomments #name").parent().removeClass('has-error');
            $(".tx_nscomments #name_error_msg").hide();
            $(".tx_nscomments #name_error").hide();
        }
    }

    if (!$('.tx_nscomments #email').val()) {
        $(".tx_nscomments #email").parent().addClass('has-error');
        $(".tx_nscomments #email_error").show();
        $(".tx_nscomments #email_error_msg").hide();
        var flag = 0;
    } else {
        if (!validateEmail($('.tx_nscomments #email').val())) {
            $(".tx_nscomments #email_error_msg").show();
            $(".tx_nscomments #email_error").hide();
            $(".tx_nscomments #email").parent().addClass('has-error');
            var flag = 0;
        } else {
            $(".tx_nscomments #email").parent().removeClass('has-error');
        }
    }

    if (!$('.tx_nscomments #comment').val()) {
        $(".tx_nscomments #comment").parent().addClass('has-error');
        $(".tx_nscomments #comment_error").show();
        var flag = 0;
    } else {
        var length = $.trim($(".tx_nscomments #comment").val()).length;
        if (length == 0) {
            $(".tx_nscomments #comment_error").show();
            $(".tx_nscomments #comment").parent().addClass('has-error');
            var flag = 0;
        } else {
            $(".tx_nscomments #comment").parent().removeClass('has-error'); // remove it
        }
    }

    if(captcha){
        if (!$('.tx_nscomments #captcha').val()) {
            $(".tx_nscomments #captcha").parent().addClass('has-error');
            $(".tx_nscomments #captcha_error").show();
            $(".tx_nscomments #captcha_valid_error").hide();
            var flag = 0;
        } else {
            if (validateCaptcha($('.tx_nscomments #captcha').val()) == 'true') {
                $(".tx_nscomments #captcha").parent().removeClass('has-error'); // remove it
            } else {
                $(".tx_nscomments #captcha_valid_error").show();
                $(".tx_nscomments #captcha_error").hide();
                $(".tx_nscomments #captcha").parent().addClass('has-error');
                var flag = 0;
            }
        }
    }

    if (terms) {
        if ( !$('.tx_nscomments input[name="tx_nscomments_comment[newComment][terms]"]:checked').length ) {
            $(".tx_nscomments #terms").closest('.ns-form-group').addClass('has-error');
            $(".tx_nscomments #terms_error").show();
            var flag = 0;
        } else {
            $(".tx_nscomments #terms").closest('.ns-form-group').removeClass('has-error');
            $(".tx_nscomments #terms_error").hide();
        }
    }

    if (flag == 1) {
        return true;
    }
}

// Custom validation for onfocus
function onFocusValidation() {

    $(".tx_nscomments #name").focusout(function() {
        elementObj = $(this);
        if (elementObj.val() != '') {
            if (!validateName($('.tx_nscomments #name').val())) {
                $(".tx_nscomments #name_error_msg").show();
                $(".tx_nscomments #name_error").hide();
                $(".tx_nscomments #name").parent().addClass('has-error');
                var flag = 0;
            } else {
                elementObj.parent().removeClass('has-error');
                $(".tx_nscomments #name_error_msg").hide();
                $(".tx_nscomments #name_error").hide();
            }
        } else {
            $(".tx_nscomments #name").parent().addClass('has-error');
            $(".tx_nscomments #name_error").show();
            $(".tx_nscomments #name_error_msg").hide();
        }
    });

    $(".tx_nscomments #email").focusout(function() {
        elementObj = $(this);
        if (elementObj.val() != '') {
            if (!validateEmail($('.tx_nscomments #email').val())) {
                $(".tx_nscomments #email_error_msg").show();
                $(".tx_nscomments #email_error").hide();
                $(".tx_nscomments #email").parent().addClass('has-error');
                var flag = 0;
            } else {
                elementObj.parent().removeClass('has-error');
                $(".tx_nscomments #email_error_msg").hide();
                $(".tx_nscomments #email_error").hide();
            }
        } else {
            $(".tx_nscomments #email").parent().addClass('has-error');
            $(".tx_nscomments #email_error").show();
            $(".tx_nscomments #email_error_msg").hide();
        }
    });

    $(".tx_nscomments #comment").focusout(function() {
        elementObj = $(this);
        if (elementObj.val() != '') {
            var length = $.trim($(".tx_nscomments #comment").val()).length;
            if (length == 0) {
                $(".tx_nscomments #comment_error").show();
                $(".tx_nscomments #comment").parent().addClass('has-error');
                var flag = 0;
            } else {
                $(".tx_nscomments #comment").parent().removeClass('has-error'); // remove it
                $(".tx_nscomments #comment_error").hide();
            }

        } else {
            $(".tx_nscomments #comment").parent().addClass('has-error');
            $(".tx_nscomments #comment_error").show();
        }
    });

    $(".tx_nscomments #captcha").focusout(function() {
        elementObj = $(this);
        if (elementObj.val() != '') {
            var length = $.trim($(".tx_nscomments #captcha").val()).length;
            if (length == 0) {
                $(".tx_nscomments #captcha_error").show();
                $(".tx_nscomments #captcha").parent().addClass('has-error');
                var flag = 0;
            } else {
                $(".tx_nscomments #captcha").parent().removeClass('has-error'); // remove it
                $(".tx_nscomments #captcha_error").hide();
                $(".tx_nscomments #captcha_valid_error").hide();
            }
        } else {
            $(".tx_nscomments #captcha").parent().addClass('has-error');
            $(".tx_nscomments #captcha_error").show();
        }
    });

    $('.tx_nscomments input[name="tx_nscomments_comment[newComment][terms]"]').on('change', function(){
        if ( !$('.tx_nscomments input[name="tx_nscomments_comment[newComment][terms]"]:checked').length ) {
            $(".tx_nscomments #terms").closest('.ns-form-group').addClass('has-error');
            $(".tx_nscomments #terms_error").show();
            var flag = 0;
        } else {
            $(".tx_nscomments #terms").closest('.ns-form-group').removeClass('has-error');
            $(".tx_nscomments #terms_error").hide();
        }
    });

}

// Remove Default Validation in reply form
function removeDefaultValidation() {
    $(".tx_nscomments #name").parent().removeClass('has-error'); // remove it
    $(".tx_nscomments #name_error").hide();
    $(".tx_nscomments #name_error_msg").hide();

    $(".tx_nscomments #email").parent().removeClass('has-error');
    $(".tx_nscomments #email_error").hide();
    $(".tx_nscomments #email_error_msg").hide();

    $(".tx_nscomments #comment").parent().removeClass('has-error');
    $(".tx_nscomments #comment_error").hide();

    $(".tx_nscomments #captcha").parent().removeClass('has-error');
    $(".tx_nscomments #captcha_error").hide();
    $(".tx_nscomments #captcha_valid_error").hide();
}

// Validate Captcha field using ajax request
function validateCaptcha(captcha) {

    var dataString = 'captcha=' + captcha;
    var url = $('.verification').val();
    var response = $.ajax({
        type: 'POST',
        async: false,
        url: url,
        data: dataString,
        success: function(response) {

        },
        error: function() {
            alert('Captcha not Mathched');
        }
    });
    return response.responseText;
}

// Validate Email field
function validateEmail($email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test($email);
}

// Validate Name field
function validateName($name) {
    var nameReg = /[^0-9]/g;
    return nameReg.test($name);
}

// Referech Captcha
function refreshCaptcha() {
    var img = document.images['captchaimg'];
    img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
}