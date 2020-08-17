var $, layer, laypage, element;
layui.use(['layer', 'element', 'laypage'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	element = layui.element;
	laypage = layui.laypage;
	var id = getUrlParam("id");
	SearchFulieList(1, id);
	GetTopArticleList();
	GetTopResourceList();
	GetOneFuli();
	GetAdv();

});

//获取福利详情
function SearchFulieList(type, id) {
	var index = layer.load(0, {
		shade: false,
		offset: ['100px']
	}); //0代表加载的风格，支持0-2

	$.ajax({
		url: apiUrl + 'api/Web/GetFuliDetail/' + id,
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		contentType: 'application/json',
		success: function(jsondata) {
			layer.close(index);
			if(jsondata.Type == 1) {
				var item = jsondata.Data;
				if(type == 1) {
					laypage.render({
						elem: 'page',
						limit: 1,
						count: jsondata.Data.detail.length,
						layout: ['prev', 'page', 'next', 'count'],
						jump: function(obj, first) {
							if(!first) {
								$("#imgUrl").attr("src", item.detail[obj.curr-1].Imgurl);
							}
						}
					});
				}
				
				$("#h1title").html(item.data.Title);
				$("#spinTime").html(item.data.Createtime.substring(0, 10));
				$("#spinReadcount").html(item.data.Readcount);
				$("#imgUrl").attr("src", item.detail[0].Imgurl);

			}
		}

	});

}