/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {

	// Load plugin specific language pack
	//tinymce.PluginManager.requireLangPack('rain');

	var tiny_mce_plugin_rain_url,
		tiny_mce_plugin_rain_upload_url;

	tinymce.create('tinymce.plugins.RainPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			tiny_mce_plugin_rain_url		= library_url + 'Form/plugins/tiny_mce/plugins/rain/';
			tiny_mce_plugin_rain_upload_url = tiny_mce_plugin_rain_url + 'upload.php?content_id='+content_id;

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceRain');
			ed.addCommand('mceRain', function() {
				//var url = "adm/inc/raincp/plugins/tiny_mce/plugins/rain/upload.php?content_id="+content_id
				ed.windowManager.open({
					file : tiny_mce_plugin_rain_upload_url,
					width : 320,
					height : 100,
					inline : 1
				}, {
					plugin_url : tiny_mce_plugin_rain_upload_url
				});

			});

			// Register rain button
			ed.addButton('rain', {
				title : 'Upload Image',
				cmd : 'mceRain',
				image : tiny_mce_plugin_rain_url + 'img/rain.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('rain', n.nodeName == 'IMG');
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Rain plugin',
				author : 'Some author',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://raincms.com',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('rain', tinymce.plugins.RainPlugin);
})();