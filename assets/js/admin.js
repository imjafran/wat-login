if (typeof ($) === 'undefined') {
    var $ = jQuery
}

class _ComboPOS_Class {

    constructor() {
        // do something on load
        console.log("ComboPOS Initialized Successfully");
    }

    // chnage tab
    changeTab(tab) {
        $('.combopos_wrap .tab-nav a').removeClass('active')
        $('.combopos_wrap .tab-nav a:nth-child(' + tab + ')').addClass('active')
        $('.combopos_wrap .tab-content li').hide()
        $('.combopos_wrap .tab-content li:nth-child(' + tab + ')').fadeIn()
    }

    loading(message = "Please wait loading..") {
        if (message) {
            Swal.fire({
                title: message,
                showConfirmButton: false,
                showCancelButton: false,
            });
            Swal.showLoading();
        } else {
            Swal.hideLoading();
            Swal.close();
        }
        return true;
    }

    message(title, icon = 'success', timer = 3000, position = 'center') {
        Swal.hideLoading();
        Swal.fire({
            title: title,
            icon: icon,
            timer: timer,
            position: position
        });
    }

    success(title) {
        this.message(title);
    }

    error(title) {
        this.message(title, 'error');
    }


}


// instance of combopos 
const ComboPOS = new _ComboPOS_Class();


// delivery time for shop_order 
$(document).on("click", ".delivery_time_quick a", function (e) {
    e.preventDefault()
    let time = $(this).data('value')
    $('input#delivery_time').val(time)
});


$(document).on("click", ".combopos_wrap .tab-nav a", function (e) {
    e.preventDefault()
    ComboPOS.changeTab(($(this).index() + 1))
});

// cpos_order_disable
$(document).on("click", "#cpos_order_disable", function () {
    $('#cpos_order_disable_reason').closest(".form-group").slideToggle();
});

// reset_cs_settings
$(document).on("click", "#reset_cs_settings", function (e) {
    e.preventDefault();
    let data = {
        action: 'reset_cs_settings'
    }
    Swal.fire({
        title: 'Are you sure?',
        html: 'This can not be UNDONE',
        icon: 'warning',
        showConfirmButton: true,
        confirmButtonText: 'Reset Settings',
        showCancelButton: true,
        cancelButtonText: 'Cancel',
    }).then((isConfirm) => {
        if (isConfirm.value) {
            ComboPOS.loading("Resettings Settings");
            $.post(ajaxurl, data, function (response) {
                if (response.status == true) {
                    ComboPOS.success(response.message ? response.message : ' Settings Reset to Default!');
                } else {
                    ComboPOS.error(response.message ? response.message : 'Something is wrong, check class\ajax.php');
                }
            });
        } else {
            Swal.close();
        }
    });
});



// save_cs_settings
$(document).on("submit", "#save_cs_settings", function (e) {
    e.preventDefault();
    let data = $(this).serialize();
    data += '&action=save_cs_settings';

    ComboPOS.loading("Saving Settings");
    $.post(ajaxurl, data, function (response) {
        // console.log(response);
        if (response.status == true) {
            ComboPOS.success(response.message ? response.message : 'Successfully Saved');
        } else {
            ComboPOS.error(response.message ? response.message : 'Settings Remains Unsaved');
        }
    })
});



$(function () {
    $('[cs_multiple]').select2({
        width: 'resolve',
        placeholder: 'Select customer',
        theme: 'classic',
        tags: "true",
        placeholder: "Select an option",
        allowClear: true
    });
});