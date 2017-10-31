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

/*
*此功能为多图上传
* 将一下html复制到页面 然后引入本js

<div class="control-group imgs">
<label class="control-label">图片 <span class="add_img badge badge-info" >添加</span> </label>
    <!--{%for item in imgs%}-->
<div class="controls" >
<div data-provides="fileupload" class="fileupload fileupload-new">
<div style="max-width: 60px; max-height: 60px;" class="fileupload-new thumbnail">
<img id="selected_game_icon" src="<!--{{item|default('/static/img/wu_ss.gif')}}-->">
</div>
<div style="max-width: 60px; max-height: 60px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>
<span class="btn btn-file"><i class="icon-picture"></i>
<span class="fileupload-new">选择照片</span><span class="fileupload-exists">Change</span>
<input id="task_img" name="img[]" type="hidden" value="<!--{{item}}-->"/>
<input type="file"  name="picFile[]" >
</span>
<a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
<span style="color: rgb(185, 74, 72);" class="img_note" hidden="hidden">图片不能为空且限于png,gif,jpeg,jpg格式</span>
<input type="button" class="del_img" value="删除"/>
</div>
</div>
    <!--{%endfor%}-->
</div>

    */
$(".add_img").click(function(){
    $(this).parent().parent().append('<div class="controls">\
            <div data-provides="fileupload" class="fileupload fileupload-new">\
            <div style="max-width: 60px; max-height: 60px;" class="fileupload-new thumbnail">\
            <img id="selected_game_icon" src="">\
            </div>\
            <div style="max-width: 60px; max-height: 60px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>\
            <span class="btn btn-file"><i class="icon-picture"></i>\
            <span class="fileupload-new">选择照片</span><span class="fileupload-exists">Change</span>\
            <input id="task_img" name="img[]" type="hidden" value=""/>\
            <input type="file"  name="picFile[]" >\
            </span>\
            <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>\
            <span style="color: rgb(185, 74, 72);" class="img_note" hidden="hidden">图片不能为空且限于png,gif,jpeg,jpg格式</span>\
            <input type="button" class="del_img" value="删除"/>\
            </div>\
            </div>');
});
$(".del_img").live('click',function(){
    if(confirm("确定删除吗？")){
        $(this).parent().parent().remove();
    }
});



