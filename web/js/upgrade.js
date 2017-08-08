$(document).ready(function(){
	$('#myModal').on('show.bs.modal', function(e){
		var button = $(e.relatedTarget);
		if (button.attr('id') == 'upgrade-btn') {
			$('#myModalContent').load(button.attr('href'));
		}
	});

	$('#myModal').on('hidden.bs.modal', function(e){
		window.location.reload();
	});

	$('body').on('submit', "form[name='upgradeform']", function(e) {
		e.preventDefault();
		$.ajax({
			type: $(this).attr('method'),
			url: $(this).attr('action'),
			data: $(this).serialize()
		})
		.done(function (data) {
	    	if (typeof data.message !== 'undefined') {
	            // alert(data.message);
	            // $('#footerModal').modal('hide');
	            // location.reload(true);
	            $('.modal-body').html(data.message);
	            $('#myModalContent').append('<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Sluit</button></div>');
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

});