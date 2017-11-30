var guid = (function() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
                   .toString(16)
                   .substring(1);
    }
    return function() {
        return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
               s4() + '-' + s4() + s4() + s4();
    };
})();


$(document).ajaxStart(function() {
    $.blockUI({message: '<div style="padding: 12px; background-color: #d9dde2"><span style="font-size: 20px">Loading...</span></div>'});
});

$(document).ajaxStop(function() {
    $.unblockUI();
});


$(document).ready(function(){
    $('form').on('click', 'input:checkbox.radio', function() {
        $this = $(this);
        $('input:checkbox[name=\''+$this.attr('name')+'\']').prop('checked', false);
        $this.prop('checked', true);
    });
    
    $('form').on('click', 'input:checkbox.boolean', function() {
        var $this = $(this);
        if ($this.prop('checked'))
            $this.val(1);
        else
            $this.val(0);
    });
});


$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    a = a.concat(
        $(this).find('input[type=checkbox]:not(:checked, .radio)').map(function() {
            return {'name': this.name, 'value': this.value};
        }).get()
    );
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};


function sendRequest(_params)
{
    if (_params.action)
    {
        var url = _params.action.charAt(0)=='/' ? _params.action : '/async/'+_params.action;
        var ajaxGlobal = _params.ajaxGlobal == undefined ? true : _params.ajaxGlobal;
        var ajaxAsync = _params.ajaxAsync == undefined ? true : _params.ajaxAsync;
    /* --- Modify by Kucher Denis 09-05-2017 for upload file  (start) --- */    
        var processData = _params.processData == undefined ? true : _params.processData;
        var contentType = _params.contentType == undefined ? 'application/x-www-form-urlencoded; charset=UTF-8' : _params.contentType;
    /* --- Modify by Kucher Denis 09-05-2017 for upload file  (end) --- */
        $.ajax({
            async: ajaxAsync,
            global: ajaxGlobal,
            type: 'POST',
            url: url,
            dataType: 'json',
            
            processData: processData,
            contentType: contentType,

            data: _params.data,
            cache: false,
            success: function(_response, _textStatus, _jqXHR) {
                //alert('Success: ' + _jqXHR.responseText);
                _params.successHandler({response: _response, requestData: _params.data});
            },
            error: function(_jqXHR, _textStatus, _errorThrown) {
                if (isBeforeunloadSupported()) {
                        if (! beforeunloadCalled) {
                            alert('Error: ' + _jqXHR.responseText);
                        }
                    } else {
                        setTimeout(function () {
                            alert('Error: ' + _jqXHR.responseText);
                        }, 1000);
                }
            },
            complete: function(_jqXHR, _textStatus)
            {
                if (_params.completeHandler)
                    _params.completeHandler({requestData: _params.data});
            }
        });
    }
}


function clearForm($_form)
{
    $_form.find('input:text, input:password, input:file, select, textarea').val('');
    $_form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
}


function populateForm($form, data)
{
    clearForm($form);
    $.each(data, function(key, value) {
        if (value instanceof Array) {
            var len = value.length;
            for (var i = 0; i < len; i++) {
                var $ctrl = $form.find('[name=\''+key+'['+i+']'+'\']');
                setFormControlValue($ctrl, value[i]);
            }
        } else if (value instanceof Object) {
            for (var i in value) {
                var $ctrl = $form.find('[name=\''+key+'['+i+']'+'\']');
                setFormControlValue($ctrl, value[i]);
            }
        } else {
            var $ctrl = $form.find('[name='+key+']');
            setFormControlValue($ctrl, value);
        }
    });
}


function setFormControlValue($_ctrl, _value)
{
    if ($_ctrl.is('select')){
        $('option', $_ctrl).each(function() {
            if ($(this).val() == _value)
                this.selected = true;
        });
    } else if ($_ctrl.is('textarea')) {
        $_ctrl.val(_value);
    } else {
        switch($_ctrl.attr("type")) {
            case "text":
            case "hidden":
                $_ctrl.val(_value);   
                break;
            case "checkbox":
                if (_value == '1')
                {
                    $_ctrl.prop('checked', true);
                    $_ctrl.val(1);
                }
                else
                {
                    $_ctrl.prop('checked', false);
                    $_ctrl.val(0);
                }
                break;
        } 
    }
}


function getCaptcha($_form)
{
    $captchaBox = $(
'<div style="display: none" id="captcha-box">'+
'    <div style="width: 160px; float: left;">'+
'        <p style="color: #000000; float: none; font: normal 18px \'Times New Roman\'; margin: 0 0 10px; padding: 0; text-align: left; width: auto;">To get your quote please enter the numbers below:</p>'+
'        <form id="captcha-form" action="/async/basic.verifycaptcha" method ="post">'+
'            <div class="captcha"></div>'+
'            <input type="text" name="captcha" value="">'+
'            <input type="submit" value="SEND">'+
'        </form>'+
'    </div>'+
'    <div style="width: 120px; float: left; padding-left: 16px; text-align: center">'+
'        <p style="color: #000000; float: none; font: normal 12px/14px \'Times New Roman\'; margin: 0 0 10px; padding: 0; text-align: justify; width: auto;">This is a verification system to ensure you are a person and not an automated system.</p>'+
'        <p style="color: #000000; float: none; font: normal 18px \'Times New Roman\'; margin: 0; padding: 0; text-align: left; width: auto;"><img src="/application/modules/basic/images/logo-captcha.png"></p>'+
'    </div>'+
'</div>'
    );
    
    
    var $captchaForm = $captchaBox.find('form#captcha-form');
    $captchaForm.off('submit');
    var $submit = $captchaForm.find('input[type=submit]');
    $submit.prop('disabled', false);
    
    
    $captchaForm.submit(function(e) {
        e.preventDefault();
        
        $submit.prop('disabled', true);
        var data = $captchaForm.serializeObject();
        
        sendRequest({
            action: $captchaForm.attr('action'),
            data: data,
            successHandler: function(_callbackParams) {
                var response = _callbackParams.response;
                if (!response.success)
                    alert(response.message);
                else
                {
                    $.modal.close();
                    var $captchaInput = $_form.find('input[name=captcha]');
                    if ($captchaInput.length == 0)
                        $_form.prepend('<input type="hidden" name="captcha" value="'+data.captcha+'" />');
                    else
                        $captchaInput.val(data.captcha);
                    $_form.submit();
                }
            },
            completeHandler: function(_callbackParams) {
                $submit.prop('disabled', false);
            }
        });
    });
    
    
    clearForm($captchaForm);
    $captchaBox.find('div.captcha').html('<img src="/captcha.php?r='+Math.random()+'" />');
    $captchaBox.modal({overflow: 'hidden'});
}


/*(function($) {
    $.fn.ajaxForm = function(_options) {
        // Default settings
		var settings = jQuery.extend({
            dummy: 0,
            clearFormOnSuccess: true,
            onSuccess: function() { alert('Your request has been sent.'); },
            beforeSubmit: function() {}
		}, _options);
        
        
        // Overwrite form's submit method
        this.submit(function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submit = $form.find('input[type=submit]');
            $submit.prop('disabled', true);
            
            settings.beforeSubmit();
            sendRequest({
                action: $form.attr('action'),
                data: $form.serializeObject(),
                successHandler: function(_callbackParams) {
                    var response = _callbackParams.response;
                    if (!response.success)
                    {
                        if (response.errors && response.errors.captcha)
                            getCaptcha($form);
                        else
                            alert(response.message);
                    }
                    else
                    {
                        if (settings.clearFormOnSuccess)
                            clearForm($form);
                        settings.onSuccess();
                    }
                },
                completeHandler: function(_callbackParams) {
                    $submit.prop('disabled', false);
                }
            });
        });
        
        
        return this;
    };
})(jQuery);*/


(function($) {
    $.fn.placeholder = function(_options) {
		var settings = jQuery.extend({
            dummy: 0,
            text: 'Enter...',
            className: 'placeholder'
		}, _options);
        
        this.val(settings.text);
        this.addClass(settings.className);
        
        this.focusin(function() {
            if ($(this).hasClass(settings.className)) {
                $(this).removeClass(settings.className);
                $(this).val('');
            }
        });
        
        this.focusout(function() {
            if ($(this).val() == '') {
                $(this).val(settings.text);
                $(this).addClass(settings.className);
            }
        });
        return this;
    };
})(jQuery);


(function($, global) {
    var field = 'beforeunloadSupported';
    if (global.localStorage &&
        global.localStorage.getItem &&
        global.localStorage.setItem &&
        ! global.localStorage.getItem(field)) {
        $(window).on('beforeunload', function () {
            global.localStorage.setItem(field, 'yes');
        });
        $(window).on('unload', function () {
            // If unload fires, and beforeunload hasn't set the field,
            // then beforeunload didn't fire and is therefore not
            // supported (cough * iPad * cough)
            if (! global.localStorage.getItem(field)) {
                global.localStorage.setItem(field, 'no');
            }
        });
    }
    global.isBeforeunloadSupported = function () {
        if (global.localStorage &&
            global.localStorage.getItem &&
            global.localStorage.getItem(field) &&
            global.localStorage.getItem(field) == "yes" ) {
            return true;
        } else {
            return false;
        }
    }
})(jQuery, window);


var beforeunloadCalled = false;
// Beware that 'beforeunload' is not supported in all browsers.
// iPad / iPhone - I'm looking at you.
$(window).on('beforeunload', function () {
    beforeunloadCalled = true;
});


function formatDateMmDdYyyy(date)
{
    return date.substring(5,7)+'-'+date.substring(8,10)+'-'+date.substring(0,4);
}


function formatDateYyyyMmDd(date)
{
    return date.substring(6,10)+'-'+date.substring(0,2)+'-'+date.substring(3,5);
}


function formatDateMmDdYyyyHhIiSs(date)
{
    return date.substring(5,7)+'-'+date.substring(8,10)+'-'+date.substring(0,4)+' '+date.substring(11,19);
}


function getUtcOffset()
{
    var date = new Date();
    var hours = -date.getTimezoneOffset()/60;
    return (hours>=0?'+':'')+hours+':00';
}


function formatFloatTwoDecimalDigits(_value)
{
    return parseFloat(Math.round(_value*100)/100).toFixed(2);
    //return _value;
}


jQuery.fn.selectText = function() {
   var doc = document;
   var element = this[0];
   if (doc.body.createTextRange) {
       var range = document.body.createTextRange();
       range.moveToElementText(element);
       range.select();
   } else if (window.getSelection) {
       var selection = window.getSelection();        
       var range = document.createRange();
       range.selectNodeContents(element);
       selection.removeAllRanges();
       selection.addRange(range);
   }
};


function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    if(text === null || text ===false){
        return '';
    }
    if(text.length){
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }else{
        return text;
    }
}

function bind_blueimp(params) {
    $(function () {
        var links = document.getElementById(params.containerId).getElementsByClassName(params.linkClass);
        blueimp.Gallery(
            links, {
                container: '#'+params.containerId+' .'+params.blueimpClass,
                carousel: true,
                slideshowInterval: parseInt(params.slideTime) * 1000,
                stretchImages: params.imgType,
                onslide: function () {
                    var $caption = $('#'+params.containerId+' .'+params.blueimpClass + ' .caption');
                    $caption.animate({'bottom': '-' + $caption.outerHeight() + 'px'}, function () {
                    });
                    $caption.find('.icons .icon').hide('slow');
                },
                onslideend: function (index, slide) {
                    var $frame = $('#'+params.containerId + ' .frames .frame:eq(' + index + ')');
                    var $href = $frame.attr('data-href');
                    var $caption = $('#'+params.containerId+' .'+params.blueimpClass + ' .caption');
                    var $title = $frame.find('.title').html();
                    if ($title) {
                        if ($href == undefined || $href == '') {
                            $caption.find('.title-normal').html($title);
                            $caption.find('.title-short').html($frame.find('.title-short').html());
                        } else {
                            $caption.find('.title-normal').html('<a href="' + $href + '" target="_blank">' + $title + '</a>');
                            $caption.find('.title-short').html('<a href="' + $href + '" target="_blank">' + $frame.find('.title-short').html() + '</a>');
                        }
                        $caption.find('.description').html($frame.find('.description').html());
                        if ($frame.attr('data-photo') == '1') $caption.find('.icons .icon-photo').attr('href', $href + '#photos').show('slow');
                        if ($frame.attr('data-video') == '1') $caption.find('.icons .icon-video').attr('href', $href + '#videos').show('slow');
                        $caption.animate({'bottom': 0});
                    }
                }
            }
        );
    });
}
