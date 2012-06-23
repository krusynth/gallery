/* UploadHandler.js

REQUIRES: jQuery

USAGE:
new UploadHandler(DivName, ArgsHash);

new UploadHandler('#my_div_to_watch', { 'upload_url': '/gallery/post/', 'debug': true });
new UploadHandler(['#div1', '#div2'], { 'upload_url': '/gallery/post/' });

Arrays of ids are ok.  .classes work *only* if each element watched element has
an actual id itself!  


This will POST the body of the image to whatever URL you give it.  It's sent
as a base64 encoded string, so it's up to you to decode and handle it! 
*/

UploadHandler = function(elms, args) {
	/* Set defaults.  These get overridden below */
	
	var self = this;
	self.finished_callback = function(result) { console.log(result); }
	self.get_extra_info = function(event) {	return {}; };

	
	
	self.upload_url = '';
	self.upload_method = 'post';
	
	self.messager = new Messager(args['debug']);

	function add_drop_listener(listening_elm) {
		$( listening_elm ).each( function(i, elm) {
			var id = $(elm).attr('id');
			
			/* TODO: Add UUIDs for elements without ids */
			
			// Can't use jQuery handlers for this yet! It's not supported!
			document.getElementById(id).addEventListener("drop", file_drop, false);
		});
	}

	file_drop = function(event) {
		console.log ('dropped!');
	
		event.stopPropagation();
		event.preventDefault();
	
		var files = event.dataTransfer.files;
		var count = files.length;
	
		// Only call the handler if 1 or more files was dropped.
		if (count > 0) {
			handleFiles(event, files);
		}
	}
	
	
	handleFiles = function(event, files) {
		self.event = event;
		
		finish_callback = function(target) {
			var result = jQuery.parseJSON(target.responseText);
			
			self.finished_callback(result);
		}
		
		finish = function () {
			if(this.counter == this.files.length) {
				send(this.fd);
			}
		}
		
		send = function(fd) {
			
			var xhr = new XMLHttpRequest();
			
			if(!self.upload_url.length) {
				self.messager.error('No upload url specified!');
			}
			
			xhr.open(self.upload_method, self.upload_url, true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			
			// Update progress bar
			xhr.upload.addEventListener("progress", function (e) {
				if (e.lengthComputable) {
					var loaded = Math.ceil((e.loaded / e.total) * 100);
					
					self.messager.debug('Sending: '+loaded);
				}
			}, false);
			
			// File uploaded
			xhr.addEventListener("load", function (e) {
				finish_callback(e.target)
			}, false);
			
			// Send data
			xhr.send(fd);
		}
		
		this.fd = new FormData();
		this.counter = 0;
		this.files = files;
	
		for( i=0; i<files.length; i++) {
			handleFile(files[i], i);
		}
	
		function handleFile(file, num) {
			this.file = file;
			var reader = new FileReader();
		
			// init the reader event handlers
			reader.onprogress = handleReaderProgress;
			reader.onloadend = handleReaderLoadEnd.bind(this);
		
			// begin the read operation
			reader.readAsDataURL(file);
		
			function handleReaderProgress(e) {
				if (e.lengthComputable) {
					var loaded = Math.ceil((e.loaded / e.total) * 100);
					
					self.messager.debug('Uploading: '+loaded);
				}
			}
			
			function handleReaderLoadEnd(evt) {
				info = self.get_extra_info(self.event);
				messager.debug(info);
				this.fd.append('files['+this.file.name+'][data]', evt.target.result);
				this.fd.append('files['+this.file.name+'][num]', num);
				
				for(key in info) {
					messager.debug(key+':'+info[key]);
					this.fd.append('files['+this.file.name+']['+key+']', info[key]);
				}
				
				this.counter++;
		
				finish();
			}
		}
	}
	
	// Set the settings
	if(typeof(args) === 'object') {
		for(key in args) {
			this[key] = args[key];
		}
	}
	
	if(elms instanceof Array) {
		for(i = 0; i < count(elms); i++) {
			add_drop_listener(elms[i]);
		}
	}
	else {
		add_drop_listener(elms);
	}
}

function Messager(debug) {
	self = this;
	
	self.debug = false;	
	if(debug) {
		self.debug = debug;
	}
	
	function debug(message) {
		if(self.debug) {
			console.log(message);
		}
	}

	function error(message) {
		console.log(message);
	}
}

function ProgressTracker(handler) {
	this.state = 'Waiting';
	this.progress = 0;
	
	this.handler = handler;
	
	function update(state, progress) {
		this.state = state;
		this.progress = progress;
		
		handle();
	}
	
	function handle() {
		this.handler.update(this);
	}
}

function ProgressBar() {
	elm = $('#progressbar');
	
	if(!elm.length) {
		$('body').append('<div id="progressbar"></div>');
	}
	
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
	
	function update(progress_tracker) {
		
		
	}
}