$(document).ready(function(){
	$('.deletor').each(function(index) {
		var that = this;
		$(that).click(function(){
			if(!confirm('Wirklich das Pad "' + $(that).attr('name') + '" unwiederbringlich löschen??')){
				return false;
			}
			var url = $(that).attr('href');
			$('#tr' + $(that).attr('name')).remove();
			$.get(url, function(data) {});			
			return false;
		});
	});	
	$('#addpad').click(function(event){		
		var name = prompt("Name des neuen Pads:");		
		if('' === name || null === name){
			alert('Dann halt nicht...');
			return false;
		}
		$("#silentLink").attr('href', 'http://192.168.0.19:9001/p/' + name).trigger('click');
		$("#silentLink").target = "_blank";
	        window.open($("#silentLink").prop('href'));
		window.setTimeout('location.reload()', 1500);
		return false;
	});
});