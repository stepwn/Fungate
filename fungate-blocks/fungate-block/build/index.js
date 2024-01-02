/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/edit.js":
/*!*********************!*\
  !*** ./src/edit.js ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./editor.scss */ "./src/editor.scss");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__);

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */


/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */





function Edit({
  attributes,
  setAttributes
}) {
  const [isModalOpen, setIsModalOpen] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(false);
  const [dateGroups, setDateGroups] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)([createInitialDateGroup()]);
  const [nftUrl, setNftUrl] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)('');
  const [anyInCollection, setAnyInCollection] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(false);
  const generateShortcode = () => {
    // Construct the shortcode using current attributes
    const shortcodeAttrs = {
      chain: attributes.chain,
      minter: attributes.minter,
      contract: attributes.contract,
      nft_id: attributes.nft_id,
      schedule: attributes.schedule
      // Add any other attributes you need to include in the shortcode
    };
    const shortcode = `[fungate ${Object.entries(shortcodeAttrs).filter(([key, value]) => value).map(([key, value]) => `${key}="${value}"`).join(' ')}]`;

    // Update the attribute
    setAttributes({
      generatedShortcode: shortcode
    });
  };

  // Update the shortcode whenever relevant attributes change
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
    generateShortcode();
  }, [attributes.chain, attributes.minter, attributes.contract, attributes.nft_id, attributes.schedule]);
  const extractNftDetails = url => {
    // Regex for Etherscan
    const etherscanRegex = /https:\/\/etherscan\.io\/nft\/(0x[a-fA-F0-9]+)\/(\d+)/;
    // Adjusted Regex for Loopring Explorer
    const loopringRegex = /https:\/\/explorer\.loopring\.io\/nft\/(0x[a-fA-F0-9]+)-\d+-(0x[a-fA-F0-9]+)-(0x[a-fA-F0-9]+)-\d+/;
    let matches;
    let details = {};

    // Check if the URL matches Etherscan pattern
    matches = url.match(etherscanRegex);
    if (matches) {
      const [, contract, nftId] = matches;
      details = {
        contract: contract,
        nft_id: nftId,
        minter: '' // Etherscan doesn't provide minter in the URL
      };
    } else {
      // Check if the URL matches Loopring pattern
      matches = url.match(loopringRegex);
      if (matches) {
        const [, minter, contract, nftId] = matches;
        details = {
          contract: contract,
          nft_id: nftId,
          minter: minter
        };
      }
    }
    return details;
  };
  const handleNftUrlChange = url => {
    setNftUrl(url);
    extractNftDetails(url);
  };
  // Updated state to manage the NFT collection option
  const [nftCollectionOption, setNftCollectionOption] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)('specific');

  // Update the function to handle changes in the SelectControl
  const handleNftCollectionOptionChange = selectedOption => {
    setNftCollectionOption(selectedOption);

    // Update the attributes based on the selected option
    if (selectedOption === 'anyInCollection') {
      setAttributes({
        nft_id: ''
      });
      setAttributes({
        minter: ''
      });
    } else if (selectedOption === 'anyByMinter') {
      setAttributes({
        contract: ''
      });
      setAttributes({
        nft_id: ''
      });
    } else if (selectedOption === 'specific') {
      setAttributes({
        minter: ''
      });
    }
  };
  function createInitialDateGroup() {
    return {
      date: new Date().toISOString().slice(0, 10),
      time: '00:00',
      minter: '',
      nft_id: '',
      contract: '',
      nftUrl: '',
      anyInCollection: false,
      nftCollectionOption: 'specific' // Add this line
    };
  }
  function handleAddDateGroup() {
    setDateGroups([...dateGroups, createInitialDateGroup()]);
  }
  function handleRemoveDateGroup(index) {
    const newDateGroups = [...dateGroups];
    newDateGroups.splice(index, 1);
    setDateGroups(newDateGroups);
  }
  function handleDateGroupChange(index, key, value) {
    const newDateGroups = [...dateGroups];
    if (key === 'nftUrl') {
      // Extract NFT details if the NFT URL changes
      const details = extractNftDetails(value);
      newDateGroups[index].nft_id = details.nft_id || '';
      newDateGroups[index].contract = details.contract || '';
      newDateGroups[index].minter = details.minter || '';
    } else if (key === 'anyInCollection') {
      // Clear the NFT ID if "any in collection" is checked
      newDateGroups[index].contract = value ? '' : newDateGroups[index].contract;
      newDateGroups[index].minter = '';
      newDateGroups[index].nft_id = '';
    } else if (key === 'anyByMinter') {
      // Clear the NFT ID if "any in collection" is checked
      newDateGroups[index].minter = value ? '' : newDateGroups[index].minter;
      newDateGroups[index].nft_id = '';
      newDateGroups[index].contract = '';
    } else if (key === 'nftCollectionOption') {
      // Update the NFT collection option for the specific date group
      newDateGroups[index].nftCollectionOption = value;
      const details = extractNftDetails(newDateGroups[index].nftUrl);
      newDateGroups[index].nft_id = details.nft_id || '';
      newDateGroups[index].contract = details.contract || '';
      newDateGroups[index].minter = details.minter || '';
      // Update attributes based on the selected option
      if (value === 'anyInCollection') {
        newDateGroups[index].nft_id = '';
        newDateGroups[index].minter = '';
      } else if (value === 'anyByMinter') {
        newDateGroups[index].contract = '';
        newDateGroups[index].nft_id = '';
      } else if (value === 'specific') {
        newDateGroups[index].minter = '';
      }
    } else {
      newDateGroups[index].nft_id = value ? '' : newDateGroups[index].nft_id;
      newDateGroups[index].minter = '';
    }
    newDateGroups[index][key] = value;
    setDateGroups(newDateGroups);
  }
  function handleSaveSchedule() {
    const scheduleArray = [];
    dateGroups.forEach(group => {
      const dateTime = `${group.date} ${group.time}`;
      const scheduleItem = {};
      scheduleItem[dateTime] = {
        minter: group.minter,
        nft_id: group.nft_id,
        contract: group.contract
      };
      scheduleArray.push(scheduleItem);
    });
    setAttributes({
      schedule: JSON.stringify(scheduleArray)
    });
    setIsModalOpen(false);
  }
  const openModal = () => setIsModalOpen(true);
  const closeModal = () => setIsModalOpen(false);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.useBlockProps)()
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Settings', 'fungate-block')
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('NFT URL', 'fungate-block'),
    value: nftUrl,
    onChange: value => handleNftUrlChange(value),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter the URL of the NFT on Etherscan.', 'fungate-block')
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('NFT Collection Option', 'fungate-block'),
    value: nftCollectionOption,
    options: [{
      label: 'Specific NFT',
      value: 'specific'
    }, {
      label: 'Any in the collection',
      value: 'anyInCollection'
    }, {
      label: 'Any by this minter',
      value: 'anyByMinter'
    }],
    onChange: handleNftCollectionOptionChange,
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select how to apply the NFT gating.', 'fungate-block')
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Chain', 'fungate-block'),
    value: attributes.chain,
    options: [{
      label: 'Ethereum',
      value: 'ethereum'
    }, {
      label: 'Loopring',
      value: 'loopring'
    }
    // Add other chains here
    ],
    onChange: chain => setAttributes({
      chain
    })
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Minter', 'fungate-block'),
    value: attributes.minter,
    onChange: minter => setAttributes({
      minter
    })
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Contract', 'fungate-block'),
    value: attributes.contract,
    onChange: contract => setAttributes({
      contract
    })
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('NFT', 'fungate-block'),
    value: attributes.nft_id,
    onChange: nft_id => setAttributes({
      nft_id
    })
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('SCHEDULE', 'fungate-block'),
    value: attributes.schedule,
    onChange: schedule => setAttributes({
      schedule
    })
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
    onClick: openModal
  }, "Open Token Gate Scheduler"))), isModalOpen && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Modal, {
    title: "Token Gate Scheduler",
    onRequestClose: closeModal
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: "dateContainer"
  }, dateGroups.map((group, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "fungate-dateGroup",
    key: index
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "date",
    value: group.date,
    onChange: e => handleDateGroupChange(index, 'date', e.target.value)
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "time",
    value: group.time,
    onChange: e => handleDateGroupChange(index, 'time', e.target.value)
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('NFT URL', 'fungate-block'),
    value: group.nftUrl,
    onChange: value => handleDateGroupChange(index, 'nftUrl', value),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter the URL of the NFT on Etherscan.', 'fungate-block')
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('NFT Collection Option', 'fungate-block'),
    value: group.nftCollectionOption,
    options: [{
      label: 'Specific NFT',
      value: 'specific'
    }, {
      label: 'Any in the collection',
      value: 'anyInCollection'
    }, {
      label: 'Any by this minter',
      value: 'anyByMinter'
    }],
    onChange: value => handleDateGroupChange(index, 'nftCollectionOption', value),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select how to apply the NFT gating.', 'fungate-block')
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    placeholder: "Minter",
    value: group.minter,
    onChange: e => handleDateGroupChange(index, 'minter', e.target.value)
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    placeholder: "NFT",
    value: group.nft_id,
    onChange: e => handleDateGroupChange(index, 'nft_id', e.target.value)
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    placeholder: "Contract",
    value: group.contract,
    onChange: e => handleDateGroupChange(index, 'contract', e.target.value)
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    className: "fungate-removeDate",
    onClick: () => handleRemoveDateGroup(index)
  }, "Remove Rule Change"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
    onClick: handleAddDateGroup
  }, "Add Rule Change")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
    className: "fungate-saveButton",
    onClick: handleSaveSchedule
  }, "Save Schedule")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InnerBlocks, null));
}

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./src/block.json");
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */


/**
 * Internal dependencies
 */




/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  /**
   * @see ./save.js
   */
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"]
});

/***/ }),

/***/ "./src/save.js":
/*!*********************!*\
  !*** ./src/save.js ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);



function save({
  attributes
}) {
  const {
    generatedShortcode
  } = attributes;
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ..._wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps.save()
  }, generatedShortcode, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks.Content, null));
}

/***/ }),

/***/ "./src/editor.scss":
/*!*************************!*\
  !*** ./src/editor.scss ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/style.scss":
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./src/block.json":
/*!************************!*\
  !*** ./src/block.json ***!
  \************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"create-block/fungate-block","version":"0.1.0","title":"Fungate Block","category":"widgets","icon":"smiley","description":"Token Gate Content with Fungate!","example":{},"attributes":{"chain":{"type":"string","default":"ethereum"},"minter":{"type":"string","default":""},"contract":{"type":"string","default":""},"nft":{"type":"string","default":""},"schedule":{"type":"string","default":""},"generatedShortcode":{"type":"string","default":""}},"supports":{"html":false,"anchor":true,"__experimentalSelector":".wp-block-create-block-fungate-block","__experimentalFeatures":{"typography":{"fontSize":true,"lineHeight":true}},"align":true,"__experimentalParent":["core/post-content"],"__experimentalChildBlocks":["core/paragraph","core/image","core/heading"],"__experimentalLayout":{"contentSize":true,"wideSize":true}},"textdomain":"fungate-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkfungate_block"] = globalThis["webpackChunkfungate_block"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map