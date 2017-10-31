/**
 * 
 */

var gameselect = function(elid){
	
	var api_search_url = '/game/api/game-select-search';
	var api_init_url = '/game/api/game-select-init';
	if($(elid).attr('data-from')=='android'){
		api_search_url = '/a_game/api/game-select-search';
		api_init_url = '/a_game/api/game-select-init';
	}
	$(elid).select2({
        placeholder:'所属游戏',
        multiple:false,
        allowClear: true,
        minimumInputLength: 1,
        ajax:{
            url:api_search_url,
            type:'GET',
            dataType:'json',
            data:function(term,page){
                return {
                    q:term,
                    page_limit: 10,
                };
            },
            results:function(data,page){                
                return {results:data.game_list};
                //return {results:[{id:'a',text:'a'},{id:'b',text:'b'}]};
            }
        },
        initSelection: function(element, callback) {
            var id=$(element).val();
            if (id!=="") {
                $.ajax({
                    url:api_init_url,
                    type:'GET',
                    dataType:'json',
                    data:{id:id},                    
                }).done(function(data) { callback(data); });
            }
        },
        //formatResult:formatOperator
    });
};

var userselect = function(elid){
	
	var api_search_url = '/user/users/select-search';
	var api_init_url = '/user/users/select-init';	
	$(elid).select2({
        placeholder:'搜索用户',
        multiple:false,
        allowClear: true,
        minimumInputLength: 2,
        ajax:{
            url:api_search_url,
            type:'GET',
            dataType:'json',
            data:function(term,page){
                return {
                    q:term,
                    page_limit: 10,
                };
            },
            results:function(data,page){                
                return {results:data.user_list};
                //return {results:[{id:'a',text:'a'},{id:'b',text:'b'}]};
            }
        },
        initSelection: function(element, callback) {
            var id=$(element).val();
            if (id!=="") {
                $.ajax({
                    url:api_init_url,
                    type:'GET',
                    dataType:'json',
                    data:{id:id},                    
                }).done(function(data) { callback(data); });
            }
        },
        //formatResult:formatOperator
    });
};

var v4userselect = function(elid){

    var api_search_url = '/v4user/users/select-search';
    var api_init_url = '/v4user/users/select-init';
    $(elid).select2({
        placeholder:'搜索用户',
        multiple:false,
        allowClear: true,
        minimumInputLength: 2,
        ajax:{
            url:api_search_url,
            type:'GET',
            dataType:'json',
            data:function(term,page){
                return {
                    q:term,
                    page_limit: 10,
                };
            },
            results:function(data,page){
                return {results:data.user_list};
                //return {results:[{id:'a',text:'a'},{id:'b',text:'b'}]};
            }
        },
        initSelection: function(element, callback) {
            var id=$(element).val();
            if (id!=="") {
                $.ajax({
                    url:api_init_url,
                    type:'GET',
                    dataType:'json',
                    data:{id:id},
                }).done(function(data) { callback(data); });
            }
        },
        //formatResult:formatOperator
    });
};

var v4useridselect = function(elid){

    var api_search_url = '/v4user/users/select-search-id';
    var api_init_url = '/v4user/users/select-init';
    $(elid).select2({
        placeholder:'搜索用户ID',
        multiple:false,
        allowClear: true,
        minimumInputLength: 1,
        ajax:{
            url:api_search_url,
            type:'GET',
            dataType:'json',
            data:function(term,page){
                return {
                    q:term,
                    page_limit: 10,
                };
            },
            results:function(data,page){
                return {results:data.user_list};
                //return {results:[{id:'a',text:'a'},{id:'b',text:'b'}]};
            }
        },
        initSelection: function(element, callback) {
            var id=$(element).val();
            if (id!=="") {
                $.ajax({
                    url:api_init_url,
                    type:'GET',
                    dataType:'json',
                    data:{id:id},
                }).done(function(data) { callback(data); });
            }
        },
        //formatResult:formatOperator
    });
};