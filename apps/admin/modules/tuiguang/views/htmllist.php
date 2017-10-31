<!--{%extends "base-layout.twig"%}-->
<!--{%block main_content%}-->
<div class="row-fluid">
    <div class="span12">
        <div class="box box-color box-bordered">
            //筛选
            <div class="box-title">
                <h3>
                    <i class="icon-table"></i>
                    <!--{{menu_name}}-->管理
                </h3>
                <div class="actions">
                    <form action="<!--{{url('a_activity2/task/audit-picture-list')}}-->" method="GET">
                        <input name="taskName" type="hidden" value="<!--{{taskName}}-->" />
                        <input name="id" type="hidden" value="<!--{{taskId}}-->" />
                        <input name="taskType" type="hidden" value="<!--{{taskType}}-->" />
                        <table class="table-filter">
                            <tr>
                                <td><span class="label label-blue">开始时间</span>
                                    <div class="input-append date " >
                                        <input name="createTimeBegin" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" type="text" class="input-medium datetimepicker" id="date_timepicker_start" value="<!--{{search['createTimeBegin']}}-->" >
                                        <span class="add-on"><i class="icon-calendar"></i></span>
                                    </div>
                                </td>
                                <td>结束时间
                                    <div class="input-append date "  data-date="">
                                        <input name="createTimeEnd" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" type="text" class="input-medium datetimepicker"  value="<!--{{search['createTimeEnd']}}-->" id="date_timepicker_end" >
                                        <span class="add-on"><i class="icon-calendar"></i></span>
                                    </div>
                                </td>
                                <td><!--{{form_select('taskStatus',taskStatus,search['taskStatus'],{'id':'complete_type','style':'margin-top:10px;width:100px'})}}--></td>
                                <td><!--{{form_select('prizeStatus',prizeStatus,search['prizeStatus'],{'id':'complete_type','style':'margin-top:10px;width:100px'})}}--></td>
                                <td><input id="userselect" name="uid" type="hidden" value="<!--{{search['uid']}}-->" class="bigdrop select2-offscreen" style="width:200px" tabindex="-1"></td>
                                <td><button class="btn btn-teal">搜索</button></td>
                                <!--{% if taskType==3 %}-->
                                <td><button type="button" class="btn btn-teal" taskId="<!--{{taskId}}-->"  id="approval_success_all">一键全审</button></td>
                                <!--{% endif %}-->
                                <td><button type="button" class="btn btn-teal" taskId="<!--{{taskId}}-->"  id="approval_by_hand">手动发奖</button></td>
                                <td><button type="button" class="btn btn-teal" taskId="<!--{{taskId}}-->"  id="release_task_stock">释放库存</button></td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
            //表格
            <div class="box-content nopadding">
                <table class="table table-hover table-nomargin">
                    <thead>
                    <tr>
                        <th class="col-60">玩家ID</th>


                        <th class="col-150">任务标题</th>
                        <th class="col-100">用户名</th>
                        <th class="col-80">奖励</th>
                        <th class="col-80">完成状态</th>
                        <th class="col-80">奖励状态</th>

                        <th class="col-100">参加时间</th>
                        <!--{% if taskType==3 %}-->
                        <th class="table-btn-group">操作</th>
                        <!--{% endif %}-->
                    </tr>
                    </thead>
                    <tbody>
                    <!--{%for item in datalist%}-->
                    <tr>
                        <td><!--{{item.uid}}--></td>
                        <td><!--{{taskName}}--></td>
                        <td>
                            <!--{% if users[item.uid] %}-->
                            <span class="badge badge-info"><!--{{item.uid}}--></span><br/><a href="<!--{{url('user/users/edit',{'id':item.uid})}}-->" target="_blank"><!--{{users[item.uid]['nickname']}}--></a>
                            <!--{% else %}-->
                            <span style="color: red; ">该用户已删除</span>
                            <!--{% endif %}-->
                        </td>
                        <td><!--{{item.prizeInfo['prizeName']}}--></td>
                        <td><span class="badge badge-success taskStatus"><!--{%if item.taskStatus==-3%}-->参与超时<!--{%elseif item.taskStatus==-2%}-->重发<!--{%elseif item.taskStatus==-1%}-->进行中/审核中<!--{%elseif item.taskStatus==0%}-->参与中<!--{%elseif item.taskStatus==1%}-->完成<!--{%elseif item.taskStatus==2%}-->失败<!--{%endif%}--></span></td>

                        <td><!--{%if item.prizeStatus==0%}--><span class="badge  badge-important">未发奖</span><!--{%else%}--><span class="badge  badge-success">已发奖</span><!--{%endif%}--></td>

                        <td><!--{{item.startTime|date('Y-m-d H:i:s')}}--></td>
                        <!--{% if taskType==3 %}-->
                        <td>
                            <a href="javascript:void(0)" data-target="#exampleModal" taskStatus="<!--{{item.taskStatus}}-->" data-toggle="modal" uid="<!--{{item.uid}}-->" userTaskId="<!--{{item.userTaskId}}-->" taskId="<!--{{item.taskId}}-->" stepId="<!--{{item.stepId}}-->" uname='<!--{% if users[item.uid] %}--><span class="badge badge-info"><!--{{item.uid}}--></span><a href="<!--{{url('user/users/edit',{'id':item.uid})}}-->" target="_blank"><!--{{users[item.uid]['nickname']}}--></a><!--{% else %}--><span style="color: red; ">该用户已删除</span><!--{% endif %}-->' imgs="<!--{{item.picUrl}}-->" class="btn btn-primary b_button">查看截图</a>
                        </td>
                        <!--{% endif %}-->
                    </tr>
                    <!--{%endfor%}-->
                    </tbody>
                </table>

                <div class="pagelink">
                    <!--{{pagelinks}}-->
                </div>
            </div>

        </div>
    </div>
</div>
<!--{%endblock%}-->

<!--{#模态框#}-->
<div class="modal fade" style="width: 900px; margin-left: -500px" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog bs-example-modal-lg"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">游戏截图-- <lable id="b_name"></lable></h4>
            </div>
            <div class="modal-body">
                <div class="form-group img_list" style="text-align: center">

                </div>

                <input type="hidden"  id="b_uid" name="b_uid" value="" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary checked"  id="approval_failure">任务失败</button>
                <button type="button" class="btn btn-primary checked"  id="approval_again">截图不符</button>
                <button type="button" class="btn btn-primary checked"  id="approval_success">通过审核</button>
                <button type="button" class="btn btn-primary"  id="next_one">下一页</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

            </div>
        </div>
    </div>
</div>

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