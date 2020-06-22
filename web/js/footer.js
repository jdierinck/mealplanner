$('body').on('click', 'a[href$="/contact"], a[href$="/about"], a[href$="/whatsnew"]', function(e){
	e.preventDefault();
	$('#footerModal').modal();
	$('#footerModalContent').load($(this).attr('href'));
});

$('body').on('submit', "form[name='contactform']", function(e) {
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

// sticky footer: add JavaScript to get the footer height and set the body marginBottom
// See https://stackoverflow.com/questions/17966140/twitter-bootstrap-3-sticky-footer/41402667#41402667
function setfooter(){
    var ht = document.getElementById("footer").scrollHeight;
    document.body.style.marginBottom = ht + "px";
}

window.addEventListener('resize', function(){
        setfooter();
    }, true);
window.addEventListener('load', function(){
    setfooter();
}, true);
