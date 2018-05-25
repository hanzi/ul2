$(document).ready(function() {
	var upload = document.getElementById('file-upload');

	$('#file-upload').on('change', function(e) {
		ul(e);
		$(this).val('');
	});

	function ul(e) {
		var files = upload.files;
		for(i in files) {
			prepareRequest(files[i]);
		}
	}

	function prepareRequest(file) {
		if( Object.prototype.toString.call(file) == "[object File]" ) {
			var identifier = new Date().getTime();
			var safename = $("div.hidden").text(file.name).html();
			$('#dragdroptext').addClass('hidden');
			$('#progressbars').append('<div id="progress'+identifier+'" class="progress progress-striped"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"><span class="label label-default">'+safename+'</span></div></div>');
			var progressbarElement = $('#progress'+identifier+' .progress-bar');
			if(file.size >= 26214400) { // 25MB
				progressbarElement.attr('aria-valuenow', '100');
				progressbarElement.css('width', '100%');
				progressbarElement.addClass('progress-bar-danger');
				progressbarElement.html(''+file.name+' ist zu groß und wurde nicht hochgeladen');
				return false;
			}
			doRequest(file, identifier, progressbarElement);
		}
	}

	function doRequest(file, identifier, progressbarElement) {
		var fd = new FormData();
		fd.append('file-upload', file);
		$.ajax({
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
					if (evt.lengthComputable) {
						var percentComplete = Math.round((evt.loaded / evt.total)*100);
						progressbarElement.attr('aria-valuenow', percentComplete);
						progressbarElement.css('width', ''+percentComplete+'%');
					}
			   }, false);
			   return xhr;
			},
			type: 'POST',
			url: "upload.php",
			data: fd,
			processData: false, 
			contentType: false,
			success: function(data){
				console.log(data);
				var result = $.parseJSON(data);
				progressbarElement.attr('aria-valuenow', '100');
				progressbarElement.css('width', '100%');
				if(result.success != null) {
					// successfully uploaded
					progressbarElement.html('')
					progressbarElement.addClass('progress-bar-success');
					var filelink = document.createElement("a");
					filelink.href = result.url;
					var filelinkIcon = document.createElement("span");
					filelinkIcon.className = "glyphicon glyphicon-new-window"
					filelinkIcon.style.marginRight = "5px"
					var filelinkInput = document.createElement("input");
					filelinkInput.type = "text"
					filelinkInput.readonly = "readonly"
					filelinkInput.onfocus = function(){ this.focus();this.select(); }
					filelinkInput.value = result.url
					
					progressbarElement.append(filelink);
					filelink.appendChild(filelinkIcon)
					progressbarElement.append(filelinkInput)

					if($('#myfiles tbody #emptyhistory').length != 0) {
						$('#myfiles tbody').html('');
					}
					$('#myfiles tbody').prepend('<tr id="f'+result.success+'" class="active">'+
						'<td class="filename"><a href='+result.url+'>'+result.filename+'</a></td>'+
						'<td class="text-right deletelink"><a data-id="'+result.success+'" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Löschen</a></td>'+
						'</tr>');
					bindDelete();
				} else {
					// upload error
					progressbarElement.addClass('progress-bar-danger');
					progressbarElement.html(result["error"]);
				}
				
			}
		});
	}

	var jumbotron = $('#uploadform');
	jumbotron.on('dragexit', function(e) {
		e.stopPropagation(); 
		e.preventDefault();
		jumbotron.removeClass('dragover');
	});
	jumbotron.on('dragenter dragover', function(e) {
		e.stopPropagation(); 
		e.preventDefault();
		jumbotron.addClass('dragover');
	});
	jumbotron.on('drop', function(e) {
		e.stopPropagation(); 
		e.preventDefault();
		jumbotron.removeClass('dragover');
		var files = e.originalEvent.dataTransfer.files;
		for(i in files) {			
			prepareRequest(files[i]);
		}
	});

	function bindDelete(){
		$('.deletelink a').on('click', function(e) {
			e.stopPropagation(); 
			e.preventDefault();
			deleteFile($(this).data('id'))
		});
	}
	bindDelete();

	function deleteFile(fileId) {
		$.ajax({
			type: "POST",
			url: "delete.php",
			data: { id: fileId }
		}).done(function(data) {
			var result = $.parseJSON(data);
			if(result.success != null) {
				$('#f'+fileId).addClass('success');
				$('#f'+fileId+' .filename a').removeAttr('href').addClass('text-muted');
				$('#f'+fileId+' .deletelink').html('<span class="glyphicon glyphicon-ok"></span> Datei gelöscht');	
			} else {
				$('#f'+fileId).addClass('warning');
				$('#f'+fileId+' .deletelink').html('Fehler beim Löschen, bitte Seite neu laden und erneut versuchen');	
			}
		});
	}

	$('#admin').on('submit', function(e) {
		$.ajax({
			type: "POST",
			url: "admin.php",
			data: { pw: $('input#pw').val() }
		}).done(function(data) {
			$('#admin-result').html(data);
		});
		return false;
	});
});
