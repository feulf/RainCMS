(function(window, undefined) {
	var jQuery = window.jQuery;
	if (window.Aloha === undefined || window.Aloha === null) {
		window.Aloha = {};		
	}
	window.Aloha.settings = {
				logLevels: {'error': false, 'warn': false, 'info': false, 'debug': false},
				errorhandling : false,
				ribbon: false,
				"i18n": {
					// you can either let the system detect the users language (set acceptLanguage on server)
					// In PHP this would would be '<?=$_SERVER['HTTP_ACCEPT_LANGUAGE']?>' resulting in
					// "acceptLanguage": 'de-de,de;q=0.8,it;q=0.6,en-us;q=0.7,en;q=0.2'
					// or set current on server side to be in sync with your backend system
					"current": "en"
				},
				"plugins": {
					"format": {
						// all elements with no specific configuration get this configuration
						config : [ 'b', 'i', 'p', 'quote', 'h1', 'h2', 'h3' ]
					}
				}
			};
})(window);