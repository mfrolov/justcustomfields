jQuery(document).ready(function($){
	
	var justCustomField = jQuery('.jcf_edit_field');
	function tagsChecker(){
		var tagsLength = 0;
		var	categoriesLength = 0;
		clearInterval(myInterval);
		var myInterval = setInterval(function(){
			if(jQuery('.tagchecklist').children().length != tagsLength) {
				tagsLength = jQuery('.tagchecklist').children().length;
				tagChanged(jQuery('.tagchecklist').children());
				catChanged(jQuery('#categorychecklist input:checked'));

			}
			if(jQuery('#categorychecklist input:checked').length != categoriesLength){
				categoriesLength = jQuery('#categorychecklist input:checked').length;
				catChanged(jQuery('#categorychecklist input:checked'));
				tagChanged(jQuery('.tagchecklist').children());
			}
		}, 500);
		

		function tagChanged(sel){
			//Checking, when we don't have any term for tags
			if(sel.length == 0 && jQuery('#categorychecklist input:checked').length == 0){
				justCustomField.each(function(){
					if(jQuery(this).hasClass('visible')){
						jQuery(this).removeClass('show');
					}
					else{
						jQuery(this).removeClass('hide');
					}
				})
			}
			sel.each(function(){
				var _break = false;
				var slug = jQuery(this).text().toLowerCase().slice(2).replace(/ /g, '-');
				jcfChecker(slug);
				if(_break == true){
					return false;
				}
			});
		}
		function catChanged(sel){
			if(sel.length == 0 && jQuery('.tagchecklist').children().length == 0){
				justCustomField.each(function(){
					if(jQuery(this).hasClass('visible')){
						jQuery(this).removeClass('show');
					}
					else{
						jQuery(this).removeClass('hide');
					}
				})
			}
			sel.each(function(){
				var _break = false;
				var slug = jQuery(this).parent().text().toLowerCase().slice(1).replace(/ /g, '-');
				jcfChecker(slug);
				if(_break == true){
					return false;
				}
			});
		}
		
		function jcfChecker(slug){
			justCustomField.each(function(){
				var jcf_terms = jQuery(this).data('terms').split(',');
				var visibility = jQuery(this).data('visibility');
				var trigger = 1;

				for(i=0; i<jcf_terms.length; i++){

					trigger = slug.localeCompare(jcf_terms[i]);
					console.log(slug);
					console.log(jcf_terms[i]);
					console.log(trigger);
					if(trigger == 0 && visibility == 'visible'){
						var _break = true;
						jQuery(this).addClass('show');
						return _break;
					}
					if(trigger == 0 && visibility == 'invisible'){
						var _break = true;
						jQuery(this).addClass('hide');
						return _break;
					}
//					if(trigger != 0 && visibility == 'visible'){
//						jQuery(this).removeClass('show');
//					}
//					else{
//						jQuery(this).removeClass('hide');
//					}
				}
			});
		}
	}
	   tagsChecker();
})