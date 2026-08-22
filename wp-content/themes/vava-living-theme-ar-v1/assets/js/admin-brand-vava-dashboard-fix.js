(function () {
	'use strict';
	var invalidValue = /^(?:undefined|null|nan)$/i;

	function cleanPanels(root) {
		var scope = root && root.querySelectorAll ? root : document;
		var bookingPanel = document.querySelector('.vava-hover-panel[data-vava-panel="booking"]');
		if (bookingPanel) {
			var stats = bookingPanel.querySelector('.vava-panel-stats');
			if (stats) {
				stats.hidden = true;
				stats.setAttribute('aria-hidden', 'true');
			}
		}
		scope.querySelectorAll('.vava-hover-panel b, .vava-hover-panel strong, .vava-hover-panel span, .vava-hover-panel small').forEach(function (node) {
			if (invalidValue.test((node.textContent || '').trim())) {
				node.textContent = '';
				node.hidden = true;
			}
		});
	}

	function start() {
		cleanPanels(document);
		new MutationObserver(function () { cleanPanels(document); }).observe(document.body, { childList: true, subtree: true });
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
}());
