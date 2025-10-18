/**
 * AYS Item Typeahead Search Component
 * 
 * Provides a modal/dropdown typeahead search interface for selecting items
 * in the invoice editor. Uses the REST endpoint: /wp-json/ays/v1/items/search
 * 
 * Usage:
 * AYSItemTypeahead.init({
 *   triggerSelector: '.btn-add-item',
 *   onSelect: function(item) {
 *     console.log('Selected item:', item);
 *   }
 * });
 */

(function () {
  'use strict';

  window.AYSItemTypeahead = {
    options: {
      triggerSelector: '.btn-add-item',
      modalId: 'ays-item-search-modal',
      searchInputId: 'ays-item-search-input',
      resultsContainerId: 'ays-item-search-results',
      onSelect: null,
    },

    debounceTimer: null,
    debounceDelay: 300,
    currentPage: 1,
    currentSearch: '',
    resultsPerPage: 20,

    /**
     * Initialize the typeahead component
     */
    init: function (options) {
      if (!options) options = {};

      // Merge options
      Object.keys(options).forEach(function (key) {
        if (options.hasOwnProperty(key)) {
          window.AYSItemTypeahead.options[key] = options[key];
        }
      });

      // Create modal HTML
      window.AYSItemTypeahead.createModal();

      // Attach event listeners
      window.AYSItemTypeahead.attachEventListeners();
    },

    /**
     * Create the modal HTML structure
     */
    createModal: function () {
      var modalHTML = '\
<div id="' + window.AYSItemTypeahead.options.modalId + '" class="ays-modal" style="display: none;">\
	<div class="ays-modal-overlay"></div>\
	<div class="ays-modal-content">\
		<div class="ays-modal-header">\
			<h2>Search Items</h2>\
			<button type="button" class="ays-modal-close">&times;</button>\
		</div>\
		<div class="ays-modal-body">\
			<div class="ays-search-input-wrapper">\
				<input \
					type="text" \
					id="' + window.AYSItemTypeahead.options.searchInputId + '" \
					class="ays-item-search-input" \
					placeholder="Search items by description or details..." \
					autocomplete="off"\
				>\
				<span class="ays-search-spinner" style="display: none;">Loading...</span>\
			</div>\
			<div id="' + window.AYSItemTypeahead.options.resultsContainerId + '" class="ays-item-search-results">\
				<p>Type to search for items...</p>\
			</div>\
		</div>\
		<div class="ays-modal-footer">\
			<button type="button" class="button ays-modal-close-btn">Close</button>\
		</div>\
	</div>\
</div>\
			';

      // Append modal to body if not already there
      if (!document.getElementById(window.AYSItemTypeahead.options.modalId)) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
      }

      // Add CSS
      window.AYSItemTypeahead.addStyles();
    },

    /**
     * Add CSS styles for the modal
     */
    addStyles: function () {
      var styleId = 'ays-typeahead-styles';
      if (document.getElementById(styleId)) return;

      var css = '\
<style id="' + styleId + '">\
.ays-modal {\
	position: fixed;\
	top: 0;\
	left: 0;\
	width: 100%;\
	height: 100%;\
	z-index: 9999;\
	display: flex;\
	align-items: center;\
	justify-content: center;\
}\
\
.ays-modal.open {\
	display: flex;\
}\
\
.ays-modal-overlay {\
	position: absolute;\
	top: 0;\
	left: 0;\
	width: 100%;\
	height: 100%;\
	background: rgba(0, 0, 0, 0.5);\
}\
\
.ays-modal-content {\
	position: relative;\
	background: white;\
	border-radius: 8px;\
	box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);\
	width: 90%;\
	max-width: 600px;\
	max-height: 80vh;\
	display: flex;\
	flex-direction: column;\
}\
\
.ays-modal-header {\
	padding: 20px;\
	border-bottom: 1px solid #e5e7eb;\
	display: flex;\
	justify-content: space-between;\
	align-items: center;\
}\
\
.ays-modal-header h2 {\
	margin: 0;\
	font-size: 18px;\
	color: #1f2937;\
}\
\
.ays-modal-close {\
	background: none;\
	border: none;\
	font-size: 24px;\
	cursor: pointer;\
	color: #6b7280;\
	padding: 0;\
	width: 30px;\
	height: 30px;\
	display: flex;\
	align-items: center;\
	justify-content: center;\
}\
\
.ays-modal-close:hover {\
	color: #1f2937;\
}\
\
.ays-modal-body {\
	padding: 20px;\
	overflow-y: auto;\
	flex: 1;\
}\
\
.ays-search-input-wrapper {\
	position: relative;\
	margin-bottom: 15px;\
}\
\
.ays-item-search-input {\
	width: 100%;\
	padding: 10px 12px;\
	border: 1px solid #d1d5db;\
	border-radius: 4px;\
	font-size: 14px;\
}\
\
.ays-item-search-input:focus {\
	outline: none;\
	border-color: #4c51bf;\
	box-shadow: 0 0 0 3px rgba(76, 81, 191, 0.1);\
}\
\
.ays-search-spinner {\
	position: absolute;\
	right: 12px;\
	top: 50%;\
	transform: translateY(-50%);\
	font-size: 12px;\
	color: #6b7280;\
}\
\
.ays-item-search-results {\
	border: 1px solid #e5e7eb;\
	border-radius: 4px;\
	max-height: 400px;\
	overflow-y: auto;\
}\
\
.ays-search-no-results {\
	padding: 20px;\
	text-align: center;\
	color: #6b7280;\
}\
\
.ays-search-result-item {\
	padding: 12px 16px;\
	border-bottom: 1px solid #f0f0f0;\
	cursor: pointer;\
	transition: background 0.2s ease;\
}\
\
.ays-search-result-item:hover {\
	background: #f9fafb;\
}\
\
.ays-search-result-item:last-child {\
	border-bottom: none;\
}\
\
.ays-result-title {\
	font-weight: 600;\
	color: #1f2937;\
	margin: 0 0 4px 0;\
}\
\
.ays-result-details {\
	font-size: 12px;\
	color: #6b7280;\
	margin: 0;\
	display: flex;\
	gap: 12px;\
}\
\
.ays-result-rate {\
	color: #10b981;\
	font-weight: 600;\
}\
\
.ays-result-taxable {\
	color: #f59e0b;\
}\
\
.ays-modal-footer {\
	padding: 16px 20px;\
	border-top: 1px solid #e5e7eb;\
	background: #f9fafb;\
	border-radius: 0 0 8px 8px;\
	display: flex;\
	justify-content: flex-end;\
	gap: 10px;\
}\
\
.ays-modal-close-btn {\
	padding: 8px 16px;\
}\
</style>\
			';

      document.head.insertAdjacentHTML('beforeend', css);
    },

    /**
     * Attach event listeners
     */
    attachEventListeners: function () {
      var modal = document.getElementById(window.AYSItemTypeahead.options.modalId);
      var searchInput = document.getElementById(window.AYSItemTypeahead.options.searchInputId);
      var triggers = document.querySelectorAll(window.AYSItemTypeahead.options.triggerSelector);
      var closeButtons = modal.querySelectorAll('.ays-modal-close, .ays-modal-close-btn');

      // Open modal on trigger click
      triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
          e.preventDefault();
          window.AYSItemTypeahead.openModal();
        });
      });

      // Close modal
      closeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          window.AYSItemTypeahead.closeModal();
        });
      });

      // Close on overlay click
      modal.querySelector('.ays-modal-overlay').addEventListener('click', function () {
        window.AYSItemTypeahead.closeModal();
      });

      // Search input with debounce
      searchInput.addEventListener('input', function (e) {
        clearTimeout(window.AYSItemTypeahead.debounceTimer);
        window.AYSItemTypeahead.currentSearch = e.target.value;
        window.AYSItemTypeahead.currentPage = 1;

        window.AYSItemTypeahead.debounceTimer = setTimeout(function () {
          if (window.AYSItemTypeahead.currentSearch.length === 0) {
            document.getElementById(window.AYSItemTypeahead.options.resultsContainerId).innerHTML = '<p>Type to search for items...</p>';
          } else {
            window.AYSItemTypeahead.searchItems(window.AYSItemTypeahead.currentSearch, 1);
          }
        }, window.AYSItemTypeahead.debounceDelay);
      });

      // Close on Escape key
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
          window.AYSItemTypeahead.closeModal();
        }
      });
    },

    /**
     * Open the modal
     */
    openModal: function () {
      var modal = document.getElementById(window.AYSItemTypeahead.options.modalId);
      modal.classList.add('open');
      document.getElementById(window.AYSItemTypeahead.options.searchInputId).focus();
    },

    /**
     * Close the modal
     */
    closeModal: function () {
      var modal = document.getElementById(window.AYSItemTypeahead.options.modalId);
      modal.classList.remove('open');
      document.getElementById(window.AYSItemTypeahead.options.searchInputId).value = '';
      document.getElementById(window.AYSItemTypeahead.options.resultsContainerId).innerHTML = '<p>Type to search for items...</p>';
    },

    /**
     * Search items via REST API
     */
    searchItems: function (query, page) {
      var spinner = document.querySelector('.ays-search-spinner');
      spinner.style.display = 'block';

      var params = new URLSearchParams();
      if (query) params.append('search', query);
      params.append('page', page || 1);
      params.append('per_page', window.AYSItemTypeahead.resultsPerPage);

      fetch('/wp-json/ays/v1/items/search?' + params.toString())
        .then(function (response) {
          spinner.style.display = 'none';
          if (!response.ok) {
            throw new Error('API request failed');
          }
          return response.json();
        })
        .then(function (data) {
          window.AYSItemTypeahead.displayResults(data);
        })
        .catch(function (error) {
          spinner.style.display = 'none';
          console.error('Error searching items:', error);
          document.getElementById(window.AYSItemTypeahead.options.resultsContainerId).innerHTML = '<div class="ays-search-no-results">Error loading items. Please try again.</div>';
        });
    },

    /**
     * Display search results
     */
    displayResults: function (data) {
      var container = document.getElementById(window.AYSItemTypeahead.options.resultsContainerId);

      if (!data.success || !data.data || data.data.length === 0) {
        container.innerHTML = '<div class="ays-search-no-results">No items found.</div>';
        return;
      }

      var html = '';
      data.data.forEach(function (item) {
        var taxableLabel = item.taxable ? '<span class="ays-result-taxable">🔷 Taxable</span>' : '';
        html += '\
<div class="ays-search-result-item" data-item-id="' + item.id + '" data-item-data=\'' + JSON.stringify(item).replace(/'/g, '&quot;') + '\'>\
	<p class="ays-result-title">' + window.AYSItemTypeahead.escapeHtml(item.description) + '</p>\
	<p class="ays-result-details">\
		<span>' + window.AYSItemTypeahead.escapeHtml(item.details) + '</span>\
		<span class="ays-result-rate">$' + item.rate.toFixed(2) + '</span>\
		' + taxableLabel + '\
	</p>\
</div>\
				';
      });

      container.innerHTML = html;

      // Attach click handlers to results
      container.querySelectorAll('.ays-search-result-item').forEach(function (item) {
        item.addEventListener('click', function () {
          var itemData = JSON.parse(this.getAttribute('data-item-data'));
          window.AYSItemTypeahead.selectItem(itemData);
        });
      });
    },

    /**
     * Handle item selection
     */
    selectItem: function (item) {
      if (typeof window.AYSItemTypeahead.options.onSelect === 'function') {
        window.AYSItemTypeahead.options.onSelect(item);
      }
      window.AYSItemTypeahead.closeModal();
    },

    /**
     * Escape HTML special characters
     */
    escapeHtml: function (text) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }
  };

  // Auto-initialize if we find a trigger element on page load
  document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.btn-add-item')) {
      // Don't auto-init, let the invoice editor initialize it explicitly
    }
  });
})();
