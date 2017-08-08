	var collectionHolder;

	// setup an "add a ingredient" link
	var addIngredientLink = $('<a href="#" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;Voeg een ingredient toe</a>');
	var newLink = $('<span></span>').append(addIngredientLink);

	// Add section link
	var addSectionLink = $('<a href="#" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;Voeg een sectie toe</a>');
	var newSectionLink = $('<span></span>').append(addSectionLink);	
	
$(document).ready(function(){
	
	// Get the table that holds the collection of ingredients
    collectionHolder = $('table#recept_ingredienten');
    
    // add a delete link to all of the existing ingredient form rows
    collectionHolder.find('tr.ingredientform').each(function() {
        addIngredientFormDeleteLink($(this));
    });

    // add the "add a ingredient" anchor after the table
//     collectionHolder.append(newLink);
	collectionHolder.after(newLink);
	collectionHolder.after(newSectionLink);
	
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder.find(':input').length/5);

    $(addIngredientLink).on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // add a new ingredient form (see next code block)
        addIngredientForm(collectionHolder, newLink);
    });

    $(addSectionLink).on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // add a new ingredient form (see next code block)
        addIngredientForm(collectionHolder, newLink);
        var newForm = $('tr.ingredientform:last');
        $(newForm).find('input').not('[id$="ingredient"]').addClass('hidden');
        $(newForm).find('input[id$="ingredient"]').attr('placeholder', 'Bv. "Voor de salade"');
        $(newForm).find('input[id$="section"]').prop('checked', true);
    });
    
	function addIngredientForm(collectionHolder, newLink) {
// 		Get the data-prototype
		var prototype = collectionHolder.data('prototype');

// 		get the new index
		var index = collectionHolder.data('index');

// 		Replace '__name__' in the prototype's HTML to
// 		instead be a number based on how many items we have
		var newForm = prototype.replace(/__name__/g, index);

// 		increase the index with one for the next item
		collectionHolder.data('index', index + 1);

// 		Append the form to the table body
		$(collectionHolder).children('tbody').append(newForm);
		
		// add a delete link to the new form
		var newForm = $('tr.ingredientform:last');
    	addIngredientFormDeleteLink(newForm);
	}
	
	function addIngredientFormDeleteLink(Form) {
		var removeFormA = $('<td><a href="#"><span class="glyphicon glyphicon-remove-circle"></span></a></td>');
		$(Form).append(removeFormA);

		$(removeFormA).on('click', function(e) {
			// prevent the link from creating a "#" on the URL
			e.preventDefault();

			// remove the tr
			Form.remove();
			
		});
	}
	
// 	$('#import').on('click', function(e){
// 		var bulk = $('#recept_ingredienten_bulk');
// 		var form = $(this).closest('form');
// 		var data = {};
//   		data['ingredienten_bulk'] = bulk.val();
// 		  $.ajax({
// 			url : '/new',
// 			type: form.attr('method'),
// 			data : data,
// 			success: function(html) {
// 			  $('table#recept_ingredienten tbody').append(
// 				$(html).find('table#recept_ingredienten tbody tr.ingredientform')
// 			  );
// // 				collectionHolder.find('tr.ingredientform').each(function() {
// // 					addIngredientFormDeleteLink($(this));
// // 				});
// 				collectionHolder.data('index', collectionHolder.find(':input').length/3);		  
// 			}
// 		  });
// 
// 	});

	// Bulk import ingredients: add new form for each one and separate quantity, unit and ingredient
	$('#import').on('click', function(e){
		var bulk = $('#recept_ingredienten_bulk');
		var data = bulk.val();
		var items = data.split('\n');
		for (i=0; i<items.length; i++) {
			addIngredientForm(collectionHolder, newLink);
			var item = items[i];
			item = item.replace(/\s{2,}/, ' ');
			item = item.trim();
			var words = item.split(' ');
			if (words[0].search(/^\d+((\.|,)\d+)?$/) > -1) {
				$('input[id$="hoeveelheid"]:last').val(words[0]);
				words.shift();
				if (words[0].search(/^(gram|gr|g|kilo|kilo\'s|kg|eetlepel|eetlepels|el|koffielepel|koffielepels|kl|theelepel|theelepels|tl|liter|liters|l|deciliter|deciliters|dl|milliliter|mililiter|mililiters|ml|stuk|stuks|cm|stengel|stengels|teen|tenen|teentje|teentjes|pot|potten|potje|potjes|kop|koppen|kopje|kopjes|blik|blikken|blikjes|blikje|bol|bollen|bolletje|bolletjes|zak|zakken|zakje|zakjes|tak|takken|takje|takjes)$/) > -1) {
					$('input[id$="eenheid"]:last').val(words[0]);
					words.shift();
				}
				var rest = words.join(' ');
				$('input[id$="ingredient"]:last').val(rest);
			} else {
				$('input[id$="ingredient"]:last').val(item);
			}
		}
		bulk.val('');
		$('#manualtab').tab('show');
	});

	// Activate CKEditor on field
	CKEDITOR.replace('recept[bereidingswijze]');

	// Make table rows sortable
	$('#sortable').sortable();

	// After reordering an ingredient, re-index each input's id and name attributes to reflect new order
	$('#sortable').on('sortstop', function(event, ui){
		var index = collectionHolder.data('index');
		for (var i=0; i<index; i++){
			var tr = collectionHolder.find('tr.ingredientform')[i];
			$(tr).find('input').each(function(){
				var id = $(this).attr('id');
				var newId = id.replace(/_[0-9]+_/, '_' + i + '_');
				var name = $(this).attr('name');
				var newName = name.replace(/\[[0-9]+]/, '[' + i + ']');
				$(this).attr({
					'id': newId,
					'name': newName
				});				
			});
		}
	});
		  
	
});