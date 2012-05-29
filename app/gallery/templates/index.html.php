<!DOCTYPE HTML> 
<html lang="en"> 
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>HTML5 Drag & Drop Uploader</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
		<style>
.gallery ul {
	list-style: none;
	margin: 0px;
	padding: 0px;
}

.gallery li {
	display: inline-block;
	margin: 0px;
	padding: 0px;
}			
		</style>
            <script>
$(function() {

	$( ".gallery ul" ).sortable({
		connectWith: ".gallery ul"
	}).disableSelection();
	
	document.getElementById("gallery1").addEventListener("drop", file_drop, false);
	document.getElementById("gallery2").addEventListener("drop", file_drop, false);
	document.getElementById("gallery3").addEventListener("drop", file_drop, false);
	
});

function file_drop(event) {
	event.stopPropagation();
	event.preventDefault();

	var files = event.dataTransfer.files;
	var count = files.length;

	// Only call the handler if 1 or more files was dropped.
	if (count > 0)
		new handleFiles(files);
}


function handleFiles(files) {
	this.finish = function () {
		if(this.counter == this.files.length) {
			this.send(this.fd);
		}
	}
	
	this.send = function(fd) {
		var xhr = new XMLHttpRequest();
		xhr.open("post", '/gallery/post/', true);
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		
		// Update progress bar
		xhr.upload.addEventListener("progress", function (e) {
			if (e.lengthComputable) {
				var loaded = Math.ceil((e.loaded / e.total) * 100);
				
				console.log(loaded);
			}
		}, false);
		
		// File uploaded
		xhr.addEventListener("load", function (e) {
			var result = jQuery.parseJSON(e.target.responseText);
			
			console.log('Uploaded');
			console.log(result);
		}, false);
		
		// Send data
		xhr.send(fd);
	}
	
	this.fd = new FormData();
	this.counter = 0;
	this.files = files;

	for( i=0; i<files.length; i++) {
		console.log(files[i].name);
		new handleFile(files[i], this);
	}
}

handleFile = function (file, handler) {
	this.handler = handler;
	this.file = file;
	var reader = new FileReader();

	// init the reader event handlers
	reader.onprogress = handleReaderProgress;
	reader.onloadend = handleReaderLoadEnd.bind(this);

	// begin the read operation
	reader.readAsDataURL(file);
	
	function handleReaderProgress(evt) {
		if (evt.lengthComputable) {
// ####			var loaded = (evt.loaded / evt.total);
	
// ####			$("#progressbar").progressbar({ value: loaded * 100 });
		}
	}
	
	function handleReaderLoadEnd(evt) {
		this.handler.fd.append('files['+this.file.name+']', evt.target.result);
		
		this.handler.counter++;

		this.handler.finish();

	}
}


function ProgressBar() {
	elm = $('#progressbar');
	
	val = 0;

	function show() {
		elm.show();
	}
	
	function hide() {
		elm.hide();
	}
	
	function value(val) {
		if(typeof val !== 'undefined') {
			this.val = val;
		}
		
		return this.val;
	}
}
			
            </script>
		
    </head>
    <body>
        <div id="devcontainer">



           
            <div id="areas">
                
                <div id="gallery1" class="gallery">
	                <ul class="images"><li><img src="/assets/images/blue.png" /></li><li><img src="/assets/images/cyan.png" /></li><li><img src="/assets/images/lime.png" /></li><li><img src="/assets/images/orange.png" /></li><li><img src="/assets/images/purple.png" /></li><li><img src="/assets/images/salmon.png" /></li><li><img src="/assets/images/yellow.png" /></li></ul>
	            </div>
                <div id="gallery2" class="gallery">
	                <ul class="images"><li><img src="/assets/images/blue.png" /></li><li><img src="/assets/images/cyan.png" /></li><li><img src="/assets/images/lime.png" /></li><li><img src="/assets/images/orange.png" /></li><li><img src="/assets/images/purple.png" /></li><li><img src="/assets/images/salmon.png" /></li><li><img src="/assets/images/yellow.png" /></li></u>
	            </div>
                <div id="gallery3" class="gallery">
	                <ul class="images"><li><img src="/assets/images/blue.png" /></li><li><img src="/assets/images/cyan.png" /></li><li><img src="/assets/images/lime.png" /></li><li><img src="/assets/images/orange.png" /></li><li><img src="/assets/images/purple.png" /></li><li><img src="/assets/images/salmon.png" /></li><li><img src="/assets/images/yellow.png" /></li></u>
	            </div>

            </div>



            <!-- /development area -->
        </div>
    </body>
</html> 
