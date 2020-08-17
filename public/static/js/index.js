var $, layer, laypage, element;
layui.use(['layer', 'element', 'laypage'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	element = layui.element;
	laypage = layui.laypage;
    getArticletype();
    getArticleListByCatalog(0,1,10);
	GetAnnouncementList();
	GetMyInfo();
	GetFriendlinkList();
	GetTopArticleList();
	GetTopResourceList();
	GetOneFuli();
	GetAdv();

    $("#btnsearch").click(function(){
        keyword=$("#inputkey").val();
        window.location.href= apiUrl + "index/index/articleList?keyword="+keyword;

    })

});

//获取公告
function GetAnnouncementList() {
	$.ajax({
		url: apiUrl + 'api/Web/GetAnnouncementList',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json', //返回的数据格式：json/xml/html/script/jsonp/text
		success: function(jsondata) {
			if(jsondata.Type == 1) {
				var anhtml = [];
				$.each(jsondata.Data, function(index, item) {
					var color = '#393D49';
					if(item.Anlevel == 2) {
						color = '#009688';
					}
					if(item.Anlevel == 3) {
						color = 'red';
					}
					anhtml.push('<li class="layui-timeline-item">' +
						'<i class="layui-icon layui-timeline-axis">&#xe63f;</i>' +
						'<div class="layui-timeline-content layui-text">' +
						'<h3 class="layui-timeline-title">' + item.Createtime.substr(0, 10) + '</h3>' +
						'<p style="color:' + color + '"> ' + item.Contentstr + ' </p>' +
						'</div></li>');
				});
				$("#ulAnnouncement").html("").html(anhtml);

			}
		},
		error: function(xhr, textStatus) {

		},
		complete: function() {

		}
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
				$("#myhead").attr("src",data.Imgurl);

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
				var anhtml = [];
				$.each(jsondata.Data, function(index, item) {
					anhtml.push('<dd><a href="' + item.Linkurl + '" target="_blank">' + item.Linkname + '</a></dd>');
				});
				anhtml.push('<dd><a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&amp;email=1063721800@qq.com" class="fly-link">申请友链</a></dd>');

				$("#dlFriendlink").html("").html(anhtml);

			}
		},
		error: function(xhr, textStatus) {

		},
		complete: function() {

		}
	});

}
//获取文章
function SearchArticleList(type, pg, sz) {
	var index = layer.load(0, {shade: false,offset: ['100px']}); //0代表加载的风格，支持0-2
	var data1 = {
		page: pg,
		size: sz
	}
	$.ajax({
		url: apiUrl + 'api/Web/SearchArticleList',
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
								SearchArticleList(0, obj.curr, obj.limit);
							}
						}
					});
				}
				$("#ulArticle").html("");
				$.each(jsondata.Data, function(index, item) {
					var lihtml = '';
					lihtml += '<li class="layui-row">';
					lihtml += '<a href="articledetail.html?id=' + item.Id + '">';
					lihtml += '<img src="' + item.Imgurl + '"  onerror="errorImg(this)" class="layui-col-xs3 layui-col-sm3 layui-col-md3" alt="" />';
					lihtml += '<div class="layui-col-xs9 layui-col-sm9 layui-col-md9">';
					lihtml += '<h2> ' + item.Articlename + '</h2>';
					lihtml += '<div class="fly-list-info">' + item.Articleoutline + '</div>';
					lihtml += '<div class="fly-list-info">';
					lihtml += '<span> <i class="fa fa-clock-o" title="时间"></i>' + item.Createtime.substr(0, 10) + '</span>';
					lihtml += '<span> <i class="fa fa-user" title="作者"></i>' + item.Author + '</span>';
					lihtml += '<span>  <i class="fa fa-tag" title="tag"></i>' + getarticletype(item.Articletype) + '</span>';
					lihtml += '<span class="fly-list-nums"><i class="fa fa-eye" title="浏览"></i>' + item.Readcount + '<i class="fa fa-commenting" title="评论"></i> ' + item.Commentcount + '</span>';
					lihtml += '</div>';
					lihtml += '<div class="fly-list-badge">';
					if(item.Istop == 1) {
						lihtml += '<span class="layui-badge layui-bg-black">置顶</span>';
					}
					if(item.Ishot == 1) {
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


//获取文章
function getArticleListByCatalog(type, pg, sz) {
    var index = layer.load(0, {shade: false,offset: ['100px']}); //0代表加载的风格，支持0-2
    var data1 = {
        page: pg,
        size: sz,
		type: type,
    }
    $.ajax({
        url: apiUrl + 'index/index/getArticleListByCatalog',
        type: 'post', //GET
        async: true, //或false,是否异步
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data1),
        success: function(jsondata) {
            layer.close(index);
            if(jsondata.status == 0) {

                $("#catalogBox").html("");
                var lihtml = '';
                $.each(jsondata.data, function(index, item) {


                    if(index % 2 == 0)
					{
                        lihtml += '<div class="layui-row grid-demo layui-col-space15">';
					}
                    lihtml += '<div class="layui-col-md6">' +
                        '<div class="grid-demo grid-demo-bg2">' +
                        '<div class="layui-card">' +
                        '<div class="layui-card-header">'+item.name+'</div>'+
						'<div class="layui-card-body">';
                    $.each(item.list, function(index1, item1) {
                        lihtml += '<dd><a href="articleDetail.html?id=' + item1.id + '">'+item1.title+'</a></dd>';
                    });
                    lihtml += '</div>';
                    lihtml += '</div>';
                    lihtml += '</div>';
                    lihtml += '</div>';
                    if(index % 2 == 1)
                    {
                        lihtml += '</div>';
                    }


                });
                $("#catalogBox").append(lihtml);

            }
        }

    });

}