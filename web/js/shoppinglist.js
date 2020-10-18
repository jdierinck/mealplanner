$(document).ready(function(){

	// custom event to update total quantity of merged ingredients
	$(document).on('update:quantity', 'span.hoeveelheid', function(){
		if (localStorage.getItem('merge') == 'true'){
			if ($(this).parent('li').is('[data-merge]')) {
				var mergeid = $(this).parent('li').attr('data-merge');
				var lis = $('li[data-merge="'+mergeid+'"]');
				var q = 0;
				for (var j=0;j<lis.length;j++) {
					var txt = $(lis[j]).find('span.hoeveelheid').text();
					q = !isNaN(parseFloat(txt)) ? q + parseFloat(txt) : q;
				}
				q = q!== 0 ? q.toFixed(1) + ' ' : '';
				var span = $(this).parents('ul.new-ul').prev('div.merge-ingr').find('span.merged-quantity');
				span.text(q);
			}
		}
	});

	// merge ingredients
	if (localStorage.getItem('merge') === null) {
		localStorage.setItem('merge', true);
	}
	if (localStorage.getItem('merge') === 'true') { 
		mergeIngredients();
	} else {
		$('a#togglemerge').find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
	}

	$('a#togglemerge').on('click', function(){
		if (localStorage.getItem('merge') === 'true') {
			$(this).find('i').replaceWith('<i class="fa fa-toggle-off fa-lg" aria-hidden="true"></i>');
			localStorage.setItem('merge', false);
			unMerge();
		} else {
			$(this).find('i').replaceWith('<i class="fa fa-toggle-on fa-lg" aria-hidden="true"></i>');
			localStorage.setItem('merge', true);
			mergeIngredients();
		}
	});

	$(".new-ul").on("hide.bs.collapse", function(){
		$(this).prev('div').find('i').replaceWith('<i class="fa fa-caret-square-o-right"></i>');
	});
	$(".new-ul").on("show.bs.collapse", function(){
		$(this).prev('div').find('i').replaceWith('<i class="fa fa-caret-square-o-down"></i>');
	});

	// Initialize sortables
	var sortables = document.querySelectorAll('ul.ingredient-list');
	// Loop through each nested sortable element
	for (var i = 0; i < sortables.length; i++) {
		new Sortable(sortables[i], {
			disabled: true,
			draggable: 'li',
			sort: false,
			group: {
				name: 'ingredient-list',
			},
			handle: '.grab',
			filter: '.extra_item', // for now extra items can't be moved because they are not persisted
			// Element is dropped into the list from another list
			onAdd: function (/**Event*/evt) {
				var ingr = evt.item.dataset.ingr;
				var dept = evt.to.dataset.dept;
				$.ajax({
					type: 'POST',
					url: '/dept/set/' + ingr,
					data: {'dept': dept},
					beforeSend: function(){
						var indicator = "<span id='loading-indicator'><i class='fa fa-spinner fa-pulse fa-fw'></i></span>";
						$(evt.item).append(indicator);
					},
					complete: function(){
						$(evt.item).find('#loading-indicator').remove();
					}
				});
				// if heading was previously marked as empty remove class
				var heading = $(evt.to).prev('h4');
				if (heading.hasClass('dept-empty')) {
					heading.removeClass('dept-empty');
				}
			},
			// Element is removed from the list into another list
			onRemove: function (/**Event*/evt) {
				// if this was last item to be removed mark heading as empty
				var heading = $(evt.from).prev('h4');
				if ($(evt.from).children().length == 0) {
					heading.addClass('dept-empty');
				}
			}
		});
	}

	localStorage.setItem('sortItems', 'false');

    $('#move-ingredients').click(function(){

		if (localStorage.getItem('sortItems') == 'false') {
			localStorage.setItem('sortItems', 'true');
			// Show all departments available for sorting
			$('.dept-empty').removeClass('hidden');
    		// first unmerge ingredients if necessary
			if (localStorage.getItem('merge') == 'true') {
				unMerge();
			}
			var btnText = 'Stop verplaatsen ingrediënten';
			$('#move-ingredients').text(btnText);
			$('div#sort-items-info').removeClass('hidden');
			// Enable sorting
			var sortables = $('ul.ingredient-list');
			sortables.each(function(){
				var sortable = Sortable.get($(this)[0]); // note: get HTMLelement from jQuery object to find sortable instance
				sortable.option('disabled', false);
			});
			$('li').addClass('grab');
		}
		// else ingredients need to be resorted and remerged so reload from server
		else {
			location.reload();
		}
    });

    $('a#revert-sort').click(function(){
    	$('#move-ingredients').click();
    });

    $('#showhiderecepten').click(function(){
    	var titel = $('.recept-titel');
    	titel.toggle();
	    if (titel.is(':visible')) {
			$(this).find('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');
		} else {
			$(this).find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
		}
    });

    // Load content into modal
    $('#myModal').on('show.bs.modal', function(e){
    	var link = $(e.relatedTarget);
    	$('#myModalContent').load(link.attr('href'), function(){
    		// Hide edit and delete buttons when finished loading
	    	if (link.hasClass('recept-lijst')) {
	    		var modal = $(this);
	    		modal.find('a#editrecipe_modal, a#deleterecipe_modal').addClass('hidden');
		    }
	    });
    });

    $('body').on('submit', "form[name='sendmail']", function (e) {
        e.preventDefault();
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            context: this
        })
	        .done(function (data) {
	            if (typeof data.message !== 'undefined') {
	                location.reload();
	            }
	        })
	        .fail(function (jqXHR, textStatus, errorThrown) {
	            if (typeof jqXHR.responseJSON !== 'undefined') {
	                if (jqXHR.responseJSON.hasOwnProperty('form')) {
						$('#myModalContent').html(jqXHR.responseJSON.form);
	                }
	                $('.form_error').html(jqXHR.responseJSON.message);
	            } else {
	                alert(errorThrown);
	            }

	        });
    });

  	$('#printlijst').click(function(){
		var element = document.getElementById('lijst-inhoud');
		printElement(element);
		window.print();
	});

	// Scale ingredients
	$('.yield-switcher-shopping-list').on('click', function(){
		var url = $(this).attr('data-url');
		// var content = $('div#lijst-inhoud');
		var eventid = $(this).attr('data-eventid');
		var recipeId = $(this).attr('data-recipe');
		var content = $('span.hoeveelheid[data-event="'+eventid+'"][data-recipe="'+recipeId+'"]');
		// var unit = $(this).siblings('#yield-unit');
		var yield = parseInt($(this).siblings('#yield').attr('data-yield'));
		switch ($(this).attr('data-op')) {
			case 'yield-minus':
				if (yield == 1) return;
				yield = yield - 1;
				break;
			case 'yield-plus':
				if (yield == 100) return;
				yield = yield + 1;
		}
		$(this).siblings('#yield').attr('data-yield', yield);
		$(this).siblings('#yield').html(yield);
		$.ajax({
			type: 'POST',
			url: url,
			data: {
				'yield': yield,
				'recipeId': recipeId,
			},
			success: function(html){
				// content.replaceWith($(html).find('div#lijst-inhoud'));
				content.each(function(){
					var ingrId = $(this).attr('data-ingr');
					$(this).replaceWith($(html).find('span.hoeveelheid[data-event="'+eventid+'"][data-recipe="'+recipeId+'"][data-ingr="'+ingrId+'"]'));
					var newSpan = $('span.hoeveelheid[data-event="'+eventid+'"][data-recipe="'+recipeId+'"][data-ingr="'+ingrId+'"]');
					newSpan.trigger('update:quantity');
				});
				// unit.replaceWith($(html).find('#yield-unit'));

			}
		});
	});

	$(document).on('mouseover', 'ul.ingredient-list li', function(){
		$(this).find('span.ingredient-remove').show();
	});
	$(document).on('mouseout', 'ul.ingredient-list li', function(){
		$(this).find('span.ingredient-remove').hide();
	});

	$(document).on('click', 'span.ingredient-remove', function(){
		// Cache ingredient removal
		var ingr = $(this).attr('data-ingr');
		var event = $(this).attr('data-event');
		var self = $(this);
		$.post('/remove/ingr/' + ingr, {
			'event': event,
		},
		function(){
			var list = self.parents('ul');
			var span = self.siblings('span.hoeveelheid');
			// for merged ingredients
			if (list.hasClass('new-ul')) {
				if (self.parents('ul.new-ul').children().length == 2) { // if only 2 <li> left remove wrappers
					self.parents('ul.new-ul').prev('.merge-ingr').remove(); 
					self.parent('li').unwrap();
				}
			}
			self.parent().remove(); // remove <li>
			span.trigger('update:quantity'); // not working, why?
			if (list.children().length === 0) {
				list.prev('h4').addClass('hidden dept-empty'); // hide heading as well
			}			
		}
		);
	});

	$('a#reset').click(function(){
		var r = confirm('Verwijderde items worden teruggeplaatst en manueel toegevoegde items worden verwijderd. Verdergaan?');
		if (r == true) {
			localStorage.removeItem('merge');
			$.post('/shopping-list/reset', function(){
				location.reload();
			});
		}
	});

});


function mergeIngredients() {
	$('ul.ingredient-list').each(function(){

		var mergeids = $('li[data-merge]', this).map(function(){
			return $(this).attr('data-merge');
		}).get();

		if (mergeids.length) {
			// get unique values in array
			var toMerge = mergeids.filter(function(value, index, self){
				return self.indexOf(value) === index;
			});

			for (var i=0;i<toMerge.length;i++) {
				var lis = $('li[data-merge="'+toMerge[i]+'"]');
				var newUl = $('<ul class="new-ul collapse" id="'+toMerge[i]+'" style="list-style:none;"></ul>');
				lis.wrapAll(newUl);
				var ingr = lis.first().find('span.ingredient').text();
				var unit = lis.first().find('span.eenheid').text();
				unit = unit.length ? unit + ' ' : '';
				lis.first().parent().before('<div class="merge-ingr" data-toggle="collapse" data-target="#'+toMerge[i]+'"><i class="fa fa-caret-square-o-right"></i>&nbsp;&nbsp;<span class="merged-quantity"></span>' + unit + ingr + '</div');
				lis.first().find('span.hoeveelheid').trigger('update:quantity'); // fill in total quantities for merged ingredients
			}
		}

	});	
}

function unMerge() {
	$('ul.new-ul li').unwrap();
	$('div.merge-ingr').remove();
}
