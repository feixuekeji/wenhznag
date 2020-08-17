//var apiUrl = "http://localhost:56713/";
var apiUrl = "/index.php/";

var $, layer, element;
layui.use(['layer', 'element', 'laypage'], function() {
	$ = layui.jquery;
	layer = layui.layer;
	element = layui.element;




});

//获取url中的参数
function getUrlParam(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
	var r = window.location.search.substr(1).match(reg); //匹配目标参数
	if(r != null) return unescape(r[2]);
	return null; //返回参数值
}


function errorImg(img) {
	img.src = "images/helloworld.png"
	img.onerror = null;
}

function errorFigure(img) {
	img.src = "images/touxiang.jpg"
	img.onerror = null;
}

function errorLink(img) {
	img.src = "images/friendlink.png"
	img.onerror = null;
}


function getArticletype(catalogId = 0) {
    var data1 = {
        catalogId: catalogId,
    }
    $.ajax({
        url: apiUrl + 'index/index/getType',
        type: 'post', //GET
        async: true, //或false,是否异步
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data1),
        success: function(jsondata) {
            if(jsondata.status == 0) {
                $("#type").html("");
                $.each(jsondata.data, function(index, item) {
                    $("#type").append('<li><a href="articleList.html?id=' + item.id + '">' + item.name + '</a></li>');


                });

            }
        },
    });
}

function getrestype(id) {
	switch(id) {
		case 0:
			return "默认分类";
			break;
		case 1:
			return "源码";
			break;
		case 2:
			return "文档";
			break;
		default:
			return "默认分类";
			break;

	}
}
//获取热门文章
function GetTopArticleList() {
	$.ajax({
		url: apiUrl + 'index/index/getTopArticleList',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		success: function(jsondata) {
			if(jsondata.status == 0) {
				$("#dlTopArticle").html("").append('<dt class="fly-panel-title">热门文章</dt>');
				$.each(jsondata.data, function(index, item) {
					$("#dlTopArticle").append('<dd><a href="articleDetail.html?id=' + item.id + '"><i class="fa fa-thumbs-up"></i>' + item.title + '</a></dd>');
				});

			}
		},
		error: function(xhr, textStatus) {

		},
		complete: function() {

		}
	});

}

//获取最新资源
function GetTopResourceList() {
	$.ajax({
		url: apiUrl + 'api/Web/GetTopResourceList',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		success: function(jsondata) {
			if(jsondata.Type == 1) {
				$("#dlTopResource").html("").append('<dt class="fly-panel-title">最新资源</dt>');
				$.each(jsondata.Data, function(index, item) {
					$("#dlTopResource").append('<dd><a href="' + item.Re_downloadurl + '"><i class="fa fa-share"></i>' + item.Re_name + '</a></dd>');
				});

			}
		},
		error: function(xhr, textStatus) {

		},
		complete: function() {

		}
	});

}

//获取今日福利
function GetOneFuli() {
	$.ajax({
		url: apiUrl + 'api/Web/GetOneFuli',
		type: 'get', //GET
		async: true, //或false,是否异步
		dataType: 'json',
		success: function(jsondata) {
			if(jsondata.Type == 1) {
				$("#dlFuli").html("").append('<dt class="fly-panel-title">今日福利</dt>').append('<dd><a href="fulidetail.html?id=' + jsondata.Data.Id + '"><i class="fa fa-heart"></i>' + jsondata.Data.Title + '</a></dd>');
			}
		},
		error: function(xhr, textStatus) {

		},
		complete: function() {

		}
	});

}

//获取广告
function GetAdv() {
	$("#divAdv").html("");	
	$("#divAdv").append('<a href="https://promotion.aliyun.com/ntms/act/ambassador/sharetouser.html?userCode=dlf1zzfd&utm_source=dlf1zzfd" target="_blank" class="fly-zanzhu" time-limit="2017.09.25-2099.01.01" style="background-color: #5FB878;">阿里云幸运券无私奉上</a>');
	$("#divAdv").append('<a href="https://originss.org/auth/register?code=3013" target="_blank" class="fly-zanzhu" time-limit="2017.09.25-2099.01.01" style="background-color: #f4516c;">墙外的世界很精彩</a>');
}