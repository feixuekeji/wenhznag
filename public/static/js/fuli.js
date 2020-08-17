var $, layer, laypage, element;
layui.use(['layer', 'element', 'laypage'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	element = layui.element;
	laypage = layui.laypage;
	
	SearchFulieList(1, 1, 10);
	GetTopArticleList();
	GetTopResourceList();
	GetOneFuli();
	GetAdv();
	
});

//获取福利
function SearchFulieList(type, pg, sz) {
	var index = layer.load(0, {
		shade: false,
		offset: ['100px']
	}); //0代表加载的风格，支持0-2
	var data1 = {
		page: pg,
		size: sz,		
	}
	$.ajax({
		url: apiUrl + 'api/Web/SearchFulieList',
		type: 'post', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		contentType: 'application/json',
		data: JSON.stringify(data1),
		success: function(jsondata) {
			layer.close(index);
			if(jsondata.Type == 1) {
				if(type == 1) {
					laypage.render({
						elem: 'page',
						count: jsondata.Count,
						layout: ['prev', 'page', 'next', 'count'],
						jump: function(obj, first) {
							if(!first) {
								SearchFulieList(0, obj.curr, obj.limit);
							}
						}
					});
				}
				$("#ulFuli").html("");
				$.each(jsondata.Data, function(index, item) {
					var lihtml = '';							
					lihtml += '<li class="layui-col-xs12 layui-col-sm6 layui-col-md4 ">';
					lihtml += '<a href="fulidetail.html?id='+item.Id+'">';
					lihtml += '<img src="'+item.Imgurl+'" />';
					lihtml += '<span class="title">'+item.Title+'</span>';
					lihtml += '<span>'+item.Createtime.substring(0,10)+'</span>';
//					lihtml+='<span class="view"><i class="fa fa-eye"></i>&nbsp;'+item.Readcount+'</span>';
					lihtml += '</a></li>';				
					$("#ulFuli").append(lihtml);
				});

			}
		}

	});

}