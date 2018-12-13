var isMobile = false;
window.datepicker_load = false;

if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
	|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4)))
{
	isMobile = true;
}


function isElementInViewport (el)
{
	if (typeof jQuery === "function" && el instanceof jQuery) {
		el = el[0];
	}
	var rect = el.getBoundingClientRect();
	return (
		rect.top >= 0 &&
		rect.left >= 0 &&
		rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
		rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
		);
}

if (window.location.protocol == "https:")
{
	FC.ajaxurl = FC.ajaxurl.replace('http:','https:');
	FC.datepickerLang = FC.datepickerLang.replace('http:','https:');
}

(function( $ ) {

	$.fn.fc_validate = function() {
		if(jQuery(this).attr('data-allow-spaces') && jQuery(this).attr('data-allow-spaces')=='true')
		{
			var alphabets = /^[A-Za-z ]+$/;
			var numbers = /^[0-9 ]+$/;
			var alphanumeric = /^[0-9A-Za-z ]+$/;
			var url = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
			var email =/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,6})+$/;
		}
		else
		{
			var alphabets = /^[A-Za-z]+$/;
			var numbers = /^[0-9]+$/;
			var alphanumeric = /^[0-9A-Za-z]+$/;
			var url = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
			var email =/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,6})+$/;
		}
		var value = jQuery(this).val();
		if (jQuery(this).is('[type="checkbox"]')||jQuery(this).is('[type="radio"]'))
		{
			var name = jQuery(this).attr('name');
			var value = jQuery('[name="'+name+'"]:checked').val();
			value = typeof value=='undefined' ? '' : value;
		}
		var this_element = jQuery(this);
		if(jQuery(this).attr('data-is-required') && jQuery(this).attr('data-is-required')=='true' && value.trim()=='')
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.is_required);
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-input-mask') && jQuery(this).attr('data-input-mask')!='' && jQuery(this).hasClass('mask-invalid'))
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.is_invalid);
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-is-required') && jQuery(this).attr('data-is-required')=='false' && value.trim()=='')
		{
			this_element.parents('.form-element').find('.error').text('');
			this_element.parents('.form-element').removeClass('error-field');
			return true;
		}
		if(jQuery(this).attr('data-min-char') && jQuery(this).attr('data-min-char')>value.length)
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.min_char.replace('[x]',jQuery(this).attr('data-min-char')));
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-max-char') && jQuery(this).attr('data-max-char')<value.length)
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.max_char.replace('[x]',jQuery(this).attr('data-max-char')));
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-val-type') && jQuery(this).attr('data-val-type')=='email' && !value.match(email))
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.allow_email);
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-val-type') && jQuery(this).attr('data-val-type')=='alphabets' && !value.match(alphabets))
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.allow_alphabets);
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-val-type') && jQuery(this).attr('data-val-type')=='numbers' && !value.match(numbers))
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.allow_numbers);
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-val-type') && jQuery(this).attr('data-val-type')=='alphanumeric' && !value.match(alphanumeric))
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.allow_alphanumeric);
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-val-type') && jQuery(this).attr('data-val-type')=='url' && !value.match(url))
		{
			this_element.parents('.form-element').find('.error').text(FC.validation.allow_url);
			this_element.parents('.form-element').addClass('error-field');
			return false;
		}
		if(jQuery(this).attr('data-val-type') && jQuery(this).attr('data-val-type')=='regexp')
		{
			var flags = jQuery(this).attr('data-regexp').replace(/.*\/([gimy]*)$/, '$1');
			var pattern = jQuery(this).attr('data-regexp').replace(new RegExp('^/(.*?)/'+flags+'$'), '$1');
			var regex = new RegExp(pattern);
			if ( regex.exec(value) == null )
			{
				this_element.parents('.form-element').find('.error').text(FC.validation.allow_regex);
				this_element.parents('.form-element').addClass('error-field');
				return false;
			}
		}
		this_element.parents('.form-element').removeClass('error-field');
		return true;

	};

}( jQuery ));


function setFormValues(form, data)
{
	for (x in data)
	{
		var element = form.find('[name="'+x+'"]').length==0 ? form.find('[name="'+x+'[]"]') : form.find('[name="'+x+'"]');
		var elementType = element.prop('type');
		elementType = element.is('select') ? 'select' : elementType;
		elementType = element.hasClass('hasDatepicker') ? 'date' : elementType;
		elementType = element.parent().parent().hasClass('files-list') ? 'file' : elementType;
		elementType = element.parents('.field-cover').hasClass('slider-cover') ? 'slider' : elementType;
		elementType = element.parents('.field-cover').hasClass('timepicker-cover') ? 'timepicker' : elementType;
		switch (elementType)
		{
			case 'text': case 'select': case 'hidden': case 'textarea': case 'date':
			if ( data[x] != element.val() )
			{
				element.val(data[x]).trigger('input').trigger('change');
			}
			break;

			case 'radio': case 'checkbox':
			if ( typeof data[x] == 'string' && data[x] == '' && form.find('[name="'+x+'[]"]').length>0 ) {
				form.find('[name="'+x+'[]"]').prop('checked',false).trigger('change');
			}
			if ( typeof data[x] == 'string' ) { var temp = data[x]; data[x] = []; data[x].push(temp); }
			data[x] = typeof data[x] == 'string' ? [data[x]] : data[x];
			for (y in data[x])
			{
				if (form.find('[name="'+x+'[]"]').length==0)
				{
					form.find('[name="'+x+'"][value="'+data[x][y]+'"]').prop('checked',true).trigger('change');
					var abc = data[x][y];
					var abcd = x;
					setTimeout(function(){
						form.find('[name="'+abcd+'"][value="'+abc+'"]').prop('checked',true).trigger('change');
					}, 300);
				}
				else
				{
					form.find('[name="'+x+'[]"][value="'+data[x][y]+'"]').prop('checked',true).trigger('change');
				}
			}
			break;

			case 'timepicker':
			element.val(data[x]).trigger('change');
			var time = data[x].replace(' ',':').split(':');
			time[0] = time[0]=='' || typeof time[0]=='undefined' ? '00' : time[0];
			time[1] = time[1]=='' || typeof time[1]=='undefined' ? '00' : time[1];
			time[2] = time[2]=='' || typeof time[2]=='undefined' ? 'am' : time[2];
			element.parents('.timepicker-cover').find('.time-fields-cover > select').eq(0).val(time[0]);
			element.parents('.timepicker-cover').find('.time-fields-cover > select').eq(1).val(time[1]);
			element.parents('.timepicker-cover').find('.time-fields-cover > input').eq(0).val(time[2]);
			break;

			case 'slider':
			if (data[x].indexOf('-')!=-1)
			{
				var temp = data[x].split('-');
				temp[0] = temp[0].replace(/[^0-9.]+/g, '');
				temp[1] = temp[1].replace(/[^0-9.]+/g, '');
				temp[0] = parseFloat(temp[0].trim());
				temp[1] = parseFloat(temp[1].trim());
				if ( !isNaN(parseFloat(temp[0])) && !isNaN(parseFloat(temp[1])) )
				{
					element.parents('.slider-cover').find('.ui-slider-cover > span').slider('values', temp);
					element.parents('.slider-cover').find('.ui-slider-handle-nos').show();
					var elementTemp = element;
					setTimeout(function(){
						elementTemp.parents('.slider-cover').find('.ui-slider-handle-nos').css('margin-left','-'+(elementTemp.parents('.slider-cover').find('.ui-slider-handle-nos').outerWidth()/2-9)+'px');
					}, 10);
				}
			}
			else
			{
				data[x] = data[x].replace(/[^0-9.]+/g, '');
				if ( !isNaN(parseFloat(data[x])) )
				{
					element.parents('.slider-cover').find('.ui-slider-cover > span').slider('value', data[x]);
					element.parents('.slider-cover').find('.ui-slider-handle-nos').show();
					var elementTemp = element;
					setTimeout(function(){
						elementTemp.parents('.slider-cover').find('.ui-slider-handle-nos').css('margin-left','-'+(elementTemp.parents('.slider-cover').find('.ui-slider-handle-nos').outerWidth()/2-9)+'px');
					}, 10);
				}
			}
			break;
		}
	}
}

function FormCraftSubmitForm(element, type, callback)
{
	form = jQuery(element);
	if (jQuery(element).hasClass('dont-submit-hidden-true'))
	{
		var form_data = form.find('.form-element').not('.state-hidden').find('input, select, textarea').serialize();
	}
	else
	{
		var form_data = form.serialize();
	}
	var hidden = [];
	form.find('.form-element.state-hidden').each(function(){
		hidden.push(jQuery(this).attr('data-identifier'));
	});
	hidden = hidden.join(',');

	var emails = '';
	if (typeof window.final_email_to!='undefined')
	{
		for (x in window.final_email_to)
		{
			emails = emails+','+encodeURIComponent(window.final_email_to[x].substr(window.final_email_to[x].indexOf(':')+1));
		}
	}
	var redirect = '';
	if (typeof window.final_redirect!='undefined')
	{
		var redirect = encodeURIComponent(window.final_redirect[window.final_redirect.length-1]);
	}
	var trigger_integration = '';
	if (typeof window.trigger_integration!='undefined')
	{
		var trigger_integration = encodeURIComponent( JSON.stringify(window.trigger_integration) );
	}
	var data = form_data+'&id='+form.attr('data-id')+'&location='+encodeURIComponent(window.location.href)+'&emails='+emails+'&hidden='+hidden+'&redirect='+redirect+'&type='+type+'&trigger_integration='+trigger_integration;

	var abort = {abort:false};
	if ( type=='all' )
	{
		form.find('.validation-lenient, .validation-strict').each(function(){
			if ( !jQuery(this).parents('.form-element').hasClass('state-hidden') ){
				var a = jQuery(this).fc_validate();
				if (a==false)
				{
					abort.abort = true;
				}
			}
		});
	}
	else
	{
		page_validate = type - 1;
		form.find('.form-page-'+page_validate+' .validation-lenient, .validation-strict').each(function(){
			if ( !jQuery(this).parents('.form-element').hasClass('state-hidden') ){
				var a = jQuery(this).fc_validate();
				if (a==false)
				{
					abort.abort = true;
				}
			}
		});
	}
	if (type=='all')
	{
		jQuery(document).trigger('formcraft_submit_trigger', [form, data, abort]);
	}
	if (abort.abort==true)
	{
		if (form.find('.error-field').length==0){return false;}
		if ( isElementInViewport(form.find('.error-field').first()) == false )
		{
			var y = form.find('.error-field').first().offset().top;
			if (form.parents('.fc-form-modal').length)
			{
				y = (form.parents('.fc-form-modal').scrollTop()+y)-(form.height()+130);
				form.parents('.fc-form-modal').animate({ scrollTop: form.find('.error-field').first().position().top }, 600);
			}
			else if (form.parents('.fc-sticky').length)
			{
				jQuery('.fc-sticky').animate({ scrollTop: form.find('.error-field').first().position().top-30 }, 600);
			}
			else if (form.parent().find('.fc-pagination.fixed').length)
			{
				jQuery('html, body').animate({ scrollTop: y-200 }, 600);
			}
			else
			{
				jQuery('html, body').animate({ scrollTop: y-120 }, 600);
			}
		}
		if (typeof callback !='undefined'){callback(false);}
		return false;
	}
	form.find('.submit-response').slideUp('fast').html();
	form.find('.submit-cover').addClass('disabled');
	form.find('.form-element').removeClass('error-field');
	if (type=='all')
	{
		form.find('.submit-button').attr('disabled','disabled').attr('data-old-width', form.find('.submit-button').outerWidth()).css('width', form.find('.submit-button').outerWidth()).css('width',form.find('.submit-button').outerHeight()).css('display','block');
	}
	temp_form = form;
	jQuery.ajax( {
		url: FC.ajaxurl,
		type: "POST",
		timeout: 30000,
		data: 'action=formcraft3_form_submit&'+data,
		dataType: "json"
	} )
	.done(function(response) {
		form = temp_form;
		if (response.debug)
		{
			if (response.debug.failed)
			{
				if (typeof toastr!='undefined') {
					for (x in response.debug.failed)
					{
						toastr["error"](response.debug.failed[x]);
					}
				}
			}
			if (response.debug.success)
			{
				if (typeof toastr!='undefined') {
					for (x in response.debug.success)
					{
						toastr["success"]("<i class='icon-ok'></i> "+response.debug.success[x]);
					}
				}
			}
		}
		if (response.failed)
		{
			if (form.parents('.fc-form-modal').length!=0)
			{
				setTimeout(function(){
					form.addClass('shake');
					//form.removeClass('shake');
				}, 600);
				setTimeout(function(){
					form.removeClass('shake');
				}, 1100);				
			}
			form.find('.validation-lenient').addClass('validation-strict').removeClass('.validation-lenient');
			form.find('.submit-response').html("<span class='has-error'>"+response.failed+"</span>").slideDown('fast');
			if (response.errors)
			{
				for (field in response.errors) {
					form.find('.form-element-'+field).addClass('error-field');
					form.find('.form-element-'+field+' .error').text(response.errors[field]);
				};
			}
			if ( form.find('.error-field').length!=0 )
			{
				if ( isElementInViewport(form.find('.error-field').first()) == false )
				{
					var y = form.find('.error-field').first().offset().top;
					if (form.parents('.fc-form-modal').length)
					{
						y = (form.parents('.fc-form-modal').scrollTop()+y)-(form.height()+130);
						form.parents('.fc-form-modal').animate({ scrollTop: form.find('.error-field').first().position().top }, 600);
					}
					else if (form.parents('.fc-sticky').length)
					{
						jQuery('.fc-sticky').animate({ scrollTop: form.find('.error-field').first().position().top-30 }, 600);
					}
					else if (form.parent().find('.fc-pagination.fixed').length)
					{
						jQuery('html, body').animate({ scrollTop: y-200 }, 600);
					}
					else
					{
						jQuery('html, body').animate({ scrollTop: y-120 }, 600);
					}
				}
			}
		}
		else if (response.success)
		{
			form.append("<div class='final-success'><i class='icon-ok-circle'></i><span>"+response.success+"</span></div>");
			form.addClass('submitted');
			form.find('.final-success').slideDown(800, function(){
			});
			form.find('.form-page').slideUp(800, function(){
				form.find('.form-element').remove();
			});
			if ( form.parents('.fc-form-modal').length == 0 && form.parents('.fc-sticky').length == 0 )
			{
				var y = form.offset().top;
				jQuery('html, body').animate({ scrollTop: y-100 }, 800);
			}
			jQuery(document).trigger('formcraft_submit_result', [form, response]);
			if (response.redirect)
			{
				var delay = parseInt(form.attr('data-delay'));
				delay = isNaN(delay) ? 2 : delay;
				delay = Math.max(0,delay);
				setTimeout(function(){
					window.location.assign(response.redirect);
				}, delay*1000);
			}
		}
		if (typeof callback !='undefined'){callback(response, form);}
	})
.fail(function(response) {
	jQuery(element).find('.response').text('Connection error');
	if (typeof callback !='undefined'){callback(false);}
})
.always(function(response) {
	jQuery(document).trigger('formcraft_submit_success_trigger', [form, response]);	
	form.find('.submit-cover').addClass('enabled');
	form.find('.submit-cover').removeClass('disabled');
	if (type=='all')
	{
		form.find('.submit-button').removeAttr('disabled').css('width', form.find('.submit-button').attr('data-old-width'));		
	}
});
}


function spinTo(selector, to, thousand, decimal)
{
	var from = jQuery(selector).text()=='' ? 0 : parseFloat(jQuery(selector).text().replace(/[^0-9.]+/g, ''));
	var to = isNaN(parseFloat(to)) ? 0 : parseFloat(to);
	var from = isNaN(parseFloat(from)) ? 0 : parseFloat(from);
	var thousand = typeof thousand=='undefined' ? '' : thousand;
	var decimal = typeof decimal=='undefined' ? '.' : decimal;
	jQuery({someValue: from}).animate({someValue: parseFloat(to)}, {
		duration: 600,
		easing:'swing',
		context: to,
		step: function() {
			if (parseInt(to)!=parseFloat(to))
			{
				val = ((Math.ceil(this.someValue*100))/100).toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			}
			else
			{
				val = Math.ceil(this.someValue).toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			}
			jQuery(selector).text(val);
		}
	});
	setTimeout(function(){
		jQuery(selector).text(parseFloat(to).toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand));
	}, 650);
}

function getFieldValue(element, type)
{
	if (jQuery(element).length==0){return 0;}
	var elementType = jQuery(element).prop('type');
	elementType = jQuery(element).is('select') ? 'select' : elementType;
	elementType = jQuery(element).hasClass('hasDatepicker') ? 'date' : elementType;
	elementType = jQuery(element).parent().parent().hasClass('files-list') ? 'file' : elementType;
	elementType = jQuery(element).parent().parent().hasClass('slider-cover') ? 'slider' : elementType;
	switch(elementType) {

		case 'text': case 'select': case 'hidden':
		var result = jQuery(element).val();
		break;

		case 'textarea':
		var result = jQuery(element).val();
		break;

		case 'slider':
		var result = jQuery(element).val();
		break;		

		case 'radio': case 'checkbox':
		result = [];
		jQuery('[name="'+jQuery(element).prop('name')+'"]:checked').each(function(){
			result.push(jQuery(this).val());
		});
		break;

		case 'date':
		date = jQuery(element).datepicker('getDate');
		if ( date==null ) { return ''; }
		var now = new Date(); 
		var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
		date = date==null ? today : date;
		var result = parseInt( (date-today) / (60*60*24*1000) );
		break;

		case 'file':
		var name = jQuery(element).attr('name');
		var i = 0;
		jQuery('[name="'+name+'"]').each(function(){
			if (jQuery(this).val()!=''){i++;}
		});
		var result = i;
		break;

		default:
		return 0;
		break;

	}
	if (type=='string')
	{
		if (typeof result=='object')
		{
			return result.join(', ');
		}
		else
		{
			return result;
		}
	}
	else
	{
		if (typeof result=='object')
		{
			var sum = 0;
			for (x in result)
			{
				sum = sum + (isNaN(parseFloat(result[x])) ? 0 : parseFloat(result[x]));
			}
			return sum;
		}
		else if (typeof result=='string' && result.indexOf('-')!=-1)
		{
			temp = result.split('-');
			result = (parseFloat(temp[0].trim()) + parseFloat(temp[1].trim()))/2;
			return isNaN(parseFloat(result)) ? 0 : parseFloat(result);
		}
		else
		{
			return isNaN(parseFloat(result)) ? 0 : parseFloat(result);
		}
	}
}

function checkIfApplyLogic(element)
{
	var parent = jQuery(element).parents('form').parents('.form-live').attr('data-uniq');
	var field_id = jQuery(element).attr('data-field-id');
	var applied = false;
	if (typeof window.FormCraftLogic[parent]!='undefined')
	{
		for (logic in window.FormCraftLogic[parent])
		{
			for (conditions in window.FormCraftLogic[parent][logic][0])
			{
				tempField = window.FormCraftLogic[parent][logic][0][conditions][2];
				if ( typeof tempField != 'undefined' && tempField.slice(0,1) == '[' && tempField.replace('[','').replace(']','')==field_id )
				{
					applyLogic(window.FormCraftLogic[parent][logic], parent);
					applied = true;
				}
				else if (window.FormCraftLogic[parent][logic][0][conditions][0]==field_id)
				{
					applyLogic(window.FormCraftLogic[parent][logic], parent);
					applied = true;
				}
			}
		}
	}
	if ( applied==true )
	{
		var form = jQuery('.uniq-'+parent+' form');
		setFormValues(form, window.set_value);
	}
	for (field in window.final_hide_show_list)
	{
		if(window.final_hide_show_list[field].length==0 || typeof window.final_hide_show_list[field]=='function'){continue;}
		window.final_hide_show_list[field] = window.final_hide_show_list[field].sort();
		var new_state = window.final_hide_show_list[field][window.final_hide_show_list[field].length-1];
		switch(new_state)
		{
			case 'hide':
			if (!jQuery('.uniq-'+parent+' form .form-element-'+field).hasClass('state-hidden'))
			{
				jQuery('.uniq-'+parent+' form .form-element-'+field).removeClass('state-hidden state-shown over-write');
				jQuery('.uniq-'+parent+' form .form-element-'+field).slideUp(300).addClass('state-hidden');				
			}
			break;

			case 'show':
			if (!jQuery('.uniq-'+parent+' form .form-element-'+field).hasClass('state-shown'))
			{
				jQuery('.uniq-'+parent+' form .form-element-'+field).removeClass('state-hidden state-shown over-write');
				jQuery('.uniq-'+parent+' form .form-element-'+field).slideDown(300).addClass('state-shown');
			}
			break;

			case 'default':
			if (jQuery('.uniq-'+parent+' form .form-element-'+field).hasClass('default-false') && jQuery('.uniq-'+parent+' form .form-element-'+field).hasClass('state-hidden'))
			{
				jQuery('.uniq-'+parent+' form .form-element-'+field).slideDown(300).removeClass('state-hidden state-shown').addClass('state-shown');
			}
			if (jQuery('.uniq-'+parent+' form .form-element-'+field).hasClass('default-true') && jQuery('.uniq-'+parent+' form .form-element-'+field).hasClass('state-shown'))
			{
				jQuery('.uniq-'+parent+' form .form-element-'+field).slideUp(300).removeClass('state-hidden state-shown').addClass('state-hidden');
			}
			break;
		}		
	}
	window.final_hide_show_list = [];
}
function applyLogic(logic, parent)
{
	window.final_hide_show_list = window.final_hide_show_list || [];
	window.final_email_to = window.final_email_to || [];
	logic_nos = window.FormCraftLogic[parent].indexOf(logic);

	var email =/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,6})+$/;
	var conditions = logic[0];
	var actions = logic[1];

	var conditions_satisfied = 0;
	var conditions_to_satisfy = logic[2] == 'or' ? 1 : conditions.length;
	for (x in conditions)
	{
		var value = getFieldValue(jQuery('[data-field-id="'+conditions[x][0]+'"]'), 'string');
		conditions[x][2] = conditions[x][2] || '';
		if ( conditions[x][2].slice(0,1) == '[' )
		{
			condition_to_check = conditions[x][2].replace('[','').replace(']','');
			condition_to_check = getFieldValue(jQuery('[data-field-id="'+condition_to_check+'"]'), 'string');
		}
		else
		{
			condition_to_check = conditions[x][2];
		}
		switch(conditions[x][1]) {
			case 'equal_to':
			if ( condition_to_check.toString().indexOf('-')!=-1 && condition_to_check.toString().indexOf('-')!=0 ){
				var temp = condition_to_check.toString().split('-');
				var now = new Date(); 
				var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
				var field_date = new Date(temp[0], parseInt(temp[1])-1, temp[2]);
				var result = parseInt( (field_date-today) / (60*60*24*1000) );
				temp_val = result;
			}
			else
			{
				temp_val = condition_to_check;
			}			
			if (temp_val==value){conditions_satisfied++;}
			break;

			case 'not_equal_to':
			if ( condition_to_check.toString().indexOf('-')!=-1 && condition_to_check.toString().indexOf('-')!=0 ){
				var temp = condition_to_check.toString().split('-');
				var now = new Date(); 
				var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
				var field_date = new Date(temp[0], parseInt(temp[1])-1, temp[2]);
				var result = parseInt( (field_date-today) / (60*60*24*1000) );
				temp_val = result;
			}
			else
			{
				temp_val = condition_to_check;
			}
			if (temp_val!=value){conditions_satisfied++;}
			break;

			case 'contains':
			if ( condition_to_check=='' )
			{
				if ( value!='' ) { conditions_satisfied++; }
				break;
			}
			if (value.toString().indexOf(condition_to_check)!=-1){conditions_satisfied++;}
			break;

			case 'contains_not':
			if (value.toString().indexOf(condition_to_check)==-1){conditions_satisfied++;}
			break;

			case 'greater_than':
			value = parseFloat(value);
			if ( condition_to_check.toString().indexOf('-')!=-1 ){
				var temp = condition_to_check.toString().split('-');
				var now = new Date(); 
				var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
				var field_date = new Date(temp[0], parseInt(temp[1])-1, temp[2]);
				var result = parseInt( (field_date-today) / (60*60*24*1000) );
				temp_val = result;
			}
			else
			{
				temp_val = condition_to_check;
			}
			if ( !isNaN(value) && value > parseFloat(temp_val) ) {conditions_satisfied++;}
			break;

			case 'less_than':
			value = parseFloat(value);
			if ( condition_to_check.toString().indexOf('-')!=-1 ){
				var temp = condition_to_check.toString().split('-');
				var now = new Date(); 
				var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
				var field_date = new Date(temp[0], parseInt(temp[1])-1, temp[2]);
				var result = parseInt( (field_date-today) / (60*60*24*1000) );
				temp_val = result;
			}
			else
			{
				temp_val = condition_to_check;
			}
			if ( !isNaN(value) && value < parseFloat(temp_val) ) {conditions_satisfied++;}
			break;
		}
	}

	for (x in actions)
	{
		switch(actions[x][0]) {
			case 'hide_fields':
			if ( typeof actions[x][1] == 'undefined' ) { continue; }
			var fields_to_hide = actions[x][1].split(',');
			for (y in fields_to_hide)
			{
				if ( typeof fields_to_hide[y] == 'function' ) { continue; }
				window.final_hide_show_list[fields_to_hide[y]] = window.final_hide_show_list[fields_to_hide[y]] || [];
				if (conditions_satisfied>=conditions_to_satisfy)
				{
					window.final_hide_show_list[fields_to_hide[y]].push('hide');
				}
				else
				{
					window.final_hide_show_list[fields_to_hide[y]].push('default');
				}
			}
			break;

			case 'show_fields':
			if ( typeof actions[x][1] == 'undefined' ) { continue; }
			var fields_to_show = actions[x][1].split(',');
			for (y in fields_to_show)
			{
				if ( typeof fields_to_show[y] == 'function' ) { continue; }
				window.final_hide_show_list[fields_to_show[y]] = window.final_hide_show_list[fields_to_show[y]] || [];
				if (conditions_satisfied>=conditions_to_satisfy)
				{
					window.final_hide_show_list[fields_to_show[y]].push('show');
				}
				else
				{
					window.final_hide_show_list[fields_to_show[y]].push('default');
				}
			}
			break;

			case 'email_to':
			if ( typeof actions[x][2] == 'undefined' ) { continue; }
			var emails = actions[x][2];
			if ( conditions_satisfied>=conditions_to_satisfy )
			{
				if ( window.final_email_to.indexOf(logic_nos+':'+emails) == -1 )
				{
					window.final_email_to.push(logic_nos+':'+emails);					
				}
			}
			else if ( window.final_email_to.indexOf(logic_nos+':'+emails) != -1 )
			{
				window.final_email_to.splice(window.final_email_to.indexOf(logic_nos+':'+emails), 1);
			}
			break;

			case 'redirect_to':
			window.final_redirect = window.final_redirect || [];
			if (conditions_satisfied>=conditions_to_satisfy)
			{
				window.final_redirect.push(actions[x][2]);
			}
			else if ( window.final_redirect.indexOf(actions[x][2]) != -1 )
			{
				window.final_redirect.splice(window.final_redirect.indexOf(actions[x][2]),1);
			}
			break;

			case 'trigger_integration':
			if ( typeof actions[x][3] == 'undefined' ) { continue; }
			window.trigger_integration = window.trigger_integration || [];
			if (conditions_satisfied>=conditions_to_satisfy)
			{
				window.trigger_integration.push(actions[x][3]);
			}
			else if ( window.trigger_integration.indexOf(actions[x][3]) != -1 )
			{
				window.trigger_integration.splice(window.trigger_integration.indexOf(actions[x][3]),1);
			}
			break;

			case 'set_value':
			if ( typeof actions[x][2] == 'undefined' ) { continue; }
			window.set_value = window.set_value || [];
			if ( actions[x][2].slice(0,1) == '[' )
			{
				actions_apply = actions[x][2].replace('[','').replace(']','');
				actions_apply = getFieldValue(jQuery('[data-field-id="'+actions_apply+'"]'), 'string');
			}
			else
			{
				actions_apply = actions[x][2];
			}
			if (conditions_satisfied>=conditions_to_satisfy)
			{

				window.set_value[actions[x][4]] = actions_apply;
			}
			else if ( typeof window.set_value[actions[x][4]] != 'undefined' && window.set_value[actions[x][4]] == actions_apply )
			{
				delete window.set_value[actions[x][4]];
			}
			break;
		}
	}
}

function calculateFormula(formula)
{
	var form = jQuery('#bind-math-'+formula.identifier).parents('form');
	var thousand = jQuery('#bind-math-'+formula.identifier).parents('form').attr('data-thousand');
	var decimal = jQuery('#bind-math-'+formula.identifier).parents('form').attr('data-decimal');
	if (formula.variables.length==1 && formula.variables[0]==formula.string)
	{
		var mathResult = getFieldValue(jQuery('[data-field-id="'+formula.variables[0]+'"]'), 'string');
		if (jQuery('#bind-math-'+formula.identifier).prop('type')=='hidden')
		{
			jQuery('#bind-math-'+formula.identifier).val(mathResult).trigger('change');
		}
		else if ( jQuery('.fc-form.spin-true').length && !isNaN(parseFloat(mathResult)) )
		{
			spinTo('#bind-math-'+formula.identifier,mathResult,thousand,decimal);
		}
		else
		{
			mathResult = mathResult.toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			jQuery('#bind-math-'+formula.identifier).text(mathResult);
		}
		jQuery(document).trigger('formcraft_math_change', [form]);
	}
	else
	{
		var string = formula.string;
		for (field in formula.variables)
		{
			if (typeof formula.variables[field]=='function'){continue;}
			var value = getFieldValue(jQuery('[data-field-id="'+formula.variables[field]+'"]'), 'number');
			var reg = new RegExp(formula.variables[field],"g");
			value = value=='' ? 0 : value;
			string = string.replace(reg, value);
		}
		string = string.replace(/--/g,'+');
		var mathResult = eval(string);
		mathResult = Math.floor(mathResult * 100) / 100;
		if (jQuery('#bind-math-'+formula.identifier).prop('type')=='hidden')
		{
			jQuery('#bind-math-'+formula.identifier).val(mathResult).trigger('change');
		}
		else if ( jQuery('.fc-form.spin-true').length )
		{
			spinTo('#bind-math-'+formula.identifier,mathResult,thousand,decimal);
		}
		else
		{
			mathResult = mathResult.toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			jQuery('#bind-math-'+formula.identifier).text(mathResult);
		}
		jQuery(document).trigger('formcraft_math_change', [form]);
	}
}

function checkIfApplyMath(element)
{
	var field_id = jQuery(element).attr('data-field-id');
	for (formula in window.FormCraftMath)
	{
		for (field in window.FormCraftMath[formula].variables)
		{
			if (window.FormCraftMath[formula].variables[field]==field_id)
			{
				calculateFormula(window.FormCraftMath[formula]);
			}
		}
	}
}
function prepareMathFormulas()
{
	window.FormCraftMath = [];
	jQuery('.fc-form .customText-cover > div, .fc-form .stripe-cover div.stripe-amount-show, .fc-form .stripe-cover input.stripe-amount-hidden, .fc-form .customText-cover input[type="hidden"], .fc-form .allow-math').each(function(){
		if (jQuery(this).prop('type')=='hidden')
		{
			var text = jQuery(this).val();
		}
		else
		{
			var text = jQuery(this).text();
			var html = jQuery(this).html();
		}
		var pattern = /\[(.*?)\]/g;
		while ((match = pattern.exec(text)) != null)
		{
			match[0] = jQuery('<div/>').text(match[0]).html();
			var identifier = Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0,8);
			if (jQuery(this).prop('type')=='hidden')
			{
				jQuery(this).attr('id','bind-math-'+identifier).val('');
			}
			else
			{
				var html = html.replace(match[0],'<span id="bind-math-'+identifier+'"></span>');
				jQuery(this).html(html);
			}

			window.FormCraftMath[identifier] = [];
			window.FormCraftMath[identifier].identifier = identifier;
			window.FormCraftMath[identifier].variables = [];
			window.FormCraftMath[identifier].string = match[1].replace(/[^a-zA-Z0-9.*()\-+\/]+/g, '').toLowerCase();
			if (window.FormCraftMath[identifier].string.slice(-1).replace(/[^.*\-+\/]+/g, '')!='')
			{
				window.FormCraftMath[identifier].string = window.FormCraftMath[identifier].string.slice(0,window.FormCraftMath[identifier].string.length-1);
			}
			if (window.FormCraftMath[identifier].string.replace(/[^.*()\-+\/]+/g, '')=='')
			{
				window.FormCraftMath[identifier].resultType = 'string';	
			}
			else
			{
				window.FormCraftMath[identifier].resultType = 'math';	
			}
			var fields = window.FormCraftMath[identifier].string.split(/[*()\-+\/]/);
			for (field in fields)
			{
				if (fields[field]==''){continue;}
				if (typeof fields[field]=='function'){continue;}
				if (parseFloat(fields[field])==fields[field]){continue;}
				window.FormCraftMath[identifier].variables.push(fields[field]);
			}
			window.FormCraftMath[identifier].variables = window.FormCraftMath[identifier].variables.sort(function(a, b){
				return parseInt(b.replace('field',''))-parseInt(a.replace('field',''));
			});
		}
	});
}


function fc_init(){
	window.FormCraftLogic = [];
	jQuery('.form-logic').each(function(){
		var obj = jQuery.parseJSON( jQuery(this).text() );
		window.FormCraftLogic[jQuery(this).parents('.form-live').attr('data-uniq')] = obj;
		jQuery(this).remove();
	});
	jQuery('.formcraft-css [data-toggle="tooltip"]').tooltip({
		container: '.fc-form'
	});
	jQuery('.star-cover label').removeClass('fake-click fake-hover active');
	jQuery('.fc-form-modal').on('shown.bs.fc_modal', function () {
		var form = jQuery(this).find('.fc-form').attr('data-id');
		jQuery.get(FC.ajaxurl+'?action=formcraft3_trigger_view&id='+form);
		if (jQuery(this).find('.textarea-cover').length!=0)
		{
			var ta = document.querySelector('.textarea-cover textarea');
			var evt = document.createEvent('Event');
			evt.initEvent('autosize.update', true, false);
			ta.dispatchEvent(evt);
		}
	});
	setTimeout(function(){
		autosize(jQuery('.textarea-cover textarea'));
	}, 100);

	if ( jQuery('.fileupload-cover').length )
	{
		jQuery('.fileupload-cover .button-file input').fileupload({
			dataType: 'json',
			add: function(e, data){
				if (jQuery(this).attr('data-allow-extensions') !='' && jQuery(this).attr('data-allow-extensions').indexOf(','))
				{
					var extensions = jQuery(this).attr('data-allow-extensions').replace(/ /g,'').split(',');
					for (file in data.files)
					{
						var file_parts = data.files[file].name.split('.');
						var file_extension = file_parts[file_parts.length-1];
						if (extensions.indexOf(file_extension)==-1){
							return false;
						}
					}
				}
				if (jQuery(this).attr('data-max-files')!='')
				{
					if ( jQuery(this).parent().parent().find('.files-list li').length >= parseInt(jQuery(this).attr('data-max-files')) )
					{
						return false;
					}
				}				
				var id = jQuery(this).parents('.fc-form').attr('data-id');
				data.url = FC_f.ajaxurl+'?action=formcraft3_file_upload&id='+id;
				var parent = jQuery(this).parent().parent();
				if(parent.find('.files-list').length==0)
				{
					parent.append('<ul class="files-list"></ul>');
				}
				parent.find('.files-list').append('<li><div></div></li>');
				data.list_position = parent.find('li').length-1;
				parent.find('.files-list li').eq(data.list_position).slideDown(100);
				window.jqXHR = data.submit();
			},
			progressall: function(e, data){
			},
			progress: function (e, data) {
				var parent = jQuery(this).parent().parent();
				var progress = parseInt(data.loaded / data.total * 100, 10);
				parent.find('.files-list li').eq(data.list_position).find('div').css('width',progress+'%');
			},
			done: function(e, data){
				var parent = jQuery(this).parent().parent();
				if (data.result.success)
				{
					var name = jQuery(this).attr('data-name-list');
					parent.find('.files-list li').eq(data.list_position).find('div').text(data.result.file_name);
					parent.find('.files-list li').eq(data.list_position).append('<span class="delete-file" title="Delete File">&times;</span><input type="hidden" data-field-id="'+name+'" name="'+name+'[]" value="'+data.result.success+'"/>');
					parent.find('.files-list li').eq(data.list_position).find('input').trigger('change');
				}
				else if (data.result.failed)
				{
					parent.find('.files-list li').eq(data.list_position).remove();
					if (typeof toastr!='undefined') { toastr["error"]("Error: "+data.result.debug); }
				}
			}
		});		
}
jQuery('.slider-cover .ui-slider-cover').each(function(){
	options = {};
	options.min = defaultValue = parseFloat(jQuery(this).find('> span').attr('range-min'));
	options.max = parseFloat(jQuery(this).find('> span').attr('range-max'));
	options.step = parseFloat(jQuery(this).find('> span').attr('range-step'));
	var range = jQuery(this).find('> span').attr('range-true')=='true' ? true : 'min';
	var prefix = jQuery(this).find('> span').attr('data-prefix');
	var suffix = jQuery(this).find('> span').attr('data-suffix');
	options.range = range;
	options.create = function( event, ui ) {
		if (options.range==true)
		{
			jQuery(this).find('.ui-slider-range').eq(0).append('<span class="ui-slider-handle-nos">0</span>');
		}
		else
		{
			jQuery(this).find('span.ui-slider-handle').eq(0).append('<span class="ui-slider-handle-nos">0</span>');
		}
		jQuery(this).parents('.slider-cover').find('input[type="hidden"]').val('').trigger('change').attr('data-prefix',prefix).attr('data-suffix',suffix);
	}
	options.change = function( event, ui ) {
		var thousand = jQuery(this).parents('.fc-form').attr('data-thousand');
		var decimal = jQuery(this).parents('.fc-form').attr('data-decimal');
		jQuery(this).parents('.slider-cover').find('.ui-slider-handle-nos').css('margin-left','-'+(jQuery(this).parents('.slider-cover').find('.ui-slider-handle-nos').outerWidth()/2-9)+'px');
		if(ui.values)
		{
			value_0 = ui.values[0].toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			value_1 = ui.values[1].toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);

			value_0_from = ui.values[0];
			value_1_from = ui.values[1];

			ui.values[0] = typeof prefix!='undefined' ? prefix+ui.values[0] : ui.values[0];
			ui.values[0] = typeof suffix!='undefined' ? ui.values[0]+suffix : ui.values[0];

			ui.values[1] = typeof prefix!='undefined' ? prefix+ui.values[1] : ui.values[1];
			ui.values[1] = typeof suffix!='undefined' ? ui.values[1]+suffix : ui.values[1];

			var value = ui.values[0]+' - '+ui.values[1];
		}
		else
		{
			var value = ui.value;
			value_0 = value.toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			value_0_from = value;
			value_1 = '';
			value_1_from = '';
			value = typeof prefix!='undefined' ? prefix+value : value;
			value = typeof suffix!='undefined' ? value+suffix : value;
		}
		valueAmount = value.replace(prefix,'').replace(suffix,'');
		jQuery(this).parents('.slider-cover').find('input').val(valueAmount).trigger('change');
		value = value.replace(value_0_from,value_0).replace(value_1_from,value_1);
		jQuery(this).parents('.slider-cover').find('.ui-slider-handle-nos').text(value);
	}
	options.slide = function( event, ui ) {
		jQuery(this).find('.ui-slider-handle-nos').show();
		var thousand = jQuery(this).parents('.fc-form').attr('data-thousand');
		var decimal = jQuery(this).parents('.fc-form').attr('data-decimal');
		jQuery(this).parents('.slider-cover').find('.ui-slider-handle-nos').css('margin-left','-'+(jQuery(this).parents('.slider-cover').find('.ui-slider-handle-nos').outerWidth()/2-9)+'px');
		if(ui.values)
		{
			value_0 = ui.values[0].toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			value_1 = ui.values[1].toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);

			value_0_from = ui.values[0];
			value_1_from = ui.values[1];

			ui.values[0] = typeof prefix!='undefined' ? prefix+ui.values[0] : ui.values[0];
			ui.values[0] = typeof suffix!='undefined' ? ui.values[0]+suffix : ui.values[0];

			ui.values[1] = typeof prefix!='undefined' ? prefix+ui.values[1] : ui.values[1];
			ui.values[1] = typeof suffix!='undefined' ? ui.values[1]+suffix : ui.values[1];

			var value = ui.values[0]+' - '+ui.values[1];
		}
		else
		{
			var value = ui.value;
			value_0 = value.toString().replace(/[.]/g,decimal).replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
			value_0_from = value;
			value_1 = '';
			value_1_from = '';
			value = typeof prefix!='undefined' ? prefix+value : value;
			value = typeof suffix!='undefined' ? value+suffix : value;
		}
		valueAmount = value.replace(prefix,'').replace(suffix,'');
		jQuery(this).parents('.slider-cover').find('input').val(valueAmount).trigger('change');
		value = value.replace(value_0_from,value_0).replace(value_1_from,value_1);
		jQuery(this).parents('.slider-cover').find('.ui-slider-handle-nos').text(value);
	}
	jQuery(this).html('<span></span>');	
	jQuery(this).find('span').slider(options);
});
jQuery('.slider-cover .ui-slider-cover').each(function(){
	var sliderElement = jQuery(this).find('.ui-slider');
	if ( sliderElement.slider( "option", "range" ) == true )
	{
		values = [];
		values[0] = sliderElement.slider( "option", "min" );
		values[1] = sliderElement.slider( "option", "min" ) + (Math.round(((sliderElement.slider( "option", "max" ) - sliderElement.slider( "option", "min" )) / sliderElement.slider( "option", "step" )) * .2))*sliderElement.slider( "option", "step" );
		sliderElement.slider('values', values );
	}
	else
	{
		sliderElement.slider('value', sliderElement.slider( "option", "min" ) );
	}
	sliderElement.parents('.slider-cover').find('.ui-slider-handle-nos').show();
	var elementTemp = sliderElement;
	setTimeout(function(){
		elementTemp.parents('.slider-cover').find('.ui-slider-handle-nos').css('margin-left','-'+(elementTemp.parents('.slider-cover').find('.ui-slider-handle-nos').outerWidth()/2-9)+'px');
	}, 10);
});

jQuery('.datepicker-cover input[type="text"]').each(function(){
	jQuery(this).removeClass('hasDatepicker');
	options = {};
	options.beforeShow = function(input, inst) {
		jQuery('#ui-datepicker-div').removeClass('ui-datepicker').addClass('fc-datepicker');
	}
	options.onClose = function (input, inst) {
		jQuery(this).trigger('blur');
	}
	options.onSelect = function(input, inst) {
		jQuery(this).trigger('change').trigger('input');
	}
	if ( jQuery(this).attr('data-date-lang') && jQuery(this).attr('data-date-lang')!='en' && window.datepicker_load == false )
	{
		jQuery.getScript(FC.datepickerLang+'datepicker-'+jQuery(this).attr('data-date-lang')+'.js');
		window.datepicker_load = true;
	}
	if ( jQuery(this).attr('data-date-format') )
	{
		options.dateFormat = jQuery(this).attr('data-date-format');
	}
	if ( jQuery(this).attr('data-date-max') )
	{
		if (jQuery(this).attr('data-date-max')!='' && parseInt(jQuery(this).attr('data-date-max'))==jQuery(this).attr('data-date-max'))
		{
			var maxDate = new Date();
			maxDate.setDate(maxDate.getDate() + parseInt(jQuery(this).attr('data-date-max')));
		}
		else
		{
			var maxDate = new Date(jQuery(this).attr('data-date-max-alt'));
		}
		options.maxDate = maxDate;
	}
	if ( jQuery(this).attr('data-date-min') )
	{
		if (jQuery(this).attr('data-date-min')!='' && parseInt(jQuery(this).attr('data-date-min'))==jQuery(this).attr('data-date-min'))
		{
			var minDate = new Date();
			minDate.setDate(minDate.getDate() + parseInt(jQuery(this).attr('data-date-min')));
		}
		else
		{
			var minDate = new Date(jQuery(this).attr('data-date-min-alt'));
		}
		options.minDate = minDate;
	}
	if ( jQuery(this).attr('data-date-days') )
	{
		var temp = jQuery.parseJSON(jQuery(this).attr('data-date-days'));
		var tempNew = [];
		for ( x in temp )
		{
			if ( temp[x] == true )
			{
				tempNew.push(x);
			}
		}
		options.beforeShowDay = function(date){
			if ( tempNew.indexOf(date.getDay().toString())!=-1 )
			{
				return [true, ''];
			}
			else
			{
				return [false, ''];
			}
		}
	}	
	options.nextText = ' ';
	options.prevText = ' ';
	options.hideIfNoPrevNext = true;
	options.changeYear = true;
	options.changeMonth = true;
	options.showAnim = false;
	options.yearRange = "c-20:c+20";
	options.shortYearCutoff = 50;
	options.showOtherMonths = true
	jQuery(this).datepicker(options);
});
}

window.lastSaveProgress = [];
function saveProgress()
{
	jQuery('.fc-form').each(function(){
		form = jQuery(this);
		if (form.hasClass('save-form-true')){
			id = form.attr('data-id');
			var data = form.find('input, textarea, select').not('.no-save').not('[type="password"]').not('.stripe-amount-hidden').serialize()+'&id='+form.attr('data-id');
			if ( typeof window.lastSaveProgress[id]=='undefined' || window.lastSaveProgress[id]!=data )
			{
				window.lastSaveProgress[id] = data;
			}
			else
			{
				return false;
			}
			jQuery.ajax( {
				url: FC.ajaxurl,
				type: "POST",
				context: form,
				data: 'action=formcraft3_form_save_progress&'+data,
				dataType: "json"
			} )
			.done(function(response) {
			});	
		}
	});
}

jQuery(document).ready(function(){

	if ( isMobile==true) {
		jQuery('.email-cover input[type="text"]').attr('type','email');
	} 

	if ( jQuery('#fc-form-preview').length==1 )
	{
		jQuery('body').addClass('formcraft-css');
	}

	jQuery('.form-element.default-true').hide();
	jQuery('.fc-form').removeClass('fc-temp-class');	
	jQuery('.fc-form .form-element.default-true').addClass('state-hidden');	
	jQuery('[data-input-mask]').each(function(){
		var options =  { 
			onComplete: function(cep, event) {
				jQuery(event.srcElement).removeClass('mask-invalid');
			},
			onChange: function(cep, event){
				jQuery(event.srcElement).addClass('mask-invalid');
			}
		};
		if ( jQuery(this).attr('data-input-mask').replace(/[^a-zA-Z0-9 ():\-\/]+/g, '').trim() != '' )
		{
			jQuery(this).mask(jQuery(this).attr('data-input-mask').replace(/[^a-zA-Z0-9 ():\-\/]+/g, ''), options);
		}
	});
	jQuery('body').on('click','.field-cover div [class^="icon-"]', function(){
		jQuery(this).parent().find('input').focus();
	});
	jQuery('[href]').each(function(){
		var href = jQuery(this).attr('href');
		if (href.indexOf('form-view/')!=-1)
		{
			var sub = href.split('form-view/');
			if (jQuery('.fc-form-modal .fc-form[data-id="'+sub[sub.length-1]+'"]').length)
			{
				var form = jQuery('.fc-form-modal .fc-form[data-id="'+sub[sub.length-1]+'"]').first();
				var uniq = form.parents('.fc-form-modal').attr('id');
				jQuery(this).removeAttr('href');
				jQuery(this).attr('data-toggle','fc_modal');
				jQuery(this).attr('data-target','#'+uniq);
			}
		}
	});
	jQuery('.fc-form-modal .form-live').each(function(){
		if (jQuery(this).attr('data-bind')!='')
		{
			var uniq = jQuery(this).attr('data-uniq');
			jQuery(jQuery(this).attr('data-bind')).each(function(){
				jQuery(this).attr('data-toggle','fc_modal');
				jQuery(this).attr('data-target','#modal-'+uniq);
			});
		}
	});
	fc_init();
	setInterval(function(){
		saveProgress();
	}, 3000);
	jQuery('.fc-form').each(function(){
		var form = jQuery(this);
		var data = form.parents('.form-live').find('.pre-populate-data').text();
		form.parent().find('.pre-populate-data').remove();
		data = jQuery.parseJSON(data);
		setFormValues(form, data);
	});
	prepareMathFormulas();
	jQuery('.fc-form').each(function(){
		form = jQuery(this);
		jQuery(document).trigger('formcraft_math_change', [form]);
	});

	jQuery('body').on('input','.textarea-cover textarea',function(){
		var len = jQuery(this).val().length;
		var max = parseInt(jQuery(this).parents('.textarea-cover').find('.count-true > span.max-count').text());
		if (len>max)
		{
			jQuery(this).parents('.textarea-cover').find('.count-true').css('color','red');
		}
		else
		{
			jQuery(this).parents('.textarea-cover').find('.count-true').css('color','inherit');
		}
		jQuery(this).parents('.textarea-cover').find('.count-true > span.current-count').text(len);
	});

	jQuery('body').on('focus','.password-cover input[type="password"],.oneLineText-cover input[type="text"],.datepicker-cover input[type="text"],.email-cover input[type="text"],.textarea-cover textarea,.dropdown-cover select,.matrix-cover input,.star-cover input,.thumb-cover input',function(){
		jQuery(this).parents('.field-cover').addClass('has-focus');
	});
	jQuery('body').on('blur','.password-cover input[type="password"],.oneLineText-cover input[type="text"],.datepicker-cover input[type="text"],.email-cover input[type="text"],.textarea-cover textarea,.dropdown-cover select,.matrix-cover input,.star-cover input,.thumb-cover input',function(){
		jQuery(this).parents('.field-cover').removeClass('has-focus');
	});

	jQuery('body').on('change','.dropdown-cover select',function(){
		if (jQuery(this).find('option:checked').length>0 && jQuery(this).find('option:checked').text()!='')
		{
			jQuery(this).parents('.field-cover').addClass('has-input');
		}
		else
		{
			jQuery(this).parents('.field-cover').removeClass('has-input');
		}
	});
	jQuery('body').on('input','.oneLineText-cover input[type="text"],.password-cover input[type="password"],.datepicker-cover input[type="text"],.email-cover input[type="text"],.textarea-cover textarea',function(){
		if ( jQuery(this).val().length>0 || ( jQuery(this).attr('placeholder').length>0 ) )
		{
			jQuery(this).parents('.field-cover').addClass('has-input');
		}
		else
		{
			jQuery(this).parents('.field-cover').removeClass('has-input');
		}
	});

	jQuery('body').on('input','.oneLineText-cover input[type="text"],.datepicker-cover input[type="text"],.email-cover input[type="text"],.textarea-cover textarea',function(){
		checkIfApplyMath(jQuery(this));
		checkIfApplyLogic(jQuery(this));
	});
	jQuery('body').on('change','.customText-cover input[type="hidden"],.timepicker-cover input[type="hidden"],.slider-cover input[type="hidden"],.fileupload-cover input[type="hidden"],.checkbox-cover input[type="radio"],.star-cover input[type="radio"],.thumb-cover input[type="radio"],.checkbox-cover input[type="checkbox"],.dropdown-cover select',function(){
		checkIfApplyMath(jQuery(this));
		checkIfApplyLogic(jQuery(this));
	});

	jQuery('.oneLineText-cover input[type="text"],.datepicker-cover input[type="text"], .email-cover input[type="text"], .textarea-cover textarea').trigger('input');
	jQuery('.customText-cover input[type="hidden"],.timepicker-cover input[type="hidden"],.slider-cover input[type="hidden"],.fileupload-cover input[type="hidden"],.checkbox-cover input[type="radio"],.star-cover input[type="radio"],.thumb-cover input[type="radio"],.checkbox-cover input[type="checkbox"],.dropdown-cover select').trigger('change');

	jQuery('body').on('input','.time-fields-cover > select,.time-fields-cover > input', function(){
		var parent = jQuery(this).parent();
		var hrs = parent.find('select').eq(0).val();
		var minute = parent.find('select').eq(1).val();
		var meridian = parent.find('input').val();
		if (jQuery(this).parent().hasClass('hide-meridian-true'))
		{
			parent.parent().find('input[type="hidden"]').val(hrs+':'+minute).trigger('change');
		}
		else
		{
			parent.parent().find('input[type="hidden"]').val(hrs+':'+minute+' '+meridian).trigger('change');			
		}
	});
	jQuery('body').on('focus','.meridian-picker',function(){
		if (jQuery(this).val()=='am')
		{
			jQuery(this).val('pm');
		}
		else if (jQuery(this).val()=='pm')
		{
			jQuery(this).val('am');
		}
		else
		{
			jQuery(this).val('am');
		}
		jQuery(this).blur();
		jQuery(this).trigger('input');
	});	
	jQuery('.fc-pagination > div').eq(0).addClass('active');
	jQuery('.fc-form .form-page-0').addClass('active');	
	jQuery('body').on('change','.checkbox-cover label input,.update-label label input', function(){
		if (jQuery(this).is(':checked'))
		{
			var name = jQuery(this).attr('name');
			jQuery('[name="'+name+'"]').parent().removeClass('active');
			jQuery(this).parent().addClass('active');
		}
	});
	var iOS = ( navigator.userAgent.match(/iPad|iPhone|iPod/g) ? true : false );
	if (iOS)
	{
		jQuery('body').on('touchstart','.star-cover label, .thumb-cover label', function(){
			jQuery(this).trigger('click');
		});
		jQuery('body').on('touchstart','[data-toggle="fc_modal"]',function(){
			jQuery(this).trigger('click');
		});
		jQuery('body').on('touchstart','[data-toggle="fc-sticky"]',function(){
			jQuery(this).trigger('click');
		});
	}
	jQuery('body').on('change','.star-cover label input', function(){
		if (jQuery(this).is(':checked'))
		{
			var name = jQuery(this).attr('name');
			jQuery('[name="'+name+'"]').parent().removeClass('active');
			jQuery(this).parent().addClass('active');
			var index = jQuery(this).parent().index();
			jQuery(this).parent().parent().find('label').removeClass('fake-click');
			jQuery(this).parent().parent().find('label').slice(0,index+1).addClass('fake-click');
		}
	});
	jQuery('.update-label label.active').removeClass('active');
	jQuery('.powered-by').each(function(){
		var width = jQuery(this).parent().find('.fc-form').outerWidth();
		jQuery(this).css('width',width+'px');
	});
	jQuery('.fc-form-modal').appendTo('body');
	jQuery('.formcraft-css.placement-right').appendTo('body');
	jQuery('.formcraft-css.placement-left').appendTo('body');
	jQuery('.body-append').appendTo('body');
	setTimeout(function(){
		jQuery('.image_button_cover a').each(function(){
			var height = (parseInt(jQuery(this).outerWidth())/2)+jQuery(this).outerHeight();
			jQuery(this).css('top',"-"+height+"px");
		});	
	}, 100);
	setTimeout(function(){
		jQuery('.image_button_cover a').each(function(){
			var height = (parseInt(jQuery(this).outerWidth())/2)+jQuery(this).outerHeight();
			jQuery(this).parents('.image_button_cover').addClass('now-show');
		});	
	}, 400);
	jQuery('body').on('click','[data-toggle="fc-sticky"]',function(){
		var element = jQuery(jQuery(this).attr('data-target'));
		var elementButton = jQuery(jQuery(this).attr('data-target')).parent().find('.fc-sticky-button');
		if ( element.hasClass('show') )
		{
			element.addClass('hiding');
			elementButton.addClass('showing');
			setTimeout(function(){
				element.removeClass('show hiding');
				elementButton.removeClass('hide showing');
			}, 400);
		}
		else
		{
			var form = element.find('.fc-form').attr('data-id');
			jQuery.get(FC.ajaxurl+'?action=formcraft3_trigger_view&id='+form);		
			element.addClass('show');
			elementButton.addClass('hide');
		}
	});
	jQuery(document).keyup(function(e) {
			jQuery('.fc-sticky').each(function(){
			if ( jQuery(this).hasClass('show') && e.which==27 )
			{
				jQuery(this).parent().find('[data-toggle="fc-sticky"]').trigger('click');
			}
		});
	});
	var body_height = parseInt(jQuery(window).height())*.8;
	jQuery('.fc-sticky').css('max-height', body_height+'px');
	jQuery(document).mouseup(function (e){
		var container1 = jQuery('.fc-sticky');
		var container2 = jQuery('.fc-datepicker');
		if (
			!container1.is(e.target)
			&& container1.has(e.target).length === 0
			&& !container2.is(e.target)
			&& container2.has(e.target).length === 0
			)
		{
			jQuery('.fc-sticky').each(function(){
				if ( jQuery(this).hasClass('show') )
				{
				jQuery(this).parent().find('[data-toggle="fc-sticky"]').trigger('click');//
			}
		});
		}
	});
	setTimeout(function(){
		jQuery('.fc-sticky').each(function(){
			if ( jQuery(this).hasClass('fc-sticky-right') || jQuery(this).hasClass('fc-sticky-left') )
			{
				var height = jQuery(this).find('.fc-form').height();
				var height = Math.min(body_height, height);
				jQuery(this).css('margin-top', '-'+(height/2)+'px' );
				jQuery(this).find('.fc-form').addClass('calculated');
			}
		});
	}, 500);

	jQuery('.fc-form-modal').each(function(){
		if ( jQuery(this).attr('data-auto') && !isNaN(parseFloat(jQuery(this).attr('data-auto'))) )
		{
			var modal = jQuery(this);
			setTimeout(function(){
				modal.fc_modal('show');
			},parseFloat(jQuery(this).attr('data-auto'))*1000);
		}
		if (jQuery(this).find('.pagination-trigger').length>1)
		{
			jQuery(this).find('.fc_close').css('margin-top','100px');
		}
	});
	jQuery('.star-cover label').hover(
		function () {
			var index = jQuery(this).index();
			jQuery(this).parent().find('label').slice(0,index+1-jQuery(this).prevAll('div').length).addClass('fake-hover');
			jQuery(this).parent().find('label').slice(index+1-jQuery(this).prevAll('div').length,jQuery(this).parent().find('label').length).addClass('fake-empty');
		}, 
		function () {
			jQuery(this).parent().find('label').removeClass('fake-hover fake-empty');
		}
		);
	jQuery('body').on('click','.files-list .delete-file', function(){
		var key = jQuery(this).parent().find('input').val();
		jQuery(this).addClass('icon-spin5 animate-spin').html('');
		jQuery.ajax( {
			url: FC.ajaxurl,
			type: "POST",
			context: jQuery(this),
			data: 'action=formcraft3_file_delete&id='+key,
			dataType: "json"
		} )
		.done(function(response) {
			if (response.success)
			{
				jQuery(this).parent().slideUp(200,function(){
					jQuery(this).find('input').val('').trigger('change');
					jQuery(this).remove();
				});
			}
			else
			{
				jQuery(this).removeClass('icon-spin5 animate-spin').html('×');
			}
		})
		.always(function(response) {
			jQuery(this).removeClass('icon-spin5 animate-spin').html('×');
		})
		;
	});
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
		jQuery('.datepicker-cover input[type="text"]').attr('readonly','readonly');
	}
	jQuery('body').on('blur change','.fc-form .validation-lenient',function(){
		if (jQuery(this).fc_validate()==false)
		{
			jQuery(this).addClass('validation-strict').removeClass('validation-lenient');
		}
	});
	jQuery('body').on('keyup change input','.fc-form .validation-strict',function(){
		if (jQuery(this).fc_validate()==false)
		{
			//jQuery(this).addClass('validation-strict').removeClass('validation-lenient');
		}
		else
		{
			//jQuery(this).addClass('validation-lenient').removeClass('validation-strict');
		}
	});
	jQuery('.required_field').hide();
	if (typeof toastr!='undefined')
	{
		toastr.options = {
			"closeButton": false,
			"debug": false,
			"newestOnTop": true,
			"progressBar": false,
			"positionClass": "toast-top-right",
			"preventDuplicates": false,
			"onclick": null,
			"showDuration": "1000",
			"hideDuration": "1000",
			"timeOut": "3000",
			"extendedTimeOut": "1000",
			"showEasing": "linear",
			"hideEasing": "linear",
			"showMethod": "slideDown",
			"hideMethod": "slideUp"
		}
	}
	jQuery('body').on('submit','.fc-form',function(event){
		event.preventDefault();	
		FormCraftSubmitForm(jQuery(this), 'all');
	});
	jQuery('.form-element-html').removeAttr('ondragstart').removeAttr('dnd-draggable').removeAttr('ondrag').removeAttr('draggable');
	jQuery('.fc-form').removeAttr('ondrop').removeAttr('ondragover');
});


!function(e,t){"use strict";"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?module.exports=t():e.autosize=t()}(this,function(){function e(e){function t(){var t=window.getComputedStyle(e,null);"vertical"===t.resize?e.style.resize="none":"both"===t.resize&&(e.style.resize="horizontal"),e.style.wordWrap="break-word";var i=e.style.width;e.style.width="0px",e.offsetWidth,e.style.width=i,n="none"!==t.maxHeight?parseFloat(t.maxHeight):!1,r="content-box"===t.boxSizing?-(parseFloat(t.paddingTop)+parseFloat(t.paddingBottom)):parseFloat(t.borderTopWidth)+parseFloat(t.borderBottomWidth),o()}function o(){var t=e.style.height,o=document.documentElement.scrollTop,i=document.body.scrollTop;e.style.height="auto";var s=e.scrollHeight+r;if(n!==!1&&s>n?(s=n,"scroll"!==e.style.overflowY&&(e.style.overflowY="scroll")):"hidden"!==e.style.overflowY&&(e.style.overflowY="hidden"),e.style.height=s+"px",document.documentElement.scrollTop=o,document.body.scrollTop=i,t!==e.style.height){var d=document.createEvent("Event");d.initEvent("autosize.resized",!0,!1),e.dispatchEvent(d)}}if(e&&e.nodeName&&"TEXTAREA"===e.nodeName&&!e.hasAttribute("data-autosize-on")){var n,r;"onpropertychange"in e&&"oninput"in e&&e.addEventListener("keyup",o),window.addEventListener("resize",o),e.addEventListener("input",o),e.addEventListener("autosize.update",o),e.addEventListener("autosize.destroy",function(t){window.removeEventListener("resize",o),e.removeEventListener("input",o),e.removeEventListener("keyup",o),e.removeEventListener("autosize.destroy"),Object.keys(t).forEach(function(o){e.style[o]=t[o]}),e.removeAttribute("data-autosize-on")}.bind(e,{height:e.style.height,overflow:e.style.overflow,overflowY:e.style.overflowY,wordWrap:e.style.wordWrap,resize:e.style.resize})),e.setAttribute("data-autosize-on",!0),e.style.overflow="hidden",e.style.overflowY="hidden",t()}}return"function"!=typeof window.getComputedStyle?function(e){return e}:function(t){return t&&t.length?Array.prototype.forEach.call(t,e):t&&t.nodeName&&e(t),t}});

/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
 !function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);


// jQuery Mask Plugin v1.11.4
// github.com/igorescobar/jQuery-Mask-Plugin
(function(b){"function"===typeof define&&define.amd?define(["jquery"],b):"object"===typeof exports?module.exports=b(require("jquery")):b(jQuery||Zepto)})(function(b){var y=function(a,d,e){a=b(a);var g=this,k=a.val(),l;d="function"===typeof d?d(a.val(),void 0,a,e):d;var c={invalid:[],getCaret:function(){try{var q,v=0,b=a.get(0),f=document.selection,c=b.selectionStart;if(f&&-1===navigator.appVersion.indexOf("MSIE 10"))q=f.createRange(),q.moveStart("character",a.is("input")?-a.val().length:-a.text().length),
	v=q.text.length;else if(c||"0"===c)v=c;return v}catch(d){}},setCaret:function(q){try{if(a.is(":focus")){var b,c=a.get(0);c.setSelectionRange?c.setSelectionRange(q,q):c.createTextRange&&(b=c.createTextRange(),b.collapse(!0),b.moveEnd("character",q),b.moveStart("character",q),b.select())}}catch(f){}},events:function(){a.on("keyup.mask",c.behaviour).on("paste.mask drop.mask",function(){setTimeout(function(){a.keydown().keyup()},100)}).on("change.mask",function(){a.data("changed",!0)}).on("blur.mask",
		function(){k===a.val()||a.data("changed")||a.triggerHandler("change");a.data("changed",!1)}).on("keydown.mask, blur.mask",function(){k=a.val()}).on("focus.mask",function(a){!0===e.selectOnFocus&&b(a.target).select()}).on("focusout.mask",function(){e.clearIfNotMatch&&!l.test(c.val())&&c.val("")})},getRegexMask:function(){for(var a=[],b,c,f,e,h=0;h<d.length;h++)(b=g.translation[d.charAt(h)])?(c=b.pattern.toString().replace(/.{1}$|^.{1}/g,""),f=b.optional,(b=b.recursive)?(a.push(d.charAt(h)),e={digit:d.charAt(h),
			pattern:c}):a.push(f||b?c+"?":c)):a.push(d.charAt(h).replace(/[-\/\\^$*+?.()|[\]{}]/g,"\\$&"));a=a.join("");e&&(a=a.replace(RegExp("("+e.digit+"(.*"+e.digit+")?)"),"($1)?").replace(RegExp(e.digit,"g"),e.pattern));return RegExp(a)},destroyEvents:function(){a.off("keydown keyup paste drop blur focusout ".split(" ").join(".mask "))},val:function(b){var c=a.is("input")?"val":"text";if(0<arguments.length){if(a[c]()!==b)a[c](b);c=a}else c=a[c]();return c},getMCharsBeforeCount:function(a,b){for(var c=0,
			f=0,e=d.length;f<e&&f<a;f++)g.translation[d.charAt(f)]||(a=b?a+1:a,c++);return c},caretPos:function(a,b,e,f){return g.translation[d.charAt(Math.min(a-1,d.length-1))]?Math.min(a+e-b-f,e):c.caretPos(a+1,b,e,f)},behaviour:function(a){a=a||window.event;c.invalid=[];var e=a.keyCode||a.which;if(-1===b.inArray(e,g.byPassKeys)){var d=c.getCaret(),f=c.val().length,n=d<f,h=c.getMasked(),k=h.length,m=c.getMCharsBeforeCount(k-1)-c.getMCharsBeforeCount(f-1);c.val(h);!n||65===e&&a.ctrlKey||(8!==e&&46!==e&&(d=c.caretPos(d,
				f,k,m)),c.setCaret(d));return c.callbacks(a)}},getMasked:function(a){var b=[],k=c.val(),f=0,n=d.length,h=0,l=k.length,m=1,p="push",t=-1,s,w;e.reverse?(p="unshift",m=-1,s=0,f=n-1,h=l-1,w=function(){return-1<f&&-1<h}):(s=n-1,w=function(){return f<n&&h<l});for(;w();){var x=d.charAt(f),u=k.charAt(h),r=g.translation[x];if(r)u.match(r.pattern)?(b[p](u),r.recursive&&(-1===t?t=f:f===s&&(f=t-m),s===t&&(f-=m)),f+=m):r.optional?(f+=m,h-=m):r.fallback?(b[p](r.fallback),f+=m,h-=m):c.invalid.push({p:h,v:u,e:r.pattern}),
			h+=m;else{if(!a)b[p](x);u===x&&(h+=m);f+=m}}a=d.charAt(s);n!==l+1||g.translation[a]||b.push(a);return b.join("")},callbacks:function(b){var g=c.val(),l=g!==k,f=[g,b,a,e],n=function(a,b,c){"function"===typeof e[a]&&b&&e[a].apply(this,c)};n("onChange",!0===l,f);n("onKeyPress",!0===l,f);n("onComplete",g.length===d.length,f);n("onInvalid",0<c.invalid.length,[g,b,a,c.invalid,e])}};g.mask=d;g.options=e;g.remove=function(){var b=c.getCaret();c.destroyEvents();c.val(g.getCleanVal());c.setCaret(b-c.getMCharsBeforeCount(b));
				return a};g.getCleanVal=function(){return c.getMasked(!0)};g.init=function(d){d=d||!1;e=e||{};g.byPassKeys=b.jMaskGlobals.byPassKeys;g.translation=b.jMaskGlobals.translation;g.translation=b.extend({},g.translation,e.translation);g=b.extend(!0,{},g,e);l=c.getRegexMask();!1===d?(e.placeholder&&a.attr("placeholder",e.placeholder),a.attr("autocomplete","off"),c.destroyEvents(),c.events(),d=c.getCaret(),c.val(c.getMasked()),c.setCaret(d+c.getMCharsBeforeCount(d,!0))):(c.events(),c.val(c.getMasked()))};
				g.init(!a.is("input"))};b.maskWatchers={};var A=function(){var a=b(this),d={},e=a.attr("data-mask");a.attr("data-mask-reverse")&&(d.reverse=!0);a.attr("data-mask-clearifnotmatch")&&(d.clearIfNotMatch=!0);"true"===a.attr("data-mask-selectonfocus")&&(d.selectOnFocus=!0);if(z(a,e,d))return a.data("mask",new y(this,e,d))},z=function(a,d,e){e=e||{};var g=b(a).data("mask"),k=JSON.stringify;a=b(a).val()||b(a).text();try{return"function"===typeof d&&(d=d(a)),"object"!==typeof g||k(g.options)!==k(e)||g.mask!==
				d}catch(l){}};b.fn.mask=function(a,d){d=d||{};var e=this.selector,g=b.jMaskGlobals,k=b.jMaskGlobals.watchInterval,l=function(){if(z(this,a,d))return b(this).data("mask",new y(this,a,d))};b(this).each(l);e&&(""!==e&&g.watchInputs)&&(clearInterval(b.maskWatchers[e]),b.maskWatchers[e]=setInterval(function(){b(document).find(e).each(l)},k));return this};b.fn.unmask=function(){clearInterval(b.maskWatchers[this.selector]);delete b.maskWatchers[this.selector];return this.each(function(){var a=b(this).data("mask");
					a&&a.remove().removeData("mask")})};b.fn.cleanVal=function(){return this.data("mask").getCleanVal()};b.applyDataMask=function(a){a=a||b.jMaskGlobals.maskElements;(a instanceof b?a:b(a)).filter(b.jMaskGlobals.dataMaskAttr).each(A)};var p={maskElements:"input,td,span,div",dataMaskAttr:"*[data-mask]",dataMask:!0,watchInterval:300,watchInputs:!0,watchDataMask:!1,byPassKeys:[9,16,17,18,36,37,38,39,40,91],translation:{0:{pattern:/\d/},9:{pattern:/\d/,optional:!0},"#":{pattern:/\d/,recursive:!0},A:{pattern:/[a-zA-Z0-9]/},
				S:{pattern:/[a-zA-Z]/}}};b.jMaskGlobals=b.jMaskGlobals||{};p=b.jMaskGlobals=b.extend(!0,{},p,b.jMaskGlobals);p.dataMask&&b.applyDataMask();setInterval(function(){b.jMaskGlobals.watchDataMask&&b.applyDataMask()},p.watchInterval)});