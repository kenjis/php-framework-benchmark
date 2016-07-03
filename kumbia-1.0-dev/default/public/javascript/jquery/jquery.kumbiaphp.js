/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * Plugin para jQuery que incluye los callbacks basicos para los Helpers
 *
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license	http://wiki.kumbiaphp.com/Licencia	 New BSD License
 */

(function($) {
	/**
	 * Objeto KumbiaPHP
	 *
	 */
	$.KumbiaPHP = {
		/**
		 * Ruta al directorio public en el servidor
		 *
		 * @var String
		 */
		publicPath : null,

		/**
		 * Plugins cargados
		 *
		 * @var Array
		 */
		plugin: [],

		/**
		 * Muestra mensaje de confirmacion
		 *
		 * @param Object event
		 */
		cConfirm: function(event) {
			var este=$(this);
			if(!confirm(este.data('msg'))) {
				event.preventDefault();
			}
		},

		/**
		 * Aplica un efecto a un elemento
		 *
		 * @param String fx
		 */
		cFx: function(fx) {
			return function(event) {
				event.preventDefault();
				var este=$(this),
					rel = $('#'+este.data('to'));
				rel[fx]();
			}
		},

		/**
		 * Carga con AJAX
		 *
		 * @param Object event
		 */
		cRemote: function(event) {
			var este=$(this), rel = $('#'+este.data('to'));
			event.preventDefault();
			rel.load(this.href);
		},

		/**
		 * Carga con AJAX y Confirmacion
		 *
		 * @param Object event
		 */
		cRemoteConfirm: function(event) {
			var este=$(this), rel = $('#'+este.data('to'));
			event.preventDefault();
			if(confirm(este.data('msg'))) {
				rel.load(this.href);
			}
		},

		/**
		 * Enviar formularios de manera asincronica, via POST
		 * Y los carga en un contenedor
		 */
		cFRemote: function(event){
			event.preventDefault();
			este = $(this);
			var button = $('[type=submit]', este);
			button.attr('disabled', 'disabled');
			var url = este.attr('action');
			var div = este.attr('data-to');
			$.post(url, este.serialize(), function(data, status){
				var capa = $('#'+div);
				capa.html(data);
				capa.hide();
				capa.show('slow');
				button.attr('disabled', null);
			});
		},

		/**
		 * Carga con AJAX al cambiar select
		 *
		 * @param Object event
		 */
		cUpdaterSelect: function(event) {
            var $t = $(this),$u= $('#' + $t.data('update'))
				url = $t.data('url');
            $u.empty();
            $.get(url, {'id':$t.val()}, function(d){
				for(i in d){
					var a = $('<option />').text(d[i]).val(i);
					$u.append(a);
				}
			}, 'json');
		},

		/**
		 * Enlaza a las clases por defecto
		 *
		 */
		bind : function() {
            // Enlace y boton con confirmacion
            $("body").on('click', "a.js-confirm, input.js-confirm",this.cConfirm);

            // Enlace ajax
            $("body").on('click', "a.js-remote",this.cRemote);

            // Enlace ajax con confirmacion
            $("body").on('click', "a.js-remote-confirm",this.cRemoteConfirm);

            // Efecto show
            $("body").on('click', "a.js-show",this.cFx('show'));

            // Efecto hide
            $("body").on('click', "a.js-hide",this.cFx('hide'));

            // Efecto toggle
            $("body").on('click', "a.js-toggle",this.cFx('toggle'));

            // Efecto fadeIn
            $("body").on('click', "a.js-fade-in",this.cFx('fadeIn'));

            // Efecto fadeOut
            $("body").on('click', "a.js-fade-out",this.cFx('fadeOut'));

            // Formulario ajax
            $("body").on('submit',"form.js-remote", this.cFRemote);

            // Lista desplegable que actualiza con ajax
            $("body").on('change',"select.js-remote", this.cUpdaterSelect);

            // Enlazar DatePicker
			$.KumbiaPHP.bindDatePicker();
			
		},

        /**
         * Implementa la autocarga de plugins, estos deben seguir
         * una convención para que pueda funcionar correctamente
         */
        autoload: function(){
            var elem = $("[class*='jp-']");
            $.each(elem, function(i, val){
                var este = $(this); //apunta al elemento con clase jp-*
                var classes = este.attr('class').split(' ');
                for (i in classes){
                    if(classes[i].substr(0, 3) == 'jp-'){
                        if($.inArray(classes[i].substr(3),$.KumbiaPHP.plugin) != -1)
                            continue;
                        $.KumbiaPHP.plugin.push(classes[i].substr(3))
                    }
                }
            });
            var head = $('head');
            for(i in $.KumbiaPHP.plugin){
                $.ajaxSetup({ cache: true});
                head.append('<link href="' + $.KumbiaPHP.publicPath + 'css/' + $.KumbiaPHP.plugin[i] + '.css" type="text/css" rel="stylesheet"/>');
				$.getScript($.KumbiaPHP.publicPath + 'javascript/jquery/jquery.' + $.KumbiaPHP.plugin[i] + '.js', function(data, text){});
            }
		},
		
		/**
		 * Carga y Enlaza Unobstrusive DatePicker en caso de ser necesario
		 *
		 */
		bindDatePicker: function() {
			
			// Selecciona los campos input
			var inputs = $('input.js-datepicker');
			/**
			 * Funcion encargada de enlazar el DatePicker a los Input
			 *
			 */
			var bindInputs = function() {
				inputs.each(function() {
					var opts = {monthSelector: true,yearSelector:true};
					var input = $(this);
					// Verifica si hay mínimo
					if(input.attr('min') != undefined) {
						opts.dateMin = input.attr('min').split('-');
					}
					// Verifica si ha máximo
					if(input.attr('max') != undefined) {
						opts.dateMax = input.attr('max').split('-');
					}

					// Crea el calendario
					input.pickadate(opts);
				});
			}

			// Si ya esta cargado Unobstrusive DatePicker, lo integra de una vez
			if(typeof($.pickadate) != "undefined") {
				return bindInputs();
			}

			// Carga la hoja de estilos
			$('head').append('<link href="' + this.publicPath + 'css/pickadate.css" type="text/css" rel="stylesheet"/>');

			// Carga Unobstrusive DatePicker, para poder usar cache
			jQuery.ajax({ dataType: "script",cache: true, url: this.publicPath + 'javascript/jquery/pickadate.js'}).done(function(){
				bindInputs();
			});
		},

		/**
		 * Inicializa el plugin
		 *
		 */
		initialize: function() {
			// Obtiene el publicPath, restando los caracteres que sobran
			// de la ruta, respecto a la ruta de ubicacion del plugin de KumbiaPHP
			// "javascript/jquery/jquery.kumbiaphp.js"
			var src = $('script:last').attr('src');
			this.publicPath = src.substr(0, src.length - 37);

			// Enlaza a las clases por defecto
			$(function(){
				$.KumbiaPHP.bind();
				$.KumbiaPHP.autoload();
				
			});
		}
	}

	// Inicializa el plugin
	$.KumbiaPHP.initialize();
})(jQuery);
