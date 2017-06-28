$('body').on('click', 'a[href="/contact"], a[href="/about"]', function(e){
	e.preventDefault();
	// $('#footerModal').modal();
	$('#footerModalContent').load($(this).attr('href'));
});

$('body').on('submit', "form[name='contactform']", function(e) {
	console.log('Ajax');
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
            $('#footerModalContent').append('<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Sluit</button></div>');
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        if (typeof jqXHR.responseJSON !== 'undefined') {
            if (jqXHR.responseJSON.hasOwnProperty('form')) {
                $('#footerModalContent').html(jqXHR.responseJSON.form);
            }
            $('.form_error').html(jqXHR.responseJSON.message);
        } else {
            alert(errorThrown);
        }
    });
});