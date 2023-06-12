$(document).ready(function(){

var conf_less = JSON.parse(lessismore_conf.replace(/&quot;/g,'"'));
for (let i = 0; i < conf_less.length; i++) {
    $(conf_less[i].selector).readmore({ speed: conf_less[i].speed, collapsedHeight: parseInt(conf_less[i].collapsedHeight), moreLink: '<span class="cursor_show_more" style="cursor:pointer;">'+conf_less[i].moreLink+'</span>', lessLink: '<span class="cursor_show_more" style="cursor:pointer;">'+conf_less[i].lessLink+'</span>' });
}


});