$(document).ready(function(){

	// var el = document.getElementById('dragFrom');
	Sortable.create(dragFrom, {
		draggable: '.mealplan-item',
		sort: false,
		group: {
			name: 'dragFrom',
			pull: 'clone',
		}
	});

	var droppables = document.querySelectorAll('td.droppable');
	// Loop through each nested sortable element
	for (var i = 0; i < droppables.length; i++) {
		new Sortable(droppables[i], {
			draggable: '.mealplan-item',
			group: {
				name: 'dragTo',
				put: ['dragFrom', 'dragTo'],
			},
			// Element is dropped into the list from another list
			onAdd: function (/**Event*/evt) {
				// var parent = evt.item.parentElement;
				var parent = evt.to;
				$.ajax({
					type: 'POST',
					url: Routing.generate('addRecipeToCal', {id: evt.item.dataset.recipeid}),
					data: {
						day: parent.dataset.day,
						slot: parent.dataset.slot,
						eventId: parent.dataset.eventid,
					},
					beforeSend: function(){
						var indicator = "<span id='loading-indicator'><i class='fa fa-spinner fa-pulse fa-fw'></i></span>";
						$(evt.item).find('.title').append(indicator);
					},
					success: function(data, textStatus, jqXHR){
						// add eventId
						parent.dataset.eventid = data;
						if (!$(evt.item).find('a.removeFromCal').length) {
							var extra = "<span class='pull-right'><a class='removeFromCal' href='/cal/remove/" + evt.item.dataset.recipeid + "' style='display:none;'><i class='fa fa-trash fa-lg'></i></a></span>";
							$(evt.item).find('.title').append(extra);
						}
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert(jqXHR.responseJSON.message);
						location.reload();
					},
					complete: function(){
						$(evt.item).find('#loading-indicator').remove();
					}
				});
			},
			// Element is removed from the list into another list
			onRemove: function (/**Event*/evt) {
				var parent = evt.from;
				$.ajax({
					type: 'POST',
					url: Routing.generate('removeFromCal', {id: evt.item.dataset.recipeid}),
					data: {
						eventid: parent.dataset.eventid,
					},
					success: function(data, textStatus, jqXHR){
						// remove eventid if needed
						if (jqXHR.responseJSON.eventRemoved) {
							parent.dataset.eventid = '';
						}
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert(jqXHR.responseJSON.message);
					},
				});
			},
		});
	}

	$('#clearCal').on('click', function(){
		var r = confirm('Bent u zeker dat u alle items van de planning wil verwijderen?');
		if (r == true) {
			var url = Routing.generate('clearCal');
			$.post(url, function(){
				location.reload();
			});
		}
	});

	$('#menuTabs a').click(function(e) {
  		e.preventDefault();
  		$(this).tab('show');
	});

	$('.deleteMenu').on('click', function(e){
		e.preventDefault();
		var menuId = $(this).data('menu-id');
		var title = $(this).parents('.title').children().first().html();
		var r = confirm('Bent u zeker dat u menu ' + title + ' wil verwijderen?');
		if (r == true) {
			var url = Routing.generate('deleteMenu', {id: menuId});
			$.post(url, function(data, textStatus, jqXHR){
				location.reload();
			})
				.fail(function(jqXHR, textStatus, errorThrown){
					alert(jqXHR.responseJSON.message);
				});
		}
	});	

	$('form[id^="addMenuOptionsForm"]').submit(function(e){
		e.preventDefault();
		if ($(this).find('input[name="menuOptions"]:checked').val() == 'overwrite') {
			var url = Routing.generate('clearCal');
			$.post(url);
		}
		var data = $(this).serialize();
		var url = Routing.generate('addMenuToCal', {id: $(this).find('input[name="menuId"]').val()});
		$.post(url, data, function(data, textStatus, jqXHR){
			location.reload();
		})
			.fail(function(jqXHR, textStatus, errorThrown){
				alert(jqXHR.responseJSON.message);
			});
	});

	$('#saveMenuForm').submit(function(e){
		e.preventDefault();
		var form = $(this);
		var title = form.find('input[name="title"]').val();
		var url = Routing.generate('saveMenuFromCal');
		$.post(url, {name: title}, function(data, textStatus, jqXHR){
			location.reload();
		})
			.fail(function(jqXHR, textStatus, errorThrown){
				alert(jqXHR.responseJSON.message);
			});

	});

    // Load content into modal
    $('#myModal').on('show.bs.modal', function(e){
    	var link = $(e.relatedTarget);
    	$('#myModalContent').load(link.attr('href'), function(){

	    });
    });

    $('[data-toggle="tooltip"]').tooltip();

	$(document).on('click', 'a.removeFromCal', function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		// var eventid = $(this).parents('td.droppable').data('eventid'); // Note: doesn't work after data attribute has been modified
		var eventid = $(this).parents('td.droppable').attr('data-eventid');
		var self = this;
		$.post(url, { eventid: eventid }, function(data, textStatus, jqXHR) {
			// remove eventid if needed
			if (jqXHR.responseJSON.eventRemoved) {
				$(self).parents('td.droppable').attr('data-eventid', '');
			}
			$(self).parents('.mealplan-item').remove();
		});
	});

	$('.datepicker').datepicker({
		todayHighlight: true,
		autoclose: true,
		format: 'dd/mm/yyyy',
		language: 'nl-BE',
		orientation: 'auto',
		container: $('.datepicker').parents('.col-sm-3'),
		startDate: 'today',
	});

	$('#shareCal').popover({
		html: true,
		placement: 'auto',
		content: function(){
			var contentwrapper = $(this).data('contentwrapper');
			return $(contentwrapper).html();
		},
		container: 'body',
		sanitize: false, // important! form elements will be blacklisted otherwise
	});
	$('#shareCal').on('shown.bs.popover', function () {
		$('[data-toggle="tooltip"]').tooltip();
	});

	// Copy iCal URL to clipboard
	$(document).on('click', '#copyBtn', function(){
		var copyText = document.getElementById('icalurl');
		// copyText.focus();
		copyText.select();
		copyText.setSelectionRange(0, 99999); /*For mobile devices*/
		document.execCommand('copy');
	});

	$(document).on('mouseover', '.mealplan-item', function(){
		$(this).find('a.removeFromCal').show();
	});
	$(document).on('mouseout', '.mealplan-item', function(){
		$(this).find('a.removeFromCal').hide();
	});

  	$('#printCal').click(function(){
		var element = document.getElementById('calContent');
		// console.log(element);
		printElement(element);
		window.print();
	});

});

