// javascript:location.href='http://mealplanner.be/import?url='+encodeURIComponent(location.href)

// javascript:(function(){document.body.appendChild(document.createElement('script')).src='http://foo.bar/baz.js';})();

// javascript:(function(){function%20t(){confirm("Er is een probleem opgetreden. Wilt u dit recept importeren op mealplanner.be?");}var%20script=document.createElement('script');script.type='text/javascript';script.src=window.location.protocol+'//mealplanner.be/js/bookmarklet.js?v='+parseInt(Math.random()*99999999);document.body.appendChild(script);try{myBookmark()}catch(i){console.log(i.message);t();}})();

// var myBookmark = function(){
// 	var url = window.location.protocol+'//mealplanner.be/app_dev.php/import?url='+encodeURIComponent(window.location.href);
// 	window.open(url,'mealplanner','location=yes,scrollbars=yes,toolbar=no,resizable=yes,width=550,height=550');
// }

javascript:(function(){var%20url='http://www.jdierinck.be/mealplanner/import?url='+encodeURIComponent(window.location.href);window.open(url,'mealplanner','location=yes,scrollbars=yes,toolbar=no,resizable=yes,width=550,height=550');})();