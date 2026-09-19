/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/front.scss"
/*!************************!*\
  !*** ./src/front.scss ***!
  \************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/ 	
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/front.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _front_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./front.scss */ "./src/front.scss");

const triggerSelector = '.infinite-scroll-trigger';
const more = trigger => {
  console.log(trigger);
  const node = document.createElement('div');
  const url = `/wp-admin/admin-ajax.php?action=load_more&page=${trigger.getAttribute('data-page')}&block=${trigger.getAttribute('data-block-key')}&query=${trigger.getAttribute('data-query-key')}`;

  // TODO add spinner

  fetch(url).then(async response => {
    node.innerHTML = await response.text();
    trigger.replaceWith(...node.querySelectorAll('ul>li'));
  });
};
const observeTrigger = trigger => {
  trigger.setAttribute('data-observed', true);
  moreObserver.observe(trigger);
};
const moreObserver = new IntersectionObserver((entries, observer) => {
  console.log(entries);
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      observer.unobserve(entry.target);
      more(entry.target);
    }
  });
}, {
  root: null,
  rootMargin: '15px',
  threshold: 1.0
});
document.querySelectorAll(triggerSelector).forEach(observeTrigger);
const domObserver = new MutationObserver((mutations, domObserver) => {
  Array.from(mutations).forEach(entry => {
    // endless scroll observer
    entry.target.querySelectorAll(triggerSelector).forEach(observeTrigger);
  });
});
domObserver.observe(document.body, {
  subtree: true,
  childList: true
});
})();

/******/ })()
;
//# sourceMappingURL=front.js.map