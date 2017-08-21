var mytable;
$(function() {
    var deviceListCache = {};
    mytable = InitDataTable({
        $el: $('#example1'), //表格dom选择器
        url: "/Home/Device/deviceListAjax", //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "device_type_id": { title: "编号", "aaSorting": "desc", "orderable": true }, //不需要显示的列定义visible： false
                "vender_name": { title: "硬件厂家" }, //不需要排序的列定义 orderable: false
                "device_type_name": { title: "设备类型" },
                "device_no": {
                    title: "设备型号",
                    render: function(data, type, row, meta) {
                        var _deviceId = row.device_type_id;
                        deviceListCache[_deviceId] = [];

                        return '<ul id="device_no' + row.device_type_id + '" class="list-unstyled"></ul>';
                    }
                }
            },
            operate: {
                "title": '操作', //自定义操作列 
                render: function(data, type, row, meta) { 

                    var _btnfh = ('<a href="/Home/Device/editDevice/id/' + row.device_type_id + '" class="btn btn-xs btn-success">修改</a>');
                    var _text = (_btnfh + ' <span data-id="' + row.device_type_id + '" onclick="removeRecord( this )" class="btn btn-xs btn-default">删除</span> ');

                    return _text;
                }
            }
        }
    }, function() {
        deviceList();
        renderDeviceNo();
    });

    /*设备型号列表数据*/
    function deviceList() {
        for (var i in deviceListCache) {
            var deviceId = i;
            $.ajax({
                url: "/Home/Device/getDevice/act/model/id/" + deviceId,
                deviceId: deviceId,
                dataType: 'json',
                type: 'GET',
                async: false,
                success: function(res) {
                    deviceListCache[deviceId] = res;
                },
                error: function(res) {
                    Alert('请求失败');
                }
            });
        };
    };

    /*渲染设备型号列表*/
    function renderDeviceNo() {
        var _data = deviceListCache;

        for( var j in _data ){
            var $ul = $('#device_no' + j);
            var _d = _data[j];
            var _ulhtml = '';

            for(var i = 0, l = _d.length; i < l; i++){
                var _lidata = _d[i];
                var _lihtml = '<li><i class="fa fa-angle-right"></i> '+ _lidata.device_model_name +'</li>';
                _ulhtml += _lihtml;
            };

            $ul.html('<ul class="list-unstyled">'+ _ulhtml +'</ul>');
        }; 
    }; 
});

/*删除列表记录*/
function removeRecord(el) {
    var _id = $(el).attr('data-id');

    Confirm('确实要删除该企业硬件吗?', function(flag) {
        if (flag) {
            AjaxJson('/Home/Device/delDevice/id/' + _id, function(res) {

                AlertHide( res.msg );
                if( res.status == 0 ){
                    return;
                };
                mytable.refresh(); 
            });
        };
    });
};
