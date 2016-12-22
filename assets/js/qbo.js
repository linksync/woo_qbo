(function ($){

    function get_laid_info(){
        $.post(ajaxurl, {action : 'get_laid_info'});
    }
    get_laid_info();

}(jQuery));