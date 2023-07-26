/*
 * CKEditor 5's FileLoader instance.
 * ckeditr image upload kezelés és toolbar definiálás
 * 
 * file upload feldolgozó szerver
 * 	 input POST egy darab file upload, sid, record JsonStr(optional)
 *   result {error:'errorTxt'} vagy {url:'teljes file URL'}
 * 
 * a ckeditorInit(selector) hivás után az editor widow.editors[selector] -al elérhető
 * szükség esetén az upload server átdefiniálható a fenti hívás előtt: 
 *      widow.ckeditorUploadServer = HREF('controller.api_upload'); default: 'upload.php'
 */

	class MyUploadAdapter {
		constructor( loader ) {
			this.loader = loader;
// ================================ config =========================================			
			// URL ahol a file upload feldolgozo server van
			if (window.ckeditorUploadServer != undefined) {
				this.url = window.ckeditorUploadServer;
			} else {	
				this.url = 'upload.php';
			}	
			// megengedett file kiterjesztések
			this.uploads = ['jpg','jpeg','png','gif','tif'];
// ================================ config =========================================			
		}

		// Starts the upload process.
		upload() {
			return this.loader.file
				.then( file => new Promise( ( resolve, reject ) => {
					this._initRequest();
					this._initListeners( resolve, reject, file );
					this._sendRequest( file ); // ez küldi fel a file -t
				} ) );
		}

		// Aborts the upload process.
		abort() {
			if ( this.xhr ) {
				this.xhr.abort();
			}
		}


		_initRequest() {
			const xhr = this.xhr = new XMLHttpRequest();

			xhr.open( 'POST', this.url ,true);
			xhr.responseType = 'json';
		}


		_initListeners( resolve, reject,file ) {
			const xhr = this.xhr;
			const loader = this.loader;
			const genericErrorText = 'Couldn\'t upload file:' + ` ${ file.name }.`;

			xhr.addEventListener( 'error', () => reject( ' A '+genericErrorText ) );
			xhr.addEventListener( 'abort', () => reject(' B ') );
			xhr.addEventListener( 'load', () => {
				const response = xhr.response;
				if ( !response || response.error ) {
					// alert('response error'+response.error);
					return reject( response && response.error ? response.error : genericErrorText );
				}
				//console.log(response);
				// If the upload is successful, resolve the upload promise with an object containing
				// at least the "default" URL, pointing to the image on the server.
				/**
				 * response.url tartalma 'relPath/fileName.ext'
				 */ 
				resolve( {
					
					default: response.url
				} );
			} );

			if ( xhr.upload ) {
				xhr.upload.addEventListener( 'progress', evt => {
					if ( evt.lengthComputable ) {
						loader.uploadTotal = evt.total;
						loader.uploaded = evt.loaded;
					}
				} );
			}
		}

		// Prepares the data and sends the request.
		_sendRequest(file) {
			// most a a file.name tartalma: 'filename.ext' 
			const data = new FormData();
			data.append('upload', file );
			data.append('sid', app.sid);
			//csrf_token CSRF protection
			//data.append('csrf_token', requestToken);  
			this.xhr.send( data );
		}
	}

	function MyCustomUploadAdapterPlugin( editor ) {
		editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
			return new MyUploadAdapter( loader );
		};
	}

	function ckeditorInit(domElementSelector) {
				if (window.editors == undefined) window.editors = [];
				if (window.editors[domElementSelector] == undefined) {
                ClassicEditor
                .create( document.querySelector( domElementSelector ), {
                    language: 'hu',
                    extraPlugins: [ MyCustomUploadAdapterPlugin],
                    toolbar : {
							items: [
								'heading',
								'findAndReplace',
								'|',
								'bold',
								'italic',
								'underline',
								'link',
								'bulletedList',
								'numberedList',
								'|',
								'outdent',
								'indent',
								'alignment',
								'|',
								'imageUpload',
								'blockQuote',
								'insertTable',
								'mediaEmbed',
								{
									label: 'Font style',
									icon: 'text',
									items: ['fontBackgroundColor','fontColor','fontFamily','fontSize']
								},	
								'removeFormat',
								'specialCharacters',
								'subscript',
								'superscript',
								'undo',
								'redo',
								'sourceEditing',
							]
					},
                    mediaEmbed: {
                        extraProviders: [
                        {
                                name: 'tiktok',
                                url: /^tiktok\.com\/(.+)/,
                                html: match => `video: https://tiktok.com/${ match[ 1 ] }`
                            },
                            {
                                name: 'fb_watch',
                                url: /^fb\.watch\/(.+)/,
                                html: match => `fb.watch video`
                            },
                            {
                                name: 'facebook',
                                url: /^facebook\.com\/(.+)/,
                                html: match => `facebook video`
                            },
                            {
                                name: 'other',
                                url: /(.+)/,
                                html: match => `other video`
                            }
                            
                        ]
            		}
                } )
                .then( editor => {
                    window.editor = editor;
					if (window.editors == undefined) window.editors = [];
					window.editors[domElementSelector] = editor;
                } )
                .catch( err => {
					console.log('ckeditor error');
                    console.log( err );
                } );
            }	
	}
