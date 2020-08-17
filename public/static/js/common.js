//$(function(){
//	$(document).on('click','.m-numinput',function(){
//		var $this = $(this);
//		var value = $(this).find('input').val() >>> 0;
//		var min = $(this).find('input').attr('min') >>> 0;
//		var max = $(this).find('input').attr('max') >>> 0;
//
//		if($(event.target).hasClass('dec')){
//			value = value - 1;
//			if(min && value<min){
//				value = min;
//			}
//		}
//		if($(event.target).hasClass('add')){
//			value = value + 1;
//			if(max && value>max){
//				value = max;
//			}
//		}
//		$(this).find('input').val(value).trigger('input');
//	});
//});