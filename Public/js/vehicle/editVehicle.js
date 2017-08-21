$(function() {
    var _id = location.href.split('id=')[1];
    var tableVm = new Vue({
        el: '#tableVue'
    });
    
    AjaxJson('/Home/Vehicle/getVehicle/id/' + _id, function(res) { 
        tableVm.$data = res;
    });
}); 
