var $, layer, form, laypage, element, layedit, editIndex;
layui.use(['layer', 'form', 'element', 'laypage', 'layedit'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	form = layui.form;
	element = layui.element;
	laypage = layui.laypage;
	layedit = layui.layedit;
	editIndex = layedit.build('L_content', {
		height: 150,
		tool: ['face', '|', 'strong', 'italic', 'underline', 'del', '|', 'link'],
	});
	//自定义验证规则
	form.verify({
		content: function(value) {
			layedit.sync(editIndex);
			value = $.trim(layedit.getContent(editIndex));
			if(value.length <= 0) {
				return "至少得有一个字吧"
			}
		}
	});
	var id = getUrlParam("id");
	//监听提交
	form.on('submit(formReply)', function(data) {
		if(!QC.Login.check()) {
			layer.msg("请先登录");
			return false;
		}
		layedit.sync(editIndex);
		var com = {
			Comment: data.field.content,
			Commentname: username,
			Commetfigureurl: userfigure,
			Createtime: new Date(),
			Articleid: id
		}
		AddComment(com, id);
		return false;
	});

	GetArticleById(id)
	GetTopArticleList();
    GetSimilarList();
	GetOneFuli();
	GetAdv();

});

function replysome(name) {
	$("html,body").animate({
		scrollTop: 1000000
	}, 500);
	var spinname = '<spin style="color: #01AAED;">@' + name + '</spin><spin style="color: black;">&nbsp;</spin>'
	$("#L_content").val(spinname);
	editIndex = layedit.build('L_content', {
		height: 150,
		tool: ['face', '|', 'strong', 'italic', 'underline', 'del', '|', 'link'],
	});
}

//获取文章
function GetArticleById(id) {
	var index = layer.load(0, {
		shade: false,
		offset: ['100px']
	}); //0代表加载的风格，支持0-2

	$.ajax({
		url: "{:url('index/index/ajaxGetArticle')}" + id,
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		contentType: 'application/json',
		success: function(jsondata) {
			layer.close(index);
			if(jsondata.status == 1) {
				var item = jsondata.data;
				var commentlist = jsondata.Data.detail;

				$("#h1title").html(item.title);
				$("#divinfo").html("");
				if(item.Istop == 1) {
					$("#divinfo").append('<span class="layui-badge layui-bg-black">置顶</span>');
				}
				if(item.Ishot == 1) {
					$("#divinfo").append('<span class="layui-badge layui-bg-red">推荐</span>');
				}
				$("#divinfo").append('<span>  <i class="fa fa-clock-o" title="时间"></i>' + item.Createtime.substr(0, 10) + '</span>');
				$("#divinfo").append('<span>  <i class="fa fa-user" title="作者"></i>' + item.Author + '</span>');
				$("#divinfo").append('<span>  <i class="fa fa-tag" title="标签"></i>' + getarticletype(item.Articletype) + '</span>');
				$("#divinfo").append('<span class="fly-list-nums"><a href="#comment"><i class="fa fa-commenting" title="回答"></i> ' + commentlist.length + '</a><i class="fa fa-eye" title="浏览"></i> ' + item.Readcount + ' </span>');
				$("#divdetail").html("").html(item.content);
				$("#jieda").html("");
				$.each(commentlist, function(index, item) {
					var jiedahtml = '';
					jiedahtml += '<li class="jieda-daan">';
					jiedahtml += '<div class="detail-about detail-about-reply">';
					jiedahtml += '<a class="fly-avatar" href="javascript:void(0)"><img src="' + item.Commetfigureurl + '" onerror="errorFigure(this)" /></a>';
					jiedahtml += '<div class="fly-detail-user">';
					jiedahtml += '<a href="javascript:void(0)" class="fly-link">';
					jiedahtml += '<cite>' + item.Commentname + '</cite>';
					jiedahtml += '</a>';
					jiedahtml += '</div>';
					jiedahtml += '<div class="detail-hits">';
					jiedahtml += '<span>' + item.Createtime.substr(0, 10) + '</span>';
					jiedahtml += '</div>';
					jiedahtml += '</div>';
					jiedahtml += '<div class="detail-body jieda-body photos">';
					jiedahtml += '<p>' + item.Comment + '</p>';
					jiedahtml += '</div>';
					jiedahtml += '<div class="jieda-reply">';
					jiedahtml += '<span onclick="replysome(&quot;' + item.Commentname + '&quot;)" type="reply">';
					jiedahtml += ' <i class="fa fa-comment"></i>';
					jiedahtml += ' 回复';
					jiedahtml += ' </span>';
					jiedahtml += ' </div></li>';
					$("#jieda").append(jiedahtml);
				});

			}
		}

	});

}
//提交评论
function AddComment(comm, id) {
	var index = layer.load(0);
	$.ajax({
		url: apiUrl + 'api/Web/AddComment',
		type: 'post', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		contentType: 'application/json',
		data: JSON.stringify(comm),
		success: function(jsondata) {
			layer.close(index);
			if(jsondata.Type == 1) {
				$("#L_content").val("");
				editIndex = layedit.build('L_content', {
					height: 150,
					tool: ['face', '|', 'strong', 'italic', 'underline', 'del', '|', 'link'],
				});
				GetArticleCommentList(id);
			}
		}

	});
}
//获取评论
function GetArticleCommentList(id) {
	var index = layer.load(0, {
		shade: false,
		offset: ['100px']
	}); //0代表加载的风格，支持0-2

	$.ajax({
		url: apiUrl + 'api/Web/GetArticleCommentList/' + id,
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		contentType: 'application/json',
		success: function(jsondata) {
			layer.close(index);
			if(jsondata.Type == 0) {
				var commentlist = jsondata.Data;
				$("#jieda").html("");
				$.each(commentlist, function(index, item) {
					var jiedahtml = '';
					jiedahtml += '<li class="jieda-daan">';
					jiedahtml += '<div class="detail-about detail-about-reply">';
					jiedahtml += '<a class="fly-avatar" href="javascript:void(0)"><img src="' + item.Commetfigureurl + '" onerror="errorFigure(this)" /></a>';
					jiedahtml += '<div class="fly-detail-user">';
					jiedahtml += '<a href="javascript:void(0)" class="fly-link">';
					jiedahtml += '<cite>' + item.Commentname + '</cite>';
					jiedahtml += '</a>';
					jiedahtml += '</div>';
					jiedahtml += '<div class="detail-hits">';
					jiedahtml += '<span>' + item.Createtime.substr(0, 10) + '</span>';
					jiedahtml += '</div>';
					jiedahtml += '</div>';
					jiedahtml += '<div class="detail-body jieda-body photos">';
					jiedahtml += '<p>' + item.Comment + '</p>';
					jiedahtml += '</div>';
					jiedahtml += '<div class="jieda-reply">';
					jiedahtml += '<span onclick="replysome(&quot;' + item.Commentname + '&quot;)" type="reply">';
					jiedahtml += ' <i class="fa fa-comment"></i>';
					jiedahtml += ' 回复';
					jiedahtml += ' </span>';
					jiedahtml += ' </div></li>';
					$("#jieda").append(jiedahtml);
				});

			}
		}

	});

}


