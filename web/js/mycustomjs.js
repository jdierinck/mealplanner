$(document).ready(function(){

	// Delete modal content when modal is hidden
	// This prevents previously loaded content from being shown (briefly) before loading new content
	$('div.modal').on('hide.bs.modal', function(e){
		// $('div.modal-content', this).html('Inhoud wordt geladen...');
		$('div.modal-content', this).html('<div class="text-center"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Loading...</span></div>');
	});

	// Show collapsed content
	$('.collapse').addClass('in');

	$('.btn-group button').click(function(){
		$(this).addClass('active').siblings().removeClass('active');
	});

	$('#list').click(function(){
		localStorage.setItem('view','list');
		$('#gridview').hide();
		$('#listview').show();
	});
 	$('#grid').click(function(){
 		localStorage.removeItem('view');
 		$('#gridview').show();
 		$('#listview').hide();
 	});

	var view = localStorage.getItem('view');
	if(view){
		$('#list').addClass('active').siblings().removeClass('active');
		$('#gridview').hide();
		$('#listview').show();
	}

	// initialize tooltips
	$('[data-toggle="tooltip"]').tooltip();
	//Initialize popovers
	$('[data-toggle="popover"]').popover();

    // Load content into modal
	// $('body').on('click','.showrecipe', function(e){
	// 	$('#myOtherModal').modal();
	// 	$('#myOtherModalContent').load($(this).data('url'));
	// 	var id = $(this).data('id');
	// 	console.log(id);
	// });

	$('#myOtherModal').on('show.bs.modal', function(e){
		var element = $(e.relatedTarget);
		var url = element.data('url');
		$('#myOtherModalContent').load(url);
	});


	$('body').on('click', 'a#addrecipe, a.editrecipe, a.deleterecipe', function(e) {
		e.preventDefault();
		$('#myModal').modal();
		$('#myModalContent').load($(this).attr('href'));
		e.stopPropagation();
    });

	// Prevent modal from opening when clicking on add to shopping list
    $('body').on('click', 'a.addtoshoppinglist', function(e) {
		e.stopPropagation();
    });

	// Prevent modal from opening when clicking on menus list
    $('body').on('click', 'a.menus-popover', function(e) {
		e.stopPropagation();
    });

    $('body').on('submit', "form[name='recept']", function(e) {
    	e.preventDefault();
    	var options = {
			target: '#myModalContent',
			dataType: 'json',
			success: function(jsondata, statusText, xhr, $form){
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

	$('input[type="checkbox"]').on('click', function(e){
		console.log($(this));
		$('form[name="filters"]').submit();
	});

	// $('form[name="filters"]').on('keyup submit change', function(e){
	// 	e.preventDefault();
	// 	var data = $(this).serialize();
	// 	sendAjaxForm(data);
	// });

	// Load content via ajax using pagination
	// $('body').on('click', 'th>a, ul.pagination>li>a', function(e){
	// 	e.preventDefault();
	// 	$.ajax({
	// 		type: 'GET',
	// 		url: $(this).attr('href'),
	// 		data: null,
	// 		success: function(html){
	// 			$('#content').replaceWith(
	// 				$(html).find('#content')
	// 			);
	// 			if ($('#list').hasClass('active')) {
	// 				$('#gridview').hide();
	// 				$('#listview').show();
	// 			}
	// 		}
	// 	});
	// });

	// Fix for Select2 input element not accepting any input
	// See https://github.com/select2/select2/issues/1436
	// and https://stackoverflow.com/questions/18487056/select2-doesnt-work-when-embedded-in-a-bootstrap-modal/19574076#19574076
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};

	// $('select.filter').change(function(){
	// 	var data = $(this).attr('id') + '=';
	// 	var value = $(this).val();
	// 	if (value){
	// 		data += value;
	// 	}
	// 	data = getFilterData($(this), data);
	// 	sendAjaxForm(data);
	// });

// 	$('form[name="filters"]').on('submit', function(e){
// 		e.preventDefault();
// 		var input = $('input#zoek');
// 		var data;
// 		var value = input.val();
// 		data = 'zoek' + '=' + value;
// 		data = getFilterData(input, data);
// 		sendAjaxForm(data);
// 	});

// 	function getFilterData(element, data){
// // 		var selects = $(element).parent().siblings('div').children('select, input');
// 		var selects = $(element).closest('form').find('select, input').not($(element));
// 		var i;
// 		for (i=0;i<selects.length;i++){
// 			if ($(selects[i]).val()) {
// 				data += '&' + $(selects[i]).attr('id') + '=' + $(selects[i]).val();
// 			}
// 		}
// 		return data;
// 	}

	function sendAjaxForm(data){
		$.ajax({
			type: 'GET',
			// url: '/recepten',
			url: Routing.generate('recipes'),
			data: data,
			success: function(html){
				$('#content').replaceWith(
					$(html).find('#content')
				);
				// if ($('#list').hasClass('active')) { $('#recepten .item').addClass('list-group-item'); }
				if ($('#list').hasClass('active')) {
					$('#gridview').hide();
					$('#listview').show();
				}
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
