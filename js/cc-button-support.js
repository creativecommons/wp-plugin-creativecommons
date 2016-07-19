////////////////////////////////////////////////////////////////////////////////
// NOTES
////////////////////////////////////////////////////////////////////////////////

// clipboard.js is a hard dependency. Do bundled and non-bundled versions

// We use "suite" to refer to CC BY/NC/ND/SA, "pd" to refer to CC Zero and the
// Public Domain Mark, and "license" to refer to both categories.

////////////////////////////////////////////////////////////////////////////////
// Polyfills
// Some other IE 8 support is attached to our object, not to Object
////////////////////////////////////////////////////////////////////////////////

// Source: https://github.com/Alhadis/Snippets/blob/master/js/polyfills/IE8-child-elements.js
if(!("firstElementChild" in document.documentElement)){
  Object.defineProperty(Element.prototype, "firstElementChild", {
    get: function(){
      for(var nodes = this.children, n, i = 0, l = nodes.length; i < l; ++i)
        if(n = nodes[i], 1 === n.nodeType) return n;
      return null;
    }
  });
}

////////////////////////////////////////////////////////////////////////////////
// Constructor
////////////////////////////////////////////////////////////////////////////////

var CCButton = function (config) {
  config = config || {};
  // How long to show toast transient notifications for
  this.TOAST_TIMEOUT = 5000;
  // The type of node we create to place the button controls inside
  this.nodeType = config['nodeType']
    || 'div';
  // The class(es) that we set the node class to
  this.nodeClass = config['nodeClass']
    || 'cc-button-element';
  // Whether to try to include a link to the media referenced by the license
  this.includeMediaLink = config['includeMediaLink']
    || false;
  // If there is a license in a block with one of these classes, ignore it
  this.containerClassesToSkip = config['containerClassesToSkip']
    || ['widget-area'];
  // The height to set our controls to (false means to guess)
  this.nodeHeight = config['nodeHeight']
    || false;

  // Set event handlers, overriding existing ones if supplied
  if (config['toastDisplay']) {
    this.toastDisplay = config['toastDisplay'];
  }
  if (config['helpDisplay']) {
    this.helpDialog = config['helpDisplay'];
  }

  this.showingHelpDialog = false;

  this.createClipboard();
};

////////////////////////////////////////////////////////////////////////////////
// Utilities
////////////////////////////////////////////////////////////////////////////////

// Handle IE < 9
CCButton.prototype.removeEventListener = function (target, name, handler) {
  document.removeEventListener
    ? target.removeEventListener(name, handler)
    : target.detachEvent('on' + name, handler);
};

// Handle IE < 9
CCButton.prototype.stopPropagation = function (event) {
  event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
};

CCButton.prototype.showElement = function (element) {
  element.style.display = 'block';
};

CCButton.prototype.hideElement = function (element) {
  element.style.display = 'none';
};

CCButton.prototype.toggleVisible = function (element) {
  if (element.style.display == 'block') {
    element.style.display = 'none';
  } else {
    element.style.display = 'block';
  }
};

// Insert newNode after referenceNode within the same parent node

CCButton.prototype.insertAfter = function (newNode, referenceNode) {
  referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
};

CCButton.prototype.parentOfClass = function (node, className) {
  var result = false;
  // Support older IE and avoid JQuery here
  var paddedClassName = ' ' + className + ' ';
  // Walk up the parents
  for (var parent = node.parentNode;
       parent !== null;
       parent = parent.parentNode) {
    // If a parent matches, return it
    if ((' ' + parent.className + ' ').indexOf(paddedClassName) > -1) {
      result = parent;
      break;
    }
  }
  return result;
};

CCButton.prototype.addClassEventListener = function (className, eventName, fun) {
  var elements = document.getElementsByClassName(className);
  Array.prototype.forEach.call(elements, function (element) {
    element.addEventListener(eventName, fun);
  });
};

////////////////////////////////////////////////////////////////////////////////
// Basic UI functions
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.toastDisplay = function (message, level) {
  var self = this;
  var toast = document.createElement('div');
  toast.className = 'cc-transient-notice';
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(function () { document.body.removeChild(toast); },
             self.TOAST_TIMEOUT);
};

CCButton.prototype.helpDialog = function (helpContent) {
  if (! this.showingHelpDialog) {
    var self = this;
    var popup = document.createElement('div');
    popup.className = 'cc-help-popup';
    popup.innerHTML = helpContent;
    // Also triggered when user clicks on "read more", which is probably OK
    document.addEventListener('click',
                              function (e) {
                                self.removeEventListener(document,
                                                         'click',
                                                         arguments.callee);
                                document.body.removeChild(popup);
                                self.showingHelpDialog = false;
                              });
    document.body.appendChild(popup);
    this.showingHelpDialog = true;
  }
};

////////////////////////////////////////////////////////////////////////////////
// Clipboard actions and messages
// Very much copied from toastr, attribute or change!
////////////////////////////////////////////////////////////////////////////////

// Simplistic detection, do not use it in production
CCButton.prototype.clipboardFallbackMessage = function (action) {
  var actionMsg = '';
  var actionKey = (action === 'cut' ? 'X' : 'C');

  if(/iPhone|iPad/i.test(navigator.userAgent)) {
    actionMsg = 'No support :(';
  }
  else if (/Mac/i.test(navigator.userAgent)) {
    actionMsg = 'Press ⌘-' + actionKey + ' to ' + action;
  }
  else {
    actionMsg = 'Press Ctrl-' + actionKey + ' to ' + action;
  }

  return actionMsg;
};

CCButton.prototype.createClipboard = function () {
  this.clipboard = new Clipboard('.cc-attribution-copy-button');

  var self = this;

  this.clipboard.on('success', function(e) {
    e.clearSelection();
    self.toastDisplay('Copied to clipboard!', 'success');
  });

  this.clipboard.on('error', function(e) {
    self.toastDisplay(self.clipboardFallbackMessage(e.action), 'warning');
  });
};
////////////////////////////////////////////////////////////////////////////////
// UI HTML and CSS
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.SUITE_HELP_CONTENT = '<div class="cc-help-modal">You can re-use CC-licensed materials as long as you follow the license conditions.<br><br>One condition of all CC licenses is <em>attribution</em>.<br><br><a target="_blank", href="https://wiki.creativecommons.org/wiki/Best_practices_for_attribution">Read more about this</a>.</div>';

CCButton.prototype.PD_HELP_CONTENT = '<div class="cc-help-modal">You can re-use material such as this work in the public domain. It doesn&apos;t hurt to attribute it though, as this will help other people. <br><br><a target="_blank", href="https://wiki.creativecommons.org/wiki/Best_practices_for_attribution">Read more about this</a>.</div>';

CCButton.prototype.option_selected = 'cc-dropdown-menu-item cc-dropdown-menu-item-selected';

CCButton.prototype.option_unselected = 'cc-dropdown-menu-item';

////////////////////////////////////////////////////////////////////////////////
// Finding UI elements from each other
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.formatDropdownFromFormatButton = function (button) {
  return button.nextElementSibling;
};

CCButton.prototype.formatDropdownFromFormatLink = function (link) {
  return link.parentElement.parentElement;
};

CCButton.prototype.formatButtonFromFormatLink = function (link) {
  return link.parentElement.parentElement.previousElementSibling;
};

CCButton.prototype.attributionButtonFromFormatLink = function (link) {
  return link.parentElement.parentElement.parentElement.previousElementSibling;
};

CCButton.prototype.formatButtonFromFormatDropdown = function (dropdown) {
  return dropdown.parentElement.previousElementSibling;
};

CCButton.prototype.linkWithinMenuItem = function (menu_item) {
  return menu_item.firstChild;
};

////////////////////////////////////////////////////////////////////////////////
// UI Element data access
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.setButtonAttributionData = function (button, format) {
  button.setAttribute('data-clipboard-text',
                      button.getAttribute('data-cc-attribution-' + format));
};

////////////////////////////////////////////////////////////////////////////////
// UI state management
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.deselectFormatOptions = function (format_dropdown) {
  var self = this;
  var options = format_dropdown.getElementsByClassName('cc-dropdown-menu-item');
  Array.prototype.forEach.call(options,
                               function (option) {
                                 option.className = self.option_unselected;
                               });
};

CCButton.prototype.selectFormatOption = function (format_dropdown, format) {
  var self = this;
  var options = format_dropdown.getElementsByClassName('cc-dropdown-menu-item');
  Array.prototype.forEach.call(options,
                               function (option) {
                                 if (self.linkWithinMenuItem(option)
                                     .getAttribute('data-cc-format')
                                     == format) {
                                   option.className = self.option_selected;
                                 }
                               });
};

////////////////////////////////////////////////////////////////////////////////
// Event handlers for UI elements in the button HTML.
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.option_labels = {'html-rdfa': 'HTML &#x25BC;',
                                    'text': 'Text &#x25BC;'};

CCButton.prototype.updateFormatOptionStates = function (dropdown, format) {
  this.deselectFormatOptions(dropdown);
  this.selectFormatOption(dropdown, format);
};

CCButton.prototype.formatLinkClick = function (event) {
  this.stopPropagation(event);
  // Don't jump to top of page
  event.preventDefault();
  var link = event.target;
  var dropdown = this.formatDropdownFromFormatLink(link);
  this.hideElement(dropdown);
  var format = link.getAttribute('data-cc-format');
  this.updateFormatOptionStates(dropdown, format);
  var attribution_button = this.attributionButtonFromFormatLink(link);
  this.setButtonAttributionData(attribution_button, format);
  this.formatButtonFromFormatLink(link).innerHTML = this.option_labels[format];
  attribution_button.click();
};

CCButton.prototype.formatButtonClick = function (event) {
  this.stopPropagation(event);
  var button = event.target;
  var dropdown = this.formatDropdownFromFormatButton(button);
  this.toggleVisible(dropdown);
};

CCButton.prototype.helpButtonClick = function (event) {
  // Default to license suite help text
  var helpText = (event.target.hasAttribute('data-cc-help-type')
                  && event.target.getAttribute('data-cc-help-type') != 'suite')
      ? this.PD_HELP_CONTENT
      : this.SUITE_HELP_CONTENT;
  this.stopPropagation(event);
  this.helpDialog(helpText);
};

////////////////////////////////////////////////////////////////////////////////
// Public API
////////////////////////////////////////////////////////////////////////////////

// Easier/more robust to add event listeners here rather than write into html

CCButton.prototype.addEventListeners = function () {
  var self = this;
  this.addClassEventListener('cc-attribution-format-select',
                             'click',
                             function (e) { self.formatButtonClick(e); });
  this.addClassEventListener('cc-dropdown-menu-item-link',
                             'click',
                             function (e) { self.formatLinkClick(e); });
  this.addClassEventListener('cc-attribution-help-button',
                             'click',
                             function (e) { self.helpButtonClick(e); });
};
