//http://www.cnblogs.com/bestfc/archive/2009/06/08/1498742.html
var ids=new Array();
var selectdialog = function(options){
    ids = [];
    var defaults={
        title:'提示',
        autoOpen: true,//如果设置为true，则默认页面加载完毕后，就自动弹出对话框；相反则处理hidden状态。 
        bgiframe: true, //解决ie6中遮罩层盖不住select的问题              // 创建模式对话框
        zIndex:19891014,
        padding:2,
        draggable:true,//是否允许拖动，默认为 true
        resizable:true,    //是否可以调整对话框的大小，默认为 true
        content:'html',
        width:650,
        height:600,
        close:function(event, ui){ 
           $("div.selectdialog").remove();
        },
        id:"selectdialog",
        url:'select-layer', //请求URL 默认为
        table:null,//数据表去前缀
        database:null,
        select:null,//SQL中需要查询的值如果有JOIN 请写 表名.Id
        jointable:null,
        selectkey:null,//弹层视图表格中 tbody tr中th的值
        primarykey:'id',//需要唯一主键KEY名称
        needFilter:'',//弹层视图展现时查询SQL的 WHERE条件 格式为 needFilter:[{"%name":"1","=表名.name":"11"}]
        threadth:['编号','名称'],//弹层视图表格中 thread tr中th的值
        tbodytd:null,
        returnField:['Id','name'],//最后在弹层中需要返回的列
        returnInput:['Id','name'],//需要赋值的表单INPUT框 于returnField数量上相等
        returnhtmltype:['text','text'],
        trWidth:['80','120'],//弹层视图表格中 thread tr中th的宽度
        is_interface:0,//是否请求为调用接口模式
        interfaceParms:'',//是否请求接口模式的参数
        is_checkboxs:0,
        searchhtml:null,
        searchkey:null,
        searchkeytable:null,
        selectclick:function(needinfo,returnInput,returnhtmltype,is_checkboxs){
              $("#tbody").find('a.select-id').click(function(){
                  var obj=$(this),obj_val=obj.attr('data-id');
                  if(is_checkboxs == 1){
                      //$("#is_checkboxs").html('<textarea rows="3"  id="my_textarea" cols="20"></textarea>');
                     if(obj.html() == '选择'){
                            ids.push(obj_val);
                            obj.html('已选择');
                            $('#is_checkboxs').html(ids.join(','));
                            $("#select-div").show();
                     }else{ 
                         if(ids.length == 1){
                            $("#select-div").hide();
                         }
                         ids.splice($.inArray(obj_val,ids),1);
                         obj.html('选择');
                         $('#is_checkboxs').html(ids.join(','));
                    }  
                  }else{
                    $.each(needinfo, function(index, val) {
                        if(typeof(returnhtmltype[index]) != 'undefined' && returnhtmltype[index] == 'img'){
                          $('#'+returnInput[index]).attr('src',$('#'+val+"_"+obj_val).attr('data-value'));
                        }else{
                          $('#'+returnInput[index]).val($('#'+val+"_"+obj_val).attr('data-value'));
                        }
                        $.dialog.list[settings.id].close();
                        $("div.selectdialog").remove();

                    });
                  }
              });
          },  
     }
    var settings=$.extend(defaults, options),body=$('body');
    $("<link>").attr({rel:"stylesheet",type:"text/css", href:"/static/js/skin/loading.css"}).appendTo("head");
    body.append('<div class="loading" id="loading" ><span class="loadingimg"><img src="/static/img/pageloading.gif"></span></span></div><div id="'+settings.id+'">');
    $.ajax({
          url:settings.url, 
          data:{'searchkey':settings.searchkey,'searchhtml':settings.searchhtml,'is_checkboxs':settings.is_checkboxs,'selectkey':settings.selectkey,'database':settings.database,'jointables':settings.jointables,'tbodytd':settings.tbodytd,'primarykey':settings.primarykey,'table':settings.table,'threadth':settings.threadth,'needField':settings.select,'needFilter':settings.needFilter,'returnField':settings.returnField,'trWidth':settings.trWidth,'is_interface':settings.is_interface},
          //data:{'searchkey':settings.searchkey,'searchhtml':settings.searchhtml,'is_checkboxs':settings.is_checkboxs,'selectkey':settings.selectkey,'tbodytd':settings.tbodytd,'primarykey':settings.primarykey,'threadth':settings.threadth,'needField':settings.select,'needFilter':settings.needFilter,'returnField':settings.returnField,'trWidth':settings.trWidth,'is_interface':settings.is_interface},
          beforeSend:function(XMLHttpRequest){ 
              $("#loading").show(); 
          },
          complete:function(XMLHttpRequest, textStatus){
              $("#loading").remove();
          },
          dataType:'html', 
          success:function(data){
              //返回数据 调用layer
              $("#loading").remove();
              body.append('<div class="selectdialog" id="selectdialog"></div>');
              settings.content=data;
              $.dialog(settings);
              //$('.aui_main').css({"display":"inline-block"});
              if(typeof(data) == 'undefined' ) return false;
              var needinfo=settings.returnField.toString().split(","),
                  returnInput=settings.returnInput.toString().split(","),
                  returnhtmltype=settings.returnhtmltype.toString().split(","),
                         tbodytd=settings.tbodytd.toString().split(","),
                               l=$("#"+settings.id);
                  
                  selectclick(needinfo,returnInput,returnhtmltype,settings.is_checkboxs);
                  mylaypage(needinfo,returnInput,returnhtmltype);
                  if(settings.is_checkboxs == 1){
                     //var returnInput=settings.returnInput.toString().split(",");
                     $("#select-ok").bind('click',function(){ 
                           $('#'+returnInput['0']).val($('#is_checkboxs').html());
                           $.dialog.list[settings.id].close();
                              $("div.selectdialog").remove();
                      });
                  }
                  if(settings.searchkey){
                      $('#mysearch').bind('click',function(event) { 
                            if(typeof(searchkey) != 'undefined' ) return ;
                            var myfrom=$("#mysearchfrom").serialize();
                            $.ajax({ 
                                 url:settings.url+'?'+myfrom,
                                 data:{'searchkey':settings.searchkey,'database':settings.database,'jointables':settings.jointables,'page':1,'table':settings.table,'needField':settings.select,'is_interface':settings.is_interface,'is_page':1},
                                 dataType:'json',
                                 success:function(data){
                                      if( typeof(data.datalist) == 'object' && data.datalist.length != 0){
                                           $("#pagelink").attr("totalCount",data.totalCount);
                                          var selectkey=settings.selectkey.toString().split(",");
                                          myhtml(data,needinfo,selectkey,returnInput,returnhtmltype);
                                          mylaypage(needinfo,returnInput,returnhtmltype,myfrom);
                                      }else{
                                          $("#pagelink").hide();
                                          $("#tbody").html("<tr><td>抱歉,没有数据</td></tr>");
                                      }
                                    }
                                 });    
                            });
                  }  
           }
     });
    function in_array(search,array){
        for(var i in array){
            if(array[i]==search){
                return true;
            }
        }
        return false;
    }

    var selectclick=function(needinfo,returnInput,returnhtmltype){
             settings.selectclick(needinfo,returnInput,returnhtmltype,settings.is_checkboxs);
        }
    var myhtml=function(data,needinfo,selectkey,returnInput,returnhtmltype){
        var strhtml='',val_='',a_select='',is_xuanzhe='选择',arrayObj = new Array()
        $.each(data.datalist, function(index, val){
                 strhtml='<tr>';
                 $.each(selectkey, function(index,Field){
                        val_=val[Field];
                         if(typeof(settings.tbodytd[index]) != 'undefined'){
                               if(settings.tbodytd[index] == 'img'){
                                   val_="<img  src='http://img.youxiduo.com/"+val_+"'  width='30' height='40' >";
                               }
                          }  
                          strhtml+=$.inArray(Field,needinfo) > -1 ? '<td id="'+Field+'_'+val[settings.primarykey]+'" data-value="'+val[Field]+'">'+val_+'</td>' :'<td>'+val_+'</td>'; 
                  });
                   is_xuanzhe=(settings.is_checkboxs == 1 && in_array(val[settings.primarykey],ids)) ? '已选择' : '选择' ;
                   a_select='<a href="javascript:void(0);" data-id="'+val[settings.primarykey]+'" class="btn btn-primary select-id">'+is_xuanzhe+'</a>';
                   strhtml+='<td>'+a_select+'</td></tr>';
                   arrayObj[index]=strhtml;
         });
         $("#tbody").html(arrayObj.join());
         selectclick(needinfo,returnInput,returnhtmltype,settings.is_checkboxs);
    }
    var  mylaypage=function(needinfo,returnInput,returnhtmltype,myfrom){
        laypage({
              cont: 'pagelink', //容器。值支持id名、原生dom对象，jquery对象。【如该容器为】：<div id="page1"></div>
              pages: $("#pagelink").attr('totalCount'), //通过后台拿到的总页数
              jump: function(e,first){ //触发分页后的回调
                  if(typeof(first) != 'undefined' ) return ; 
                  var url =settings.url;
                  if(typeof(myfrom) != 'undefined' && myfrom){
                      url=settings.url+'?'+myfrom;
                  }
                  $.ajax({
                            url:url,
                            data:{'database':settings.database,'jointables':settings.jointables,'page':e.curr,'table':settings.table,'needField':settings.select,'is_interface':settings.is_interface,'is_page':1},
                            dataType:'json',
                            success:function(data){
                                if( typeof(data.datalist) != 'undefined' || data.datalist.length > 0){
                                    var selectkey=settings.selectkey.toString().split(",");
                                    myhtml(data,needinfo,selectkey,returnInput,returnhtmltype);
                                }else{
                                    $("#tbody").html("<tr><td>抱歉,没有数据</td></tr>");
                                }
                            }
                    });
              },
        }); 
    }

}
