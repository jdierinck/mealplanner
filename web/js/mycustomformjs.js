	var collectionHolder;

	// setup an "add a ingredient" link
	var addIngredientLink = $('<a href="#" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;Voeg een ingredient toe</a>');
	var newLink = $('<p></p>').append(addIngredientLink);
	
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
	
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder.find(':input').length/3);

    $(addIngredientLink).on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // add a new ingredient form (see next code block)
        addIngredientForm(collectionHolder, newLink);
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

	$('#import').on('click', function(e){
		var bulk = $('#recept_ingredienten_bulk');
		var data = bulk.val();
		var items = data.split('\n');
		for (i=0; i<items.length; i++) {
			addIngredientForm(collectionHolder, newLink);
			$('input[id$="ingredient"]:last').val(items[i]);
		}
		bulk.val('');
		$('#manualtab').tab('show');
	});
		  
	
});