 //收藏
 $(".collect").click(function () {
    $(this).toggleClass("active")
})
//公众号列表页选中状态
$('.publist-list .public-list-item').on('click',function(){
    $(this).addClass("active").siblings().removeClass("active");
})