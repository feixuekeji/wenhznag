var $, layer, laypage, element;
layui.use(['layer', 'element', 'laypage'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	element = layui.element;
	laypage = layui.laypage;
	
	SearchResourceList(1, 1, 10);
	GetTopArticleList();
	GetTopResourceList();
	GetOneFuli();
	GetAdv();
	
});

//获取资源
function SearchResourceList(type, pg, sz,id) {
	var index = layer.load(0, {
		shade: false,
		offset: ['100px']
	}); //0代表加载的风格，支持0-2
	var data1 = {
		page: pg,
		size: sz,		
	}
	$.ajax({
		url: apiUrl + 'api/Web/SearchResourceList',
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
								SearchResourceList(0, obj.curr, obj.limit);
							}
						}
					});
				}
				$("#ulResource").html("");
				$.each(jsondata.Data, function(index, item) {
					var lihtml = '';
					lihtml += '<li class="layui-col-xs12 layui-col-sm12 layui-col-md12">';
					lihtml += '<a href="'+item.Re_demourl+'">';
					lihtml += '<img src="' + item.Re_imgurl + '"  onerror="errorImg(this)" class="layui-col-xs3 layui-col-sm3 layui-col-md3" alt="" /></a>';
					lihtml += '<div class="layui-col-xs9 layui-col-sm9 layui-col-md9">';
					lihtml += '<a href="'+item.Re_demourl+'">';
					lihtml += '<h2> ' + item.Re_name + '</h2>';
				
					lihtml += '<div class="fly-list-info">' + item.Re_outline + '</div>	';
					lihtml += '<div class="fly-list-info">';
					lihtml += '<span> <i class="fa fa-user" title="作者"></i>' + item.Re_author + '</span>';
					lihtml += '<span>  <i class="fa fa-tag" title="tag"></i>' + getrestype(item.Re_type) + '</span></div></a>';
					lihtml += '<a href="'+item.Re_downloadurl+'" class="layui-btn layui-btn-primary layui-btn-sm"><i class="fa fa-download"></i>下载</a>';
				
					lihtml += '</div></li>';
					$("#ulResource").append(lihtml);
				});

			}
		}

	});

}