$(document).ready(function(){
	
// 	$('#sortable').sortable();
// 	$( "#sortable" ).disableSelection();
	
    $('#calendar').fullCalendar({
        height: "auto",
        // defaultView: "basicWeek",
        header: {
            left:   'prev,next today',
    		center: 'title',
    		right:  'basicWeek,month,listYear'
        },
        droppable: true,
		editable: true,
		displayEventTime: false,
// 		allDayDefault: true
// 		eventOverlap: false,
		events: function(start, end, timezone, callback){
			var titels = $('a.recept-lijst');
			var events=[];
			var today = moment();
			titels.each(function(i){
			 	events.push({title:$(this).text().trim(),start:moment(today).add(i,'day')});
			});
			callback(events);
		},
    });
    
    $('#myCalModal').on('shown.bs.modal', function () {
   		$('#calendar').fullCalendar('render');
//    		$('#calendar').fullCalendar('refetchEvents');
	});
    
    $('#printCal').on('click', function (){
		var element = document.getElementById('section-to-print');
		printElement(element);
		window.print();
  	});
  	
  	$('#printlijst').click(function(){
// 		var content = $('#lijst-inhoud').html();
// 		printPage(content);
		var element = document.getElementById('lijst-inhoud');
		printElement(element);
		window.print();
	});

	function printElement(elem, append, delimiter) {
		var domClone = elem.cloneNode(true);

		var $printSection = document.getElementById("printSection");

		if (!$printSection) {
			$printSection = document.createElement("div");
			$printSection.id = "printSection";
			document.body.appendChild($printSection);
		}

		if (append !== true) {
			$printSection.innerHTML = "";
		}

		else if (append === true) {
			if (typeof (delimiter) === "string") {
				$printSection.innerHTML += delimiter;
			}
			else if (typeof (delimiter) === "object") {
				$printSection.appendChlid(delimiter);
			}
		}

		$printSection.appendChild(domClone);
	}
	
	$('#saveCal').on('click', function (){
    var events = $('#calendar').fullCalendar('clientEvents');
    var url = '/boodschappen';
    var data = {events: JSON.stringify(events)};
	console.log(data);
	
    $.ajax({
      type: "POST",
      url: url,
      data: data,
      dataType: "json",
      success: function(response){
      	alert(response);
      },
      error: function(xhr){
		alert(xhr);
		}
    });
  	
  	});
	
    // Load content into modal
	$('body').on('click', 'a#voegitemtoe, a#showrecipe, a#sendmail', function(e) {
		e.preventDefault();
		$('#myModal').modal();
		$('#myModalContent').load($(this).attr('href'));
    });
    
	// Reload page when modal is closed
//     $('#myModal').on('hidden.bs.modal', function () {
//  	location.reload();
// 	});
	
    $('body').on('submit', "form[name='sendmail'], form[name='additem']", function (e) {
        e.preventDefault();
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            context: this
        })
        .done(function (data) {
            if (typeof data.message !== 'undefined') {
                alert(data.message);
                location.reload();
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            if (typeof jqXHR.responseJSON !== 'undefined') {
                if (jqXHR.responseJSON.hasOwnProperty('form')) {
//                     $(this).replaceWith(jqXHR.responseJSON.form);
					$('#myModalContent').html(jqXHR.responseJSON.form);
                }
				
                $('.form_error').html(jqXHR.responseJSON.message);

            } else {
                alert(errorThrown);
            }

        });
    });
    
    $('#showhideafdelingen').click(function(){
    	$('.afdelingselect').toggleClass('hidden');
    });

    $('#showhiderecepten').click(function(){
    	var titel = $('.recept-titel');
    	titel.toggle();
    	$(this).find('i').replaceWith(titel.is(':visible') ? '<i class="fa fa-toggle-on fa-lg" aria-hidden="true"></i>' : '<i class="fa fa-toggle-off fa-lg" aria-hidden="true"></i>');
    });
   
//     $('select').each(function(){
// // 			$(this).find('option[selected]').attr('selected', true);
// 			var val = $(this).find('option[selected]').val();
// 			$(this).val(val);
// 	});
	
	$(document).on('change', 'select', function(){
			$.ajax({
			type: 'POST',
			url: '/boodschappen',
			data: $(this).serialize(),
			success: function(html){
				$('#lijst-inhoud').replaceWith(
					$(html).find('#lijst-inhoud')
				);
				$('.afdelingselect').removeClass('hidden');
				groupDuplicateItems();
				if($('#showhiderecepten').find('i').hasClass('fa-toggle-on')){$('.recept-titel').show();}
			}
		});
	});
	
	groupDuplicateItems();
	
	$(".new-ul").on("hide.bs.collapse", function(){
		$(this).prev('div').find('span').replaceWith('<span class="glyphicon glyphicon-expand"></span>');
	});
	$(".new-ul").on("show.bs.collapse", function(){
		$(this).prev('div').find('span').replaceWith('<span class="glyphicon glyphicon-collapse-down"></span>');
	});


// 	$('ul.ingredient-list').each(function(){
// 		var duplicates = [];
// 		
// 		var text = $('li', this).map(function(){ 
// 			return $(this).contents().eq(2).text().trim();
// 		}).get();
// 		
// 		function getLastWord(someString){
// 			someString = someString.split(' ');
// 			return someString[someString.length-1];
// 		}
// 		
// 		function findDuplicates(array){
// 			var out = [], counts = {};
// 			for(var i=0; i<array.length;i++){
// 				var item = getLastWord(array[i]);
// 				counts[item] = counts[item] >= 1 ? counts[item]+1 : 1;
// 				if(counts[item] === 2){
// 					out.push(item);
// 				}
// 			}
// 			return out;
// 		}
// 		
// 		duplicates = findDuplicates(text);
// 		
// 		var duplicates_pos = [];
// 		for(i=0; i<duplicates.length; i++){
// 			duplicates_pos[i]=[];
// 			for(x=0;x<text.length;x++){
// 				if(duplicates[i]==getLastWord(text[x])){
// 				duplicates_pos[i].push(x);
// 				}
// 			}
// 		}

// 		for(i=0; i<duplicates_pos.length; i++){
// 			var lis = $('li', this).filter(function(index){
// 				return $.inArray(index, duplicates_pos[i]) > -1;		
// 				});
// 			var newLis = lis.wrapAll('<ul class="new-ul"></ul>');
// 		}			
// 	});
// 	$('.new-ul').before('<li>Duplicate items</li>');
			

	
// 	Add checkbox to each li
// 	var checkbox = "<input type='checkbox'>&nbsp;&nbsp;";
// 	$('ul.ingredient-list li').each(function(){
// 		$(this).prepend(checkbox);
// 	});
// 	
// 	$('#remove-item').click(function(){
// 		$('input[type="checkbox"]:checked').each(function(){
// 			$(this).parent().hide();
// 			elements need to be removed from the dom completely in order for jsPDF to ignore them
// 			$(this).parent().remove();
// 		});
// 	});
// 	
// 	$('#undo').click(function(){
// 		if li's are hidden (instead of removed), get them back
// 		$('li[style="display: none;"]').each(function(){
// 			$(this).show();
// 			$(this).children('input[type="checkbox"]').prop('checked', false);
// 		});
// 		location.reload();
// 	});

	
// 	Export to PDF using jsPDF
// 	var specialElementHandlers = { 
//     '#divid': function (element, renderer) { 
//         return true; 
//     	} 
// 	};
// 	$('#exporttopdf').click(function(e) { 
// 		e.preventDefault();
// 		var doc = new jsPDF(); 
// 		doc.fromHTML($('#lijst-inhoud').html(), 15, 15, { 
// 			'width': 190, 
// 			'elementHandlers': specialElementHandlers 
// 		}); 
// 		doc.save('sample-page.pdf');
// 	});
	
});

function printPage(content) {
    var w = window.open();
	
	var header = $('head').html();

    var html = "<!DOCTYPE HTML>";
    html += '<html lang="nl">';
//     html += '<head><style></style></head>';
	html += header;
    html += "<body>";
    html += "<h2>Boodschappenlijst</h2>";
	html += content;
    html += "</body>";
    
    w.document.write(html);
    w.document.close(); // necessary for IE >=10
    w.focus(); // necessary for IE >=10
    w.print();
    w.close();
};

function groupDuplicateItems(){
	$('ul.ingredient-list').each(function(){

		var ingr = $('li', this).map(function(){
			return $(this).find('span').filter('.hoeveelheid, .eenheid, .ingredient');
		});
	
		function findDuplicates(array){
		
			var dupl = [], counts = {}, indexes = {}, sum = {};
		
			function count(item, index, quantity){
				counts[item] = counts[item] >= 1 ? counts[item]+1 : 1;
				if(counts[item] === 2){
					dupl.push(item);
				}
				indexes[item] = counts[item] >= 2 ? indexes[item]+','+index : index;
				sum[item] = counts[item] >= 2 ? sum[item]+quantity : quantity;
			}
		
			for(var i=0; i<array.length;i++){
				if(array[i][0].innerHTML === "" && array[i][1].innerHTML === ""){
					var item = array[i][2].innerHTML;
					count(item, i, 0);
				}
				if(array[i][0].innerHTML !== "" && array[i][1].innerHTML !== ""){
					var item = array[i][1].innerHTML + " " + array[i][2].innerHTML;
					var q = Number(array[i][0].innerHTML);
					count(item, i, q);
				}
				if(array[i][0].innerHTML !== "" && array[i][1].innerHTML === ""){
					var item = array[i][2].innerHTML;
					var q = Number(array[i][0].innerHTML);
					count(item, i, q);
				}
			}
// 				console.log(dupl);
// 				console.log(counts);
// 				console.log(indexes);
// 				console.log(sum);
			
			out = {};
			for(var i=0;i<dupl.length;i++){
				if(sum[dupl[i]]>0){
				out[sum[dupl[i]]+' '+dupl[i]] = indexes[dupl[i]]; 
				}
				else{
				out[dupl[i]] = indexes[dupl[i]];
				}
			}
// 				console.log(out);
		
			for (x in out){
				var temp;
				temp = out[x].split(',');
				for (a in temp){
					temp[a] = parseInt(temp[a], 10);
				}
				out[x] = temp;
			}
		
			return out;
		}
	
		var duplicates = findDuplicates(ingr);
// 			console.log(duplicates);

		for(x in duplicates){
			var lis = $('li', this).filter(function(index){
				return $.inArray(index, duplicates[x]) > -1;		
				});
			newUl = $('<ul class="new-ul collapse" id="' + x.replace(/[^\w]/gi, '') + '"></ul>');
			lis.wrapAll(newUl);
			$('li', this).eq(duplicates[x][0]).parent().before('<div data-toggle="collapse" data-target="#' + x.replace(/[^\w]/gi, '') + '">' + '<span class="glyphicon glyphicon-expand"></span>&nbsp;&nbsp;' + x + '</div>');

		}			
	});
}