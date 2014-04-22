if (typeof LETSCAN == 'undefined' || !LETSCAN) {
	var LETSCAN = {};
}
LETSCAN.apps = LETSCAN.apps || {};
(function() {
	var defaultConfig = {
		trigger : null
	};
	LETSCAN.apps.Grabimo = function(userConfig) {
		var that = this;
		that.UI = {};
		that._init(userConfig);
		return {
			setTrigger : function(el) {
				that._setTrigger(el);
			},
			startFlow : function(alias, font) {
				var win = that._render();
				font = encodeURIComponent(font || '');
				var url = "http://www.grabimo.com/app/addGig.html?alias=" + alias + "&font=" + font; 
				if (win.location) {
					win.location = url;
				} else {
					win.src = url;
				}
			},
			closeFlow : function() {
				that._destroy();
			},
			isOpen : function() {
				if (that.grabimoWindow) {
					if (that.grabimoWindow == 'incontext') {
						return that.isOpen;
					} else {
						if (typeof that.grabimoWindow == 'object') {
							return (!that.grabimoWindow.closed);
						} else {
							return false;
						}
					}
				} else {
					return that.isOpen;
				}
			}
		};
	};
	LETSCAN.apps.Grabimo.prototype = {
		name : 'GrabimoFrame',
		isOpen : false,
		grabimoWindow : 'incontext',
		_init : function(userConfig) {
			if (userConfig) {
				for ( var key in defaultConfig) {
					if (typeof userConfig[key] !== 'undefined') {
						this[key] = userConfig[key];
					} else {
						this[key] = defaultConfig[key];
					}
				}
			}
			if (this.trigger) {
				this._setTrigger(this.trigger);
			}
		},
		_launchMask : function() {
			this._createMask();
			this._centerLightbox();
			this._bindEvents();
		},
		_render : function() {
			var ua = navigator.userAgent, win;
			if (ua.match(/iPhone|iPod|iPad|Android|Blackberry.*WebKit/i)) {
				win = window.open('', this.name);
				this.grabimoWindow = win;
				return win;
			} else {
				this._buildDOM();
				this._launchMask();
				this.isOpen = true;
				return this.UI.iframe;
			}
		},
		_buildDOM : function() {
			this.UI.wrapper = document.createElement('div');
			this.UI.wrapper.id = this.name;
			
			this.UI.panel = document.createElement('div');
			this.UI.panel.className = 'panel';
			
			try {
				this.UI.iframe = document.createElement('<iframe name="' + this.name + '">');
			} catch (e) {
				this.UI.iframe = document.createElement('iframe');
				this.UI.iframe.name = this.name;
			}
			this.UI.iframe.scrolling = 'no';
			this.UI.iframe.allowTransparency = 'true';
			
			this.UI.close = document.createElement('a');
			this.UI.close.href = "javascript:;";
			this.UI.close.title = "Close";
			this.UI.close.id = "close";
			this.UI.close.onclick = this._destroy;

			this.UI.mask = document.createElement('div');
			this.UI.mask.className = 'mask';
			this.UI.panel.appendChild(this.UI.iframe);
			this.UI.panel.appendChild(this.UI.close);
			this.UI.wrapper.appendChild(this.UI.mask);
			this.UI.wrapper.appendChild(this.UI.panel);
			document.body.appendChild(this.UI.wrapper);
		},
		_createMask : function(e) {
			var windowWidth, windowHeight, scrollWidth, scrollHeight, width, height;
			var actualWidth = (document.documentElement) ? document.documentElement.clientWidth
					: window.innerWidth;
			if (window.innerHeight && window.scrollMaxY) {
				scrollWidth = actualWidth + window.scrollMaxX;
				scrollHeight = window.innerHeight + window.scrollMaxY;
			} else if (document.body.scrollHeight > document.body.offsetHeight) {
				scrollWidth = document.body.scrollWidth;
				scrollHeight = document.body.scrollHeight;
			} else {
				scrollWidth = document.body.offsetWidth;
				scrollHeight = document.body.offsetHeight;
			}
			if (window.innerHeight) {
				windowWidth = actualWidth;
				windowHeight = window.innerHeight;
			} else if (document.documentElement
					&& document.documentElement.clientHeight) {
				windowWidth = document.documentElement.clientWidth;
				windowHeight = document.documentElement.clientHeight;
			} else if (document.body) {
				windowWidth = document.body.clientWidth;
				windowHeight = document.body.clientHeight;
			}
			width = (windowWidth > scrollWidth) ? windowWidth : scrollWidth;
			height = (windowHeight > scrollHeight) ? windowHeight
					: scrollHeight;
			this.UI.mask.style.width = width + 'px';
			this.UI.mask.style.height = height + 'px';
		},
		_centerLightbox : function(e) {
			var width, height, scrollY;
			if (window.innerWidth) {
				width = window.innerWidth;
				height = window.innerHeight;
				scrollY = window.pageYOffset;
			} else if (document.documentElement
					&& (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
				width = document.documentElement.clientWidth;
				height = document.documentElement.clientHeight;
				scrollY = document.documentElement.scrollTop;
			} else if (document.body
					&& (document.body.clientWidth || document.body.clientHeight)) {
				width = document.body.clientWidth;
				height = document.body.clientHeight;
				scrollY = document.body.scrollTop;
			}

			this.UI.panel.style.left = Math
					.round((width - this.UI.iframe.offsetWidth) / 2)
					+ 'px';
			var panelTop = Math
					.round((height - this.UI.iframe.offsetHeight) / 2)
					+ scrollY;

			if (panelTop < 5) {
				panelTop = 10;
			}
			this.UI.panel.style.top = panelTop + 'px';
		},
		_bindEvents : function() {
			addEvent(window, 'resize', this._createMask, this);
			addEvent(window, 'resize', this._centerLightbox, this);
			addEvent(window, 'unload', this._destroy, this);
		},
		_setTrigger : function(el) {
			if (el.constructor.toString().indexOf('Array') > -1) {
				for (var i = 0; i < el.length; i++) {
					this._setTrigger(el[i]);
				}
			} else {
				el = (typeof el == 'string') ? document.getElementById(el) : el;
				if (el && el.form) {
					el.form.target = this.name;
				} else if (el && el.tagName.toLowerCase() == 'a') {
					el.target = this.name;
				}
				addEvent(el, 'click', this._triggerClickEvent, this);
			}
		},
		_triggerClickEvent : function(e) {
			this._render();
		},
		_destroy : function(e) {
			if (typeof this.grabimoWindow == 'object') {
				try {
					this.grabimoWindow.close();
				} catch (er) {
				}
			}
			if (document.getElementById('GrabimoFrame')) {
				var parentDiv = document.getElementById('GrabimoFrame').parentNode;
				parentDiv.removeChild(document.getElementById('GrabimoFrame'));
			}
			if (this.isOpen && this.UI.wrapper.parentNode) {
				this.UI.wrapper.parentNode.removeChild(this.UI.wrapper);
			}
			if (this.interval) {
				clearInterval(this.interval);
			}
			removeEvent(window, 'resize', this._createMask);
			removeEvent(window, 'resize', this._centerLightbox);
			removeEvent(window, 'unload', this._destroy);
			removeEvent(window, 'message', this._windowMessageEvent);
			this.isOpen = false;
		}
	};

	// === process events =================================================
	// add, remove enevts...
	// ====================================================================
	var eventCache = [];
	function addEvent(obj, type, fn, scope) {
		scope = scope || obj;
		var wrappedFn;
		if (obj) {
			if (obj.addEventListener) {
				wrappedFn = function(e) {
					fn.call(scope, e);
				};
				obj.addEventListener(type, wrappedFn, false);
			} else if (obj.attachEvent) {
				wrappedFn = function() {
					var e = window.event;
					e.target = e.target || e.srcElement;
					e.preventDefault = function() {
						window.event.returnValue = false;
					};
					fn.call(scope, e);
				};
				obj.attachEvent('on' + type, wrappedFn);
			}
		}
		eventCache.push([ obj, type, fn, wrappedFn ]);
	}

	function removeEvent(obj, type, fn) {
		var wrappedFn, item, len, i;
		for (i = 0; i < eventCache.length; i++) {
			item = eventCache[i];
			if (item[0] == obj && item[1] == type && item[2] == fn) {
				wrappedFn = item[3];
				if (wrappedFn) {
					if (obj.removeEventListener) {
						obj.removeEventListener(type, wrappedFn, false);
					} else if (obj.detachEvent) {
						obj.detachEvent('on' + type, wrappedFn);
					}
				}
			}
		}
	}
}());

var grab_multimedia_feedback = new LETSCAN.apps.Grabimo();
