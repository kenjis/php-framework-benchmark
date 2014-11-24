function Toolbar(options) {
	this.button = options.button;
	this.panelButtons = options.panelButtons;
	this.content = options.content;
	this.panelClose = options.panelClose;
	this.keyboardScope = options.keyboardScope;
	this.currentRequest = options.currentRequest;
	this.originalRequest = options.originalRequest;
}

Toolbar.prototype = {
	_currentPanel: null,
	_lastPanel: null,
	_state: 0,
	currentRequest: null,
	originalRequest: null,

	states: [
		'collapse',
		'toolbar'
	],

	toggle: function() {
		var state = this.nextState();
		this.updateButtons(state);
		this.updateToolbarState(state);
		window.parent.postMessage(state, window.location.origin)
	},

	state: function() {
		return this.states[this._state];
	},

	nextState: function() {
		this._state++;
		if (this._state == this.states.length) {
			this._state = 0;
		}
		this.saveState();
		return this.state();
	},

	saveState: function() {
		if (!window.localStorage) {
			return;
		}
		window.localStorage.setItem('toolbar_state', this._state);
	},

	loadState: function() {
		if (!window.localStorage) {
			return;
		}
		var old = window.localStorage.getItem('toolbar_state');
		if (!old) {
			old = 0;
		}
		if (old == 0) {
			return this.hideContent();
		}
		if (old == 1) {
			return this.toggle();
		}
	},

	updateToolbarState: function(state) {
		if (state === 'toolbar') {
			this.button.addClass('open');
		}
		if (state === 'collapse') {
			this.button.removeClass('open');
		}
	},

	updateButtons: function(state) {
		if (state === 'toolbar') {
			this.panelButtons.show();
		}
		if (state === 'collapse') {
			this.panelButtons.hide();
		}
	},

	isExpanded: function() {
		return this.content.hasClass('enabled');
	},

	hideContent: function() {
		// slide out - css animation
		this.content.removeClass('enabled');
		// remove the active state on buttons
		this.currentPanelButton().removeClass('panel-active');
		var _this = this;

		// Hardcode timer as one does.
		setTimeout(function() {
			_this._currentPanel = null;
			window.parent.postMessage(_this.state(), window.location.origin);
		}, 250);
	},

	loadPanel: function(id) {
		var url = baseUrl + 'debug_kit/panels/view/' + id;
		var contentArea = this.content.find('#panel-content');
		var _this = this;
		var timer;
		var loader = $('#loader');

		if (this._lastPanel != id) {
			timer = setTimeout(function() {
				loader.addClass('loading');
			}, 500);
		}

		this._currentPanel = id;
		this._lastPanel = id;

		window.parent.postMessage('expand', window.location.origin);

		$.get(url, function(response) {
			clearTimeout(timer);
			loader.removeClass('loading');

			// Slide panel into place - css transitions.
			_this.content.addClass('enabled');
			contentArea.html(response);
			_this.bindNeatArray();
		});
	},

	bindNeatArray: function() {
		var lists = this.content.find('.depth-0');
		lists.find('ul').hide()
			.parent().addClass('expandable collapsed');

		lists.on('click', 'li', function(event) {
			event.stopPropagation();
			var el = $(this);
			el.children('ul').toggle();
			el.toggleClass('expanded')
				.toggleClass('collapsed');
		});
	},

	currentPanel: function() {
		return this._currentPanel;
	},

	currentPanelButton: function() {
		return this.button.find("[data-id='" + this.currentPanel() + "']");
	},

	keyboardListener: function() {
		var _this = this;
		this.keyboardScope.keydown(function(event) {
			// Check for Esc key
			if (event.keyCode === 27) {
				// Close active panel
				if (_this.isExpanded()) {
					return _this.hideContent();
				} 
				// Collapse the toolbar
				if (_this.state() === "toolbar") {
					return _this.toggle();
				}
			}
			// Check for left arrow
			if (event.keyCode === 37 && _this.isExpanded()) {
				_this.panelButtons.removeClass('panel-active');
				var prevPanel = _this.currentPanelButton().prev();
				if (prevPanel.hasClass('panel')) {
					prevPanel.addClass('panel-active');
					return _this.loadPanel(prevPanel.data('id'));
				}
			}
			// Check for right arrow
			if (event.keyCode === 39 && _this.isExpanded()) {
				_this.panelButtons.removeClass('panel-active');
				var nextPanel = _this.currentPanelButton().next();
				if (nextPanel.hasClass('panel')) {
					nextPanel.addClass('panel-active');
					return _this.loadPanel(nextPanel.data('id'));
				}	
			}
		});
	},

	mouseListener : function() {
		var _this = this;
		this.panelButtons.on('click', function(e) {
			_this.panelButtons.removeClass('panel-active');
			e.preventDefault();
			e.stopPropagation();
			var id = $(this).attr('data-id');
			var samePanel = _this.currentPanel() === id;

			if (_this.isExpanded() && samePanel) {
				_this.hideContent();
			}
			if (samePanel) {
				return false;
			}
			$(this).addClass('panel-active');
			_this.loadPanel(id);
		});

		this.button.on('click', function(e) {
			_this.toggle();
		});

		toolbar.panelClose.on('click', function(e) {
			_this.hideContent();
			return false;
		});
	},

	initialize: function() {
		this.mouseListener();
		this.keyboardListener();
		this.loadState();
	}
};
