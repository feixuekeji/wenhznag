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
			Articleid: -1
		}
		AddComment(com);
		return false;
	});
	
	GetMyInfo();
	GetMySite();
	GetFriendlinkList();
	GetCommentList();
});
function replysome(name) {
	
   $("html,body").animate({scrollTop:200}, 500);
	var spinname = '<spin style="color: #01AAED;">@' + name + '</spin><spin style="color: black;">&nbsp;</spin>'
	$("#L_content").val(spinname);
	editIndex = layedit.build('L_content', {
		height: 150,
		tool: ['face', '|', 'strong', 'italic', 'underline', 'del', '|', 'link'],
	});
}
//获取博主信息
function GetMyInfo() {
	$.ajax({
		url: apiUrl + 'api/Web/GetMyInfo',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json', //返回的数据格式：json/xml/html/script/jsonp/text
		success: function(jsondata) {
			if(jsondata.Type == 1) {
				var data = jsondata.Data;
				$("#myname").html(data.Name);
				$("#oneword").html(data.Oneword);
				$("#myaddress").html(data.Address);
				var mylinkhtml = '';
				mylinkhtml += '<a target="_blank" title="QQ交流" href="http://wpa.qq.com/msgrd?v=3&uin=' + data.Qq + '&site=qq&menu=yes"><i class="fa fa-qq fa-2x"></i></a>';
				mylinkhtml += '<a target="_blank" title="给我写信" href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&amp;email=' + data.Email + '"><i class="fa fa-envelope fa-2x"></i></a>';
				mylinkhtml += '<a target="_blank" title="新浪微博" href="' + data.Weibo + '"><i class="fa fa-weibo fa-2x"></i></a>';
				mylinkhtml += '<a target="_blank" title="github" href="' + data.Git + '"><i class="fa fa-git fa-2x"></i></a>';
				$("#mylink").html("").html(mylinkhtml);
				$("#myinfo").html(data.Info);
				$("#myhead").attr("src",data.Imgurl);

			}
		}

	});

}

//获取博客信息
function GetMySite() {
	$.ajax({
		url: apiUrl + 'api/Web/GetMySite',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json', //返回的数据格式：json/xml/html/script/jsonp/text
		success: function(jsondata) {
			if(jsondata.Type == 1) {
				var data = jsondata.Data;
				$("#sitename").html(data.Name);
				$("#siteoutline").html(data.Oneword);
				$("#siteinfo").html(data.Info);

			}
		}

	});
}
//获取友链
function GetFriendlinkList() {
	$.ajax({
		url: apiUrl + 'api/Web/GetFriendlinkList',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json', //返回的数据格式：json/xml/html/script/jsonp/text
		success: function(jsondata) {
			if(jsondata.Type == 1) {
				$("#ulfriendlink").html("");
				$.each(jsondata.Data, function(index, item) {
					var lihtml = '';
					lihtml += '<li class="layui-col-xs12 layui-col-sm6 layui-col-md3">';
					lihtml += '<a target="_blank" href="' + item.Linkurl + '" title="Layui" class="friendlink-item">';
					lihtml += '<div class="layui-col-xs3 layui-col-sm3 layui-col-md3"><img src="' + item.Linkimg + '" onerror="errorLink(this)" class="layui-col-xs3 layui-col-sm3 layui-col-md3" alt="' + item.Linkname + '"></div>';
					lihtml += '<div class="layui-col-xs9 layui-col-sm9 layui-col-md9"><p class="friendlink-item-title">' + item.Linkname + '</p>';
					lihtml += '<p >' + item.Linkurl + '</p></div>';
					lihtml += '</a></li>';
					$("#ulfriendlink").append(lihtml);
				});

			}
		}

	});

}

//获取留言
function GetCommentList() {
	$.ajax({
		url: apiUrl + 'api/Web/GetCommentList',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json', //返回的数据格式：json/xml/html/script/jsonp/text
		success: function(jsondata) {
			if(jsondata.Type == 1) {
				$("#jieda").html("");
				$.each(jsondata.Data, function(index, item) {
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
function AddComment(comm) {
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
				GetCommentList();
			}
		}

	});
}
