if (typeof ($) == 'undefined') {
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