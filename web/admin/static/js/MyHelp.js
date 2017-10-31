/**
 * Created by fujiajun on 15/9/18.
 * 此JS旨在提供更简单的前台操作封装
 */
//多功能编辑
function getEditor(id){
    UE.getEditor('+id+');
}

//开始时间和结束时间 需要 2个ID  StartDate 和  EndDate
function getStart_End_date(type){
    var mindate=laydate.now();
    if(type=='search'){
        mindate='1900-01-01 00:00:00';
    }
    var start = {
        elem: '#StartDate',
        format: 'YYYY-MM-DD hh:mm:ss',
        min: mindate, //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: true,
        istoday: false,
        choose: function(datas){
            end.min = datas; //开始日选好后，重置结束日的最小日期
            end.start = datas //将结束日的初始值设定为开始日
        }
    };
    var end = {
        elem: '#EndDate',
        format: 'YYYY-MM-DD hh:mm:ss',
        min: mindate,
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: false,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    laydate(start);
    laydate(end);
}
//单个增加时间
function getLaydate(settings)
{
    var options;
    options = {
        elem:'#datetime', //需显示日期的元素选择器
        event:'click', //触发事件
        format: 'YYYY-MM-DD hh:mm:ss', //日期格式
        istime: false, //是否开启时间选择
        isclear: true, //是否显示清空
        istoday: true, //是否显示今天
        issure: true, //是否显示确认
        festival: true, //是否显示节日
        min: '1900-01-01 00:00:00', //最小日期
        max: '2099-12-31 23:59:59', ////
        start: '2015-12-01 23:00:00',    //开始日期
        fixed: false, //是否固定在可视区域
        zIndex: 99999999, //css z-index
        choose: function(dates){ //选择好日期的回调
        }
    };
    var settings = $.extend(options,settings);
    laydate(settings);
}



function checksBylayer(ids,settings)
{
    if(!ids) return false;
    var jsonstr={},
        jsonid={},
        options={
        needforinput:'uid',//接受框的ID值
        needforType:'users',//
        isCheckboxs:true,//是否支持多选
        url:'',
        data:'',
        isEdit:false,
        /**layer参数**/
        type:1,//0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）。 若你采用layer.open({type: 1})方式调用，则type为必填项（信息框除外）
        title:'标题',//title: ['文本', 'font-size:18px;']，数组第二项可以写任意css样式；如果你不想显示标题栏，你可以title: false
        content:'',//类型：String/DOM/Array，默认：'' content可传入的值是灵活多变的，不仅可以传入普通的html内容，还可以指定DOM，更可以随着type的不同而不同
        skin:'',//样式类名 类型：String，默认：'' 目前layer内置的skin有：layui-layer-lan layui-layer-molv，未来我们还会选择性地内置更多，但更推荐您自己来定义
        area: ['700px', '680px'],//- 宽高 类型：String/Array，默认：'auto'
        offset:'auto',//坐标 类型：String/Array，默认：'auto'['100px', '200px']
        icon:1,//图标。信息框和加载层的私有参数 类型：Number，默认：-1（信息框）/0 如果是加载层，可以传入0-2。
        btn:'',//类型：String/Array，默认：'确认' btn: ['yes', 'no']
        shade:0.3, //遮罩 shade: [0.8, '#393D49']
        shadeClose:false,// 是否点击遮罩关闭
        time:0, //动关闭所需毫秒
        maxmin:false,//最大最小化。
        fix:true,// fix - 固定 类型：Boolean，默认：true 即鼠标滚动时，层是否固定在可视区域。如果不想，设置fix: false即可
        scrollbar:true, //默认允许浏览器滚动，如果设定scrollbar: false，则屏蔽
        zIndex:19891014,//层叠顺序
        move:'.layui-layer-title', //触发拖动的元素 类型：String/DOM/Boolean，默认：'.layui-layer-title' 如果你想单独定义，指向元素的选择器或者DOM即可。如move: '.mine-move'。你还配置设定move: false来禁止拖拽
        moveType:0,//拖拽风格 //你可以设定moveType: 1切换到传统的拖拽模式
        moveOut:false, //是否允许拖拽到窗口外
        moveEnd:null,//拖动完毕后的回调方法
        success:function(layero,index){
            var  l=$(layero),url=settings.url;
            var returnArr=dataArr.needforinput.toString().split(",");
            selectclick(l,layer,dataArr,returnArr);
            pages(l,layer,dataArr,returnArr,url);
            $("#mysearch").on('click',function(){
                var keyword=$("#keyword").val();
                dataArr.keyword=keyword;
                dataArr.isPage=1;
                var index_ = layer.load(14, {
                    shade: [0.8,'#fff'], //0.1透明度的白色背景
                    offset:[left+'px', top+'px'],
                });
                $.ajax({
                    url:url,
                    data:dataArr,
                    dataType:'html',
                    success:function(data){
                        layer.close(index_);
                        $("#layer_tbody").html(data);
                        selectclick(l,layer,dataArr,returnArr);
                        pages(l,layer,dataArr,returnArr,url);
                    }
                });

            });
            $("#select-ok").on('click',function(){
                var strdata='';
                if(settings.isEdit){
                    $.each(returnArr,function(index1,val1){
                        $.each(jsonid, function(index2, val) {
                            /* iterate through array or object $('#'+val+"_"+obj_val).attr('data-value') *///
                            strdata+=jsonstr[index2+'_'+returnArr[index1]]+',';
                        });

                        $("#"+val1).val(strdata+$("#"+val1).val());
                        strdata='';
                    });
                }else{
                    $.each(returnArr,function(index1,val1){
                        $.each(jsonid, function(index2, val) {
                            /* iterate through array or object $('#'+val+"_"+obj_val).attr('data-value') *///
                            strdata+=jsonstr[index2+'_'+returnArr[index1]]+',';
                        });
                        $("#"+val1).val(strdata);
                        strdata='';
                    });
                }

                layer.closeAll();
            });
        },//层弹出后的成功回调方法 分别是当前层DOM当前层索引。
        yes:null,//确定按钮回调方法 该回调携带一个参数，即当前层索引
        end:function(){
            dataArr.keyword='';
            dataArr.page=1;
            dataArr.isPage=0;
            jsonstr={},jsonid={};
            dataArr.is_check=''
        },//层销毁后触发的回调 无论是确认还是取消，只要层被销毁了，end都会执行，不携带任何参数。
        cancel:function(){
            layer.closeAll();
        },//取消和关闭按钮触发的回调 如果不想关闭，return false即可，如 cancel: function(index){ return false; } 则不会关闭；
    };
    var settings = $.extend(options,settings),_this=$(ids);
    var url=settings.url == '' ? _this.attr('url') : settings.url,
        dataArr=(settings.data=='')? {} :$("#"+settings.data).serialize();
        dataArr.needforinput=settings.needforinput;
        dataArr.needforType=settings.needforType;
        dataArr.isCheckboxs=settings.isCheckboxs;
        dataArr.is_curl=settings.is_curl;

    var index_=0;
    $(ids).on('click',function(){
        $.ajax({
            type: "get",
            url: settings.url,
            data: dataArr,
            dataType:'html',
            beforeSend:function(){
               index_ = layer.load(14, {
                    shade: [0.8, '#fff'], //0.1透明度的白色背景
                });
            },
            complete:function(){
                layer.close(index_);
            },
            success:function(data){
                //返回数据 调用layer
                settings.content=data;
                layer.open(settings);
            }
        });
    });
    var selectclick=function(layero,layer,dataArr,returnArr){
        layero.find('a.select-id').click(function(){
            if(typeof(returnArr) == 'undefined' || returnArr==''){
                alert('需要的参数未指定');
                return false;
            }
            var obj=$(this),
                obj_val=obj.attr('data-id');
            if(dataArr.isCheckboxs){
                if(obj.html()!='已选择'){
                    $.each(returnArr, function(index, val) {
                        /* iterate through array or object $('#'+val+"_"+obj_val).attr('data-value') *///
                        jsonstr[obj_val+'_'+val]=$('#'+val+"_"+obj_val).attr('dataValue');
                        //$("#"+val).val();
                    });
                    jsonid[obj_val]=obj_val;
                    obj.html('已选择');
                    $("#select-div").show();
                    dataArr.is_check=JSON.stringify(jsonid);
                }else{
                    $.each(returnArr, function(index, val) {
                        delete jsonstr[obj_val+'_'+val];
                    });
                    delete jsonid[obj_val];
                    obj.html('选择');
                }
                $("#is_checkboxs").html(getjsonStr(jsonstr));
            }
        });
    }
    var pages=function(l,layer,dataArr,returnArr,url)
    {
        laypage({
            cont: 'pagelink', //容器。值支持id名、原生dom对象，jquery对象。【如该容器为】：<div id="page1"></div>
            pages: $("#totalCount").val(), //通过后台拿到的总页数
            skip: true,
            groups: 5,
            skin: 'molv',
            jump: function (e, first) { //触发分页后的回调
                if (typeof(first) != 'undefined') return;
                var left = l.offsetLeft * 1 + 300, top = l.offsetTop * 1 + 134, width = l.width(), height = l.height();
                var index_ = layer.load(14, {
                    shade: [0.8, '#fff'], //0.1透明度的白色背景
                    offset: [left + 'px', top + 'px'],
                });
                if (typeof(e.curr) == 'undefined' || e.curr=='') e.curr=1;
                dataArr.page=e.curr;
                dataArr.isPage=1;
                $.ajax({
                    url:url,
                    data:dataArr,
                    dataType:'html',
                    success:function(data){
                        layer.close(index_);
                        $("#layer_tbody").html(data);
                        selectclick(l,layer,dataArr,returnArr);
                    }
                });

            }
        });
    }
    var getjsonStr=function(JsonArr){
        var str='';
        $.each(JsonArr,function(index,value){
            str+=value+'-';
        });
        return str;
    }
}


function ajaxCZ(settings)
{
    var id, data, _this, options;
    id = 0;
    data = '';
    _this = $('th');
    options = {
        elem: 'a.caozuo', //需要处理事件的ID多个逗号分割
        event: 'click', //触发事件
        alertifyText: '确认此次操作吗?',
        url: '',
        //url 后台传输地址 1.自行填写 2.由当前点击（elem）标签data属性中填写url  url="<!--{{url('v4product/welfare/set',{type:'editWelfare',method:'post'})}}-->"
        data: '',
        //data 后台传输数据 1:直接给1个FROM标签ID 2.可以直接给空 由当前点击（elem）标签data属性中填写JSON数据如果没有就交由后续 myval 回调函数自行处理 data='{"relevanceId":"<!--{{item.relevanceId}}-->","active":"false"}'
        ts: false,//是否开启测试 测试提交前的数据
        isReload: false,//操作结束后是否刷新页面 只有在默认回调函数中起作用
        ismodel: true,
        myval: function (data) { //对AJAX DATA请求数据进行在整理
            return data;
        },
        choose: function (data) { //一般处理的回调函数
            data.errorCode == 1 ? alertify.error(data.msg) : alertify.success(data.msg);
            if (settings.isReload)  window.location.reload();
            sub = false;
            return sub;
        },
        del: function (data) { //删除的回调函数
            if (data.errorCode == 0) {
                alertify.success(data.msg);
                _this.parents('tr:first').remove()
            } else {
                alertify.error(data.msg);
            }
            if (settings.isReload)  window.location.reload();
            sub = false;
            return sub;
        },
        beforeSend: function () {
            $('<div id="modal_backdrop"  class="modal-backdrop fade in" style="z-index: 99999" />').appendTo(document.body);
        },
        complete: function () {
            $("#modal_backdrop").remove();
        },
    };
    var settings = $.extend(options,settings),sub = false;
    $(settings.elem).unbind(settings.event);
    $(settings.elem).on(settings.event,function(){
            _this=$(this);
        //为了判断快点信息临时加上
        var _Data = jQuery.parseJSON(_this.attr("data"));
        if(_Data){
            if(_Data.kuai_di!="undefined"&&_Data.kuai_di==""&&_Data.productType=="1"){
                alert("此数据没有快递信息");
                return false;
            }
        }

        //end
            var url=settings.url == '' ? _this.attr('url') : settings.url,
                is_del=_this.hasClass('del'),
                t=_this.attr('dataid');
            data=settings.data=='' ? $.parseJSON(_this.attr('data')) :$("#"+settings.data).serialize();
            id=t||typeof(t)!='undefined'?t:0;

        alertify.confirm(settings.alertifyText,function(e){
            if(e){
                if(sub === true){
                    return;
                }
                sub = true;
                data=settings.myval(data,_this);
                if(!url || settings.ts){
                    console.log(data);console.log(id);console.log(url);
                    return false;
                }
                if(settings.ismodel){
                    $.ajax({
                        type: "get",
                        url: url,
                        data: data,
                        dataType:'json',
                        success:id || is_del?settings.del:settings.choose,
                        beforeSend:settings.beforeSend(_this,data,url),
                        complete:settings.complete()
                    });
                }else{
                    $.get(url,data,id || is_del?settings.del:settings.choose, 'json');
                }
            }
            return;
         });
    });

}

$('input.switchbox').bootstrapSwitch();
$('input').iCheck({
    checkboxClass: 'icheckbox_square-orange',
    radioClass: 'iradio_square-orange',
    increaseArea: '20%'
});


function setAdv_href_type(Value){
    if(Value == '外部url' ||  Value == '内部safari'){
        $("#data_url,#three").fadeIn();
        $("#data_id,#span_search").fadeOut();
        return true;
    }else if(Value == '活动列表' || Value == '礼包列表' || Value =='游币商城列表' || Value =='钻石商城列表' || Value =='任务列表' || Value =='大转盘' || Value =='天天彩' || Value =='新手任务列表' || Value =='账号共享'){
        $("#data_url,#data_id,#three").fadeOut();
    }else{

        if(Value == '帖子详情'){
            $("#data_id").fadeIn();
        }else{
            $("#data_id,#span_search").fadeIn();
        }
        $("#data_url,#three").fadeOut();
    }
    var arr_=['专题','新游预告','新闻文章','礼包详情','视频详情','任务详情','商品详情','指定聊天室','游戏详情','活动详情'];
    if(jQuery.inArray(Value,arr_)!=-1){
        $('#span_search').fadeIn();
        span_search(Value);
    }else{
        $('#span_search').fadeOut();
    }
}

function  span_search(Value)
{
    var options = {
        script: "/v4adv/carousel/autosearch?key="+Value+"&",
        varname: "variableName",
        json: true,
        maxresults: 35,
        callback:function(object){
            $("input[name='urlId']").val(object.id).attr('readonly','readonly');
        }
    };
    var as_json = new bsn.AutoSuggest('search_name', options);
    return as_json;
}

function set_adv_type(adv_type){
    if(adv_type == 2){
        $("#adv_href_type_div,#data_id").fadeOut();
        //$("#data_url,#three").fadeIn();
    }else{
        var Value=$("#adv_href_type").val();
        if(Value == 'Safari浏览器' ||  Value == '内置浏览器' || Value == '活动列表' || Value == '礼包列表' || Value =='游币商城列表' || Value =='钻石商城列表' || Value =='任务列表' || Value =='大转盘' || Value =='天天彩' || Value =='新手任务列表' || Value =='账号共享'){
            $("#adv_href_type_div").fadeIn();
        }else{
            $("#adv_href_type_div,#data_id").fadeIn();
        }

    }
}

