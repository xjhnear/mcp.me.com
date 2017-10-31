<!--{%extends "base-layout.twig"%}-->
<!--{%block main_content%}-->
<link href="/static/css/app.css" rel="stylesheet" type="text/css"/>
<div class="row-fluid">
    <div class="span12">
        <div class="box">
            <div class="box-title">
                <h3>任务信息</h3>
            </div>
            <div class="box-content">
                <form id="activity-task" action="<!--{{url('IOS_activity/task/task-add')}}-->" method="POST" class='form-horizontal form-validate' enctype="multipart/form-data">



                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary sub"><i class="icon-ok"></i>保存</button>
                        <input type="hidden" name="stepListStr" value="<!--{{stepListStr}}-->"/>
                        <input type="hidden" name="ids" class="ids" value=""/>
                        <input type="hidden" name="taskId"  value="<!--{{atask.taskId}}-->"/>
                        <input type="hidden" name="prizeId"  value="<!--{{atask.prizeList[0]['prizeId']}}-->"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--{%endblock%}-->

<div class="row-fluid time">
    <div class="span4">
        <div class="control-group">
            <label class="control-label">开始时间</label>
            <div class="controls">
                <div class="input-append date">
                    <input name="start_time" type="text" class="input-medium datetimepicker"  value="<!--{{atask.startTime}}-->" >
                    <span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="span4">
        <div class="control-group">
            <label class="control-label">结束时间</label>
            <div class="controls">
                <div class="input-append date">
                    <input name="end_time" type="text" class="input-medium datetimepicker" value="<!--{{atask.endTime}}-->" >
                    <span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="control-group">
    <label class="control-label">关键字</label>
    <div class="controls">
        <div class="span9"><input type="text" name="keyword" id="tags" class="tagsinput" value="<!--{{data.keyword}}-->"></div>
    </div>
</div>
<script src="<!--{{asset('/static/js/plugins/tagsinput/jquery.tagsinput.min.js')}}-->"></script>
<script>
    $(function(){
        if($(".tagsinput").length > 0){
        $('.tagsinput').tagsInput({width:'auto', height:'auto',autocomplete_url:'<!--{{url('yxvl_eSports/api/tags')}}-->'});
        }
    }
    })

</script>

<div class="control-group">
    <label class="control-label">是否开启</label>
    <div class="controls">
        <input name="is_show" <!--{% if(input_old('is_show')) %}-->checked<!--{% endif %}--> data-on-color="success" data-on-text="开启" data-off-color="danger" data-off-text="关闭" class="switchbox" type="checkbox" checked/>
    </div>
</div>
<script>
    $('.switchbox').bootstrapSwitch();
</script>


<div class="control-group out_url">
    <label class="control-label">帖子ID/URL</label>
    <div class="controls">
        <input type="text" name="linkValue" class="input-small" value="<!--{{atask.linkValue}}-->" />
    </div>
</div>

<div class="control-group">
    <label class="control-label">图片</label>
    <div class="controls">
        <div data-provides="fileupload" class="fileupload fileupload-new">
            <div style="max-width: 60px; max-height: 60px;" class="fileupload-new thumbnail">
                <img id="selected_game_icon" src="<!--{{atask.taskIcon|default('/static/img/wu_ss.gif')}}-->">
            </div>
            <div style="max-width: 60px; max-height: 60px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>
		<span class="btn btn-file"><i class="icon-picture"></i>
			<span class="fileupload-new">选择照片</span><span class="fileupload-exists">Change</span>
			<input id="task_img" name="task_img" type="hidden" value="<!--{{atask.taskIcon}}-->"/>
			<input type="file"  name="task_icon" >
		</span>
            <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
            <span style="color: rgb(185, 74, 72);" class="img_note" hidden="hidden">图片不能为空且限于png,gif,jpeg,jpg格式</span>
        </div>
    </div>
</div>

<div class="control-group">
    <div class="control-group">
        <label class="control-label">活动展示类型</label>
        <div class="controls taskType">
            <!--{{form_radio('selTaskType',0,selType==0)}}-->普通任务
            <!--{{form_radio('selTaskType',1,selType==1)}}-->子任务
            <!--{{form_radio('selTaskType',2,selType==2)}}-->连续任务
        </div>
    </div>
</div>
<script>
    $(function(){
        $('input:radio').iCheck({
            checkboxClass: 'icheckbox_square-orange',
            radioClass: 'iradio_square-orange',
            increaseArea: '20%'
        });
    })
</script>

<div class="control-group">
    <label class="control-label">内容<span class="add_img badge badge-info" >添加</span></label>
    <!--{% if data.content %}-->
    <!--{%for item in data.content%}-->
    <div class="controls">
        <div class="input-prepend">
            <span class="add-on">名称<i class="tip-star"></i></span><input type="text" name="gameName[]" class="input-medium" value="<!--{{item.name}}-->"  />
            <span class="add-on">链接<i class="tip-star"></i></span><input type="text" name="gameUrl[]" class="input" value="<!--{{item.url}}-->"  />
            <input type="button" class="del_img" value="删除"/>
        </div>
    </div>
    <!--{%endfor%}-->
    <!--{% else %}-->
    <div class="controls">
        <div class="input-prepend">
            <span class="add-on">名称<i class="tip-star"></i></span><input type="text" name="gameName[]" class="input-medium" value=""  />
            <span class="add-on">链接<i class="tip-star"></i></span><input type="text" name="gameUrl[]" class="input" value=""  />
            <input type="button" class="del_img" value="删除"/>
        </div>
    </div>
    <!--{% endif %}-->
</div>
<script>
    $(".add_img").click(function(){
        $(this).parent().parent().append('<div class="controls">\
	<div class="input-prepend">\
	<span class="add-on">名称<i class="tip-star"></i></span><input type="text" name="gameName[]" class="input-medium" value=""  />\
	<span class="add-on">链接<i class="tip-star"></i></span><input type="text" name="gameUrl[]" class="input" value=""  />\
	<input type="button" class="del_img" value="删除"/>\
	</div>\
	</div>');
    });
    $(".del_img").live('click',function(){
        if(confirm("确定删除吗？")){
            $(this).parent().parent().remove();
        }
    });
</script>

<div class="control-group">
    <label class="control-label">图片 <span class="add_img badge badge-info" >添加</span> </label>
    <!--{% if imgs %}-->
    <!--{%for item in imgs%}-->
    <div class="controls" >
        <div data-provides="fileupload" class="fileupload fileupload-new">
            <div style="max-width: 60px; max-height: 60px;" class="fileupload-new thumbnail">
                <a href="#bigimg" data-toggle="modal"><img id="selected_game_icon" src="<!--{{item|default('/static/img/wu_ss.gif')}}-->"></a>
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
    <!--{% else %}-->
    <div class="controls" >
        <div data-provides="fileupload" class="fileupload fileupload-new">
            <div style="max-width: 60px; max-height: 60px;" class="fileupload-new thumbnail">
                <a href="#bigimg" data-toggle="modal"><img id="selected_game_icon" src="<!--{{data.picUrl|default('/static/img/wu_ss.gif')}}-->"></a>
            </div>
            <div style="max-width: 60px; max-height: 60px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>
			<span class="btn btn-file"><i class="icon-picture"></i>
				<span class="fileupload-new">选择照片</span><span class="fileupload-exists">Change</span>
				<input id="task_img" name="img[]" type="hidden" value="<!--{{data.picUrl}}-->"/>
				<input type="file"  name="picFile[]" >
			</span>
            <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
            <span style="color: rgb(185, 74, 72);" class="img_note" hidden="hidden">图片不能为空且限于png,gif,jpeg,jpg格式</span>
            <input type="button" class="del_img" value="删除"/>
        </div>
    </div>
    <!--{% endif %}-->
</div>
<script>
    $(function() {
        v4userselect('#userselect');

        $(".add_img").click(function(){
            $(this).parent().parent().append('<div class="controls">\
            <div data-provides="fileupload" class="fileupload fileupload-new">\
            <div style="max-width: 60px; max-height: 60px;" class="fileupload-new thumbnail">\
            <a href="#bigimg" data-toggle="modal"><img id="selected_game_icon" src="<!--{{data.picUrl|default('/static/img/wu_ss.gif')}}-->"></a>\
            </div>\
            <div style="max-width: 60px; max-height: 60px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>\
            <span class="btn btn-file"><i class="icon-picture"></i>\
            <span class="fileupload-new">选择照片</span><span class="fileupload-exists">Change</span>\
            <input id="task_img" name="img[]" type="hidden" value="<!--{{data.picUrl}}-->"/>\
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
    });
</script>

<div class="control-group">
    <label for="textfield" class="control-label">内容</label>
    <div class="controls">
        <textarea name="message"><!--{{ data.formatContent }}--></textarea>
    </div>
</div>

<div class="control-group">
    <label for="textfield" class="control-label">内容</label>
    <div class="controls">
        <script id="container" name="message" type="text/plain"><!--{{ input_old('message') }}--></script>
    </div>
</div>
<script>
    UE.getEditor('container');
</script>

//下拉列表
<!--{{form_select('stepType[]',stepType（数组）,atask.stepType（值）,{'style':'width:160px;margin-left:240px;','class':'stepType'})}}-->

<!--{%block footer_js%}-->
<script src="<!--{{asset('/static/js/ueditor/ueditor.config.js')}}-->"></script>
<script src="<!--{{asset('/static/js/ueditor/ueditor.all.js')}}-->"></script>
<script src="<!--{{asset('/static/js/ueditor/lang/zh-cn/zh-cn.js')}}-->"></script>
<script src="<!--{{asset('/static/js/plugins/tagsinput/jquery.tagsinput.min.js')}}-->"></script>
<script src="<!--{{asset('/static/js/plugins/jquery-ui/jquery-ui-1.9.2.custom.min.js')}}-->"></script>
<script>
    $(function(){
        var editor = UE.getEditor('container',{initialFrameWidth:800});
        $('input.datetimepicker').datetimepicker({format:'Y-m-d',lang:'ch'});

</script>
<!--{%endblock%}-->

<!--<!--{%if one.picUrl%}-->-->
<!--<div class="control-group">-->
<!--    <label class="control-label">图片</label>-->
<!--    <div class="controls">-->
<!--        <input name="picUrl[]" type="hidden" value="<!--{{one.picUrl}}-->" />-->
<!--        <a href="#bigimg" data-toggle="modal"><img src="<!--{{one.picUrl}}-->" width="150px" /></a>-->
<!--    </div>-->
<!--</div>-->
<!--<!--{%endif%}-->-->
<!--<div class="control-group">-->
<!--    <label class="control-label">图片<i class="tip-star">*</i></label>-->
<!--    <div class="controls">-->
<!--        <input name="filedata[]" type="file" class="myfile" value="" />-->
<!--    </div>-->
<!--</div>-->