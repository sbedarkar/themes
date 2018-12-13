function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	}
	return "";
}

function plotChart(labels, views, submissions, payments)
{

	Chart.defaults.global = {
		animation: true,
		animationSteps: 60,
		animationEasing: "easeOutQuart",
		showScale: true,
		scaleOverride: false,
		scaleSteps: null,
		scaleStepWidth: null,
		scaleStartValue: null,
		scaleLineColor: "rgba(0,0,0,.15)",
		scaleLineWidth: 1,
		scaleShowLabels: true,
		scaleLabel: "<%=value%>",
		scaleIntegersOnly: true,
		scaleBeginAtZero: false,
		scaleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
		scaleFontSize: 11,
		scaleFontStyle: "normal",
		scaleFontColor: "#778",
		responsive: false,
		maintainAspectRatio: false,
		showTooltips: true,
		tooltipEvents: ["mousemove", "touchstart", "touchmove"],
		tooltipXOffset: 0,
		tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
		multiTooltipTemplate: "<%= value %>",
		onAnimationProgress: function(){},
		onAnimationComplete: function(){}
	}
	Chart.defaults.global.pointHitDetectionRadius = 1;
	Chart.defaults.global.customTooltips = function(tooltip) {
		var index = window.labels.indexOf(tooltip.title);
		var amount = window.payments[index];
		var tooltipEl = jQuery('#tooltip');

		if (!tooltip) {

			return;
		}

		tooltipEl.removeClass('above below');
		tooltipEl.addClass(tooltip.yAlign);

		if (jQuery('.fc-header .small-4').length==0)
		{
			var innerHtml = '<div class="chartjs-tooltip-section"><span class="chartjs-tooltip-key">' + tooltip.title + '</span><span class="chartjs-tooltip-value">' + tooltip.labels[0] + ' views</span><span class="chartjs-tooltip-value">' + tooltip.labels[2] + ' submissions</span><span class="chartjs-tooltip-value">' + amount + ' charges</span></div>';
		}
		else
		{
			var innerHtml = '<div class="chartjs-tooltip-section"><span class="chartjs-tooltip-key">' + tooltip.title + '</span><span class="chartjs-tooltip-value">' + tooltip.labels[1] + ' views</span><span class="chartjs-tooltip-value">' + tooltip.labels[0] + ' submissions</span></div>';			
		}

		tooltipEl.html(innerHtml);
	};


	views = window.views = views;
	labels = window.labels = labels;
	submissions = window.submissions = submissions;
	payments = window.payments = payments;

	var views_sum = 0;
	var submissions_sum = 0;
	var payments_sum = 0;

	for (x in views)
	{ 
		views_sum = views_sum + views[x];
	}
	for (y in submissions)
	{ 
		submissions_sum = submissions_sum + submissions[y];
	}
	for (z in payments)
	{ 
		payments_sum = payments_sum + payments[z];
	}
	var conversion = Math.round(parseFloat(submissions_sum/views_sum)*1000)/10;
	var conversion = views_sum == 0 ? 0 : conversion;

	var conversion_payments = Math.round(parseFloat(payments_sum/views_sum)*1000)/10;
	var conversion_payments = views_sum == 0 ? 0 : conversion_payments;

	spinTo('#views',views_sum);
	spinTo('#submissions',submissions_sum);
	spinTo('#conversion',conversion);
	spinTo('#conversion_payments',conversion_payments);
	spinTo('#payments',payments_sum);
	Chart.defaults.global.responsive = true;
	data = {};
	data.labels = labels;
	data.datasets = [];
	if (payments_sum>0)
	{
		jQuery('.pay-class').css('display','inline-block');
		jQuery('.fc-header .small-4').addClass('small-3').removeClass('small-4');
		data.datasets.push({
			label: "Charges",
			fillColor: "rgba(93,168,93,0.2)",
			strokeColor: "rgba(93,168,93,0.8)",
			pointColor: "rgba(93,168,93,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(93,168,93,1)",
			data: payments
		});
	}
	else
	{
		jQuery('.pay-class').css('display','none');
		jQuery('.fc-header .small-3').addClass('small-4').removeClass('small-3');
	}
	data.datasets.push({
		label: "Submissions",
		fillColor: "rgba(59,161,218,0.2)",
		strokeColor: "rgba(59,161,218,0.8)",
		pointColor: "rgba(59,161,218,1)",
		pointStrokeColor: "#fff",
		pointHighlightFill: "#fff",
		pointHighlightStroke: "rgba(59,161,218,1)",
		data: submissions
	});
	data.datasets.push({
		label: "Views",
		fillColor: "rgba(237, 133, 66, 0.2)",
		strokeColor: "rgba(237, 133, 66, 0.8)",
		pointColor: "rgba(237, 133, 66, 1)",
		pointStrokeColor: "#fff",
		pointHighlightFill: "#fff",
		pointHighlightStroke: "rgba(237, 133, 66,1)",
		data: views
	});	
	if (typeof window.myLineChartIs!='undefined')
	{
		myLineChart.destroy();
		jQuery('#chart').css('height','317px');
		jQuery('#chart').attr('height','335');
	}
	var ctx = document.getElementById("chart").getContext("2d");
	myLineChart = new Chart(ctx).Line(data, options);
	window.myLineChartIs = true;
}



jQuery(document).mouseup(function (e)
{
	var container = jQuery('.global-options');
	if (!container.is(e.target)
		&& container.has(e.target).length === 0)
	{
		jQuery('.active .cog').trigger('click');
	}
	var container = jQuery('.subs_cover');
	if (!container.is(e.target)
		&& container.has(e.target).length === 0)
	{
		jQuery('#search-subs').removeClass('active');
		jQuery('#search-form').removeClass('active');
		jQuery('#export-subs').removeClass('active');
	}	
});

function spinTo(selector, to)
{
	var from = jQuery(selector).text()=='' ? 0 : parseFloat(jQuery(selector).text());
	var to = isNaN(parseFloat(to)) ? 0 : parseFloat(to);
	duration = (to-from) < 100 ? 200 : 700;
	jQuery({someValue: from}).animate({someValue: parseFloat(to)}, {
		duration: duration,
		easing:'swing',
		context: to,
		step: function() {
			if (parseInt(to)!=parseFloat(to))
			{
				val = (Math.ceil(this.someValue*10))/10;
			}
			else
			{
				val = Math.ceil(this.someValue);
			}
			jQuery(selector).text(val);
		}
	});
	setTimeout(function(){
		jQuery(selector).text(parseFloat(to));
	}, duration+100);
}

function updateChart()
{
	var from = encodeURIComponent(jQuery.datepicker.formatDate( "yy-M-dd", jQuery('#chart-from').datepicker('getDate') ));
	var to = encodeURIComponent(jQuery.datepicker.formatDate( "yy-M-dd", jQuery('#chart-to').datepicker('getDate') ));
	var form = encodeURIComponent(jQuery('#chart-form').val());
	jQuery('#chart-cover').addClass('loading');
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "GET",
		cache: false,
		context: jQuery(this),
		data: 'action=formcraft3_get_stats&from='+from+'&to='+to+'&form='+form,
		dataType: "json"
	} )
	.done(function(response) {
		if (response.success)
		{
			plotChart(response.labels, response.views,response.submissions,response.payments);
		}
	})
	.fail(function(response) {
		toastr["error"]("Connection Error");
	})
	.always(function(response){
		jQuery('#chart-cover').removeClass('loading');
	});
}

var previousPoint = null;

function showTooltip(x, y, contents, item) {
	var width = 110;
	if(jQuery('#tooltip').length)
	{
		jQuery('#tooltip').html(contents).stop(true, true).animate({
			top: y - 96,
			left: x - (width/2)
		}, 250);
	}
	else
	{
		jQuery("<div id='tooltip'>" + contents + "</div>").css({
			position: "absolute",
			width: width+'px',
			display: "none",
			top: y - 94,
			left: x - (width/2)
		}).appendTo("body").show();
	}
}

var lastChecked = null;
toastr.options = {
	"closeButton": false,
	"debug": false,
	"newestOnTop": true,
	"progressBar": false,
	"positionClass": "toast-top-right",
	"preventDuplicates": false,
	"onclick": null,
	"showDuration": "300",
	"hideDuration": "300",
	"timeOut": "3000",
	"extendedTimeOut": "300",
	"showEasing": "linear",
	"hideEasing": "linear",
	"showMethod": "slideDown",
	"hideMethod": "slideUp"
}

function getSubmissions(page, form, query, sort_what, sort_order)
{
	form = (typeof form === "undefined") ? 0 : form;
	query = (typeof query === "undefined") ? '' : query;
	sort_what = sort_what || window.subs_sort_what || 'created';
	sort_order = sort_order || window.subs_sort_order || 'DESC';
	window.subs_sort_what = sort_what;
	window.subs_sort_order = sort_order;
	jQuery('.subs_list .loader').show();
	jQuery('.subs_list').removeClass('no-subs');
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "POST",
		context: jQuery(this),
		data: 'action=formcraft3_get_submissions&page='+page+'&form='+form+'&query='+query+'&sort_what='+sort_what+'&sort_order='+sort_order,
		dataType: "json"
	} )
	.done(function(response) {
		jQuery('.subs_list .sortable').removeClass('ASC DESC');
		jQuery('.subs_list [data-sort="'+sort_what+'"]').removeClass('ASC DESC').addClass(sort_order);		
		jQuery('.subs_list .tbody').html('');
		jQuery('.subs_list .loader').hide();
		if (response.total)
		{
			spinTo('#total-submissions',response.total);
		}
		for (line in response.submissions)
		{
			var new_line = '';
			var new_line = new_line + "<div class='tr'>";
			var new_line = new_line + "<span style='width:10%'><label><input value='"+response.submissions[line].id+"' class='subs_checked' name='subs_checked' type='checkbox'></label></span>";
			var new_line = new_line + "<span style='width:51%'><a class='load-submission' data-id='"+response.submissions[line].id+"'>"+response.submissions[line].form_name+"</a></span>";
			var new_line = new_line + "<span style='width:39%'><a class='load-submission' data-id='"+response.submissions[line].id+"'>"+response.submissions[line].created+"</a></span>";
			var new_line = new_line + "</div>";
			jQuery('.subs_list .tbody').append(new_line);
		}
		var i = 1;
		jQuery('.subs_list .pagination > div').html('');
		while (i <= response.pages) {
			var add_class = i==page ? 'active' : '';
			jQuery('.subs_list .pagination > div').append('<span class="'+add_class+'">'+i+'</span>');
			i++;
		}
		if(response.pages==0)
		{
			jQuery('.subs_list').addClass('no-subs');
		}
	})
.fail(function(response) {
	jQuery(this).find('.response').text('Connection error');
})
.always(function(response) {
	jQuery(this).find('button,[type="submit"]').removeAttr('disabled');
	jQuery(this).find('.fcb-spinner').hide();
});
}

function getForms(page, query, sort_what, sort_order)
{
	query = (typeof query === "undefined") ? '' : query;
	sort_what = sort_what || window.form_sort_what || 'modified';
	sort_order = sort_order || window.form_sort_order || 'DESC';
	window.form_sort_what = sort_what;
	window.form_sort_order = sort_order;
	jQuery('.form_list .loader').show();
	jQuery('.form_list').removeClass('no-subs');
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "POST",
		context: jQuery(this),
		data: 'action=formcraft3_get_forms&page='+page+'&query='+query+'&sort_what='+sort_what+'&sort_order='+sort_order,
		dataType: "json"
	} )
	.done(function(response) {
		jQuery('.form_list .sortable').removeClass('ASC DESC');
		jQuery('.form_list [data-sort="'+sort_what+'"]').removeClass('ASC DESC').addClass(sort_order);
		jQuery('.form_list .tbody').html('');
		jQuery('.form_list .loader').hide();
		if (response.total)
		{
			spinTo('#total-forms',response.total);
		}
		for (line in response.forms)
		{
			var new_line = '';
			var new_line = new_line + "<div class='tr form-"+response.forms[line].id+"'>";
			var new_line = new_line + "<span style='width:9%'><a href='admin.php?page=formcraft3_dashboard&id="+response.forms[line].id+"'>"+response.forms[line].id+"</a></span>";
			var new_line = new_line + "<span title='"+response.forms[line].name+"' style='width:46%'><a href='admin.php?page=formcraft3_dashboard&id="+response.forms[line].id+"'>"+response.forms[line].name+"</a></span>";
			var new_line = new_line + "<span style='width:35%'><a href='admin.php?page=formcraft3_dashboard&id="+response.forms[line].id+"'>"+response.forms[line].modified+"</a></span>";
			var new_line = new_line + "<span style='width:10%'><i data-id='"+response.forms[line].id+"' class='trash-icon trash-form icon-trash-1'></i></span>";
			var new_line = new_line + "</div>";
			jQuery('.form_list .tbody').append(new_line);
		}
		var i = 1;
		jQuery('.form_list .pagination > div').html('');
		while (i <= response.pages) {
			var add_class = i==page ? 'active' : '';
			jQuery('.form_list .pagination > div').append('<span class="'+add_class+'">'+i+'</span>');
			i++;
		}
		if(response.pages==0)
		{
			jQuery('.form_list').addClass('no-subs');
		}
	})
.fail(function(response) {
	jQuery(this).find('.response').text('Connection error');
})
.always(function(response) {
	jQuery(this).find('button,[type="submit"]').removeAttr('disabled');
	jQuery(this).find('.fcb-spinner').hide();
});
}



function getFiles(page, query)
{
	query = (typeof query === "undefined") ? '' : query;
	jQuery('.file_list .loader').show();
	jQuery('.file_list').removeClass('no-subs');
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "POST",
		context: jQuery(this),
		data: 'action=formcraft3_get_files&page='+page+'&query='+query,
		dataType: "json"
	} )
	.done(function(response) {
		jQuery('.file_list .tbody').html('');
		jQuery('.file_list .loader').hide();
		jQuery('.files_checked').first().trigger('change');
		if (response.total)
		{
			spinTo('#total-files',response.total);
		}
		for (line in response.files)
		{
			var new_line = '';
			var new_line = new_line + "<div class='tr'>";
			var new_line = new_line + "<span style='width:8%'><label><input class='files_checked' type='checkbox' value='"+response.files[line].id+"' name='del_files'/></label></span>";
			var new_line = new_line + "<span style='width:43%'><a target='_blank' href='"+FC_1.baseurl+'?formcraft3_download_file='+response.files[line].uniq_key+"'>"+response.files[line].name+"</a></span>";
			var new_line = new_line + "<span style='width:20%'>"+response.files[line].mime+"</span>";
			var new_line = new_line + "<span style='width:29%'>"+response.files[line].created+"</span>";
			var new_line = new_line + "</div>";
			jQuery('.file_list .tbody').append(new_line);
		}
		var i = 1;
		jQuery('.file_list .pagination > div').html('');
		while (i <= response.pages) {
			var add_class = i==page ? 'active' : '';
			jQuery('.file_list .pagination > div').append('<span class="'+add_class+'">'+i+'</span>');
			i++;
		}
		if(response.pages==0)
		{
			jQuery('.file_list').addClass('no-subs');
		}
	})
.fail(function(response) {
	jQuery(this).find('.response').text('Connection error');
})
.always(function(response) {
	jQuery(this).find('button,[type="submit"]').removeAttr('disabled');
	jQuery(this).find('.fcb-spinner').hide();
});
}
function whatDecimalSeparator() {
	var n = 1.1;
	n = n.toLocaleString().substring(1, 2);
	return n;
}

jQuery(document).ready(function(){
	jQuery('body').on('click','#trigger-key-tab',function(){
		jQuery('a[href="#license-tab"]').trigger('click');
	});

	jQuery('body').on('click','#show-license-form',function(){
		jQuery('.not-activated').slideToggle();
	});

	jQuery('body').on('submit','#activate-license',function(event){
		event.preventDefault();
		jQuery(this).find('button').addClass('fc-disabled').attr('disabled','disabled');
		jQuery('#refresh-license').find('button').addClass('fc-disabled').attr('disabled','disabled');
		var data = jQuery(this).serialize();
		jQuery(this).find('.response').text('');
		jQuery.ajax( {
			url: FC_1.ajaxurl,
			type: "POST",
			context: jQuery(this),
			data: 'action=formcraft3_verify_license&'+data,
			dataType: "json"
		} )
		.done(function(response) {
			if (response.success)
			{
				jQuery('#activation-tab').addClass('activated');
				jQuery('#activation-tab .not-activated').slideUp();
				jQuery('#activation-tab .is-activated').slideDown();
				jQuery('#purchased-on').text(response.purchased);
				jQuery('#valid-till').text(response.expires);
				jQuery('#verified-on').text(response.registered);
			}
			else if (response.failed)
			{
				jQuery(this).find('.response').text(response.failed);
			}
			else
			{
				toastr["error"]("Something wen't wrong");
			}
			jQuery(this).find('button').removeClass('fc-disabled').removeAttr('disabled');
			jQuery('#refresh-license').removeClass('fc-disabled').removeAttr('disabled');
		})
		.fail(function(response) {
			jQuery(this).find('button').removeClass('fc-disabled').removeAttr('disabled');
			jQuery('#refresh-license').removeClass('fc-disabled').removeAttr('disabled');
			toastr["error"]("Connection Error");
		});
	});
jQuery('body').on('click','#refresh-license',function(){
	jQuery(this).addClass('fc-disabled');
	jQuery('#activate-license').trigger('submit');
});

jQuery('body').on('change','.checkbox-cover label input', function(){
	if (jQuery(this).is(':checked'))
	{
		var name = jQuery(this).attr('name');
		jQuery('[name="'+name+'"]').parent().removeClass('active');
		jQuery(this).parent().addClass('active');
	}
});
jQuery('body').on('change','[name="template-select-slider"]',function(){
	jQuery( "#template-showcase-form" ).load( FC_1.ajaxurl+'?action=formcraft3_get_template&name='+encodeURIComponent(jQuery('[name="template-select-slider"]:checked').val()), function(response) {
		response =  jQuery.evalJSON(response);
		jQuery('#template-showcase-form').html(response.html);
		jQuery('#template-showcase-form .fc-pagination > div').eq(0).addClass('active');
		jQuery('#template-showcase-form .fc_form .form-page-0').addClass('active');	
		if ( response.config )
		{
			if ( typeof response.config.config.font_family!='undefined' && response.config.config.font_family.indexOf('Arial')==-1 && response.config.config.font_family.indexOf('Courier')==-1 && response.config.config.font_family.indexOf('sans-serif')==-1 && response.config.config.font_family.indexOf('inherit')==-1 )
			{
				response.config.config.font_family = response.config.config.font_family.replace(/ /g,'+');
				jQuery('head').append("<link href='"+(location.protocol=='http:'?'http:':'https:')+"//fonts.googleapis.com/css?family="+response.config.config.font_family+":400,600,700' rel='stylesheet' type='text/css'>");
			}
		}
	});
})
jQuery('body').on('change','[name="new_form_type"]', function(){
	var value = jQuery('[name="new_form_type"]:checked').val();

	jQuery('#select-template-cover').slideUp();
	jQuery('#new_form').parent().animate({width:'490px', 'padding-top': '40px', 'padding-bottom': '40px'}, 250);
	jQuery('#new_form_modal').animate({'padding-top': '50px'}, 250);
	switch (value){
		case 'blank':
		jQuery('#import-which-form, #duplicate-which-form').parent().slideUp();
		break;

		case 'import':
		jQuery('#duplicate-which-form').parent().slideUp();
		jQuery('#import-which-form').parent().slideDown();
		break;

		case 'duplicate':
		jQuery('#import-which-form').parent().slideUp();
		jQuery('#duplicate-which-form').parent().slideDown();
		break;

		case 'template':
		jQuery('#import-which-form, #duplicate-which-form').parent().slideUp();
		jQuery('#new_form_modal').animate({'padding-top': '32px'}, 250);
		jQuery('#new_form').parent().animate({width:'825px', 'padding-top': '0px', 'padding-bottom': '0px'}, 250);
		jQuery('#select-template-cover').slideDown();
		jQuery('.template-select-slider label:nth-child(2)').trigger('click');
		break;
	}
});

jQuery('#which-form-export').change(function(){
	if ( whatDecimalSeparator()==',' )
	{
		jQuery(this).parent().find('a').attr('href',FC_1.baseurl+'/?formcraft_export_entries='+jQuery(this).val()+'&sep=,');
	}
	else
	{
		jQuery(this).parent().find('a').attr('href',FC_1.baseurl+'/?formcraft_export_entries='+jQuery(this).val());
	}
});
jQuery('#which-form-export').trigger('change');
jQuery('body').on('submit','#subs-search-form',function(event){
	event.preventDefault();
	getSubmissions(1,jQuery('#which-form').val(),jQuery('#subs-search-input').val());
});
jQuery('body').on('submit','#form-search-form',function(event){
	event.preventDefault();
	getForms(1,jQuery('#form-search-input').val());
});
jQuery('body').on('click','.form_list .sortable',function(event){
	var sort_what = jQuery(this).attr('data-sort');
	var sort_order = jQuery(this).hasClass('DESC') ? 'ASC' : 'DESC';
	getForms(1,jQuery('#form-search-input').val(),sort_what,sort_order);
});
jQuery('body').on('click','.subs_list .sortable',function(event){
	var sort_what = jQuery(this).attr('data-sort');
	var sort_order = jQuery(this).hasClass('ASC') ? 'DESC' : 'ASC';
	getSubmissions(1,jQuery('#which-form').val(),jQuery('#subs-search-input').val(),sort_what,sort_order);
});
if ( getCookie('hideEmpty') == 'true' )
{
	jQuery('#submission_body_cover').addClass('show-empty');
	jQuery('#show-empty-sub').addClass('active');
}
jQuery('body').on('click','#show-empty-sub',function(event){
	jQuery('#submission_body_cover').toggleClass('show-empty');
	if ( !jQuery('#submission_body_cover').hasClass('show-empty') )
	{
		setCookie('hideEmpty', 'false',365);
	}
	else
	{
		setCookie('hideEmpty', 'true',365);
	}
	jQuery(this).toggleClass('active');
});
jQuery('body').on('click','#edit-sub',function(event){
	if (!jQuery('#submission_body_cover').attr('data-id')){return false;}
	jQuery('#submission_body_cover').toggleClass('editing');
	jQuery('#submission_body .value').each(function(){
		if ( jQuery(this).attr('data-editable')=='true' ){
			var value = jQuery(this).text();
			var identifier = jQuery(this).attr('data-identifier');
			var is_array = jQuery(this).attr('data-array');
			jQuery(this).html("<textarea rows='1' class='array-"+is_array+"' name='"+identifier+"'>"+value+"</textarea>");
			autosize(jQuery(this).find('textarea'));
		}
	});
});
jQuery('body').on('click','#reset-analytics',function(){
	var r = confirm("Sure? This action can't be reversed.");
	if (r != true) {
		return false;
	}
	toastr["success"]("Please wait ...");
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "POST",
		context: jQuery(this),
		data: 'action=formcraft3_reset_analytics',
		dataType: "json"
	} )
	.done(function(response) {
		if (response.success)
		{
			toastr["success"]("<i class='icon-ok'></i> "+response.success);
			updateChart();
		}
		else if (response.failed)
		{
			toastr["error"](response.failed);
		}
		else
		{
			toastr["error"]("Something went wrong");
		}		
	})
	.fail(function(response) {
		toastr["error"]("Connection Error");
	});
});	
jQuery('body').on('click','#save-sub',function(){
	var data1 = jQuery('#submission_body .value textarea.array-false').serialize();
	var data2 = '';
	jQuery('#submission_body .value textarea.array-true').each(function(){
		valueArray = jQuery(this).val().split("\n");
		for(x in valueArray)
		{
			data2 = data2 + '&'+jQuery(this).attr('name')+'[]='+valueArray[x];
		}
	});
	jQuery('#submission_body_cover').addClass('loading');
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "POST",
		context: jQuery(this),
		data: 'action=formcraft3_update_submission_content&id='+jQuery('#submission_body_cover').attr('data-id')+'&'+data1+data2,
		dataType: "json"
	} )
	.done(function(response) {
		if (response)
		{
			jQuery('.subs_list .tbody .tr.active .load-submission').first().trigger('click');
		}
		else
		{
			toastr["error"]("Something went wrong");
		}
	})
	.fail(function(response) {
		toastr["error"]("Connection Error");
	})
	.always(function(response){
		jQuery('#submission_body_cover').removeClass('loading');
	});
});		
jQuery('body').on('click','#search-subs, #search-form',function(){
	jQuery(this).parent().find('.active').removeClass('active');
	jQuery(this).addClass('active');
	jQuery(this).find('input').focus();
});
jQuery('body').on('click','#export-subs',function(){
	jQuery(this).parent().find('.active').removeClass('active');
	jQuery(this).addClass('active');
	jQuery(this).find('input').focus();
});	
jQuery('body').on('click','.pagination-move .icon-angle-left',function(){
	var element = jQuery(this).parent().parent().find('.pagination > div');
	var left = parseInt(element.css('left'))+150;
	left = Math.min(left,0);
	element.animate({'left':left+'px'}, 250, 'linear');
});
jQuery('body').on('click','.pagination-move .icon-angle-right',function(){
	var element = jQuery(this).parent().parent().find('.pagination > div');
	var left = parseInt(element.css('left'))-150;
	var len = -(Math.max(0,(element.find('span').length-11))*40);
	console.log(len);
	left = Math.max(left,len);
	element.animate({'left':left+'px'}, 250, 'linear');
});
options = {};
options.beforeShow = function(input, inst) {
	jQuery('#ui-datepicker-div').removeClass('ui-datepicker').addClass('fc-datepicker');
}
options.onClose = function (input, inst) {
	if (jQuery(this).attr('id')=='chart-from')
	{
		var minDate = jQuery('#chart-from').datepicker( "getDate" );
		jQuery('#chart-to').datepicker( "option", "minDate", minDate );
		jQuery('#chart-to').trigger('focus');
	}
	if (jQuery(this).attr('id')=='chart-to')
	{
		jQuery('[name="pre-selected"]').removeAttr('checked').trigger('change');
		updateChart();
	}
}
options.onSelect = function(input, inst) {
	jQuery(this).trigger('change').trigger('input');
}
options.nextText = '❯';
options.prevText = '❮';
options.hideIfNoPrevNext = true;
options.changeYear = true;
options.changeMonth = true;
options.showAnim = false;
options.yearRange = "c-2:c+2";
options.dateFormat = 'd M, yy';
jQuery('.fc-date').datepicker(options);

jQuery('body').on('click','.nav-tabs > span,.nav-tabs > a',function(){
	var selector = jQuery(this).parent().attr('data-content');
	if ( jQuery(selector).find(' > div').eq(jQuery(this).index()).length > 0 )
	{
		jQuery(this).parent().find('> span,> a').removeClass('active');
		jQuery(this).addClass('active');
		jQuery(selector).find(' > div').removeClass('active');
		jQuery(selector).find(' > div').eq(jQuery(this).index()).addClass('active');
	}
});
jQuery('body').on('click','.main-tabs > span, .main-tabs > a',function(){
	var selector = jQuery(this).parent().attr('data-content');
	var new_tab = jQuery(this).attr('href');
	if ( new_tab )
	{
		new_tab = new_tab.replace('#','');
		jQuery(selector).find(' > div').removeClass('active');
		jQuery(this).parent().find('> span,> a').removeClass('active');
		jQuery(this).addClass('active');
		jQuery('[data-tab-id="'+new_tab+'"]').addClass('active');
	}
});
var hash = window.location.hash.substr(1);
if ( typeof hash != 'undefined' && hash != '' )
{
	jQuery('a[href="#'+hash+'"]').trigger('click');
}

jQuery('.icon-cog.cog').click(function(){
	if (jQuery('#fc-global-options-cover').hasClass('active'))
	{
		jQuery('#fc-global-options-cover').addClass('hiding');
		setTimeout(function(){
			jQuery('#fc-global-options-cover').removeClass('active hiding');
		}, 350);
	}
	else
	{
		jQuery('#fc-global-options-cover').addClass('active');
	}
});

jQuery('#chart-form').change(function(){
	updateChart();
});
jQuery('body').on('change','.update-checkbox label input', function(){
	var name = jQuery(this).attr('name');
	jQuery('[name="'+name+'"]').parent().removeClass('active');
	jQuery('[name="'+name+'"]:checked').parent().addClass('active');
});
jQuery('[name="pre-selected"]').change(function(){
	var type = jQuery('[name="pre-selected"]:checked').val();
	if (type=='week')
	{
		var from = new Date();
		if ( from.getDay()==0 )
		{
			jQuery('#chart-from').datepicker( 'setDate', -7 );
			jQuery('#chart-to').datepicker( 'setDate', from );
		}
		else
		{
			jQuery('#chart-from').datepicker( 'setDate', -from.getDay() );
			jQuery('#chart-to').datepicker( 'setDate', -from.getDay()+7 );
		}
		updateChart();
	}
	if (type=='month')
	{
		var from = new Date();
		var to = new Date(from.getUTCFullYear(),from.getUTCMonth()+1,0);
		var from2 = new Date(from.getUTCFullYear(),from.getUTCMonth(),1);
		jQuery('#chart-from').datepicker( 'setDate', from2 );
		jQuery('#chart-to').datepicker( 'setDate', to );
		updateChart();
	}
	if (type=='year')
	{
		var temp = new Date();
		var from = new Date(temp.getUTCFullYear(),0,1);
		var to = new Date(temp.getUTCFullYear(),11,31);
		jQuery('#chart-from').datepicker( 'setDate', from );
		jQuery('#chart-to').datepicker( 'setDate', to );
		updateChart();
	}
});
jQuery('.pre-week input').prop('checked',true).trigger('change');
updateChart();

jQuery("#chart").bind("plothover", function (event, pos, item) {
	if (item) {
		if (previousPoint != item.dataIndex) {
			previousPoint = item.dataIndex;
			var x = Object.keys(item.series.xaxis.categories)[item.datapoint[0]],
			y = item.datapoint[1];
			var abc = '<span>' + x + '</span>' + window.views[item.datapoint[0]][1] + ' visits <br> ' + window.submissions[item.datapoint[0]][1] + ' submissions';
			showTooltip(item.pageX, item.pageY, abc, item);
		}
	} else {
		jQuery("#tooltip").remove();
		previousPoint = null;
	}
});

jQuery('body').on('click','.subs_list .pagination > div span',function(){
	getSubmissions(jQuery(this).text(),jQuery('#which-form').val(),jQuery('#subs-search-input').val());
});
jQuery('body').on('click','.form_list .pagination > div span',function(){
	getForms(jQuery(this).text(),jQuery('#form-search-input').val());
});
jQuery('body').on('click','.file_list .pagination > div span',function(){
	getFiles(jQuery(this).text());
});		
if (jQuery('.pagination').length)
{
	jQuery('.forms-pagination .pagination > div:first-child span').trigger('click');
	jQuery('.subs-pagination .pagination > div:first-child span').trigger('click');
}
jQuery('#import_form_input').fileupload({
	dataType: 'json',
	add: function(e, data){
		jQuery(this).attr('disabled','disabled');
		var parent = jQuery(this).parent().parent();
		parent.find('.icon-up-circled2').hide();
		parent.find('.icon-spin5').show();
		parent.find('.button-file').removeClass('active');
		window.jqXHR = data.submit();
	},
	done: function(e, data){
		jQuery(this).removeAttr('disabled');
		var response = data.result;
		if (response.success)
		{
			if (response.debug)
			{
				toastr["success"]("<i class='icon-ok'></i> "+response.debug);
			}
			jQuery(this).attr('data-file',response.success);
			var parent = jQuery(this).parent().parent();
			parent.find('.file-name').html(response.success);
			parent.find('.button-file').addClass('active');
		}
		else if (response.failed)
		{
			var parent = jQuery(this).parent().parent();
			toastr["error"](response.failed);
		}
		else
		{
			var parent = jQuery(this).parent().parent();
			toastr["error"]("Unknown Error");
		}
		parent.find('.icon-up-circled2').show();
		parent.find('.icon-spin5').hide();			
	}
});
jQuery('[data-target="#new_form_modal"]').click(function(){
	jQuery('#form_name').focus();
});
jQuery('#file_uploads').on('shown.bs.fc_modal', function () {
	if (jQuery('.file_list .pagination > div span.active').length==0)
	{
		jQuery('.file_list .pagination > div span').eq(0).trigger('click');
	}
});
jQuery('body').on('click','.load-submission',function(){
	var id = jQuery(this).attr('data-id');
	jQuery(this).parents('.tbody').find('.tr.active').removeClass('active');
	jQuery(this).parents('.tr').addClass('active');
	jQuery('#submission_body_cover').addClass('loading');
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "GET",
		context: jQuery(this),
		data: 'action=formcraft3_get_submission_content&id='+id,
		dataType: "json"
	} )
	.done(function(response) {
		jQuery('#submission_title').text(response[0].form_name);
		var html = '';
		jQuery('#submission_meta').html('<span>#'+response[0].id+'</span>');
		jQuery('#submission_meta').append('<span> on '+response[0].created_date+' at '+response[0].created_time+'</span>');
		jQuery('#submission_body_cover').attr('data-id',response[0].id);
		if (response[0].visitor.IP){jQuery('#submission_meta').append('<span>'+response[0].visitor.IP+'</span>');}
		if (response[0].visitor.URL){jQuery('#submission_meta').append('<span><a target="_blank" href="'+response[0].visitor.URL+'">'+response[0].visitor.URL+'</a></span>');}
		var new_content = [];
		for (x in response[0].content)
		{
			if ( typeof response[0].content[x].page == 'undefined' ) { new_content = response[0].content; break; }
			new_content[response[0].content[x].page_name] = new_content[response[0].content[x].page_name] || [];
			if ( response[0].content[x].type=='dropdown' || response[0].content[x].type=='checkbox' )
			{
				if (typeof response[0].content[x].value=='string')
				{
					response[0].content[x].value = [response[0].content[x].value];
				}
				for ( y in response[0].content[x].value )
				{
					for ( z in response[0].content[x].options )
					{
						if ( response[0].content[x].options[z].value==response[0].content[x].value[y] )
						{
							response[0].content[x].value[y] = response[0].content[x].options[z].show;
						}
					}
				}
			}
			else if ( response[0].content[x].type=='matrix' && typeof response[0].content[x].value[y] != 'string' )
			{
				for ( y in response[0].content[x].value )
				{
					response[0].content[x].value[y] = response[0].content[x].value[y].question+': '+response[0].content[x].value[y].value;
				}
			}
			new_content[response[0].content[x].page_name].push({
				label: 			response[0].content[x].label,
				identifier: 	response[0].content[x].identifier,
				type: 			response[0].content[x].type,
				width: 			response[0].content[x].width,
				value: 			response[0].content[x].value,
				url: 			response[0].content[x].url
			});
		}
		for (page in new_content)
		{
			html = html + "<div><span class='title show-"+Object.keys(new_content).length+"'>"+page+"</span>";
			for (field in new_content[page])
			{
				if ( typeof new_content[page][field].url != 'undefined' )
				{
					tempValue = [];
					for (x in new_content[page][field].value)
					{
						tempValue[x] = '<a href="'+new_content[page][field].url[x]+'">'+new_content[page][field].value[x]+'</a>';
					}
					new_content[page][field].value = '';
					new_content[page][field].value = tempValue.join("\n");
				}
				else if (typeof new_content[page][field].value=='object')
				{
					new_content[page][field].value = new_content[page][field].value.join("\n");
				}
				if ( new_content[page][field].type=='checkbox' || new_content[page][field].type=='fileupload' )
				{
					var is_array = true;
				}
				else
				{
					var is_array = false;
				}
				if ( new_content[page][field].type=='fileupload' )
				{
					var is_editable = false;
				}
				else
				{
					var is_editable = true;
				}
				new_content[page][field].width = typeof new_content[page][field].width =='undefined' ? '100%' : new_content[page][field].width;
				var new_class = new_content[page][field].value.trim()=='' ? 'empty' : '';
				if ( new_content[page][field].type=='heading' )
				{
					html = html + "<div style='width: "+new_content[page][field].width+"' class='"+new_class+"'><span data-array='"+is_array+"' data-editable='"+is_editable+"' data-identifier='"+new_content[page][field].identifier+"' class='is-heading value editable-"+is_editable+"'>"+new_content[page][field].value+"</span></div>";
				}
				else
				{
					html = html + "<div style='width: "+new_content[page][field].width+"' class='"+new_class+"'><span class='label'>"+new_content[page][field].label+"</span><span data-array='"+is_array+"' data-editable='"+is_editable+"' data-identifier='"+new_content[page][field].identifier+"' class='value editable-"+is_editable+"'>"+new_content[page][field].value+"</span></div>";
				}
			}
			html = html + "</div>";
		}
		jQuery('#submission_body').html(html);
		jQuery('#submission_body_cover').removeClass('editing');
	})
.fail(function(response) {
	toastr["error"]("Connection Error");
})
.always(function(response){
	jQuery('#submission_body_cover').removeClass('loading');
});
});
jQuery('body').on('click','.trash-form',function(event){
	event.preventDefault();
	var r = confirm(FC_1.confirm_delete);
	if (r == false) {
		return false;
	}
	var form = jQuery(this).attr('data-id');
	jQuery(this).css('opacity',.2);
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "GET",
		context: jQuery(this),
		data: 'action=formcraft3_del_form&form='+form,
		dataType: "json"
	} )
	.done(function(response) {
		if (response.failed)
		{
			toastr["error"](response.failed);
		}
		else if(response.success)
		{
			jQuery('.form_list .form-'+response.form_id).slideUp();
			toastr["success"]("<i class='icon-ok'></i> "+response.success);
		}
	})
	.fail(function(response) {
		jQuery(this).find('.response').text('Connection error');
	})
	.always(function(response) {
		jQuery(this).find('button,[type="submit"]').removeAttr('disabled');
		jQuery(this).find('.fcb-spinner').hide();
	});
});
jQuery('body').on('click','#trash-files',function(event){
	event.preventDefault();
	list = [];
	jQuery('.files_checked:checked').each(function(){
		list.push(jQuery(this).val());
	});
	if (list.length==0){return false;}
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "GET",
		context: jQuery(this),
		data: 'action=formcraft3_file_delete_admin&files='+list,
		dataType: "json"
	} )
	.done(function(response) {
		if (response.failed)
		{
			toastr["error"](response.failed);
		}
		else if(response.success)
		{
			jQuery('.files-pagination span.active').trigger('click');
			toastr["success"]("<i class='icon-ok'></i> "+response.success);
		}
	})
	.fail(function(response) {
		jQuery(this).find('.response').text('Connection error');
	})
	.always(function(response) {
		jQuery(this).find('button,[type="submit"]').removeAttr('disabled');
		jQuery(this).find('.fcb-spinner').hide();
	});
});
jQuery('body').on('click','#trash-subs',function(){
	list = [];
	jQuery('.subs_checked:checked').each(function(){
		list.push(jQuery(this).val());
	});
	if (list.length==0){return false;}
	jQuery('.subs_list .loader').show();
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "GET",
		context: jQuery(this),
		data: 'action=formcraft3_del_submissions&list='+list,
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
			jQuery('.subs_checked_parent').prop('checked', false).trigger('change');
			getSubmissions(1,jQuery('#which-form').val(),jQuery('#subs-search-input').val());
		}
	})
	.fail(function(response) {
		toastr["error"]("Connection Error");
	})
	.always(function(response){
		jQuery('.subs_list .loader').hide();
	});
});
jQuery('body').on('change','#which-form',function(){
	getSubmissions(1,jQuery(this).val(),jQuery('#subs-search-input').val());
});
jQuery('[data-toggle="tooltip"]').tooltip({
	hide: function() {
		jQuery(this).animate({marginTop: -100}, function() {
			jQuery(this).css({marginTop: ''});
		});
	}
});
jQuery('input:file').change(function (){
	var fileName = jQuery(this).val();
	var fileName = fileName.replace(/^.*[\\\/]/, '')
	jQuery(this).parent().parent().find('.filename').text(fileName);
});
jQuery('body').on('click','.subs_checked',function(e){
	var checkbox = jQuery('.subs_checked');
	if(!lastChecked) {
		lastChecked = this;
		return;
	}
	if(e.shiftKey) {
		var start = checkbox.index(this);
		var end = checkbox.index(lastChecked);
		checkbox.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked).trigger('change');
	}
	lastChecked = this;
});
jQuery('body').on('click','.files_checked',function(e){
	var checkbox = jQuery('.files_checked');
	if(!lastChecked) {
		lastChecked = this;
		return;
	}
	if(e.shiftKey) {
		var start = checkbox.index(this);
		var end = checkbox.index(lastChecked);
		checkbox.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked).trigger('change');
	}
	lastChecked = this;
});
jQuery('body').on('change','.subs_checked',function(event){
	var len = jQuery('.subs_checked:checked').length;
	if (len==0)
	{
		jQuery('.subs_cover').removeClass('show_options');
		jQuery('.subs_checked_parent').prop('checked', false).trigger('change');
	}
	else
	{
		jQuery('.subs_cover').addClass('show_options');
	}
	if (len==jQuery('.subs_checked').length)
	{
		jQuery('.subs_checked_parent').prop('checked', true).trigger('change');
	}
});
jQuery('body').on('change','.files_checked',function(event){
	var len = jQuery('.files_checked:checked').length;
	if (len==0)
	{
		jQuery('.files_cover').removeClass('show_options');
		jQuery('.files_checked_parent').prop('checked', false).trigger('change');
	}
	else
	{
		jQuery('.files_cover').addClass('show_options');
	}
	if (len==jQuery('.files_checked').length)
	{
		jQuery('.files_checked_parent').prop('checked', true).trigger('change');
	}
});
jQuery('body').on('change','.subs_checked_parent',function(event){
	if (jQuery(this).is(':checked'))
	{
		jQuery('.subs_checked').each(function(){
			if (!jQuery(this).is(':checked')) {
				jQuery(this).prop('checked', true).trigger('change');
			}
		});
	}
	else
	{
		jQuery('.subs_checked').each(function(){
			if (jQuery(this).is(':checked')) {
				jQuery(this).prop('checked', false).trigger('change');
			}
		});
	}
});
jQuery('body').on('change','.files_checked_parent',function(event){
	if (jQuery(this).is(':checked'))
	{
		jQuery('.files_checked').each(function(){
			if (!jQuery(this).is(':checked')) {
				jQuery(this).prop('checked', true).trigger('change');
			}
		});
	}
	else
	{
		jQuery('.files_checked').each(function(){
			if (jQuery(this).is(':checked')) {
				jQuery(this).prop('checked', false).trigger('change');
			}
		});
	}
});
jQuery('body').on('submit','#new_form',function(event){
	event.preventDefault();
	var data = jQuery(this).serialize();
	if (jQuery('#import_form_input').attr('data-file'))
	{
		var data = data + '&file='+jQuery('#import_form_input').attr('data-file');
	}
	jQuery(this).find('.submit-btn').attr('disabled','disabled').addClass('fc-disabled');
	jQuery(this).find('.response').text('').hide();
	jQuery.ajax( {
		url: FC_1.ajaxurl,
		type: "POST",
		cache: false,
		context: jQuery(this),
		data: 'action=formcraft3_new_form&'+data,
		dataType: "json"
	} )
	.done(function(response) {
		if (response.failed)
		{
			jQuery(this).find('.response').html(response.failed).show();
		}
		else if(response.success)
		{
			jQuery(this).find('.response').html(response.success).show();
		}
		if (response.redirect)
		{
			window.location = window.location.href.replace(window.location.hash,'')+response.redirect;
		}
	})
	.fail(function(response) {
		jQuery(this).find('.response').text('Connection error').show();
		jQuery(this).find('.submit-btn').removeAttr('disabled').removeClass('fc-disabled');
	})
	.always(function(response) {
		jQuery(this).find('.submit-btn').removeAttr('disabled').removeClass('fc-disabled');
	});
});
});