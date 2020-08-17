var $, layer, laypage, element;
layui.use(['layer', 'element', 'laypage'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	element = layui.element;
	var id=getUrlParam("id");
	
	GetTimelineList();
	GetTopArticleList();
	GetTopResourceList();
	GetOneFuli();
	GetAdv();
	$("#btnsearch").click(function(){
		SearchArticleList(1, 1, 10,id);
	})
});

//获取时光轴
function GetTimelineList() {
	var index = layer.load(0, {
		shade: false,
		offset: ['100px']
	}); //0代表加载的风格，支持0-2
	
	$.ajax({
		url: apiUrl + 'api/Web/GetTimelineList',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		contentType: 'application/json',	
		success: function(jsondata) {
			layer.close(index);
			if(jsondata.Type == 1) {				
				$("#ulTimeline").html("");
				$.each(jsondata.Data, function(index, item) {

					var lihtml = '';
					lihtml += '<li class="layui-timeline-item">';
					lihtml += '<i class="layui-icon layui-timeline-axis">&#xe63f;</i>';
					lihtml += '<div class="layui-timeline-content layui-text">';
					lihtml += '<h3 class="layui-timeline-title">'+item.Createtime.substr(0,10)+'</h3>';
					lihtml += '<p>';
					lihtml += item.Contentstr;
					lihtml += '</p>';				
					lihtml += '</div></li>';
					$("#ulTimeline").append(lihtml);
				});

			}
		}

	});

}