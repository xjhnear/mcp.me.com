//http://layer.layui.com/api.html
var selectlayer = function(options,type){
        var defaults={
                    type:1,//layer提供了5种层类型。可传入的值有：0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）。 若你采用layer.open({type: 1})方式调用，则type为必填项（信息框除外）
                    title:'提示',//若你还需要自定义标题区域样式，那么你可以title: ['文本', 'font-size:18px;']
                    content:'内容',//content可传入的值是灵活多变的，不仅可以传入普通的html内容，还可以指定DOM，更可以随着type的不同而不同
                    skin:'',//样式类名
                    area:['700px','580px'],//当你宽高都要定义时，你可以area: ['500px', '300px']    
                    offset :'auto',//你可以offset: ['100px', '200px']。除此之外，你还可以定义offset: 'rb'，表示右下角
                    icon:'-1',//当你想显示图标时，可以传入0-16如果是加载层，可以传入0-2
                    btn:'',//btn: ['按钮1', '按钮2', '按钮3', …]回调为btn3: function(){}，
                    shade:[0.8, '#393D49'],//-遮罩  默认是0.3透明度的黑色背景（'#000'）shade: [0.8, '#393D49']
                    closeBtn:'1',//-关闭按钮 layer提供了两种风格的关闭按钮，可通过配置1和2来展示，如果不显示，则closeBtn: false
                    shadeClose:false,//- 是否点击遮罩关闭
                    time:0,//- 自动关闭所需毫秒 自动关闭所需毫秒 可以time: 5000，即代表5秒后自动关闭，注意单位是毫秒（1秒=1000毫秒）
                    shift:0,//- 动画 我们的出场动画全部采用CSS3。这意味着除了ie6-9，其它所有浏览器都是支持的。目前shift可支持的动画类型有0-6
                    maxmin:true,// - 最大最小化。 该参数值对type:1和type:2有效。默认不显示最大小化按钮。需要显示配置maxmin: true即可
                    fix:true,//- 固定 即鼠标滚动时，层是否固定在可视区域。如果不想，设置fix: false即可
                    scrollbar:true,// - 是否禁用浏览器滚动条 默认允许浏览器滚动，如果设定scrollbar: false，则屏蔽
                    maxWidth:360,//- 最大宽度 当area: 'auto'时，maxWidth的设定才有效。
                    zIndex:19891014, //- 层叠顺序 一般用于解决和其它组件的层叠冲突。
                    move:'.layui-layer-title',//- 触发拖动的元素 默认是触发标题区域拖拽。如果你想单独定义，指向元素的选择器或者DOM即可。如move: '.mine-move'。你还配置设定move: false来禁止拖拽
                    moveType:0,// - 拖拽风格 //默认的拖拽风格正如你所见到的，会有个过度的透明框。但是如果你不喜欢，你可以设定moveType: 1切换到传统的拖拽模式
                    moveOut:false,// 是否允许拖拽到窗口外 //默认只能在窗口内拖拽，如果你想让拖到窗外，那么设定moveOut: true
                    moveEnd:null, //- 拖动完毕后的回调方法 如果你需要，设定moveEnd: function(){}即可
                    tips:2,//tips层的私有参数。支持上右下左四个方向，通过1-4进行方向设定。如tips: 3则表示在元素的下面出现。有时你还可能会定义一些颜色，可以设定tips: [1, '#c00']
                    tipsMore:false,//允许多个意味着不会销毁之前的tips层。通过tipsMore: true开启
                    success:function(layero,index){
                            var needinfo=settings.returnField.toString().split(","),
                                returnInput=settings.returnInput.toString().split(","),
                                   l=$(layero);
                             selectclick(l,layer,needinfo,returnInput);
                             //分页方法
                             laypage({
                                cont: 'pagelink', //容器。值支持id名、原生dom对象，jquery对象。【如该容器为】：<div id="page1"></div>
                                pages: $("#pagelink").attr('totalCount'), //通过后台拿到的总页数
                                jump: function(e,first){ //触发分页后的回调
                                     if(typeof(first) != 'undefined' ) return ; 
                                     var left=l.offsetLeft*1+300,top=l.offsetTop*1+134,width=l.width(),height=l.height();
                                     var index_ = layer.load(14, {
                                            shade: [0.8,'#fff'], //0.1透明度的白色背景
                                            offset:[left+'px', top+'px'],
                                    });
                                    $.ajax({
                                        url:settings.url,
                                        data:{'page':e.curr,'table':settings.table,'needField':settings.needField,'is_interface':settings.is_interface,'is_page':1},
                                        dataType:'json',
                                        success:function(data){
                                            layer.close(index_);
                                            if( typeof(data.datalist) != 'undefined' || data.datalist.length > 0){
                                                var arrayObj = new Array(),needField=settings.needField.toString().split(","),strhtml='',is_xuanze='选择';
                                                $.each(data.datalist, function(index, val){
                                                    strhtml='<tr>';
                                                     $.each(needField, function(index,Field){
                                                         strhtml+=$.inArray(Field,needinfo) > -1 ? '<td id="'+Field+'_'+val[settings.primarykey]+'" data-value="'+val[Field]+'">'+val[Field]+'</td>' :'<td>'+val[Field]+'</td>'; 
                                                     });
                                                     if(settings){
                                                        
                                                     }
                                                     strhtml+='<td><a href="javascript:void(0);" data-id="'+val[settings.primarykey]+'" class="btn btn-primary select-id">选择</a></td></tr>';
                                                     arrayObj[index]=strhtml;
                                                });
                                                $("#tbody").html(arrayObj.join());
                                                selectclick(layero,layer,needinfo,returnInput);
                                            }
                                        }
                                    });
                                },
                             });    

                    },//- 层弹出后的成功回调方法 //当你需要在层创建完毕时即执行一些语句，可以通过该回调。success会携带两个参数，分别是1当前层DOM 2当前层索引  function(layero, index) 
                    yes:null,//- 确定按钮回调方法
                    cancel:null,//- 取消和关闭按钮触发的回调
                    end:null,//- 层销毁后触发的回调 //    无论是确认还是取消，只要层被销毁了，end都会执行，不携带任何参数。 full/min/restore -分别代表最大化、最小化、还原 后触发的回调 类型：Function，默认：null 携带一个参数，即当前层DOM
                    //下方参数不属于LAYER插件参数 为弹层之后内容所需
                    url:'select-layer', //请求URL 默认为
                    table:'',//数据表去前缀
                    needField:['Id','name'],//弹层视图表格中 tbody tr中th的值 也是SQL中需要查询的值如果有JOIN 请写 表名.Id
                    needFilter:'',//弹层视图展现时查询SQL的 WHERE条件 格式为 [{"%name":"1","=表名.name":"11"}]
                    threadth:['编号','名称'],//弹层视图表格中 thread tr中th的值
                    returnField:['Id','name'],//最后在弹层中需要返回的列
                    returnInput:['Id','name'],//需要赋值的表单INPUT框 于returnField数量上相等
                    trWidth:['80','120'],//弹层视图表格中 thread tr中th的宽度
                    is_interface:0,//是否请求为调用接口模式
                    interfaceParms:'',//是否请求接口模式的参数
                    primarykey:'Id',//需要唯一主键KEY名称
                }

        var settings=$.extend(defaults, options);
        if(type == 'ajax'){
            if(typeof(settings.table) == 'undefined' || settings.table==''){
                 layer.alert('抱歉您table参数未填写',function(index){
                         return false;
                 });                        
            }
            layer.use('skin/loading.css');
            $('body').append('<div class="loading" id="loading" ><span class="loadingimg"><img src="/static/img/pageloading.gif"></span></span></div>');
            //后台请求数据
            $.ajax({
                    url:settings.url,
                    data:{'primarykey':settings.primarykey,'table':settings.table,'threadth':settings.threadth,'needField':settings.needField,'needFilter':settings.needFilter,'returnField':settings.returnField,'trWidth':settings.trWidth,'is_interface':settings.is_interface},
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
                        settings.content=data;
                        layer.open(settings);
                }
             });
        }else{
            return layer.open(settings);
        }
        var selectclick=function(layero,layer,needinfo,returnInput){
                layero.find('a.select-id').click(function(){
                    var obj=$(this),obj_val=obj.attr('data-id');
                    $.each(needinfo, function(index, val) {
                         /* iterate through array or object *///
                        $('#'+returnInput[index]).val($('#'+val+"_"+obj_val).attr('data-value'));
                        $("#loading").remove();
                    });
                 layer.closeAll();
                                     
            });
        }
        /****
        if (typeof String.prototype.deentityify !== 'function') {  
                String.prototype.deentityify = function() {  
                    var entity = {  
                        quot    : '"',  
                        '#039'  : '\'',  
                        lt      : '<',  
                        gt      : '>'  
                    };  
                          
                    return function () {  
                        return this.replace(/&([^&;]+);/g,   
                            function(a, b) {  
                                var r = entity[b];  
                                return typeof r === 'string' ? r : a;  
                            }  
                        );  
                    };  
                }();  
            }  
  
            if (typeof String.prototype.entityify !== 'function') {  
                String.prototype.entityify = function() {  
                    var character = {  
                        '<'      : '&lt;',  
                        '>'      : '&gt;',  
                        '&'     : '&amp;',  
                        '"'     : '&quot;',  
                        "'"     : '&#039;'  
                    };  
                          
                    return function () {  
                        return this.replace(/[<>&"']/g,   
                            function(c) {  
                                return character[c];  
                            }  
                        );  
                    };  
                }();  
            } 
           ****/ 
           
    }
