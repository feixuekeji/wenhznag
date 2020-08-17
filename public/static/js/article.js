var $, layer, laypage, element;
layui.use(['layer', 'element', 'laypage'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	element = layui.element;
	laypage = layui.laypage;
	
	var id=getUrlParam("id");

    var keyword=getUrlParam("keyword");

	SearchArticleList(1, 1, 10,id,keyword);
    getArticletype(id);
	GetTopArticleList();
	GetTopResourceList();
	GetOneFuli();
	GetAdv();

	$("#btnsearch").click(function(){
        var keyword=$("#inputkey").val();
		SearchArticleList(1, 1, 10,id,keyword);
	})
});

//获取文章
function SearchArticleList(type, pg, sz,id,keyword) {
	var index = layer.load(0, {
		shade: false,
		offset: ['100px']
	}); //0代表加载的风格，支持0-2


	var data1 = {
		page: pg,
		size: sz,
		type:id,
		keyword:keyword
	}
	
	$.ajax({
		url: apiUrl + 'index/index/getArticleList',
		type: 'post', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		contentType: 'application/json',
		data: JSON.stringify(data1),
		success: function(jsondata) {
			layer.close(index);
			if(jsondata.status == 0) {
				if(type == 1) {
					laypage.render({
						elem: 'page',
						count: jsondata.num,
						layout: ['prev', 'page', 'next', 'count'],
						jump: function(obj, first) {
							if(!first) {
								SearchArticleList(0, obj.curr, obj.limit);
							}
						}
					});
				}
				$("#ulArticle").html("");
				$.each(jsondata.data, function(index, item) {
					var lihtml = '';
					lihtml += '<li class="layui-row">';
					lihtml += '<a href="articledetail.html?id=' + item.id + '">';
					lihtml += '<img src="' + item.picture + '"  onerror="errorImg(this)" class="layui-col-xs3 layui-col-sm3 layui-col-md3" alt="" />';
					lihtml += '<div class="layui-col-xs9 layui-col-sm9 layui-col-md9">';
					lihtml += '<h2> ' + item.title + '</h2>';
					lihtml += '<div class="fly-list-info">' + item.abstract + '</div>';
					lihtml += '<div class="fly-list-info">';
					lihtml += '<span> <i class="fa fa-clock-o" title="时间"></i>' + item.updated_at.substr(0, 10) + '</span>';
					lihtml += '<span> <i class="fa fa-user" title="作者"></i>' + "管理员" + '</span>';
					/*lihtml += '<span>  <i class="fa fa-tag" title="tag"></i>' + getarticletype(item.Articletype) + '</span>';*/
					lihtml += '<span class="fly-list-nums"><i class="fa fa-eye" title="浏览"></i>' + item.view + '<i class="fa fa-commenting" title="评论"></i> ' + 0 + '</span>';
					lihtml += '</div>';
					lihtml += '<div class="fly-list-badge">';
					if(item.istop == 1) {
						lihtml += '<span class="layui-badge layui-bg-black">置顶</span>';
					}
					if(item.ishot == 1) {
						lihtml += '<span class="layui-badge layui-bg-red">推荐</span>';
					}
					lihtml += '</div>';
					lihtml += '</div></a></li>';
					$("#ulArticle").append(lihtml);
				});

			}
		}

	});

}