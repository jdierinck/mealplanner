$(document).ready(function(){
	$('.btn-group button').click(function(){
		$(this).addClass('active').siblings().removeClass('active');
	});
	
	$('#list').click(function(){$('#recepten .item').addClass('list-group-item');});
 	$('#grid').click(function(){$('#recepten .item').removeClass('list-group-item');});
	
	// initialize tooltips
	$('[data-toggle="tooltip"]').tooltip();
	
	// Load content into modal
	$('body').on('click', 'a#addrecipe, a#showrecipe, a#editrecipe, a#deleterecipe', function(e) {
		e.preventDefault();
		$('#myModal').modal();
		$('#myModalContent').load($(this).attr('href'));
    });
    
    $('body').on('submit', "form[name='recept']", function (e) {
    	
    	e.preventDefault();
    	
    	var options = {
		target: '#myModalContent',
		dataType: 'json',
		success: function(jsondata, statusText, xhr, $form){
//            $('#myModalContent').html(jsondata.message);
 			$('#myModal').modal('hide');
            location.reload(true);
			},
		error: function(jsondata, statusText, xhr, $form) {
			if (jsondata.responseJSON.hasOwnProperty('form')) {
				$('#myModalContent').html(jsondata.responseJSON.form);
				}
			$('.form_error').html(jsondata.responseJSON.message);
			}
		};
		
		$('form[name="recept"]').ajaxSubmit(options);

	});
	
	
	$('select.filter').change(function(){
		var data = $(this).attr('id') + '=';
		var value = $(this).val();
		if (value){
			data += value;
		}
		data = getFilterData($(this), data);
		sendAjaxForm(data);
	});

// 	$('input#zoek').next().children('button').on('click', function(e){
	$('form[name="filters"]').on('submit', function(e){
		e.preventDefault();
		var input = $('input#zoek');
		var data;
		var value = input.val();
		data = 'zoek' + '=' + value;
		data = getFilterData(input, data);
		sendAjaxForm(data);		
	});
	
	function getFilterData(element, data){
// 		var selects = $(element).parent().siblings('div').children('select, input');
		var selects = $(element).closest('form').find('select, input').not($(element));
		var i;
		for (i=0;i<selects.length;i++){
			if ($(selects[i]).val()) {
				data += '&' + $(selects[i]).attr('id') + '=' + $(selects[i]).val();
			}
		}
		return data;	
	}
	
	function sendAjaxForm(data){
		$.ajax({
			type: 'GET',
			url: '/recepten',
			data: data,
			success: function(html){
				$('#content').replaceWith(
					$(html).find('#content')
				);
				if ($('#list').hasClass('active')) { $('#recepten .item').addClass('list-group-item'); }
			}
		});	
	}

});

// function initAjaxForm()
// {
//     $('body').on('submit', "form[name='recept']", function (e) {
// 
//         e.preventDefault();
// 
//         $.ajax({
//             type: $(this).attr('method'),
//             url: $(this).attr('action'),
//             data: $(this).serialize()
//         })
//         .done(function (data) {
//             if (typeof data.message !== 'undefined') {
// //                 alert(data.message);
//                 $('#myModal').modal('hide');
//                 location.reload(true);
//             }
//         })
//         .fail(function (jqXHR, textStatus, errorThrown) {
//             if (typeof jqXHR.responseJSON !== 'undefined') {
//                 if (jqXHR.responseJSON.hasOwnProperty('form')) {
//                     $('#myModalContent').html(jqXHR.responseJSON.form);
//                 }
// 
//                 $('.form_error').html(jqXHR.responseJSON.message);
// 
//             } else {
//                 alert(errorThrown);
//             }
// 
//         });
//     });
// }  
