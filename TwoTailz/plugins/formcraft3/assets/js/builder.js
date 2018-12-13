window.dragged_location = null;
window.dragged_location_page = 0;
window.helpQuery = [];
window.helpQueryType = [];
window.helpPointer = -1;


window.onbeforeunload = function (event)
{

	if (window.last_save_fields_nos != window.last_checked_fields_nos)
	{
		var message = "You will lose any un-saved changes.";
		if (typeof event == 'undefined') {
			event = window.event;
		}
		if (event) {
			event.returnValue = message;
		}
		return message;
	}
};


function fixAutosize()
{
	var ta = document.querySelector('#custom-css-textarea');
	var evt = document.createEvent('Event');
	evt.initEvent('autosize.update', true, false);
	ta.dispatchEvent(evt);
}
function builderInit()
{
	jQuery('.fields-list-sortable, .fields-list-sortable > li').sortable({
		connectWith: ".form-page-content",
		helper: "clone",
		placeholder: "form-element ui-sortable-placeholder",
		start: function(e, ui){
			ui.placeholder.width('100%');
			ui.placeholder.height('56.5px');
			jQuery(this).find('.ui-sortable-placeholder').after("<div class='button to-remove'>"+ui.item[0].innerText+"</div>");
		},
		beforeStop: function(event, ui) {
			window.dragged_location = ui.placeholder.index()-1;
			window.dragged_location_page = ui.placeholder.parents('.form-page').index();
			if ( ui.placeholder.parents('.form-page-content').length==1 )
			{
				jQuery(ui.item).trigger('click');
			}
			jQuery(this).sortable('cancel');
			jQuery('.button.to-remove').remove();
		},
		stop: function(event, ui)
		{
			jQuery(this).sortable('cancel');
		}
	}).disableSelection();
}

function getURLParameter(name) {
	return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
}

function applySelectFix(id, value)
{
	setTimeout(function(){
		jQuery('#'+id).val(value);
	}, 500);
}

jQuery.fn.cleanWhitespace = function() {
	textNodes = this.contents().filter(
		function() { return (this.nodeType == 3 && !/\S/.test(this.nodeValue)); })
	.remove();
	return this;
}

function shadeColor(color, percent) {

	if (typeof color=='undefined'){return '#666666';}

	var R = parseInt(color.substring(1,3),16);
	var G = parseInt(color.substring(3,5),16);
	var B = parseInt(color.substring(5,7),16);

	R = parseInt(R * (100 + percent) / 100);
	G = parseInt(G * (100 + percent) / 100);
	B = parseInt(B * (100 + percent) / 100);

	R = (R<255)?R:255;  
	G = (G<255)?G:255;  
	B = (B<255)?B:255;  

	var RR = ((R.toString(16).length==1)?"0"+R.toString(16):R.toString(16));
	var GG = ((G.toString(16).length==1)?"0"+G.toString(16):G.toString(16));
	var BB = ((B.toString(16).length==1)?"0"+B.toString(16):B.toString(16));

	return "#"+RR+GG+BB;
}

function loadAddons()
{
	jQuery('.new-addons').html("<i class='animate-spin icon-spin5'></i>");
	jQuery.ajax( {
		url: FC.ajaxurl+'?action=formcraft3_get&URL='+encodeURIComponent('http://formcraft-wp.com/?type=get_addons&key='+FC.licenseKey),
		type: 'GET',
		context: jQuery(this),
		cache: false,
		dataType: "json"
	})
	.done(function(response) {
		if (response.failed)
		{
			toastr["error"](response.failed);
			jQuery('.new-addons').html('');
			return false;
		}		
		var html = '';
		var temp = [];
		var addons = [];
		addons['free'] = [];
		addons['purchased'] = [];
		addons['other'] = [];
		for (x in response.addons)
		{
			if ( jQuery('.addon-id-'+response.addons[x].ID).length>0 ) { continue; }
			if ( response.addons[x].price==0 )
			{
				addons['free'].push(response.addons[x]);
			}
			else if ( response.addons[x].purchased==true )
			{
				addons['purchased'].push(response.addons[x]);
			}
			else
			{
				addons['other'].push(response.addons[x]);
			}
		}
		var nos = 0;		
		for (type in addons)
		{
			if ( addons[type].length==0 ) { continue; }
			if ( type == 'free' ) { addon_type = 'Free'; }
			if ( type == 'purchased' ) { addon_type = 'Purchased'; }
			if ( type == 'other' ) { addon_type = 'Paid'; }
			html = html + "<div class='addon-type'>"+addon_type+"</div>";
			for ( addon in addons[type] )
			{
				addons[type][addon].price = parseInt(addons[type][addon].price);
				if ( addons[type][addon].price==0 || addons[type][addon].purchased==true )
				{
					var button = 
					"<br><button class='toggle-install button blue small' data-plugin='"+addons[type][addon].ID+"' class='install-plugin-btn'>" +
					"<span>Install Plugin</span>" +
					"<i class='icon-spin5 animate-spin'></i>" +
					"</button><a class='read-more-addon' href='http://formcraft-wp.com/addons/?page_id="+addons[type][addon].ID+"' target='_blank'>read more</a>";
				}
				else
				{
					var button = 
					"<br><a target='_blank' href='http://formcraft-wp.com/buy/?addons="+addons[type][addon].ID+"&key="+FC.licenseKey+"' class='button purchase small' class='install-plugin-btn'>" +
					"<span>Purchase for $" + addons[type][addon].price + "</span>" +
					"</a><a class='read-more-addon' href='http://formcraft-wp.com/addons/?page_id="+addons[type][addon].ID+"' target='_blank'>read more</a>";
				}
				html = html +
				"<div class='addon'> " + 
				"<div class='addon-head ac-toggle'>" +
				"<div class='addon-logo-cover'>" +
				"<img class='addon-logo' src='"+addons[type][addon].logo+"'/>" +
				"</div>" +
				"<span class='addon-title'>"+addons[type][addon].addon_name+"</span>" +
				"<span class='toggle-angle'>" +
				"<i class='icon-angle-down'></i>" +
				"<i class='icon-angle-up'></i>" +
				"</span>" +
				"</div>" +
				"<div class='addon-content ac-inner addon-excerpt'>" +
				addons[type][addon].addon_description.replace(/-&gt;/g,'→') +
				button +
				"</div>" +
				"</div>";
				nos++;
			}
		}
		if (nos==0)
		{
			html = "<div class='no-addons'>Nothing Left To Install</div>";
		}
		jQuery('.new-addons').html(html);
	})
.fail(function(response){
	jQuery('.new-addons').html('');
	toastr["error"]('Please check your internet connection');
});
}

function updateHelp(query, type, log)
{
	jQuery('#help-content').addClass('loading');
	jQuery.ajax( {
		url: FC.ajaxurl+'?action=formcraft3_get&URL='+encodeURIComponent(query),
		type: 'GET',
		context: jQuery(this),
		cache: false,
		dataType: "json"
	} )
	.done(function(response) {
		if ( response.failed )
		{
			jQuery('#help-content-content').html('<div style="line-height:normal;letter-spacing:0px;font-size:1.5em;margin:50px 0;text-align:center">Something broke: <br>'+response.failed+'</div>');
			return false;
		}
		if (log==true)
		{
			window.helpQuery.push([query,type]);
			window.helpPointer++;			
		}
		var html = "<div id='help-top'><span id='help-back'>← back</span><span id='help-home'>Index</span><span class='close' data-dismiss='fc_modal' aria-label='Close'>close</span></div>";
		if (type=='categories')
		{
			var html_li = '';
			if (query.indexOf('search=')!=-1)
			{
				var search = query.split('search=');
				html = html + '<h2>Search: '+search[search.length-1]+'</h2><div style="padding-top:3%; overflow: auto; height: 475px; padding-bottom: 10%">';
			}
			else
			{
				html = html + '<h2>Help Topics</h2><div style="padding-top:3%; overflow: auto; height: 475px; padding-bottom: 10%">';
			}
			html_array = [];
			if (response.length==0)
			{
				html = html + "<div class='no-posts'><i class='icon-emo-unhappy'></i> Sorry, nothing here</div>";
			}
			else
			{
				for (x in response)
				{
					for (i in response[x].terms.group)
					{
						var ID = response[x].terms.group[i].slug;
						break;
					}
					html_array[ response[x].terms.group[0].name ] = html_array[ response[x].terms.group[0].name ] || [];
					html_array[ response[x].terms.group[0].name ].push(["<div class='post' data-id='"+response[x].ID+"'>"+response[x].title+"</div>",ID]);
				}
				for (y in html_array)
				{
					html = html + '<h3 class="category" data-id="'+html_array[y][0][1]+'">'+y+'</h3>';
					html_li = html_li + '<li class="category" data-id="'+html_array[y][0][1]+'">'+y+'</li>';
					var current = 0;
					for (z in html_array[y])
					{
						if (current==6){break;}
						current++;
						html = html + html_array[y][z][0];
					}
				}
				html_li = html_li + "<li><a style='box-shadow:none;outline:none;color:inherit;text-decoration:none;margin:-8px -12px;display: block;height: 34px;line-height: 34px;padding-left: 12px;' target='_blank' href='http://formcraft-wp.com/support'>Contact Support</a></li>";
			}
			jQuery('#help-menu ul').html(html_li);
			html = html + '</div>';	
		}
		else if (type=='posts')
		{
			html = html + '<h2>'+response[0].terms.group[0].name+'</h2><div style="padding-top:3%; overflow: auto; max-height: 100%; padding-bottom: 10%">';
			for (x in response)
			{
				html = html + "<div class='post' data-id='"+response[x].ID+"'>"+response[x].title+"</div>";
			}
			html = html + '</div>';	
		}
		else if (type=='post')
		{
			response.content = response.content.replace(/<pre>/g,'<code class="code">').replace(/<pre/g,'<code class="code"').replace(/<\/pre>/g,'</code>').replace(/-&gt;/g,'→');
			html = html + '<h2>'+response.title+'</h2><article>'+response.content+'</article>';
		}
		jQuery('#help-content-content').html(html);
		jQuery('#help-content-content code').each(function(i, block) {
			hljs.highlightBlock(block);
		});
	})
.fail(function(response) {
	toastr["error"]('Please check your internet connection');
})
.always(function(response) {
	jQuery('#help-content').removeClass('loading');
	if (window.helpPointer==0){jQuery('#help-top').addClass('disabled');}else{jQuery('#help-top').removeClass('disabled');}
});
}

function addElement(event) {
	jQuery('.fc-form .form-element.ui-sortable-placeholder').remove();
	if (window.dnd_active==true)
	{
		jQuery('.fc-form .hide-it').removeClass('hide-it');
		window.dragged_location = jQuery('.form-page .dndPlaceholder').index();
		if (jQuery(event.srcElement).parents('.form-page'))
		{
			window.dragged_location_page = jQuery(event.srcElement).parents('.form-page').attr('index');
		}
		jQuery('.fc-form .dndPlaceholder').remove();
		window.dragged.trigger('click');
		event.preventDefault();
	}
}

jQuery(document).mouseup(function (e){
	jQuery('.icons-list').each(function(){
		var container = jQuery(this);
		if (
			!container.is(e.target)
			&& container.has(e.target).length === 0
			)
		{
			if ( container.find('.hide-checkbox.ng-hide').length==0 )
			{
				container.find('div span:nth-child(2)').trigger('click');
			}
		}
	});
});

jQuery(document).ready(function(){
	var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
	jQuery('.option-box').css('height',(h-71)+'px');
	jQuery('.option-box .nav-content-slide > div').css('height',(h-71-63)+'px');
	jQuery('body').addClass('formcraft-css');
	jQuery('body').on('change','.update-label label input', function(){
		if (jQuery(this).is(':checked'))
		{
			var name = jQuery(this).attr('name');
			jQuery('[name="'+name+'"]').parent().removeClass('active');
			jQuery(this).parent().addClass('active');
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

	jQuery('body').on('focus','.password-cover input[type="password"],.oneLineText-cover input[type="text"],.datepicker-cover input[type="text"],.email-cover input[type="text"],.textarea-cover textarea,.dropdown-cover select',function(){
		jQuery(this).parents('.field-cover').addClass('has-focus');
	});
	jQuery('body').on('blur','.password-cover input[type="password"],.oneLineText-cover input[type="text"],.datepicker-cover input[type="text"],.email-cover input[type="text"],.textarea-cover textarea,.dropdown-cover select',function(){
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
		if ( jQuery(this).val().length>0 || jQuery(this).attr('placeholder').length>0 )
		{
			jQuery(this).parents('.field-cover').addClass('has-input');
		}
		else
		{
			jQuery(this).parents('.field-cover').removeClass('has-input');
		}
	});
	setTimeout(function(){
		jQuery('.oneLineText-cover input[type="text"],.password-cover input[type="password"],.datepicker-cover input[type="text"],.email-cover input[type="text"],.textarea-cover textarea').trigger('input');	
	}, 1000);

	jQuery('body').on('click','.toggle-install',function(){
		jQuery(this).addClass('loading').attr('disabled','disabled');
		jQuery.ajax( {
			url: FC.ajaxurl,
			type: "POST",
			context: jQuery(this),
			data: 'action=formcraft3_install_plugin&plugin='+jQuery(this).attr('data-plugin'),
			dataType: "json"
		} )
		.done(function(response) {
			if (response.failed)
			{
				toastr["error"](response.failed);
				jQuery(this).removeClass('loading').removeAttr('disabled');
			}
			else if(response.success)
			{
				window.plugin_installed = response.plugin;
				jQuery('#plugin-save').trigger('click');
			}
			else
			{
				toastr["error"]('Unknown Error.');
				jQuery(this).removeClass('loading').removeAttr('disabled');
			}
		})
		.fail(function(response) {
			toastr["error"]('Unknown Error');
			jQuery(this).removeClass('loading').removeAttr('disabled');
		});
	})
	jQuery('body').on('click','.ac-toggle',function(e) {
		e.preventDefault();
		var jQthis = jQuery(this);
		if (jQthis.next()[0].classList.contains('show')) {
			jQthis.next().removeClass('show');
			jQthis.next().slideUp(300);
			jQthis.removeClass('active');
		} else {
			jQthis.parent().parent().find('.ac-inner').removeClass('show');
			jQthis.parent().parent().find('.ac-inner').slideUp(300);
			jQthis.parent().parent().find('.ac-toggle').removeClass('active');
			jQthis.next().toggleClass('show');
			jQthis.next().slideToggle(300);
			jQthis.addClass('active');
		}
	});
	jQuery('.simple-toggle').click(function() {
		var jQthis = jQuery(this);
		if (jQthis.next()[0].classList.contains('show')) {
			jQthis.next().removeClass('show');
			jQthis.next().slideUp(300);
			jQthis.removeClass('active');
		} else {
			jQthis.parent().parent().find('.simple-inner').removeClass('show');
			jQthis.parent().parent().find('.simple-inner').slideUp(300);
			jQthis.parent().parent().find('.simple-toggle').removeClass('active');
			jQthis.next().toggleClass('show');
			jQthis.next().slideToggle(300);
			jQthis.addClass('active');
		}
	});	
	jQuery('body').on('click','.form-cover-builder',function(event){
		if (jQuery(event.target).parents('.fc-form').length==0)
		{
			jQuery('.iris-picker').hide();
			if (jQuery('#form_styling_box').hasClass('state-true'))
			{
				jQuery('#form_styling_button').trigger('click');
			}
			if (jQuery('#form_addon_box').hasClass('state-true'))
			{
				jQuery('#form_addons_button').trigger('click');
			}
			if (jQuery('#form_options_box').hasClass('state-true'))
			{
				jQuery('#form_options_button').trigger('click');
			}
			if (jQuery('#form_logic_box').hasClass('state-true'))
			{
				jQuery('#form_logic_button').trigger('click');
			}
			jQuery('.options-true .form-element-html').trigger('click');
		}
	});
	jQuery('body').on('click','.trigger-help', function(){
		jQuery('#help-content-content').html('.');
		jQuery('.fc_modal').fc_modal('hide');
		jQuery('#help_modal').fc_modal('show');
		window.helpPointer = window.helpQuery.length-1;
		updateHelp('http://formcraft-wp.com/wp-json/posts/'+jQuery(this).attr('data-post-id')+'/?type=help','post',true);
	});
	jQuery('body').on('submit','#help-search',function(){
		window.helpPointer = window.helpQuery.length-1;
		updateHelp('http://formcraft-wp.com/wp-json/posts/?type=help&filter[posts_per_page]=50&filter[s]='+jQuery(this).find('input').val(),'categories',true);		
	});
	jQuery('body').on('click','#help_modal .category', function(){
		jQuery('#help_modal .category.active').removeClass('active');
		jQuery('.category[data-id="'+jQuery(this).attr('data-id')+'"]').addClass('active');
		window.helpPointer = window.helpQuery.length-1;
		updateHelp('http://formcraft-wp.com/wp-json/posts/?type=help&filter[posts_per_page]=50&filter[order]=ASC&filter[group]='+jQuery(this).attr('data-id'),'posts',true);
	});
	jQuery('body').on('click','#help-content-content .post, #help-content-content .trigger-post', function(event){
		event.preventDefault();
		window.helpPointer = window.helpQuery.length-1;
		updateHelp('http://formcraft-wp.com/wp-json/posts/'+jQuery(this).attr('data-id')+'/?type=help','post',true);
	});	
	jQuery('body').on('click','#help-back', function(){
		if ( typeof window.helpQuery!='undefined' && !jQuery(this).parent().hasClass('disabled') )
		{
			window.helpPointer = window.helpPointer - 1;
			window.helpQuery.splice(window.helpPointer+1,window.helpQuery.length);
			updateHelp(window.helpQuery[window.helpPointer][0],window.helpQuery[window.helpPointer][1],false);
		}
	});
	jQuery('body').on('click','#help-home', function(){
		window.helpPointer = window.helpQuery.length-1;
		updateHelp('http://formcraft-wp.com/wp-json/posts/?type=help&filter[posts_per_page]=50&filter[order]=ASC','categories',true);
	});	
	jQuery('#help_modal').on('shown.bs.fc_modal', function () {
		if (jQuery('#help-content-content').html().trim()=='')
		{
			window.helpPointer = window.helpQuery.length-1;
			updateHelp('http://formcraft-wp.com/wp-json/posts/?type=help&filter[posts_per_page]=50&filter[order]=ASC','categories',true);
		}
	});
	jQuery('body').on('focus','.wp-picker-input-wrap .color-picker', function(){
		jQuery(this).parent().find('.wp-color-picker').trigger('change');
	});
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
	jQuery('.fake-hover').hover(
		function () {
			jQuery(this).addClass('hover-true');
		}, 
		function () {
			setTimeout(function(){
				jQuery('.fake-hover').removeClass('hover-false');
			}, 200);
			jQuery(this).removeClass('hover-true');
			jQuery(this).addClass('hover-false');
		}
		);
	jQuery('[data-toggle="tooltip"]').tooltip({html:true});
	jQuery('body').on('change','.parent-toggle',function(){
		var name = jQuery(this).attr('name');
		jQuery('[name="'+name+'"]').parent().removeClass('active');
		jQuery('[name="'+name+'"]:checked').parent().addClass('active');
	});
	jQuery('body').on('click','.nav-tabs > span',function(){
		var selector = jQuery(this).parent().attr('data-content');
		jQuery(this).parent().find('> span').removeClass('active');
		jQuery(this).addClass('active');
		jQuery(selector).find(' > div').removeClass('active');
		jQuery(selector).find(' > div').eq(jQuery(this).index()).addClass('active');
	});
	jQuery('body').on('click','.nav-tabs-slide > span',function(){
		var selector = jQuery(this).parent().attr('data-content');
		jQuery(this).parent().find('> span').removeClass('active');
		jQuery(this).addClass('active');
		if (window.isRTL==true)
		{
			var left = (parseInt(jQuery(this).index())*100)+'%';
		}
		else
		{
			var left = "-"+(parseInt(jQuery(this).index())*100)+'%';			
		}
		jQuery(selector).css('-webkit-transform',"translate3d("+left+", 0px, 0px)");
		jQuery(selector).css('transform',"translate3d("+left+", 0px, 0px)");
		jQuery(selector).find(' > div').removeClass('active');
		jQuery(selector).find(' > div').eq(jQuery(this).index()).addClass('active');
		setTimeout(function(){
			var ta = document.querySelector('#success-message');
			var evt = document.createEvent('Event');
			evt.initEvent('autosize.update', true, false);
			ta.dispatchEvent(evt);
		}, 200);
		if (jQuery(selector).find(' > div').eq(jQuery(this).index()).hasClass('new-addons') && jQuery(selector).find(' > div').eq(jQuery(this).index()).html().trim()=='' )
		{
			loadAddons();
		}
	});
});


toastr.options = {
	"closeButton": false,
	"debug": false,
	"newestOnTop": true,
	"progressBar": false,
	"positionClass": "toast-top-right",
	"preventDuplicates": false,
	"onclick": null,
	"showDuration": "500",
	"hideDuration": "500",
	"timeOut": "3000",
	"extendedTimeOut": "500",
	"showEasing": "linear",
	"hideEasing": "linear",
	"showMethod": "slideDown",
	"hideMethod": "slideUp"
}
function saveFormJQuery(builder, addons, addField, callback)
{
	var meta_builder = {};
	meta_builder.fields = [];
	meta_builder.config = builder.Config;

	for (page in builder.FormElements)
	{
		for (element in builder.FormElements[page]) {
			if (typeof builder.FormElements[page][element].elementDefaults=='undefined'){continue;}
			var is_payment = false;
			for (x in addField['payments'])
			{
				is_payment = addField['payments'][x].name == builder.FormElements[page][element].type ? true : is_payment;
			}
			meta_builder.fields.push({
				identifier: builder.FormElements[page][element].elementDefaults.identifier,
				type: builder.FormElements[page][element].type,
				elementDefaults: builder.FormElements[page][element].elementDefaults,
				page: parseInt(page)+1,
				is_payment: is_payment
			});
		}
	}
	window.last_save_fields_nos = meta_builder.fields.length;
	meta_builder.page_count = builder.FormElements.length;
	if (jQuery('.fc-form .customText-cover').length!=0)
	{
		jQuery('.fc-form .customText-cover img').each(function(){
			var height = jQuery(this).attr('height');
			jQuery(this).css('height',height+'px');
			var width = jQuery(this).attr('width');
			jQuery(this).css('width',width+'px');
		});
	}
	if (jQuery('.fc-form .textarea-cover').length!=0)
	{
		jQuery('.fc-form .textarea-cover textarea').each(function(){
			jQuery(this).css('min-height',jQuery(this).outerHeight()+'px');
		});
	}
	if (jQuery('.fc-form .datepicker-cover').length!=0)
	{
		jQuery('.fc-form .datepicker-cover input').each(function(){
			jQuery(this).removeAttr('id');
		});
	}

	var meta_builder = encodeURIComponent(angular.toJson(meta_builder));
	var builder = encodeURIComponent(deflate(angular.toJson(builder)));
	var addons = encodeURIComponent(angular.toJson(addons));
	var html = jQuery('#form-cover-html').html().trim();
	if (jQuery('.fc-form .textarea-cover').length!=0)
	{
		jQuery('.fc-form .textarea-cover textarea').each(function(){
			jQuery(this).css('min-height', '0');
		});
	}	
	html = html.replace(/ng-repeat="[^"]*"/g, "");
	html = html.replace(/ng-class="[^"]*"/g, "");
	html = html.replace(/ng-click="[^"]*"/g, "");
	html = html.replace(/ng-class-odd="[^"]*"/g, "");
	html = html.replace(/ng-init="[^"]*"/g, "");
	html = html.replace(/ui-sortable="[^"]*"/g, "");
	html = html.replace(/watch-show-options="[^"]*"/g, "");
	html = html.replace(/ng-class-even="[^"]*"/g, "");
	html = html.replace(/ng-model="[^"]*"/g, "");
	html = html.replace(/ondrop="[^"]*"/g, "");
	html = html.replace(/dnd-list="[^"]*"/g, "");
	html = html.replace(/compile="[^"]*"/g, "");
	var html = minify(html, {
		removeComments: false,
		removeEmptyAttributes: true
	});
	html = html.replace(/<!--RFH-->[\s\S]*?<!--RTH-->/g, '');
	html = html.replace(/<!-- end ngRepeat: page in Builder.FormElements -->/g, '');
	html = html.replace(/<!-- ngRepeat: page in Builder.FormElements -->/g, '');
	html = html.replace(/<!-- end ngRepeat: element in page -->/g, '');
	html = html.replace(/<!-- ngRepeat: element in page -->/g, '');
	html = html.replace(/ng-binding/g, '');
	html = html.replace(/ng-scope/g, '');
	html = html.replace(/ng-dirty/g, '');
	html = html.replace(/ui-sortable/g, '');
	html = html.replace(/ui-sortable-handle/g, '');
	html = html.replace(/ng-valid-parse/g, '');
	html = html.replace(/class=""/g, '');
	html = html.replace(/ng-untouched/g, '');
	html = html.replace(/ng-valid/g, '');	
	var html = encodeURIComponent(html);
	var data = 'builder='+builder+'&addons='+addons+'&id='+jQuery('#form_id').val()+'&html='+html+'&meta_builder='+meta_builder;
	jQuery('#form_save_button').attr('disabled','disabled');
	jQuery('#form_save_button').addClass('saving');
	jQuery.ajax( {
		url: FC.ajaxurl,
		type: "POST",
		context: jQuery(this),
		data: 'action=formcraft3_form_save&'+data,
		dataType: "json"
	} )
	.done(function(response) {
		if (response.failed)
		{
			toastr["error"](response.failed);
		}
		else if(response.success)
		{
			toastr["success"]("<i class='icon-ok'></i> "+response.success);
			callback(true);
		}
		else
		{
			toastr["error"]('Failed Saving. Unknown Error.');
		}
	})
	.fail(function(response) {
		toastr["error"]('Failed Saving');
	})
	.always(function(response) {
		jQuery('#form_save_button').removeClass('saving');
		jQuery('#form_save_button').removeAttr('disabled');
	});
}

var FormCraftApp = angular.module('FormCraft', ['textAngular','ui.sortable']);

FormCraftApp
.directive('compile', function($compile) {
	return function(scope, element, attrs) {
		scope.$watch(
			function(scope) {
				return scope.$eval(attrs.compile);
			},
			function(value) {
				element.html(value);
				$compile(element.contents())(scope);
			}
			);
	};
})
.directive('updateLabel', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if ($element[0].checked==true)
				{
					$element.parent().addClass('active');
				}
				else
				{
					$element.parent().removeClass('active');
				}
			});
		}

	}
})
.directive('watchShowOptions', function($compile) {
	return function($scope, $element, $attrs) {
		$attrs.$observe("watchShowOptions", function innerObserveFunction(){
			$scope.element.show_options_animate = $scope.element.show_options_animate || false;
			if ( typeof $scope.element.show_options == 'undefined' )
			{
				$scope.isShowPristine = false;
			}
			if ( $scope.element.show_options==false && $scope.element.show_options_animate != false )
			{
				$scope.element.show_options_animate = 'hiding';
				setTimeout(function(){
					$scope.$apply(function(){$scope.element.show_options_animate = false;});
				}, 250);
			}
			else
			{
				$scope.element.show_options_animate = true;
			}

			if ( $scope.element.show_options==true )
			{
				$scope.element.show_options_animate = true;
			}

			$scope.isShowPristine = typeof $scope.isShowPristine == 'undefined' ? true : false;
			if ( $attrs.watchShowOptions=='true' && $scope.isShowPristine==false )
			{
				for (x in $scope.$parent.$parent.Builder.FormElements)
				{
					for ( y in $scope.$parent.$parent.Builder.FormElements[x] )
					{
						if ( typeof $scope.$parent.$parent.Builder.FormElements[x][y].show_options == 'undefined' ){ continue; }
						if ( $scope.$even==true && y%2 == 0 && y!=$scope.$index)
						{
							$scope.$parent.$parent.Builder.FormElements[x][y].show_options = false;
						}
						if ( $scope.$odd==true && y%2 != 0 && y!=$scope.$index)
						{
							$scope.$parent.$parent.Builder.FormElements[x][y].show_options = false;
						}
					}
				}
			}
		});
};
})
.directive('selectFields', function($compile) {
	return function($scope, $element, $attrs) {
		$scope.$watch('listOfFields', function(){
			setTimeout(function(){
				var instance = $element[0].selectize;
				if ( typeof instance != 'undefined' )
				{
					instance.destroy();
				}
				$element.selectize({
					valueField: 'identifier',
					labelField: 'label',
					sortField: 'text',
					openOnFocus: true,
					preload: true,
					options: $scope.listOfFields,
					onChange: function(value){
						var placeholder = $attrs.placeholder;
						if ( typeof placeholder != 'undefined' )
						{
							$element.parent().find('.selectize-input > input').attr('placeholder', placeholder);
						}
					}
				});
				var placeholder = $attrs.placeholder;
				if ( typeof placeholder != 'undefined' )
				{
					$element.parent().find('.selectize-input > input').attr('placeholder', placeholder);
				}
			}, 500);
		});
	};
})
.directive('ngSlideToggle', function($compile) {
	return function($scope, $element, $attrs) {
		$scope.$watch($attrs.ngSlideToggle, function(e){
			if ( typeof e == 'undefined' || e == false )
			{
				$element.slideUp(250);
			}
			else
			{
				$element.slideDown(250);
			}
		});
	};
})
.directive('checkboxList', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if ( typeof $scope.element.elementDefaults.options_list_show == 'undefined' )
				{
					$scope.isPristine = false;
				}				
				if ( typeof $scope.isPristine == 'undefined' || $scope.isPristine == true )
				{
					$scope.isPristine = false;
					return false;
				}
				if ( typeof ngModelCtrl.$modelValue == 'number' )
				{
					var temp = $scope.element.elementDefaults.options_list.split('\n');
				}
				else
				{
					var temp = ngModelCtrl.$modelValue.split('\n');
				}
				$scope.element.elementDefaults.options_list_show=[];
				tempList = [];
				for (x in temp)
				{
					if (temp[x].indexOf('==')!=-1)
					{
						var temp2 = temp[x].split('==');
						tempList.push({
							value:  temp2[0],
							show: temp2[1]
						});	
					}
					else
					{
						tempList.push({
							value:  temp[x],
							show: temp[x]
						});	
					}
				}
				$scope.element.elementDefaults.options_list_show = tempList;
			});
		}

	}
})
.directive('matrixRows', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if ( typeof $scope.element.elementDefaults.matrix_rows_output == 'undefined' )
				{
					$scope.isPristineRows = false;
				}
				if ( typeof $scope.isPristineRows == 'undefined' || $scope.isPristineRows == true )
				{
					$scope.isPristineRows = false;
					return false;
				}				
				if ( typeof ngModelCtrl.$modelValue == 'number' )
				{
					var temp = $scope.element.elementDefaults.matrix_rows.split('\n');
				}
				else
				{
					var temp = ngModelCtrl.$modelValue.split('\n');
				}
				$scope.element.elementDefaults.matrix_rows_output=[];
				tempList = [];
				for (x in temp)
				{
					tempList.push({
						value:  temp[x]
					});	
				}
				$scope.element.elementDefaults.matrix_rows_output = tempList;
			});
		}

	}
})
.directive('matrixCols', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if ( typeof $scope.element.elementDefaults.matrix_cols_output == 'undefined' )
				{
					$scope.isPristineCols = false;
				}
				if ( typeof $scope.isPristineCols == 'undefined' || $scope.isPristineCols == true )
				{
					$scope.isPristineCols = false;
					return false;
				}				
				if ( typeof $scope.element.elementDefaults.matrix_cols == 'undefined' )
				{
					$scope.element.elementDefaults.matrix_cols = $scope.element.elementDefaults.options_list;
					delete $scope.element.elementDefaults.options_list;
					var temp = $scope.element.elementDefaults.matrix_cols.split('\n');
				}
				else if ( typeof ngModelCtrl.$modelValue == 'number' )
				{
					var temp = $scope.element.elementDefaults.matrix_cols.split('\n');
				}
				else
				{
					var temp = ngModelCtrl.$modelValue.split('\n');
				}
				$scope.element.elementDefaults.matrix_cols_output=[];
				tempList = [];
				for (x in temp)
				{
					tempList.push({
						value:  temp[x]
					});	
				}
				$scope.element.elementDefaults.matrix_cols_output = tempList;
			});
}

}
})
.directive('imageList', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if ($scope.element.elementDefaults.allow_images==true)
				{
					var temp = ngModelCtrl.$modelValue.split('\n');
					$scope.element.elementDefaults.images_list_show=[];
					for (x in temp)
					{
						{
							$scope.element.elementDefaults.images_list_show.push({
								url:  temp[x]
							});	
						}
					}					
				}
				else
				{
					$scope.element.elementDefaults.images_list_show = [];
				}
			});
		}

	}
})
.directive('updateHours', function() {
	return {
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$attrs.$observe("hrsMin", function innerObserveFunction(){
				var min = parseInt($attrs.hrsMin);
				min = Math.max(min,0);
				var max = parseInt($attrs.hrsMax);
				max = Math.min(max,24);
				var step = parseInt($attrs.hrsStep);
				step = Math.max(1,step);

				min = isNaN(min) ? 0: min;
				max = isNaN(max) ? 24: max;
				step = isNaN(step) ? 1: step;
				var stop = false;
				var i = min;
				var a = 0;
				$scope.element.elementDefaults.hrs_range = [];
				while(stop==false)
				{
					a++;
					padded = ("0" + i).substr(-2,2);
					$scope.element.elementDefaults.hrs_range.push(padded);
					i = i + step;
					if (i>max){stop=true;}
					if (a==24){stop=true;}
				}
			});
			$attrs.$observe("hrsMax", function innerObserveFunction(){
				var min = parseInt($attrs.hrsMin);
				min = Math.max(min,0);
				var max = parseInt($attrs.hrsMax);
				max = Math.min(max,24);
				var step = parseInt($attrs.hrsStep);
				step = Math.max(1,step);

				min = isNaN(min) ? 0: min;
				max = isNaN(max) ? 24: max;
				step = isNaN(step) ? 1: step;
				var stop = false;
				var i = min;
				var a = 0;
				$scope.element.elementDefaults.hrs_range = [];
				while(stop==false)
				{
					a++;
					padded = ("0" + i).substr(-2,2);
					$scope.element.elementDefaults.hrs_range.push(padded);
					i = i + step;
					if (i>max){stop=true;}
					if (a==24){stop=true;}
				}
			});
			$attrs.$observe("hrsStep", function innerObserveFunction(){
				var min = parseInt($attrs.hrsMin);
				min = Math.max(min,0);
				var max = parseInt($attrs.hrsMax);
				max = Math.min(max,24);
				var step = parseInt($attrs.hrsStep);
				step = Math.max(1,step);

				min = isNaN(min) ? 0: min;
				max = isNaN(max) ? 24: max;
				step = isNaN(step) ? 1: step;
				var stop = false;
				var i = min;
				var a = 0;
				$scope.element.elementDefaults.hrs_range = [];
				while(stop==false)
				{
					a++;
					padded = ("0" + i).substr(-2,2);
					$scope.element.elementDefaults.hrs_range.push(padded);
					i = i + step;
					if (i>max){stop=true;}
					if (a==24){stop=true;}
				}
			});
		}

	}
})
.directive('updateMinutes', function() {
	return {
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$attrs.$observe("minuteStep", function innerObserveFunction(){
				var step = parseInt($attrs.minuteStep);
				step = Math.max(1,step);
				step = Math.min(60,step);
				step = isNaN(step) ? 30: step;

				var stop = false;
				var i = 0;
				var a = 0;
				$scope.element.elementDefaults.minute_range = [];
				while(stop==false)
				{
					a++;
					padded = ("0" + i).substr(-2,2);
					$scope.element.elementDefaults.minute_range.push(padded);
					i = i + step;
					if (i>=60){stop=true;}
					if (a==60){stop=true;}
				}
			});
		}

	}
})
.directive('subLabel', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if (ngModelCtrl.$modelValue=='')
				{
					$scope.element.elementDefaults.has_sub_label = false;
				}
				else
				{
					$scope.element.elementDefaults.has_sub_label = true;
				}
			});
		}

	}
})
.directive('fcPlaceholder', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if ($scope.$parent.Builder.label_style=='placeholder')
				{
					$scope.element.elementDefaults.main_label_placeholder = $scope.element.elementDefaults.main_label;
				}
				else
				{
					$scope.element.elementDefaults.main_label_placeholder = '';
				}
			});
		}
	}
})
.directive('fcPlaceholderUpdate', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$scope.$watch($attrs.ngModel, function(){
				if (typeof ngModelCtrl.$viewValue!='undefined')
				{
					if (ngModelCtrl.$viewValue=='placeholder')
					{
						for(x in $scope.Builder.FormElements)
						{
							for (y in $scope.Builder.FormElements[x])
							{
								if (typeof $scope.Builder.FormElements[x][y]=='object')
								{
									var temp = $scope.Builder.FormElements[x][y].elementDefaults.main_label;
									$scope.Builder.FormElements[x][y].elementDefaults.main_label_placeholder = temp;
								}
							}
						}
					}
					else
					{
						for(x in $scope.Builder.FormElements)
						{
							for (y in $scope.Builder.FormElements[x])
							{
								if (typeof $scope.Builder.FormElements[x][y]=='object')
								{
									if ( typeof $scope.Builder.FormElements[x][y].elementDefaults.maskPlaceholder != 'undefined' && $scope.Builder.FormElements[x][y].elementDefaults.maskPlaceholder.trim()!='' )
									{
										$scope.Builder.FormElements[x][y].elementDefaults.main_label_placeholder = $scope.Builder.FormElements[x][y].elementDefaults.maskPlaceholder;
									}
									else
									{
										$scope.Builder.FormElements[x][y].elementDefaults.main_label_placeholder = '';
									}
								}
							}
						}
					}
				}
			});
}
}
})
.directive('autosize', function() {
	return {
		link: function($scope, $element, $attrs, ngModelCtrl) {
			autosize($element);
		}
	}
})
.directive('angularColor', function() {
	return {
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$element.wpColorPicker({
				width: 237,
				change: function(event, ui) {
					if ( ui.color.toString() != jQuery(this).val() )
					{
						jQuery(this).val(ui.color.toString()).trigger('change');
					}
				},
				clear: function() {
					ngModelCtrl.$setViewValue('');
				}
			});
		}
	}
})
.directive('tooltip', function() {
	return {
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$element.tooltip({html:true});
			$attrs.$observe("title",function innerObserveFunction(){
				$element.tooltip('destroy');
				if ( $attrs.title.trim()!='' && $attrs.title.indexOf('{{')==-1 )
				{
					$element.tooltip({html:true});
				}
				else
				{
					$element.attr('data-original-title','');
				}
			});
		}

	}
})
.directive('inputMask', function() {
	return {
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$attrs.$observe("inputMask",function innerObserveFunction(){
				if ($attrs.inputMask.trim()=='')
				{
					$element.unmask();
				}
				else
				{
					$element.mask($attrs.inputMask.replace(/[^a-zA-Z0-9():\-\/]+/g, ''));
				}
			});
		}

	}
})
.directive('maskPlaceholder', function() {
	return {
		link: function($scope, $element, $attrs, ngModelCtrl) {
			$attrs.$observe("maskPlaceholder",function innerObserveFunction(){
				if ($scope.$parent.Builder.label_style!='placeholder')
				{
					$scope.element.elementDefaults.main_label_placeholder = $scope.element.elementDefaults.maskPlaceholder;
					setTimeout(function(){
						$element.trigger('input');
					}, 200);
				}
			});
		}

	}
})
.directive('slider', function() {
	return {
		restrict: 'A',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			options = {};
			options.range = 'min';
			options.create = function( event, ui ) {
				if (options.range==true)
				{
					jQuery(this).find('.ui-slider-range').eq(0).append('<span class="ui-slider-handle-nos">0</span>');
				}
				else
				{
					jQuery(this).find('span.ui-slider-handle').eq(0).append('<span class="ui-slider-handle-nos">0</span>');
				}
			}
			options.change = function( event, ui ) {
				jQuery(this).find('.ui-slider-handle-nos').css('margin-left','-'+(jQuery(this).find('.ui-slider-handle-nos').outerWidth()/2-9)+'px');
				if(ui.values)
				{
					ui.values[0] = typeof jQuery(this).attr('data-prefix')!='undefined' ? jQuery(this).attr('data-prefix')+ui.values[0] : ui.values[0];
					ui.values[0] = typeof jQuery(this).attr('data-suffix')!='undefined' ? ui.values[0]+jQuery(this).attr('data-suffix') : ui.values[0];
					ui.values[1] = typeof jQuery(this).attr('data-prefix')!='undefined' ? jQuery(this).attr('data-prefix')+ui.values[1] : ui.values[1];
					ui.values[1] = typeof jQuery(this).attr('data-suffix')!='undefined' ? ui.values[1]+jQuery(this).attr('data-suffix') : ui.values[1];
					var value = ui.values[0]+' - '+ui.values[1];
				}
				else
				{
					var value = ui.value;
					value = typeof jQuery(this).attr('data-prefix')!='undefined' ? jQuery(this).attr('data-prefix')+value : value;
					value = typeof jQuery(this).attr('data-suffix')!='undefined' ? value+jQuery(this).attr('data-suffix') : value;
				}
				jQuery(this).find('.ui-slider-handle-nos').text(value);
				jQuery(this).parent().parent().find('input').val(value).trigger('change');				
			}
			options.slide = function( event, ui ) {
				jQuery(this).find('.ui-slider-handle-nos').css('margin-left','-'+(jQuery(this).find('.ui-slider-handle-nos').outerWidth()/2-9)+'px');				
				jQuery(this).find('.ui-slider-handle-nos').show();
				if(ui.values)
				{
					ui.values[0] = typeof jQuery(this).attr('data-prefix')!='undefined' ? jQuery(this).attr('data-prefix')+ui.values[0] : ui.values[0];
					ui.values[0] = typeof jQuery(this).attr('data-suffix')!='undefined' ? ui.values[0]+jQuery(this).attr('data-suffix') : ui.values[0];
					ui.values[1] = typeof jQuery(this).attr('data-prefix')!='undefined' ? jQuery(this).attr('data-prefix')+ui.values[1] : ui.values[1];
					ui.values[1] = typeof jQuery(this).attr('data-suffix')!='undefined' ? ui.values[1]+jQuery(this).attr('data-suffix') : ui.values[1];
					var value = ui.values[0]+' - '+ui.values[1];
				}
				else
				{
					var value = ui.value;
					value = typeof jQuery(this).attr('data-prefix')!='undefined' ? jQuery(this).attr('data-prefix')+value : value;
					value = typeof jQuery(this).attr('data-suffix')!='undefined' ? value+jQuery(this).attr('data-suffix') : value;
				}
				jQuery(this).find('.ui-slider-handle-nos').text(value);
				jQuery(this).parent().parent().find('input').val(value).trigger('change');				
			}
			$element.slider(options);
			$attrs.$observe(
				"rangeMin",
				function innerObserveFunction()
				{
					$element.slider( 'option', 'min', parseFloat($attrs.rangeMin) );
				}
				);
			$attrs.$observe(
				"rangeStep",
				function innerObserveFunction()
				{
					$element.slider( 'option', 'step', parseFloat($attrs.rangeStep) );
				}
				);			
			$attrs.$observe(
				"rangeMax",
				function innerObserveFunction()
				{
					$element.slider( 'option', 'max', parseFloat($attrs.rangeMax) );
				}
				);	
			$attrs.$observe(
				"rangeTrue",
				function innerObserveFunction()
				{
					range = $attrs.rangeTrue=='true' ? true : 'min';
					$element.slider( 'option', 'range', range );
				}
				);	
		}

	}
})
.directive('datepicker', function() {
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function($scope, $element, $attrs, ngModelCtrl) {
			options = {};
			options.nextText = '❯';
			options.prevText = '❮';
			options.hideIfNoPrevNext = true;
			options.changeYear = true;
			options.changeMonth = true;
			options.showAnim = false;
			options.yearRange = "c-20:c+20";
			if ( typeof $attrs.dateFormat!='undefined' && typeof $attrs.defaultDate!='undefined' && $attrs.defaultDate!='' && $attrs.dateFormat!='' )
			{
				if ( parseInt($attrs.defaultDate) != $attrs.defaultDate )
				{
					options.dateFormat = $attrs.dateFormat;
					options.defaultDate = $attrs.defaultDate;
				}
			}
			options.beforeShow = function(input, inst) {
				jQuery('#ui-datepicker-div').removeClass('ui-datepicker').addClass('fc-datepicker');
			}
			options.onSelect = function(input, inst) {
				jQuery(this).trigger('change').trigger('input');
				$scope.$apply(function() {
					ngModelCtrl.$setViewValue(input);
				});
			}			
			$element.datepicker(options);
			$attrs.$observe("defaultDate", function innerObserveFunction() {
				if ($element.val()!='' && typeof $element.attr('hasLoaded')=='undefined')
				{
					var temp = $attrs.defaultDate;
					setTimeout(function(){
						$element.val(temp).trigger('change');
						$element.attr('hasLoaded','true');
					}, 500);
				}
			});
			$attrs.$observe("dateFormat", function innerObserveFunction() {
				$element.datepicker( "option", "dateFormat", $attrs.dateFormat );
				$element.trigger('change');
			});
			$scope.$watch($attrs.ngModel, function(){
				var date = jQuery.datepicker.formatDate( "yy/mm/dd", $element.datepicker( "getDate" ) );
				if($attrs.ngModel=='element.elementDefaults.minDate')
				{
					$scope.element.elementDefaults.minDateAlt = date;
				}
				if($attrs.ngModel=='element.elementDefaults.maxDate')
				{
					$scope.element.elementDefaults.maxDateAlt = date;
				}
			});
			$attrs.$observe(
				"dateLang",
				function innerObserveFunction() {
					if ($attrs.dateLang!='en')
					{
						$element.datepicker( "option", "dateFormat", $attrs.dateFormat );
						$element.datepicker( "option", "altFormat", 'yy-mm-dd' );
						jQuery.get(FC.datepickerLang+'datepicker-'+$attrs.dateLang+'.js');
					}
				}
				);
			$attrs.$observe("dateMin", function innerObserveFunction() {
				if ($attrs.dateMin!='' && parseInt($attrs.dateMin)==$attrs.dateMin)
				{
					var someDate = new Date();
					someDate.setDate(someDate.getDate() + parseInt($attrs.dateMin));
					$element.datepicker( "option", "minDate", $attrs.dateMin );
				}
				else
				{
					$element.datepicker( "option", "dateFormat", $attrs.dateFormat );
					$element.datepicker( "option", "altFormat", 'yy-mm-dd' );
					$element.datepicker( "option", "minDate", $attrs.dateMin );
				}
			});
			$attrs.$observe("dateDays", function innerObserveFunction() {
				var temp = jQuery.parseJSON($attrs.dateDays);
				var tempNew = [];
				for ( x in temp )
				{
					if ( temp[x] == true )
					{
						tempNew.push(x);
					}
				}
				$element.datepicker( "option", "beforeShowDay", function(date){
					if ( tempNew.indexOf(date.getDay().toString())!=-1 )
					{
						return [true, ''];
					}
					else
					{
						return [false, ''];
					}
				});
			});
			$attrs.$observe("dateMax", function innerObserveFunction() {
				if ($attrs.dateMax!='' && parseInt($attrs.dateMax)==$attrs.dateMax)
				{
					var someDate = new Date();
					someDate.setDate(someDate.getDate() + parseInt($attrs.dateMax));
					$element.datepicker( "option", "maxDate", $attrs.dateMax );
				}
				else
				{
					$element.datepicker( "option", "dateFormat", $attrs.dateFormat );
					$element.datepicker( "option", "altFormat", 'yy-mm-dd' );
					$element.datepicker( "option", "maxDate", $attrs.dateMax );
				}
			});
		}
	}
})
.controller('FormController', function($scope, $locale, $http) {

	$scope.addField = {};
	$scope.addField.payments = [];
	$scope.addField.defaults = [];
	$scope.addField.others = [];

	function createOptions (listName) {
		var _listName = listName;
		var options = {
			connectWith: ".form-page-content",
			helper: "",
			start: function(event, ui)
			{
				ui.placeholder.html(ui.item[0].innerHTML);
			}
		};
		return options;
	}

	$scope.testEmail = function() {
		$scope.TestEmailResult = '<div class="fc-spinner small" style="display:block"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>';
		var postData = {};
		postData.emails = $scope.Builder.TestEmails;
		postData.config = $scope.Builder.Config.notifications;
		$http.post(FC.ajaxurl+'?action=formcraft3_test_email', postData).
		success(function(data, status, headers, config) {
			$scope.TestEmailResult = data;
		}).
		error(function(data, status, headers, config) {
			$scope.TestEmailResult = 'Error';
		});
	}

	$scope.FormElements = function() {
		$http.get(FC.ajaxurl+'?action=formcraft3_load_form_data&type=builder&id='+jQuery('#form_id').val()).success(function(response){
			if (response.builder.trim()=='')
			{
				$scope.Builder = {};
				$scope.Builder.Config = {};
				$scope.Builder.Config.Logic = [];
				$scope.Builder.Config.messages = {};
				$scope.Builder.Config.autoresponder = {};
				$scope.Builder.Config.notifications = {};
				$scope.Builder.FormElements = [];
				$scope.Builder.FormElements[0] = [];
				$scope.Builder.Config.page_names = [];
				$scope.Builder.Config.page_names[0] = 'Step 1';
				$scope.Builder.Options = {};
				$scope.Builder.form_background = 'white';
				$scope.Builder.Config.color_scheme_button = '#48e';
				$scope.Builder.Config.color_scheme_step = '#48e';
				$scope.Builder.Config.color_scheme_font = '#fff';
			}
			else if (response.builder.indexOf('[BREAK]')!=-1)
			{
				$scope.Builder = {};
				$scope.Builder.Config = {};
				$scope.Builder.Config.Logic = [];
				$scope.Builder.Config.messages = {};
				$scope.Builder.Config.autoresponder = {};
				$scope.Builder.Config.notifications = {};
				$scope.Builder.FormElements = [];
				$scope.Builder.FormElements[0] = [];
				$scope.Builder.Config.page_names = [];
				$scope.Builder.Config.page_names[0] = 'Step 1';
				$scope.Builder.Options = {};
				$scope.Builder.form_background = 'white';
				$scope.Builder.Config.color_scheme_button = '#48e';
				$scope.Builder.Config.color_scheme_step = '#48e';
				$scope.Builder.Config.color_scheme_font = '#fff';
				$scope.Addons = {};
				$scope.Options = {};
				var imported = response.builder.split('[BREAK]');
				var build = imported[0];
				var options = imported[1];
				options = jQuery.evalJSON(inflate(decodeURIComponent(options.trim())));
				var con = imported[2].replace(/\\(.?)/g, function(s, n1) {
					switch (n1) {
						case '\\':
						return '\\';
						case '0':
						return '\u0000';
						case '':
						return '';
						default:
						return n1;
					}
				});
				con = jQuery.evalJSON(con);
				var recipients = imported[3].replace(/\\(.?)/g, function(s, n1) {
					switch (n1) {
						case '\\':
						return '\\';
						case '0':
						return '\u0000';
						case '':
						return '';
						default:
						return n1;
					}
				}).replace(/"/g,'');
				$scope.Builder.Config.notifications.recipients = recipients;
				$scope.Builder.Config.Messages = $scope.Builder.Config.Messages || {};
				build = jQuery.evalJSON(inflate(decodeURIComponent(build.trim())));

				if ( typeof con[0].user_save_form != 'undefined' && con[0].user_save_form=='save_form' )
				{
					$scope.Builder.Config.save_progress = true;
				}
				if ( typeof con[0].frame != 'undefined' && con[0].frame=='noframe' )
				{
					$scope.Builder.form_frame = 'hidden';
				}
				if ( typeof con[0].bg_image != 'undefined' && con[0].bg_image!=''  )
				{
					$scope.Builder.form_background_custom_image = con[0].bg_image;
				}
				if ( typeof con[0].number_spin != 'undefined' && con[0].number_spin=='spin' )
				{
					$scope.Builder.Config.spin_effect = true;
				}
				if ( typeof con[0].allow_multi != 'undefined' && con[0].allow_multi=='no_allow_multi' )
				{
					$scope.Builder.Config.disable_multiple = true;
				}
				if ( typeof con[0].placeholder != 'undefined' && con[0].placeholder=='placeholder' )
				{
					$scope.Builder.label_style = 'placeholder';
				}
				if ( typeof con[0].multi_error != 'undefined' )
				{
					$scope.Builder.Config.disable_multiple_message = con[0].multi_error;
				}

				if ( typeof con[0].error_gen != 'undefined' )
				{
					$scope.Builder.Config.Messages.failed = con[0].error_gen;
				}
				if ( typeof con[0].success_msg != 'undefined' )
				{
					$scope.Builder.Config.Messages.success = con[0].success_msg;
				}
				if ( typeof con[0].error_email != 'undefined' )
				{
					$scope.Builder.Config.Messages.allow_email = con[0].error_email;
				}
				if ( typeof con[0].error_only_integers != 'undefined' )
				{
					$scope.Builder.Config.Messages.allow_numbers = con[0].error_only_integers;
				}
				if ( typeof con[0].error_only_alpha != 'undefined' )
				{
					$scope.Builder.Config.Messages.allow_alphabets = con[0].error_only_alpha;
				}
				if ( typeof con[0].error_only_alnum != 'undefined' )
				{
					$scope.Builder.Config.Messages.allow_alphanumeric = con[0].error_only_alnum;
				}
				if ( typeof con[0].error_required != 'undefined' )
				{
					$scope.Builder.Config.Messages.is_required = con[0].error_required;
				}
				if ( typeof con[0].error_min != 'undefined' )
				{
					con[0].error_min = con[0].error_min.replace('[min_chars]','[x]');
					$scope.Builder.Config.Messages.min_char = con[0].error_min;
				}
				if ( typeof con[0].error_max != 'undefined' )
				{
					con[0].error_max = con[0].error_max.replace('[max_chars]','[x]');
					$scope.Builder.Config.Messages.max_char = con[0].error_max;
				}


				if ( typeof con[0].autoreply_s != 'undefined' )
				{
					$scope.Builder.Config.autoresponder.email_subject = con[0].autoreply_s;
				}
				if ( typeof con[0].email_sub != 'undefined' )
				{
					$scope.Builder.Config.notifications.email_subject = con[0].email_sub;
				}
				if ( typeof con[0].mail_type != 'undefined' && con[0].mail_type=='smtp' )
				{
					$scope.Builder.Config.notifications._method = 'smtp';
				}

				if ( typeof con[0].from_name != 'undefined' )
				{
					$scope.Builder.Config.notifications.general_sender_name = con[0].from_name;
				}
				if ( typeof con[0].smtp_username != 'undefined' )
				{
					$scope.Builder.Config.notifications.smtp_sender_username = con[0].smtp_username;
				}
				if ( typeof con[0].smtp_pass != 'undefined' )
				{
					$scope.Builder.Config.notifications.smtp_sender_password = con[0].smtp_pass;
				}
				if ( typeof con[0].smtp_host != 'undefined' )
				{
					$scope.Builder.Config.notifications.smtp_sender_host = con[0].smtp_host;
				}
				if ( typeof con[0].smtp_port != 'undefined' )
				{
					$scope.Builder.Config.notifications.smtp_sender_port = con[0].smtp_port;
				}
				if ( typeof con[0].email_body != 'undefined' )
				{
					con[0].email_body = con[0].email_body.replace(/\n/g,"<br>");
					$scope.Builder.Config.notifications.email_body = con[0].email_body;
				}
				if ( typeof con[0].if_ssl != 'undefined' && con[0].if_ssl=='ssl' )
				{
					$scope.Builder.Config.notifications.smtp_sender_security = 'ssl';
				}
				if ( typeof con[0].if_ssl != 'undefined' && con[0].if_ssl=='tls' )
				{
					$scope.Builder.Config.notifications.smtp_sender_security = 'tls';
				}

				if ( typeof con[0].autoreply_name != 'undefined' )
				{
					$scope.Builder.Config.autoresponder.email_sender_name = con[0].autoreply_name;
				}
				if ( typeof con[0].autoreply_email != 'undefined' )
				{
					$scope.Builder.Config.autoresponder.email_sender_email = con[0].autoreply_email;
				}
				if ( typeof con[0].autoreply_s != 'undefined' )
				{
					$scope.Builder.Config.autoresponder.email_subject = con[0].autoreply_s;
				}
				if ( typeof con[0].autoreply_s != 'undefined' )
				{
					con[0].autoreply = con[0].autoreply.replace(/\n/g,'<br>');
					$scope.Builder.Config.autoresponder.email_body = con[0].autoreply;
				}

				if ( typeof con[0].from_email != 'undefined' )
				{
					$scope.Builder.Config.notifications.general_sender_email = con[0].from_email;
				}
				if ( typeof con[0].placeholder != 'undefined' && con[0].placeholder == 'placeholder' )
				{
					$scope.Builder.label_style = 'placeholder';
				}
				if ( typeof con[0].block_label != 'undefined' && con[0].block_label == 'block_label' )
				{
					$scope.Builder.label_style = 'block';
				}
				if ( typeof con[0].allow_multi != 'undefined' && con[0].allow_multi == 'allow_multi' )
				{
					$scope.Builder.Config.disable_multiple = true;
				}
				if ( typeof con[0].multi_error != 'undefined' )
				{
					$scope.Builder.Config.disable_multiple_message = con[0].multi_error;
				}				
				if ( typeof con[0].fw != 'undefined' )
				{
					$scope.Builder.form_width = con[0].fw;
				}
				if ( typeof con[0].bg_image != 'undefined' )
				{
					$scope.Builder.form_background_custom_image = con[0].bg_image.replace('url(','').replace(')','');
				}

				if ( typeof con[0].form_title != 'undefined' && con[0].form_title!='' )
				{
					$scope.addFormElement('heading');
					$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['field_value'] = con[0].form_title;
					$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['headingSize'] = 1.8;
					$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['headingWeight'] = true;
					if (typeof con[0].ftalign!='undefined')
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['headingAlignment'] = con[0].ftalign;
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['paddingTop'] = '15px';
					}
				}

				for (x in build)
				{
					if (build[x].el_b.indexOf('One-line Text Input')!=-1)
					{
						$scope.addFormElement('oneLineText');
					}
					else if (build[x].el_b.indexOf('Hidden Field')!=-1)
					{
						$scope.addFormElement('customText');
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['html'] = '';
						if (typeof build[x].hidval!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['field_value'] = build[x].hidval;
						}
					}
					else if (build[x].el_b.indexOf('Divider')!=-1)
					{
						$scope.addFormElement('heading');
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['field_value'] = build[x].cap1;
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['headingSize'] = 1.5;
					}
					else if (build[x].el_b.indexOf('Email Input')!=-1)
					{
						$scope.addFormElement('email');
						if (typeof build[x].autoreply!='undefined' && build[x].autoreply=='autoreply')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['autoresponder'] = true;
						}
						if (typeof build[x].replyto!='undefined' && build[x].replyto=='replyto')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['replyTo'] = true;
						}					
					}
					else if (build[x].el_b.indexOf('Paragraph Text Input')!=-1)
					{
						$scope.addFormElement('textarea');
					}
					else if (build[x].el_b.indexOf('Custom Text')!=-1)
					{
						$scope.addFormElement('customText');
						if (typeof build[x].customText!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['html'] = build[x].customText;
						}
						if (typeof build[x].hValue!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['field_value'] = build[x].hValue;
						}
					}
					else if (build[x].el_b.indexOf('Image')!=-1)
					{
						$scope.addFormElement('customText');
						if (typeof build[x].image!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['html'] = "<img src='"+build[x].image+"'/>";
						}
					}
					else if (build[x].el_b.indexOf('TimePicker')!=-1)
					{
						$scope.addFormElement('timepicker');
					}
					else if (build[x].el_b.indexOf('DatePicker')!=-1)
					{
						$scope.addFormElement('datepicker');
					}					
					else if (build[x].el_b.indexOf('Slider Group')!=-1)
					{
						$scope.addFormElement('slider');
						if (typeof build[x].min!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['range_min'] = build[x].min;
						}
						if (typeof build[x].max!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['range_max'] = build[x].max;
						}
					}
					else if (build[x].el_b.indexOf('Slider Range Group')!=-1)
					{
						$scope.addFormElement('slider');
						if (typeof build[x].min!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['range_min'] = build[x].min;
						}
						if (typeof build[x].max!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['range_max'] = build[x].max;
						}
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['range_true'] = true;
					}					
					else if (build[x].el_b.indexOf('Submit Button')!=-1)
					{
						$scope.addFormElement('submit');
					}
					else if (build[x].el_b.indexOf('File Upload')!=-1)
					{
						$scope.addFormElement('fileupload');
						if (typeof build[x].file_type!='undefined')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['allow_extensions'] = build[x].file_type.replace(/ /g, ', ');
						}
					}
					else if (build[x].el_b.indexOf('Choice Matrix')!=-1)
					{
						$scope.addFormElement('matrix');
						var temp = [];
						if (typeof build[x].matrix1!='undefined')
						{
							temp.push(build[x].matrix1);
						}
						if (typeof build[x].matrix2!='undefined')
						{
							temp.push(build[x].matrix2);
						}
						if (typeof build[x].matrix3!='undefined')
						{
							temp.push(build[x].matrix3);
						}
						if (typeof build[x].matrix4!='undefined')
						{
							temp.push(build[x].matrix4);
						}
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['options_list'] = temp.join("\n");
						var temp = build[x].el_f.split('opt in option[');
						var temp2 = temp[1].split(']');
						var temp3 = [];
						if ( typeof options[temp2[0]] != 'undefined' )
						{
							for ( y in options[temp2[0]].Drop )
							{
								temp3.push(options[temp2[0]].Drop[y].val);
							}
						}
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['matrix_rows'] = temp3.join("\n");
					}	
					else if (build[x].el_b.indexOf('Star Rating')!=-1 || build[x].el_b.indexOf('Smiley Rating')!=-1)
					{
						$scope.addFormElement('star');
						var temp = build[x].el_f.split('opt in option[');
						var temp2 = temp[1].split(']');
						var temp3 = [];
						if ( typeof options[temp2[0]] != 'undefined' )
						{
							for ( y in options[temp2[0]].Drop )
							{
								temp3.push(options[temp2[0]].Drop[y].val);
							}
						}
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['options_list'] = temp3.join("\n");
					}
					else if (build[x].el_b.indexOf('Radio Group')!=-1)
					{
						$scope.addFormElement('checkbox');
					}
					else if (build[x].el_b.indexOf('Dropdown Box')!=-1)
					{
						$scope.addFormElement('dropdown');
					}
					else if (build[x].el_b.indexOf('CheckBox Group')!=-1)
					{
						$scope.addFormElement('checkbox');
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['allow_multiple'] = 'checkbox';
					}
					else
					{
						console.log(build[x]);
					}

					if (typeof build[x].inst!='undefined')
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['instructions'] = build[x].inst;
					}
					if (typeof build[x].cap1!='undefined')
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['main_label'] = build[x].cap1;
					}
					if (typeof build[x].options_raw!='undefined')
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['options_list'] = build[x].options_raw;
					}					
					if (typeof build[x].uploadtext!='undefined')
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['button_label'] = build[x].uploadtext;
					}					
					if (typeof build[x].cap2!='undefined')
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['sub_label'] = build[x].cap2;
					}
					if (typeof build[x].req!='undefined' && build[x].req==1)
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['sub_label'] = true;
					}
					if (typeof build[x].default!='undefined' && build[x].default=='is_hidden')
					{
						$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['hidden_default'] = true;
					}
					if (typeof build[x].inline!='undefined')
					{
						if (build[x].inline=='inline4')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['field_width'] = '25%';
						}
						if (build[x].inline=='inline3')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['field_width'] = '33.3%';
						}
						if (build[x].inline=='inline2')
						{
							$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['elementDefaults']['field_width'] = '50%';
						}
					}
					$scope.Builder.FormElements[0][$scope.Builder.FormElements[0].length-1]['show_options'] = false;				
				}
			}
			else
			{
				var raw_builder = inflate(decodeURIComponent(response.builder.trim()));
				if (response.old_url!=false)
				{
					var reg = new RegExp(response.old_url,"g");
					raw_builder = raw_builder.replace(reg, response.new_url);
				}
				$scope.Builder = jQuery.evalJSON(raw_builder);
			}
			if (response.addons==null || response.addons.trim()=='')
			{
				$scope.Addons = {};
			}
			else
			{
				$scope.Addons = jQuery.evalJSON(decodeURIComponent(response.addons));
			}
			if (response.meta_builder!=null && response.meta_builder!='')
			{
				var raw_meta = response.meta_builder;
				if (response.old_url!=false)
				{
					var reg = new RegExp(response.old_url,"g");
					raw_meta = raw_meta.replace(reg, response.new_url);
				}
				$scope.Builder.Config = jQuery.evalJSON(raw_meta);
			}
			if (response.name)
			{
				$scope.Builder.Config.form_name = response.name;
			}
			$scope.Options = {};
			$scope.Builder.Config = $scope.Builder.Config || {};
			$scope.Builder.Config.autoresponder = $scope.Builder.Config.autoresponder || {};
			$scope.Builder.Config.Messages = $scope.Builder.Config.Messages || {};
			$scope.Builder.Config.notifications = $scope.Builder.Config.notifications || {};

			if(typeof $scope.Builder.FormElements[0].element != 'undefined')
			{
				var temp = $scope.Builder.FormElements;
				$scope.Builder.FormElements = [];
				$scope.Builder.FormElements[0] = temp;
			}

			$scope.Options.show_options = false;
			$scope.Options.show_addons = false;
			$scope.Options.show_styling = false;
			$scope.Options.show_logic = false;

			$scope.Builder.Config.decimal_separator = $scope.Builder.Config.decimal_separator==undefined ? '.' : $scope.Builder.Config.decimal_separator;
			$scope.Builder.Config.thousand_separator = $scope.Builder.Config.thousand_separator==undefined ? '' : $scope.Builder.Config.thousand_separator;

			$scope.Builder.label_style = $scope.Builder.label_style==undefined ? 'inline' : $scope.Builder.label_style;
			$scope.Builder.form_width = $scope.Builder.form_width==undefined ? '420px' : $scope.Builder.form_width;
			if ( $scope.Builder.form_width.match(/^[0-9]+$/) != null )
			{
				$scope.Builder.form_width = $scope.Builder.form_width + 'px';
			}
			$scope.Builder.form_frame = $scope.Builder.form_frame==undefined ? 'visible' : $scope.Builder.form_frame;
			$scope.Builder.font_size = $scope.Builder.font_size==undefined ? 100 : $scope.Builder.font_size;
			$scope.Builder.Config.font_color = $scope.Builder.Config.font_color==undefined ? '#666666' : $scope.Builder.Config.font_color;
			$scope.Builder.Config.field_font_color = $scope.Builder.Config.field_font_color || '#777';

			$scope.Builder.Config.webhook_method = $scope.Builder.Config.webhook_method || 'POST';

			$scope.Builder.nextText = $scope.Builder.nextText==undefined ? 'Next' : $scope.Builder.nextText;
			$scope.Builder.prevText = $scope.Builder.prevText==undefined ? 'Previous' : $scope.Builder.prevText;

			$scope.Builder.Config.font_family = $scope.Builder.Config.font_family==undefined ? 'inherit' : $scope.Builder.Config.font_family;
			$scope.Builder.form_internal_alignment = $scope.Builder.form_internal_alignment==undefined ? 'left' : $scope.Builder.form_internal_alignment;

			$scope.Builder.Config.Messages.is_required = $scope.Builder.Config.Messages.is_required || 'Required';
			$scope.Builder.Config.Messages.is_invalid = $scope.Builder.Config.Messages.is_invalid || 'Invalid';
			$scope.Builder.Config.Messages.min_char = $scope.Builder.Config.Messages.min_char || 'Min [x] characters required';
			$scope.Builder.Config.Messages.max_char = $scope.Builder.Config.Messages.max_char || 'Max [x] characters allowed';
			$scope.Builder.Config.Messages.min_files = $scope.Builder.Config.Messages.min_files || 'Min [x] file(s) required';
			$scope.Builder.Config.Messages.max_files = $scope.Builder.Config.Messages.max_files || 'Max [x] file(s) allowed';
			$scope.Builder.Config.Messages.allow_email = $scope.Builder.Config.Messages.allow_email || 'Invalid Email';
			$scope.Builder.Config.Messages.allow_url = $scope.Builder.Config.Messages.allow_url || 'Invalid URL';
			$scope.Builder.Config.Messages.allow_regexp = $scope.Builder.Config.Messages.allow_regexp || 'Invalid Expression';
			$scope.Builder.Config.Messages.allow_alphabets = $scope.Builder.Config.Messages.allow_alphabets || 'Only alphabets';
			$scope.Builder.Config.Messages.allow_numbers = $scope.Builder.Config.Messages.allow_numbers || 'Only numbers';
			$scope.Builder.Config.Messages.allow_alphanumeric = $scope.Builder.Config.Messages.allow_alphanumeric || 'Should be alphanumeric';
			$scope.Builder.Config.Messages.failed = $scope.Builder.Config.Messages.failed || 'Please correct the errors and try again';
			$scope.Builder.Config.Messages.success = $scope.Builder.Config.Messages.success || 'Message received';

			$scope.Builder.Config.autoresponder.email_body = $scope.Builder.Config.autoresponder.email_body || '<p>Hello [Name],</p><p><br></p><p>We have received your submission. Here are the details you have submitted to us:</p><p>[Form Content]</p><p><br></p><p>Regards,</p><p>Nishant</p>';
			$scope.Builder.Config.autoresponder.email_subject = $scope.Builder.Config.autoresponder.email_subject || 'Thank you for your submission';

			$scope.Builder.Config.notifications._method = $scope.Builder.Config.notifications._method || 'php';
			$scope.Builder.Config.notifications.form_layout = $scope.Builder.Config.notifications.form_layout || false;
			$scope.Builder.Config.notifications.email_body = $scope.Builder.Config.notifications.email_body || '<p>Hello,</p><p><br></p><p>You have received a new form submission for the form [Form Name]. Here are the details:</p><p>[Form Content]</p><p><br></p><p>Page: [URL]<br>Unique ID: #[Entry ID]<br>Date: [Date]<br>Time: [Time]</p>';
			$scope.Builder.Config.notifications.email_subject = $scope.Builder.Config.notifications.email_subject || '[Form Name] - New Form Submission';

			var f3_activated = getURLParameter('f3_activated');
			if (f3_activated!=null)
			{
				$scope.Options.show_addons = true;
				setTimeout(function(){
					jQuery('.fc_highlight').slideDown();
				}, 1500);
				setTimeout(function(){
					jQuery('.fc_highlight').removeClass('fc_highlight');
				}, 3000);
			}

			jQuery('.form-builder-cover').removeClass('hide-form');
			setTimeout(function(){
				jQuery('.dropdown-cover select').trigger('change');
			}, 300);
			setTimeout(function(){
				jQuery('.form-cover-builder').removeClass('hide-form-options');
			}, 1000);
			$scope.$watch('Builder.form_width', function(newValue, oldValue) {
				width = jQuery('.fc-form').width();
				$scope.Builder.form_width_nos = parseInt(width)+760;
			});
			$scope.$watch('Builder.Config.font_family', function(newValue, oldValue) {
				if ( typeof $scope.Builder.Config.font_family!='undefined' && $scope.Builder.Config.font_family.indexOf('Arial')==-1 && $scope.Builder.Config.font_family.indexOf('Courier')==-1 && $scope.Builder.Config.font_family.indexOf('sans-serif')==-1 && $scope.Builder.Config.font_family.indexOf('inherit')==-1 )
				{
					jQuery('head').append("<link href='"+(location.protocol=='http:'?'http:':'https:')+"//fonts.googleapis.com/css?family="+($scope.Builder.Config.font_family.replace(/ /g,'+'))+":400,600,700' rel='stylesheet' type='text/css'>");
				}
			});
			$scope.$watch('Color_scheme', function(){
				if (typeof $scope.Color_scheme!='undefined')
				{
					$scope.Builder.Config.color_scheme_font = '#fff';
					$scope.Builder.Config.font_color = '#666';
					$scope.Builder.Config.field_font_color = '#777';
					$scope.Builder.Config.color_field_background = '#fafafa';
					$scope.Builder.Config.color_scheme_checkbox = $scope.Color_scheme;
					$scope.Builder.Config.color_scheme_button = $scope.Color_scheme;
					$scope.Builder.Config.color_scheme_step = $scope.Color_scheme;
					setTimeout(function(){
						jQuery('.custom-color .wp-color-picker').trigger('change');
					},100);
				}
			});

			$scope.$watch('Builder.Config.color_scheme_button', function(){
				$scope.Builder.Config.color_scheme_button_dark = shadeColor($scope.Builder.Config.color_scheme_button,-12);
				setTimeout(function(){
					jQuery('.custom-color .wp-color-picker').trigger('change');
				},100);
			});
			$scope.$watch('Builder.Config.color_scheme_step', function(){
				$scope.Builder.Config.color_scheme_step_dark = shadeColor($scope.Builder.Config.color_scheme_step,-12);
				setTimeout(function(){
					jQuery('.custom-color .wp-color-picker').trigger('change');
				},100);
			});

			$scope.$watch('Builder.form_background_raw', function(newValue, oldValue) {
				if ( typeof $scope.Builder.form_background_raw!='undefined' && $scope.Builder.form_background_raw!='' && ( typeof $scope.Builder.form_background_custom_image=='undefined' || $scope.Builder.form_background_custom_image=='' ) )
				{
					$scope.Builder.form_background = $scope.Builder.form_background_raw;
				}
			});
			$scope.$watch('Builder.form_background_custom_image', function(newValue, oldValue) {
				if ( typeof $scope.Builder.form_background_custom_image!='undefined' && $scope.Builder.form_background_custom_image!='' )
				{
					$scope.Builder.form_background = "url("+$scope.Builder.form_background_custom_image+")";
					$scope.Builder.form_background_raw = '';
				}
			});
			
			$scope.Pristine = $scope.Builder.FormElements;
			var initY = 0;
			$scope.toX = 0;
			for (x in $scope.Builder.FormElements)
			{
				$scope.$watchCollection('Builder.FormElements['+x+']', function(newCol, oldCol, scope) {
					$scope.applyLogicFix();
				});
				$scope.$watchCollection('Builder.FormElements', function(newCol, oldCol, scope) {
					$scope.sortableOptions = [];
					for ( x in $scope.Builder.FormElements )
					{
						$scope.sortableOptions.push(createOptions(x));
					}
				});

				for ( y in $scope.Builder.FormElements[x] )
				{
					$scope.$watchCollection('Builder.FormElements['+x+']['+y+'].elementDefaults.main_label', function(newCol, oldCol, scope) {
						if ( $scope.toX < initY )
						{
							$scope.toX++;
						}
						if ( $scope.toX == initY )
						{
							$scope.updateListOfFields();
						}
					});
					initY++;
				}
			}

			$scope.$watch('Options.show_options', function(newValue, oldValue) {
				setTimeout(function(){fixAutosize();},500);
				if ( oldValue==true && newValue==false )
				{
					$scope.Options.show_options = 'hiding';
					setTimeout(function(){
						$scope.$apply(function(){$scope.Options.show_options = false;});
					}, 350);
				}
			});
			$scope.$watch('Options.show_styling', function(newValue, oldValue) {
				setTimeout(function(){fixAutosize();},500);
				if ( oldValue==true && newValue==false )
				{
					$scope.Options.show_styling = 'hiding';
					setTimeout(function(){
						$scope.$apply(function(){$scope.Options.show_styling = false;});
					}, 350);
				}
			});
			$scope.$watch('Options.show_addons', function(newValue, oldValue) {
				setTimeout(function(){fixAutosize();},500);
				if ( oldValue==true && newValue==false )
				{
					$scope.Options.show_addons = 'hiding';
					setTimeout(function(){
						$scope.$apply(function(){$scope.Options.show_addons = false;});
					}, 350);
				}
			});
			$scope.$watch('Options.show_logic', function(newValue, oldValue) {
				setTimeout(function(){fixAutosize();},500);
				if ( oldValue==true && newValue==false )
				{
					$scope.Options.show_logic = 'hiding';
					setTimeout(function(){
						$scope.$apply(function(){$scope.Options.show_logic = false;});
					}, 350);
				}
			});
		});
}

$scope.FormElements();

$scope.builderInit = function(){
	builderInit();
}

$scope.clearCustom = function(){
	$scope.Builder.form_background_custom_image = '';
	jQuery('.color-schemes .color-picker').val('');
}

$scope.updateListOfFields = function(){
	$scope.listOfFields = [];
	var i = 0;
	for (a in $scope.Builder.FormElements)
	{
		if ( typeof $scope.Builder.FormElements[a] != 'object' ) { continue; }
		for (b in $scope.Builder.FormElements[a])
		{
			if ( typeof $scope.Builder.FormElements[a][b] != 'object' ) { continue; }
			i++;
			$scope.listOfFields.push({
				identifier: $scope.Builder.FormElements[a][b].identifier,
				label: $scope.Builder.FormElements[a][b].elementDefaults.main_label
			});
		}
	}
	window.last_checked_fields_nos = i;
	window.last_save_fields_nos = typeof window.last_save_fields_nos == 'undefined' ? i : window.last_save_fields_nos;
}
$scope.applyLogicFix = function(){
	for (x in $scope.Builder.Config.Logic)
	{
		applySelectFix('select_fix_'+x, $scope.Builder.Config.Logic[x][1][0][4]);
		for (y in $scope.Builder.Config.Logic[x][0])
		{
			applySelectFix('select_fix_'+x+'_'+y, $scope.Builder.Config.Logic[x][0][y][0]);
		}
	}
}
$scope.saveForm = function(followup){
	if (followup=='preview')
	{
		if (typeof previewForm=='undefined')
		{
			previewForm = window.open(FC.baseurl+'/form-view/'+FC.form_id+'?preview=true', 'myWindow');
		}
		else
		{
			previewForm = window.open(FC.baseurl+'/form-view/'+FC.form_id+'?preview=true', 'myWindow');
			if (previewForm.document.getElementById('form-cover')!=null)
			{
				previewForm.document.getElementById('form-cover').innerHTML = "<span class='fc-spinner form-spinner small' style='display: block; margin: 150px auto'><div class='bounce1'></div><div class='bounce2'></div><div class='bounce3'></div></span>";
			}
		}
		previewForm.location = FC.baseurl+'/form-view/'+FC.form_id+'?preview=true', 'myWindow';
	}
	saveFormJQuery($scope.Builder, $scope.Addons, $scope.addField, function(it_worked){
		if (followup=='plugin_installed' && it_worked==true)
		{
			window.location.assign(window.location.href+'&f3_activated='+window.plugin_installed);
		}
	});
}
$scope.toggleOptions = function($parent, $index)
{
	$scope.Builder.FormElements[$parent][$index].show_options = !$scope.Builder.FormElements[$parent][$index].show_options;
	var open = false;
	for (page in $scope.Builder.FormElements)
	{
		for (element in $scope.Builder.FormElements[page])
		{
			if ($scope.Builder.FormElements[page][element].show_options==true){var open = true;}
		}
	}
	if (open==true)
	{
		jQuery('.fc-form').addClass('options-fade');
	}
	else
	{
		jQuery('.fc-form').removeClass('options-fade');
	}
}
$scope.addLogic = function()
{
	if (typeof $scope.Builder.Config.Logic=='undefined')
	{
		$scope.Builder.Config.Logic = [];
	}
	$scope.Builder.Config.Logic.push([]);
	var len = $scope.Builder.Config.Logic.length-1;
	$scope.Builder.Config.Logic[len][0] = [];
	$scope.Builder.Config.Logic[len][0].push([]);
	$scope.Builder.Config.Logic[len][1] = [];
	$scope.Builder.Config.Logic[len][1].push([]);
	$scope.Builder.Config.Logic[len][2] = 'and';
}
$scope.removeLogic = function($index)
{
	$scope.Builder.Config.Logic.splice($index, 1);
}
$scope.addLogicAction = function($index)
{
	$scope.Builder.Config.Logic[$index][0].push([]);
	var len = $scope.Builder.Config.Logic[$index][0].length-2;	
}
$scope.removeLogicAction = function($parent, $index)
{
	$scope.Builder.Config.Logic[$parent][0].splice($index, 1);
}
$scope.addLogicResult = function($index)
{
	$scope.Builder.Config.Logic[$index][1].push([]);
}
$scope.removeLogicResult = function($parent, $index)
{
	$scope.Builder.Config.Logic[$parent][1].splice($index, 1);
}
$scope.removeFormElement = function ($parent, $index)
{
	$scope.Builder.FormElements[$parent].splice($index, 1);
	var open = false;
	for (page in $scope.Builder.FormElements)
	{
		for (element in $scope.Builder.FormElements[page])
		{
			if ($scope.Builder.FormElements[page][element].show_options==true){var open = true;}
		}
	}
	if (open==true)
	{
		jQuery('.fc-form').addClass('options-fade');
	}
	else
	{
		jQuery('.fc-form').removeClass('options-fade');
	}
	$scope.updateListOfFields();
}
$scope.duplicateFormElement = function ($parent, $index)
{
	$scope.Builder.FormElements[$parent].splice($index,0,angular.copy($scope.Builder.FormElements[$parent][$index]));
	var position = $index + 1;
	$scope.Builder.elements_counter = $scope.Builder.elements_counter + 1;
	$scope.Builder.FormElements[$parent][position].elementDefaults.identifier = 'field'+$scope.Builder.elements_counter;
	$scope.Builder.FormElements[$parent][position].identifier = 'field'+$scope.Builder.elements_counter;
	$scope.updateListOfFields();
}
$scope.addCountries = function ($parent, $index)
{
	var countries = "AF==Afghanistan\nAL==Albania\nDZ==Algeria\nAS==American Samoa\nAD==Andorra\nAO==Angola\nAI==Anguilla\nAQ==Antarctica\nAG==Antigua And Barbuda\nAR==Argentina\nAM==Armenia\nAW==Aruba\nAU==Australia\nAT==Austria\nAZ==Azerbaijan\nBS==Bahamas\nBH==Bahrain\nBD==Bangladesh\nBB==Barbados\nBY==Belarus\nBE==Belgium\nBZ==Belize\nBJ==Benin\nBM==Bermuda\nBT==Bhutan\nBO==Bolivia\nBA==Bosnia And Herzegovina\nBW==Botswana\nBV==Bouvet Island\nBR==Brazil\nIO==British Indian Ocean Territory\nBN==Brunei Darussalam\nBG==Bulgaria\nBF==Burkina Faso\nBI==Burundi\nKH==Cambodia\nCM==Cameroon\nCA==Canada\nCV==Cape Verde\nKY==Cayman Islands\nCF==Central African Republic\nTD==Chad\nCL==Chile\nCN==China\nCX==Christmas Island\nCC==Cocos (keeling) Islands\nCO==Colombia\nKM==Comoros\nCG==Congo\nCD==Congo, The Democratic Republic Of The\nCK==Cook Islands\nCR==Costa Rica\nCI==Cote D'ivoire\nHR==Croatia\nCU==Cuba\nCY==Cyprus\nCZ==Czech Republic\nDK==Denmark\nDJ==Djibouti\nDM==Dominica\nDO==Dominican Republic\nTP==East Timor\nEC==Ecuador\nEG==Egypt\nSV==El Salvador\nGQ==Equatorial Guinea\nER==Eritrea\nEE==Estonia\nET==Ethiopia\nFK==Falkland Islands (malvinas)\nFO==Faroe Islands\nFJ==Fiji\nFI==Finland\nFR==France\nGF==French Guiana\nPF==French Polynesia\nTF==French Southern Territories\nGA==Gabon\nGM==Gambia\nGE==Georgia\nDE==Germany\nGH==Ghana\nGI==Gibraltar\nGR==Greece\nGL==Greenland\nGD==Grenada\nGP==Guadeloupe\nGU==Guam\nGT==Guatemala\nGN==Guinea\nGW==Guinea-bissau\nGY==Guyana\nHT==Haiti\nHM==Heard Island And Mcdonald Islands\nVA==Holy See (vatican City State)\nHN==Honduras\nHK==Hong Kong\nHU==Hungary\nIS==Iceland\nIN==India\nID==Indonesia\nIR==Iran, Islamic Republic Of\nIQ==Iraq\nIE==Ireland\nIL==Israel\nIT==Italy\nJM==Jamaica\nJP==Japan\nJO==Jordan\nKZ==Kazakstan\nKE==Kenya\nKI==Kiribati\nKP==Korea, Democratic People's Republic Of\nKR==Korea, Republic Of\nKV==Kosovo\nKW==Kuwait\nKG==Kyrgyzstan\nLA==Lao People's Democratic Republic\nLV==Latvia\nLB==Lebanon\nLS==Lesotho\nLR==Liberia\nLY==Libyan Arab Jamahiriya\nLI==Liechtenstein\nLT==Lithuania\nLU==Luxembourg\nMO==Macau\nMK==Macedonia, The Former Yugoslav Republic Of\nMG==Madagascar\nMW==Malawi\nMY==Malaysia\nMV==Maldives\nML==Mali\nMT==Malta\nMH==Marshall Islands\nMQ==Martinique\nMR==Mauritania\nMU==Mauritius\nYT==Mayotte\nMX==Mexico\nFM==Micronesia, Federated States Of\nMD==Moldova, Republic Of\nMC==Monaco\nMN==Mongolia\nMS==Montserrat\nME==Montenegro\nMA==Morocco\nMZ==Mozambique\nMM==Myanmar\nNA==Namibia\nNR==Nauru\nNP==Nepal\nNL==Netherlands\nAN==Netherlands Antilles\nNC==New Caledonia\nNZ==New Zealand\nNI==Nicaragua\nNE==Niger\nNG==Nigeria\nNU==Niue\nNF==Norfolk Island\nMP==Northern Mariana Islands\nNO==Norway\nOM==Oman\nPK==Pakistan\nPW==Palau\nPS==Palestinian Territory, Occupied\nPA==Panama\nPG==Papua New Guinea\nPY==Paraguay\nPE==Peru\nPH==Philippines\nPN==Pitcairn\nPL==Poland\nPT==Portugal\nPR==Puerto Rico\nQA==Qatar\nRE==Reunion\nRO==Romania\nRU==Russian Federation\nRW==Rwanda\nSH==Saint Helena\nKN==Saint Kitts And Nevis\nLC==Saint Lucia\nPM==Saint Pierre And Miquelon\nVC==Saint Vincent And The Grenadines\nWS==Samoa\nSM==San Marino\nST==Sao Tome And Principe\nSA==Saudi Arabia\nSN==Senegal\nRS==Serbia\nSC==Seychelles\nSL==Sierra Leone\nSG==Singapore\nSK==Slovakia\nSI==Slovenia\nSB==Solomon Islands\nSO==Somalia\nZA==South Africa\nGS==South Georgia And The South Sandwich Islands\nES==Spain\nLK==Sri Lanka\nSD==Sudan\nSR==Suriname\nSJ==Svalbard And Jan Mayen\nSZ==Swaziland\nSE==Sweden\nCH==Switzerland\nSY==Syrian Arab Republic\nTW==Taiwan, Province Of China\nTJ==Tajikistan\nTZ==Tanzania, United Republic Of\nTH==Thailand\nTG==Togo\nTK==Tokelau\nTO==Tonga\nTT==Trinidad And Tobago\nTN==Tunisia\nTR==Turkey\nTM==Turkmenistan\nTC==Turks And Caicos Islands\nTV==Tuvalu\nUG==Uganda\nUA==Ukraine\nAE==United Arab Emirates\nGB==United Kingdom\nUS==United States\nUM==United States Minor Outlying Islands\nUY==Uruguay\nUZ==Uzbekistan\nVU==Vanuatu\nVE==Venezuela\nVN==Viet Nam\nVG==Virgin Islands, British\nVI==Virgin Islands, U.s.\nWF==Wallis And Futuna\nEH==Western Sahara\nYE==Yemen\nZM==Zambia\nZW==Zimbabwe";
	$scope.Builder.FormElements[$parent][$index]['elementDefaults']['options_list'] = countries;
}
$scope.addNationalities = function ($parent, $index)
{
	var nationalities = "Afghan\nAlbanian\nAlgerian\nAmerican\nAndorran\nAngolan\nAntiguans\nArgentinean\nArmenian\nAustralian\nAustrian\nAzerbaijani\nBahamian\nBahraini\nBangladeshi\nBarbadian\nBarbudans\nBatswana\nBelarusian\nBelgian\nBelizean\nBeninese\nBhutanese\nBolivian\nBosnian\nBrazilian\nBritish\nBruneian\nBulgarian\nBurkinabe\nBurmese\nBurundian\nCambodian\nCameroonian\nCanadian\nCape Verdean\nCentral African\nChadian\nChilean\nChinese\nColombian\nComoran\nCongolese\nCongolese\nCosta Rican\nCroatian\nCuban\nCypriot\nCzech\nDanish\nDjibouti\nDominican\nDominican\nDutch\nDutchman\nDutchwoman\nEast Timorese\nEcuadorean\nEgyptian\nEmirian\nEquatorial Guinean\nEritrean\nEstonian\nEthiopian\nFijian\nFilipino\nFinnish\nFrench\nGabonese\nGambian\nGeorgian\nGerman\nGhanaian\nGreek\nGrenadian\nGuatemalan\nGuinea-Bissauan\nGuinean\nGuyanese\nHaitian\nHerzegovinian\nHonduran\nHungarian\nI-Kiribati\nIcelander\nIndian\nIndonesian\nIranian\nIraqi\nIrish\nIrish\nIsraeli\nItalian\nIvorian\nJamaican\nJapanese\nJordanian\nKazakhstani\nKenyan\nKittian and Nevisian\nKuwaiti\nKyrgyz\nLaotian\nLatvian\nLebanese\nLiberian\nLibyan\nLiechtensteiner\nLithuanian\nLuxembourger\nMacedonian\nMalagasy\nMalawian\nMalaysian\nMaldivan\nMalian\nMaltese\nMarshallese\nMauritanian\nMauritian\nMexican\nMicronesian\nMoldovan\nMonacan\nMongolian\nMoroccan\nMosotho\nMotswana\nMozambican\nNamibian\nNauruan\nNepalese\nNetherlander\nNew Zealander\nNi-Vanuatu\nNicaraguan\nNigerian\nNigerien\nNorth Korean\nNorthern Irish\nNorwegian\nOmani\nPakistani\nPalauan\nPanamanian\nPapua New Guinean\nParaguayan\nPeruvian\nPolish\nPortuguese\nQatari\nRomanian\nRussian\nRwandan\nSaint Lucian\nSalvadoran\nSamoan\nSan Marinese\nSao Tomean\nSaudi\nScottish\nSenegalese\nSerbian\nSeychellois\nSierra Leonean\nSingaporean\nSlovakian\nSlovenian\nSolomon Islander\nSomali\nSouth African\nSouth Korean\nSpanish\nSri Lankan\nSudanese\nSurinamer\nSwazi\nSwedish\nSwiss\nSyrian\nTaiwanese\nTajik\nTanzanian\nThai\nTogolese\nTongan\nTrinidadian or Tobagonian\nTunisian\nTurkish\nTuvaluan\nUgandan\nUkrainian\nUruguayan\nUzbekistani\nVenezuelan\nVietnamese\nWelsh\nWelsh\nYemenite\nZambian\nZimbabwean";
	$scope.Builder.FormElements[$parent][$index]['elementDefaults']['options_list'] = nationalities;
}
$scope.addLanguages = function ($parent, $index)
{
	var languages = "AF==Afrikanns\nSQ==Albanian\nAR==Arabic\nHY==Armenian\nEU==Basque\nBN==Bengali\nBG==Bulgarian\nCA==Catalan\nKM==Cambodian\nZH==Chinese (Mandarin)\nHR==Croation\nCS==Czech\nDA==Danish\nNL==Dutch\nEN==English\nET==Estonian\nFJ==Fiji\nFI==Finnish\nFR==French\nKA==Georgian\nDE==German\nEL==Greek\nGU==Gujarati\nHE==Hebrew\nHI==Hindi\nHU==Hungarian\nIS==Icelandic\nID==Indonesian\nGA==Irish\nIT==Italian\nJA==Japanese\nJW==Javanese\nKO==Korean\nLA==Latin\nLV==Latvian\nLT==Lithuanian\nMK==Macedonian\nMS==Malay\nML==Malayalam\nMT==Maltese\nMI==Maori\nMR==Marathi\nMN==Mongolian\nNE==Nepali\nNO==Norwegian\nFA==Persian\nPL==Polish\nPT==Portuguese\nPA==Punjabi\nQU==Quechua\nRO==Romanian\nRU==Russian\nSM==Samoan\nSR==Serbian\nSK==Slovak\nSL==Slovenian\nES==Spanish\nSW==Swahili\nSV==Swedish \nTA==Tamil\nTT==Tatar\nTE==Telugu\nTH==Thai\nBO==Tibetan\nTO==Tonga\nTR==Turkish\nUK==Ukranian\nUR==Urdu\nUZ==Uzbek\nVI==Vietnamese\nCY==Welsh\nXH==Xhosa";
	$scope.Builder.FormElements[$parent][$index]['elementDefaults']['options_list'] = languages;
}
$scope.addStates = function ($parent, $index)
{
	var states = "AL==Alabama\nAK==Alaska\nAZ==Arizona\nAR==Arkansas\nCA==California\nCO==Colorado\nCT==Connecticut\nDE==Delaware\nFL==Florida\nGA==Georgia\nHI==Hawaii\nID==Idaho\nIL==Illinois\nIN==Indiana\nIA==Iowa\nKS==Kansas\nKY==Kentucky\nLA==Louisiana\nME==Maine\nMD==Maryland\nMA==Massachusetts\nMI==Michigan\nMN==Minnesota\nMS==Mississippi\nMO==Missouri\nMT==Montana\nNE==Nebraska\nNV==Nevada\nNH==New Hampshire\nNJ==New Jersey\nNM==New Mexico\nNY==New York\nNC==North Carolina\nND==North Dakota\nOH==Ohio\nOK==Oklahoma\nOR==Oregon\nPA==Pennsylvania\nRI==Rhode Island\nSC==South Carolina\nSD==South Dakota\nTN==Tennessee\nTX==Texas\nUT==Utah\nVT==Vermont\nVA==Virginia\nWA==Washington\nWV==West Virginia\nWI==Wisconsin\nWY==Wyoming";
	$scope.Builder.FormElements[$parent][$index]['elementDefaults']['options_list'] = states;
}
$scope.addDays = function ($parent, $index)
{
	var days = "Sunday\nMonday\nTuesday\nWednesday\nThursday\nFriday\nSaturday";
	$scope.Builder.FormElements[$parent][$index]['elementDefaults']['options_list'] = days;
}
$scope.addMonths = function ($parent, $index)
{
	var months = "January\nFebruary\nMarch\nApril\nMay\nJune\nJuly\nAugust\nSeptember\nOctober\nNovember\nDecember";
	$scope.Builder.FormElements[$parent][$index]['elementDefaults']['options_list'] = months;
}
$scope.listIcons = [];
$scope.listIcons.push({'Name':'None','Value':'no-icon'});
$scope.listIcons.push({'Name':'Datepicker','Value':'icon-calendar'});
$scope.listIcons.push({'Name':'Search','Value':'icon-search'});
$scope.listIcons.push({'Name':'Email','Value':'icon-mail'});
$scope.listIcons.push({'Name':'Attach','Value':'icon-attach'});
$scope.listIcons.push({'Name':'Picture','Value':'icon-picture'});
$scope.listIcons.push({'Name':'Phone','Value':'icon-phone'});
$scope.listIcons.push({'Name':'Heart','Value':'icon-heart'});
$scope.listIcons.push({'Name':'Star','Value':'icon-star'});
$scope.listIcons.push({'Name':'Help','Value':'icon-help'});
$scope.listIcons.push({'Name':'Code','Value':'icon-code'});
$scope.listIcons.push({'Name':'Square','Value':'icon-check-empty'});
$scope.listIcons.push({'Name':'Circle','Value':'icon-circle-thin'});
$scope.listIcons.push({'Name':'Clock','Value':'icon-clock-1'});
$scope.listIcons.push({'Name':'Scissors','Value':'icon-scissors'});
$scope.listIcons.push({'Name':'Pencil','Value':'icon-pencil'});
$scope.listIcons.push({'Name':'Document','Value':'icon-doc-text'});
$scope.listIcons.push({'Name':'Cog','Value':'icon-cog'});
$scope.listIcons.push({'Name':'Move','Value':'icon-move'});
$scope.listIcons.push({'Name':'Cancel','Value':'icon-cancel'});
$scope.listIcons.push({'Name':'Thumb','Value':'icon-thumbs-up'});
$scope.listIcons.push({'Name':'Thumb','Value':'icon-thumbs-down'});
$scope.listIcons.push({'Name':'Facebook','Value':'icon-facebook-squared'});
$scope.listIcons.push({'Name':'Twitter','Value':'icon-twitter'});
$scope.listIcons.push({'Name':'Youtube','Value':'icon-youtube-play'});
$scope.listIcons.push({'Name':'Dropbox','Value':'icon-dropbox'});
$scope.listIcons.push({'Name':'Github','Value':'icon-github'});
$scope.listIcons.push({'Name':'Linkedin','Value':'icon-linkedin-squared'});
$scope.listIcons.push({'Name':'Pinterest','Value':'icon-pinterest-circled'});
$scope.listIcons.push({'Name':'WordPress','Value':'icon-wordpress'});
$scope.listIcons.push({'Name':'User','Value':'icon-user'});
$scope.listIcons.push({'Name':'Bookmark','Value':'icon-bookmark-empty'});
$scope.listIcons.push({'Name':'Home','Value':'icon-home'});
$scope.listIcons.push({'Name':'PDF','Value':'icon-file-pdf'});
$scope.listIcons.push({'Name':'Word','Value':'icon-file-word'});
$scope.listIcons.push({'Name':'Excel','Value':'icon-file-excel'});
$scope.listIcons.push({'Name':'Powerpoint','Value':'icon-file-powerpoint'});
$scope.listIcons.push({'Name':'Image','Value':'icon-file-image'});
$scope.listIcons.push({'Name':'Archive','Value':'icon-file-archive'});
$scope.listIcons.push({'Name':'Audio','Value':'icon-file-audio'});
$scope.listIcons.push({'Name':'Video','Value':'icon-file-video'});
$scope.listIcons.push({'Name':'Code','Value':'icon-file-code'});
$scope.listIcons.push({'Name':'Folder','Value':'icon-folder-empty'});
$scope.listIcons.push({'Name':'Mic','Value':'icon-mic'});
$scope.listIcons.push({'Name':'Volume','Value':'icon-volume-up'});
$scope.listIcons.push({'Name':'Lightbulb','Value':'icon-lightbulb'});
$scope.listIcons.push({'Name':'Laptop','Value':'icon-laptop'});
$scope.listIcons.push({'Name':'Tablet','Value':'icon-tablet'});
$scope.listIcons.push({'Name':'Mobile','Value':'icon-mobile'});
$scope.listIcons.push({'Name':'Globe','Value':'icon-globe'});
$scope.listIcons.push({'Name':'Briefcase','Value':'icon-briefcase'});
$scope.listIcons.push({'Name':'Off','Value':'icon-off'});
$scope.listIcons.push({'Name':'Smile','Value':'icon-smile'});
$scope.listIcons.push({'Name':'Frown','Value':'icon-frown'});
$scope.listIcons.push({'Name':'Meh','Value':'icon-meh'});

$scope.dateLang = ['af','ar-DZ','ar','az','be','bg','bs','ca','cs','cy-GB','da','de','el','en-AU','en-GB','en-NZ','eo','es','et','eu','fa','fi','fo','fr-CA','fr-CH','fr','gl','he','hi','hr','hu','hy','id','is','it-CH','it','ja','ka','kk','km','ko','ky','lb','lt','lv','mk','ml','ms','nb','nl-BE','nl','nn','no','pl','pt-BR','pt','rm','ro','ru','sk','sl','sq','sr-SR','sr','sv','ta','th','tj','tr','uk','vi','zh-CN','zh-HK','zh-TW'];

$scope.fieldHTMLTemplate = [];
$scope.fieldOptionTemplate = [];

$scope.fieldHTMLTemplate['heading'] = "<div style='background-color: {{element.elementDefaults.background_color}}' class='heading-cover field-cover'><div style='text-align: {{element.elementDefaults.headingAlignment}}; font-size: {{element.elementDefaults.headingSize}}em; padding-top: {{element.elementDefaults.paddingTop}}; padding-bottom: {{element.elementDefaults.paddingBottom}}; color: {{element.elementDefaults.font_color}}' class='bold-{{element.elementDefaults.headingWeight}}' compile='element.elementDefaults.field_value'></div><input type='hidden' data-field-id='{{element.elementDefaults.identifier}}' name='{{element.elementDefaults.identifier}}[]' value='{{element.elementDefaults.field_value}}'></div>";
$scope.fieldOptionTemplate['heading'] = "<label class='w-2'><span>Label</span><input fc-placeholder type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Heading Text</span><input sub-label type='text' ng-model='element.elementDefaults.field_value'></label><label class='w2-1'><span>Padding Top</span><input type='text' ng-model='element.elementDefaults.paddingTop' placeholder='10px'/></label><label class='w2-1'><span>Padding Bottom</span><input type='text' ng-model='element.elementDefaults.paddingBottom' placeholder='10px'/></label><div class='w-3 heading-sizes hide-checkbox'><span class='button active' ng-click='element.elementDefaults.headingSize = element.elementDefaults.headingSize + .1'>A+</span><span class='button active' ng-click='element.elementDefaults.headingSize = element.elementDefaults.headingSize - .1'>A-</span><label class='button' style='margin-left: 10px'>B<input type='checkbox' value='bold' ng-model='element.elementDefaults.headingWeight' update-label/></label><label class='button' style='margin-left: 10px'><i class='icon-align-left'></i><input type='radio' value='left' ng-model='element.elementDefaults.headingAlignment' update-label/></label><label class='button'><i class='icon-align-center'></i><input type='radio' value='center' ng-model='element.elementDefaults.headingAlignment' update-label/></label><label class='button'><i class='icon-align-right'></i><input type='radio' value='right' ng-model='element.elementDefaults.headingAlignment' update-label/></label></div><div class='w-3'><p style='width: 161px'>Font Color</p><input angular-color type='text' value='#fff' class='color-picker' ng-model='element.elementDefaults.font_color'></div><div class='w-3'><p style='width: 161px'>Background Color</p><input angular-color type='text' value='#fff' class='color-picker' ng-model='element.elementDefaults.background_color'></div><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['oneLineText'] = "<div class='oneLineText-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><input type='text' placeholder='{{element.elementDefaults.main_label_placeholder}}' data-field-id='{{element.elementDefaults.identifier}}' name='{{element.elementDefaults.identifier}}[]' data-min-char='{{element.elementDefaults.Validation.minChar}}' data-max-char='{{element.elementDefaults.Validation.maxChar}}' data-val-type='{{element.elementDefaults.Validation.allowed}}' data-regexp='{{element.elementDefaults.Validation.regexp}}' data-is-required='{{element.elementDefaults.required}}' data-allow-spaces='{{element.elementDefaults.Validation.spaces}}' class='validation-lenient' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='focus' data-html='true' data-input-mask='{{element.elementDefaults.mask}}' data-mask-placeholder='{{element.elementDefaults.maskPlaceholder}}'><i class='{{element.elementDefaults.selectedIcon}}'></i></div></div>";
$scope.fieldOptionTemplate['oneLineText'] = "<label class='w-1'><span>Label</span><input fc-placeholder type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-2'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><div class='w-1 icons-list'><div><span class='super'>Icon: </span><span ng-click='element.showIcons = !element.showIcons'><i class='{{element.elementDefaults.selectedIcon}}'></i></span><div class='hide-checkbox' ng-show='element.showIcons'><label ng-repeat='icon in listIcons'><input type='radio' name='{{element.elementDefaults.identifier}}_icon' update-label ng-model='element.elementDefaults.selectedIcon' value='{{icon.Value}}'/><i class='{{icon.Value}}'></i></label></div></div></div><label class='w2-1'><span>Input Mask</span><input type='text' ng-model='element.elementDefaults.mask'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='<strong>Common Masks:</strong><br><strong>US Telephone:</strong> (000) 000-0000<br><strong>Zip Code:</strong> 0000-000<br><strong>Social Security:</strong> 000-00-0000<br><strong>CPF:</strong> 000.000.000-00<br><strong>Legend:</strong><br><strong>0</strong> = allow 0 - 9<br><strong>A</strong> = allow a - z, or 0 - 9<br><strong>S</strong> = allow a - z' class='icon-help'></i></label><label class='w2-1'><span>Mask Placeholder</span><input type='text' ng-model='element.elementDefaults.maskPlaceholder'></label><label class='w-3'><span>Validation</span><select ng-model='element.elementDefaults.Validation.allowed'><option value=''>None</option><option value='alphabets'>Only Alphabets</option><option value='numbers'>Only Numbers</option><option value='alphanumeric'>Only Alphabets & Numbers</option><option value='url'>URL</option><option value='regexp'>RegEx</option></select></label><label ng-slide-toggle="+'"element.elementDefaults.Validation.allowed=='+"'regexp'"+'"'+" class='w-3'><span>RegEx</span><input type='text' ng-model='element.elementDefaults.Validation.regexp'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='<strong>Common RegExp:</strong><br><strong>/^[a-z0-9_-]{6,18}$/</strong>: allow only alphabets, numbers, underscore and hyphen, and between 6 to 18 characters.<br><strong>/^[a-z0-9-]+$/</strong>: allow only alphabets, numbers and hyphens.<br><strong>/^[a-zA-Z]*$/</strong>: alphabets only, lower or upper case<br><strong>/^[0-9]*$/</strong>: digits only' class='icon-help'></i></label><label class='w2-1'><span>Min Chars</span><input type='text' ng-model='element.elementDefaults.Validation.minChar'></label><label class='w2-1'><span>Max Chars</span><input type='text' ng-model='element.elementDefaults.Validation.maxChar'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.Validation.spaces'> Allow Spaces</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['email'] = "<div class='email-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><input placeholder='{{element.elementDefaults.main_label_placeholder}}' data-field-id='{{element.elementDefaults.identifier}}' type='text' data-val-type='email' data-is-required='{{element.elementDefaults.required}}' name='{{element.elementDefaults.identifier}}' class='validation-lenient' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='focus' data-html='true'><i class='icon-mail'></i></div></div>";
$scope.fieldOptionTemplate['email'] = "<label class='w-1'><span>Label</span><input fc-placeholder type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.autoresponder'> Send Autoresponder <a data-toggle='fc_modal' data-target='#autoresponder_modal'>(configure)</a></label></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.replyTo'> Set as Reply-To Address</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['password'] = "<div class='password-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><input placeholder='{{element.elementDefaults.main_label_placeholder}}' data-field-id='{{element.elementDefaults.identifier}}' type='password' data-min-char='{{element.elementDefaults.Validation.minChar}}' data-max-char='{{element.elementDefaults.Validation.maxChar}}' data-val-type='{{element.elementDefaults.Validation.allowed}}' data-regexp='{{element.elementDefaults.Validation.regexp}}' data-is-required='{{element.elementDefaults.required}}' name='{{element.elementDefaults.identifier}}' class='validation-lenient' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='focus' data-html='true'></div></div>";
$scope.fieldOptionTemplate['password'] = "<label class='w-1'><span>Label</span><input fc-placeholder type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w-3'><span>Validation</span><select ng-model='element.elementDefaults.Validation.allowed'><option value=''>None</option><option value='alphabets'>Only Alphabets</option><option value='numbers'>Only Numbers</option><option value='alphanumeric'>Only Alphabets & Numbers</option><option value='regexp'>RegEx</option></select></label><label ng-slide-toggle="+'"element.elementDefaults.Validation.allowed=='+"'regexp'"+'"'+" class='w-3'><span>RegEx</span><input type='text' ng-model='element.elementDefaults.Validation.regexp'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='<strong>Common RegExp:</strong><br><strong>/^[a-z0-9_-]{6,18}$/</strong>: allow only alphabets, numbers, underscore and hyphen, and between 6 to 18 characters.<br><strong>/^[a-z0-9-]+$/</strong>: allow only alphabets, numbers and hyphens.<br><strong>/^[a-zA-Z]*$/</strong>: alphabets only, lower or upper case<br><strong>/^[0-9]*$/</strong>: digits only' class='icon-help'></i></label><label class='w2-1'><span>Min Chars</span><input type='text' ng-model='element.elementDefaults.Validation.minChar'></label><label class='w2-1'><span>Max Chars</span><input type='text' ng-model='element.elementDefaults.Validation.maxChar'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.Validation.spaces'> Allow Spaces</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['textarea'] = "<div class='textarea-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><textarea data-field-id='{{element.elementDefaults.identifier}}' placeholder='{{element.elementDefaults.main_label_placeholder}}' class='validation-lenient' name='{{element.elementDefaults.identifier}}' value='' rows='{{element.elementDefaults.field_height}}' data-min-char='{{element.elementDefaults.Validation.minChar}}' data-max-char='{{element.elementDefaults.Validation.maxChar}}' data-is-required='{{element.elementDefaults.required}}' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='focus' data-html='true'></textarea><div class='count-{{element.elementDefaults.show_count}}'><span class='current-count'>0</span> / <span class='max-count'>{{element.elementDefaults.Validation.maxChar}}</span></div></div></div>";
$scope.fieldOptionTemplate['textarea'] = "<label class='w-1'><span>Label</span><input fc-placeholder type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w-1'><span>Rows</span><input type='text' ng-model='element.elementDefaults.field_height'></label><label class='w-1'><span>Min Chars</span><input type='text' ng-model='element.elementDefaults.Validation.minChar'></label><label class='w-1'><span>Max Chars</span><input type='text' ng-model='element.elementDefaults.Validation.maxChar'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.show_count'> Show Character Count</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['checkbox'] = "<div class='images-{{element.elementDefaults.allow_images}} checkbox-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><span class='error'></span><label ng-repeat='opt in element.elementDefaults.options_list_show' style='width: {{element.elementDefaults.option_width}}'><img src='{{element.elementDefaults.images_list_show[$index].url}}'/><input data-field-id='{{element.elementDefaults.identifier}}' type='{{element.elementDefaults.allow_multiple}}' data-is-required='{{element.elementDefaults.required}}' name='{{element.elementDefaults.identifier}}[]' value='{{opt.value}}' class='validation-lenient'><span compile='opt.show'></span></label></div></div>";
$scope.fieldOptionTemplate['checkbox'] = "<label class='w-1'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w-3'><input type='checkbox' value='true' ng-model='element.elementDefaults.allow_images'> Add Images</label><div class='images-{{element.elementDefaults.allow_images}}'><label class='w-3'><i class='icon-help-circled' tooltip data-toggle='tooltip' title='You can set the value of the checkbox different from the text, using this pattern: <br><strong>100==Apple</strong><br>Here, <strong>100</strong> would be the value, and <strong>Apple</strong> would be the text.<img style="+'"height: 50px"'+" src=" + '"'+FC.pluginurl+'/assets/images/value==text.png"' + "/>'></i><span>Options</span><a data-post-id='489' class='trigger-help read-more-textarea'>read more</a><textarea rows='5' ng-model='element.elementDefaults.options_list' checkbox-list></textarea></label><label class='w-3 w-3-images'><i class='icon-help-circled' tooltip data-toggle='tooltip' title='Paste the URL of an image here. This image will be assigned to the first option in your field.<br>Press enter, and paste another URL. This will be assigned to the second option, and so on ...'></i><span>Images</span><textarea rows='5' ng-model='element.elementDefaults.images_list' image-list></textarea></label></div><label class='w-3'><span>Option Width</span><input type='text' ng-model='element.elementDefaults.option_width'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.allow_multiple' ng-true-value='"+'"checkbox"'+"' ng-false-value='"+'"radio"'+"'> Allow Multiple Selections</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['dropdown'] = "<div class='dropdown-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><select data-is-required='{{element.elementDefaults.required}}' class='validation-lenient' data-field-id='{{element.elementDefaults.identifier}}' name='{{element.elementDefaults.identifier}}' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><option value='{{opt.value}}' ng-repeat='opt in element.elementDefaults.options_list_show'>{{opt.show}}</option></select></div></div>";
$scope.fieldOptionTemplate['dropdown'] = "<label class='w-1'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input fc-placeholder sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w-3'><i class='icon-help-circled' tooltip data-toggle='tooltip' title='You can set the value of the dropdown options different from the text, using this pattern: <br><strong>en==English</strong><br>Here, <strong>en</strong> would be the value, and <strong>English</strong> would be the text.<img style="+'"height: 50px"'+" src=" + '"'+FC.pluginurl+'/assets/images/value==text2.png"' + "/>'></i><span>Options</span><a data-post-id='489' class='trigger-help read-more-textarea'>read more</a><textarea rows='5' ng-model='element.elementDefaults.options_list' checkbox-list></textarea></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label><div class='w-3' style='white-space:normal'>Autofill: <a ng-click='addCountries($parent.$index, $index)'>Countries</a>, <a ng-click='addStates($parent.$index, $index)'>US States</a>, <a ng-click='addNationalities($parent.$index, $index)'>Nationalities</a>, <a ng-click='addLanguages($parent.$index, $index)'>Languages</a>, <a ng-click='addDays($parent.$index, $index)'>Days of the Week</a>, <a ng-click='addMonths($parent.$index, $index)'>Months</a></div>";

$scope.fieldHTMLTemplate['datepicker'] = "<div class='datepicker-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><input data-field-id='{{element.elementDefaults.identifier}}' data-field-id='{{element.elementDefaults.identifier}}' placeholder='{{element.elementDefaults.main_label_placeholder}}' type='text' class='validation-lenient' data-is-required='{{element.elementDefaults.required}}' datepicker data-date-format='{{element.elementDefaults.dateFormat}}' data-date-days='{{element.elementDefaults.dateDays}}' data-date-min='{{element.elementDefaults.minDate}}' data-date-min-alt='{{element.elementDefaults.minDateAlt}}' data-date-max-alt='{{element.elementDefaults.maxDateAlt}}' data-date-max='{{element.elementDefaults.maxDate}}' data-date-lang='{{element.elementDefaults.dateLang}}' name='{{element.elementDefaults.identifier}}' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='focus' data-html='true' ng-model='temp'><i class='icon-calendar'></i></div></div>";
$scope.fieldOptionTemplate['datepicker'] = "<label class='w-1'><span>Label</span><input fc-placeholder type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w2-1'><span>Lang</span><select ng-model='element.elementDefaults.dateLang'><option value='en'>English</option><option ng-repeat='lang in dateLang' ng-value='lang'>{{lang}}</option></select></label><label class='w2-1'><span>Format</span><select ng-model='element.elementDefaults.dateFormat'><option>M d, yy</option><option>d M yy</option><option>yy-mm-dd</option><option>dd/mm/yy</option><option>mm/dd/yy</option></select></label><label class='w2-1'><i class='icon-help-circled' tooltip data-toggle='tooltip' title='To set today as the min allowed date, type in:<br><strong>0</strong><br>To set day-before-yesterday as the min date, type in <br><strong>-2</strong>'></i><span>Min Date</span><input data-default-date='{{element.elementDefaults.minDate}}' type='text' data-date-format='yy-mm-dd' datepicker ng-model='element.elementDefaults.minDate'></label><label class='w2-1'><i class='icon-help-circled' tooltip data-toggle='tooltip' title='To set today as the max allowed date, type in:<br><strong>0</strong><br>To set day-after-tomorrow as the max date, type in <br><strong>2</strong>'></i><span>Max Date</span><input data-default-date='{{element.elementDefaults.maxDate}}' type='text' data-date-format='yy-mm-dd' datepicker ng-model='element.elementDefaults.maxDate' data-date-min='{{element.elementDefaults.minDate}}'></label><div class='w-3 hide-checkbox week-days'><strong>Days Allowed:</strong><br><label class='button'>Sunday<input type='checkbox' ng-model='element.elementDefaults.dateDays[0]' update-label/></label><label class='button'>Monday<input type='checkbox' ng-model='element.elementDefaults.dateDays[1]' update-label/></label><label class='button'>Tuesday<input type='checkbox' ng-model='element.elementDefaults.dateDays[2]' update-label/></label><label class='button'>Wednesday<input type='checkbox' ng-model='element.elementDefaults.dateDays[3]' update-label/></label><br><label class='button'>Thursday<input type='checkbox' ng-model='element.elementDefaults.dateDays[4]' update-label/></label><label class='button'>Friday<input type='checkbox' ng-model='element.elementDefaults.dateDays[5]' update-label/></label><label class='button'>Saturday<input type='checkbox' ng-model='element.elementDefaults.dateDays[6]' update-label/></label></div><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['customText'] = "<div class='absolute-{{element.elementDefaults.floating_type}} customText-cover field-cover' style='left: {{element.elementDefaults.leftPosition}}; top: {{element.elementDefaults.topPosition}};right: {{element.elementDefaults.rightPosition}};bottom: {{element.elementDefaults.bottomPosition}};color: {{element.elementDefaults.font_color}} !important; background-color: {{element.elementDefaults.background_color}}'><div class='full' compile='element.elementDefaults.html' style='text-align: {{element.elementDefaults.alignment}}'></div><input type='hidden' name='{{element.elementDefaults.identifier}}' value='{{element.elementDefaults.field_value}}' data-field-id='{{element.elementDefaults.identifier}}'></div>";
$scope.fieldOptionTemplate['customText'] = "<label class='w-2'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Field Value</span><input type='text' ng-model='element.elementDefaults.field_value'></label><div class='w-1' style='width: 20%; font-weight: bold'>Margin</div><div style='width: 80%' class='w-2 stuck-input'><span>Top</span><input type='text' placeholder='100px' ng-model='element.elementDefaults.topPosition'/><span>Right</span><input type='text' placeholder='100px' ng-model='element.elementDefaults.rightPosition'/><span>Bottom</span><input type='text' placeholder='100px' ng-model='element.elementDefaults.bottomPosition'/><span>Left</span><input type='text' placeholder='100px' ng-model='element.elementDefaults.leftPosition'/></div><div class='hide-checkbox'><div class='w-1' style='width: 20%; font-weight: bold; vertical-align: bottom'>Display</div><label style='width:35%;margin-right:4%' class='w-1'><img src='"+FC.pluginurl+'/assets/images/display-floating.png'+"'/><input type='radio' update-label name='pos_float_{{$index}}' ng-model='element.elementDefaults.floating_type' value='true'/> Floating</label><label style='width:35%' class='w-1'><img src='"+FC.pluginurl+'/assets/images/display-inline.png'+"'/><input type='radio' update-label name='pos_float_{{$index}}' ng-model='element.elementDefaults.floating_type' value='false'/> Inline</label></div><div class='w-3'><span style='position: relative; left: 10px; font-weight: bold; top: 8px; background: white; color: #777'>Text Content</span><text-angular class='textangular' ng-model='element.elementDefaults.html'></text-angular></div><div class='w-3'><p style='width: 192px'>Font Color</p><input angular-color type='text' value='#fff' class='color-picker' ng-model='element.elementDefaults.font_color'></div><div class='w-3'><p style='width: 192px'>Background Color</p><input angular-color type='text' value='#fff' class='color-picker' ng-model='element.elementDefaults.background_color'></div><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['submit'] = "<div class='animate-{{element.elementDefaults.doAnimate}} align-{{element.elementDefaults.alignment}} wide-{{element.elementDefaults.isWide}} submit-cover field-cover'><button type='submit' class='button submit-button'><span class='text'>{{element.elementDefaults.main_label}}</span><span class='spin-cover'><i style='color: {{element.elementDefaults.font_color}}' class='loading-icon icon-cog animate-spin'></i></span></button></div><div class='submit-response'></div><input type='text' class='required_field' name='website'>";
$scope.fieldOptionTemplate['submit'] = "<label class='w-2'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><div class='label w-3 hide-checkbox align-icons'><label class='button'><input type='radio' update-label name='{{element.elementDefaults.identifier}}_name' ng-model='element.elementDefaults.alignment' value='left'><i class='icon-align-left'></i></label><label class='button'><input type='radio' update-label name='{{element.elementDefaults.identifier}}_name' ng-model='element.elementDefaults.alignment' value='center'><i class='icon-align-center'></i></label><label class='button'><input type='radio' update-label name='{{element.elementDefaults.identifier}}_name' ng-model='element.elementDefaults.alignment' value='right'><i class='icon-align-right'></i></label></div><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.doAnimate'> Animate on click</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.isWide'> Wide Button</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['fileupload'] = "<div class='wide-{{element.elementDefaults.isWide}} fileupload-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><div class='button button-file fileupload-button' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><i class='icon-upload-cloud'></i><span>{{element.elementDefaults.button_label}}</span><input type='file' data-name-list='{{element.elementDefaults.identifier}}' name='files' multiple data-allow-extensions='{{element.elementDefaults.allow_extensions}}' data-min-files='{{element.elementDefaults.min_files}}' data-max-files='{{element.elementDefaults.max_files}}'/></div></div></div>";
$scope.fieldOptionTemplate['fileupload'] = "<label class='w-2'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-2'><span>Button</span><input type='text' ng-model='element.elementDefaults.button_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w-3'><span>Allowed Extensions</span><i class='icon-help-circled' tooltip data-toggle='tooltip' title='Enter the file extensions users are allowed to upload, separated by a comma.<br>Leave blank to allow all file-types.'></i><input type='text' placeholder='jpg, png, gif' ng-model='element.elementDefaults.allow_extensions'></label><label class='w2-1'><span>Min Files</span><input type='text' ng-model='element.elementDefaults.min_files'></label><label class='w2-1'><span>Max Files</span><input type='text' ng-model='element.elementDefaults.max_files'></label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['slider'] = "<div class='slider-cover field-cover show-scale-{{element.elementDefaults.scale_true}}'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><span class='error'></span><span class='range-min'>{{element.elementDefaults.prefix}}{{element.elementDefaults.range_min}}{{element.elementDefaults.suffix}}</span><span class='ui-slider-cover'><span range-min='{{element.elementDefaults.range_min}}' range-max='{{element.elementDefaults.range_max}}' range-step='{{element.elementDefaults.range_step}}' range-true='{{element.elementDefaults.range_true}}' data-prefix='{{element.elementDefaults.prefix}}' data-suffix='{{element.elementDefaults.suffix}}' slider></span></span><span class='range-max'>{{element.elementDefaults.prefix}}{{element.elementDefaults.range_max}}{{element.elementDefaults.suffix}}</span><input name='{{element.elementDefaults.identifier}}' data-field-id='{{element.elementDefaults.identifier}}' type='hidden' class='validation-lenient' data-is-required='{{element.elementDefaults.required}}'/></div></div>";
$scope.fieldOptionTemplate['slider'] = "<label class='w-1'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w-1'><span>Min</span><input type='text' ng-model='element.elementDefaults.range_min'></label><label class='w-1'><span>Max</span><input type='text' ng-model='element.elementDefaults.range_max'></label><label class='w-1'><span>Step</span><input type='text' ng-model='element.elementDefaults.range_step'></label><label class='w-1'><span>Prefix</span><input type='text' ng-model='element.elementDefaults.prefix' ng-trim='false'></label><label class='w-1'><span>Suffix</span><input type='text' ng-model='element.elementDefaults.suffix' ng-trim='false'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.range_true' value='true'> Range Selector</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.scale_true' value='true'> Show Scale</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['timepicker'] = "<div class='timepicker-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><span class='time-fields-cover hide-meridian-{{element.elementDefaults.format_24}}' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><select update-hours hrs-min='{{element.elementDefaults.hrs_min}}' hrs-max='{{element.elementDefaults.hrs_max}}' hrs-step='{{element.elementDefaults.hrs_step}}'><option ng-repeat='hours in element.elementDefaults.hrs_range' value='{{hours}}'>{{hours}}</option></select><select update-minutes minute-step='{{element.elementDefaults.minute_step}}'><option ng-repeat='minute in element.elementDefaults.minute_range' value='{{minute}}'>{{minute}}</option></select><input type='text' class='meridian-picker' value='am'></span><input type='hidden' name='{{element.elementDefaults.identifier}}' data-field-id='{{element.elementDefaults.identifier}}'><i class='icon-clock-1'></i></div></div>";
$scope.fieldOptionTemplate['timepicker'] = "<label class='w-1'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><label class='w2-1'><span>Min Hrs</span><input type='text' ng-model='element.elementDefaults.hrs_min'></label><label class='w2-1'><span>Max Hrs</span><input type='text' ng-model='element.elementDefaults.hrs_max'></label><label class='w2-1'><span>Hrs Step</span><input type='text' ng-model='element.elementDefaults.hrs_step'></label><label class='w2-1'><span>Minute Step</span><input type='text' ng-model='element.elementDefaults.minute_step'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.format_24'> Hide AM / PM</label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['star'] = "<div class='star-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div class='star-label-cover' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><span class='error'></span><div><label ng-repeat='opt in element.elementDefaults.options_list_show' tooltip data-toggle='tooltip' title='{{opt.show}}' style='width: {{element.elementDefaults.option_width}}'><div></div><input data-field-id='{{element.elementDefaults.identifier}}' type='radio' data-is-required='{{element.elementDefaults.required}}' name='{{element.elementDefaults.identifier}}' value='{{opt.value}}' class='validation-lenient'></label></div></div></div>";
$scope.fieldOptionTemplate['star'] = "<label class='w-1'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><div><label class='w-3'><i class='icon-help-circled' tooltip data-toggle='tooltip' title='You can set the value of the star-rating different from the text, using this pattern: <br><strong>5==Excellent!</strong><br>Here, <strong>5</strong> would be the value, and <strong>Excellent!</strong> would be the text visible to the user.'></i><span>Options</span><textarea rows='5' ng-model='element.elementDefaults.options_list' checkbox-list></textarea></label></div><label class='w-3'><span>Option Width</span><input type='text' ng-model='element.elementDefaults.option_width'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['thumb'] = "<div class='thumb-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div class='thumb-label-cover hide-checkbox update-label' data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><span class='error'></span><div><label ng-class-odd='"+'"odd"'+"' ng-repeat='opt in element.elementDefaults.options_list_show' tooltip data-toggle='tooltip' title='{{opt.show}}' style='width: {{element.elementDefaults.option_width}}'><i class='icon-thumbs-up thumbs-up'></i><i class='icon-thumbs-down thumbs-down'></i><input data-field-id='{{element.elementDefaults.identifier}}' type='radio' data-is-required='{{element.elementDefaults.required}}' name='{{element.elementDefaults.identifier}}' value='{{opt.value}}' class='validation-lenient'></label></div></div></div>";
$scope.fieldOptionTemplate['thumb'] = "<label class='w-1'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><div><label class='w-3'><i class='icon-help-circled' tooltip data-toggle='tooltip' title='You can set the value of the thumb-rating different from the text, using this pattern: <br><strong>5==Excellent!</strong><br>Here, <strong>5</strong> would be the value, and <strong>Excellent!</strong> would be the text visible to the user.'></i><span>Options</span><textarea rows='5' ng-model='element.elementDefaults.options_list' checkbox-list></textarea></label></div><label class='w-3'><span>Option Width</span><input type='text' ng-model='element.elementDefaults.option_width'></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.fieldHTMLTemplate['matrix'] = "<div class='matrix-cover field-cover'><span class='sub-label-{{element.elementDefaults.has_sub_label}}'><span compile='element.elementDefaults.main_label' class='main-label'></span><span class='sub-label' compile='element.elementDefaults.sub_label'></span></span><div><span class='error'></span><div data-placement='right' data-toggle='tooltip' tooltip title='{{element.elementDefaults.instructions}}' data-trigger='hover' data-html='true'><table cellspacing='0' cellpadding='0'><thead><th></th><th ng-repeat='col in element.elementDefaults.matrix_cols_output'>{{col.value}}</th></thead><tbody><tr ng-repeat='row in element.elementDefaults.matrix_rows_output'><td>{{row.value}}</td><td ng-repeat='col in element.elementDefaults.matrix_cols_output'><label><input type='radio' name='{{element.elementDefaults.identifier}}_{{$parent.$index}}' value='{{col.value}}'></label></td></tr></tbody></table></div></div></div>";
$scope.fieldOptionTemplate['matrix'] = "<label class='w-1'><span>Label</span><input type='text' ng-model='element.elementDefaults.main_label'></label><label class='w-1'><span>Sub Label</span><input sub-label type='text' ng-model='element.elementDefaults.sub_label'></label><label class='w-1'><span>Col. Width</span><input type='text' ng-model='element.elementDefaults.field_width'><i data-html='true' tooltip data-placement='top' data-toggle='tooltip' title='Set the widths of two fields to <Strong>50%</strong> each to fit them in one row.<br>You can have any number of fields in the same row, as long as the sum of widths is <strong>100%</strong><img src="+'"'+FC.pluginurl+'/assets/images/width-info.png"'+" style="+'"width: 100%; height: 54px"'+" />' class='icon-help'></i></label><label class='w-3'><span>Instructions</span><input type='text' ng-model='element.elementDefaults.instructions'></label><div><label class='w-3'><span>Options</span><textarea rows='5' ng-model='element.elementDefaults.matrix_rows' matrix-rows></textarea></label><label class='w-3'><span>Columns</span><textarea rows='5' ng-model='element.elementDefaults.matrix_cols' matrix-cols></textarea></label><label class='w-3'><input type='checkbox' ng-model='element.elementDefaults.required'> Required Field</label><label class='w-3'><input value='1' type='checkbox' ng-model='element.elementDefaults.hidden_default'> Hide Field on Page Load</label>";

$scope.addFormElement = function (type, position) {
	var total = 0;
	total = total + $scope.Builder.FormElements.length;

	$scope.elementTemp = {};
	$scope.elementTemp.field_width = '100%';
	$scope.Builder.elements_counter = $scope.Builder.elements_counter==undefined ? 1 : $scope.Builder.elements_counter + 1;
	var temp_var = $scope.Builder.elements_counter;
	$scope.elementTemp.identifier = 'field'+parseInt(temp_var);
	$scope.elementTemp.hidden_default = false;
	$scope.elementTemp.required = false;
	$scope.restrict = false;

	switch (type)
	{
		case 'heading':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Heading';
		$scope.elementTemp.field_value = 'Some Title';
		$scope.elementTemp.headingSize = 1.5;
		$scope.elementTemp.headingAlignment = 'left';
		break;

		case 'oneLineText':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Name';
		$scope.elementTemp.sub_label = 'your full name';
		$scope.elementTemp.selectedIcon = 'no-icon';
		break;

		case 'password':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Password';
		$scope.elementTemp.sub_label = 'check your caps';
		break;

		case 'email':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate.email'></div>";
		$scope.elementTemp.main_label = 'Email';
		$scope.elementTemp.sub_label = 'a valid email';
		break;

		case 'textarea':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Comments';
		$scope.elementTemp.sub_label = 'more details';
		$scope.elementTemp.field_height = '5';
		break;

		case 'checkbox':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Favorite Fruits';
		$scope.elementTemp.sub_label = 'pick one!';
		$scope.elementTemp.allow_multiple = 'checkbox';
		$scope.elementTemp.options_list = 'Apple\nOrange\nWatermelon';
		break;

		case 'dropdown':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Language';
		$scope.elementTemp.sub_label = 'pick one!';
		$scope.elementTemp.options_list = '==Select An Option\nEnglish\nFrench\nSpanish';
		break;

		case 'datepicker':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Date';
		$scope.elementTemp.sub_label = 'of appointment';
		$scope.elementTemp.dateLang = 'en';
		$scope.elementTemp.dateFormat = 'dd/mm/yy';
		$scope.elementTemp.dateDays = {"0":true,"1":true,"2":true,"3":true,"4":true,"5":true,"6":true};
		break;

		case 'customText':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.html = 'Add some text or <strong>HTML</strong> here';
		$scope.elementTemp.main_label = 'Text Field';
		$scope.elementTemp.font_color = '#666666';
		$scope.elementTemp.floating_type = 'false';
		$scope.elementTemp.alignment = 'left';
		break;

		case 'submit':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Submit Form';
		$scope.elementTemp.isWide = false;
		$scope.elementTemp.doAnimate = false;
		$scope.elementTemp.alignment = 'right';
		break;

		case 'fileupload':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'File';
		$scope.elementTemp.sub_label = 'upload';
		$scope.elementTemp.button_label = 'Upload';
		break;

		case 'slider':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Slider';
		$scope.elementTemp.sub_label = 'take your pick';
		$scope.elementTemp.range_true = 'min';
		$scope.elementTemp.range_step = 5;
		$scope.elementTemp.range_min = 10;
		$scope.elementTemp.range_max = 100;
		$scope.elementTemp.scale_true = false;
		break;

		case 'timepicker':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Time';
		$scope.elementTemp.sub_label = 'of appointment';
		$scope.elementTemp.format_24 = false;
		$scope.elementTemp.hrs_min = 0;
		$scope.elementTemp.hrs_max = 24;
		$scope.elementTemp.hrs_step = 2;
		break;

		case 'star':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Rate';
		$scope.elementTemp.sub_label = 'our support';
		$scope.elementTemp.options_list = '1==Bad\n2==Could be better\n3==So so\n4==Good\n5==Excellent!';
		break;

		case 'thumb':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Liked the food?';
		$scope.elementTemp.sub_label = 'let us know';
		$scope.elementTemp.options_list = '1==Yep\n0==Nope';
		break;

		case 'matrix':
		$scope.element = "<div compile='fieldHTMLTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementOptions = "<div compile='fieldOptionTemplate["+'"'+type+'"'+"]'></div>";
		$scope.elementTemp.main_label = 'Survey';
		$scope.elementTemp.sub_label = '';
		$scope.elementTemp.matrix_rows = 'How Was the Food?\nHow Was the Service?';
		$scope.elementTemp.matrix_cols = 'Poor\nAverage\nGood';
		break;

		default:
		for (x in $scope.addField.payments)
		{
			if ( $scope.addField.payments[x].name == type )
			{
				for (y in $scope.Builder.FormElements)
				{
					for (z in $scope.Builder.FormElements[y])
					{
						if ($scope.Builder.FormElements[y][z].type==$scope.addField.payments[x].name && $scope.Builder.FormElements[y][z].restrict==true)
						{
							return false;
						}
					}
				}
				$scope.element = "<div compile='addField.payments["+x+"].fieldHTMLTemplate'></div>";
				$scope.elementOptions = "<div compile='addField.payments["+x+"].fieldOptionTemplate'></div>";
				$scope.restrict = true;
				for (y in $scope.addField.payments[x].defaults)
				{
					$scope.elementTemp[y] = $scope.addField.payments[x].defaults[y];
				}
			}
		}
		for (x in $scope.addField.others)
		{
			if ( $scope.addField.others[x].name == type )
			{
				$scope.element = "<div compile='addField.others["+x+"].fieldHTMLTemplate'></div>";
				$scope.elementOptions = "<div compile='addField.others["+x+"].fieldOptionTemplate'></div>";
				$scope.restrict = true;
				for (y in $scope.addField.others[x].defaults)
				{
					$scope.elementTemp[y] = $scope.addField.others[x].defaults[y];
				}
			}
		}
		break;
	}
	position = window.dragged_location==null ? $scope.Builder.FormElements[0].length : window.dragged_location;
	position_page = window.dragged_location_page==null ? 0 : window.dragged_location_page;
	position_page = Math.max(position_page, 0);
	$scope.Builder.FormElements[position_page].splice(position,0,{
		element: $scope.element,
		restrict: $scope.restrict,
		identifier: 'field'+parseInt(temp_var),
		type: type,
		elementOptions: $scope.elementOptions,
		elementDefaults: $scope.elementTemp
	});	
	setTimeout(function(){
		jQuery('.dropdown-cover select').trigger('change');
	}, 300);
	$scope.updateListOfFields();
	$scope.Options.show_fields = false;

	window.dragged_location = null;
	window.dragged = false;
}
});


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