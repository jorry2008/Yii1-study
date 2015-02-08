//兼容处理
(function($) { 
	$(function() {
		var content = $('#contextual-help-wrap');
		var link = $('#contextual-help-link');
		link.on('click',function(){
			content.slideToggle('fast', function(){
				var linkspan = link.children();
				linkspan.removeClass();
				if(content.css('display') == 'none')
					linkspan.addClass('glyphicon glyphicon-triangle-bottom');
				else 
					linkspan.addClass('glyphicon glyphicon-triangle-top');	
			});
		});
	});
})(jQuery);




