////////////////////////////////////////////////////////////////////////////////
//TODO
////////////////////////////////////////////////////////////////////////////////

// Remove dependencies:
//  - toastr. Replace with simple js.
//  - simplemodal. Replace with simple js.
// This will allow the removal of JQuery as a dependency.

// Add media type determination for mediaHTML/mediaText .
// Add embed /link / text handling for other media types.

// Detect button height and match, or set in params.


////////////////////////////////////////////////////////////////////////////////
// NOTES
////////////////////////////////////////////////////////////////////////////////

// clipboard.js is a hard dependency. Do bundled and non-bundled versions


var CCButton = function (config) {
    // The type of node we create to place the button controls inside
    this.nodeType = config['nodeType']
	|| 'span';
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
    this.createClipboard();
};

////////////////////////////////////////////////////////////////////////////////
// Resources
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.CC_ICON_18px = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAABmJLR0QA/wD/AP+gvaeTAAACMElEQVQ4y5WUv0sbcRjGP5dLRjnbRQ1Ceqk4piHVyXbImsVMAcFkCQgSm5xa6B+guPoX2FCoZOoQKhkylv4B7WQcWigIbpUKQrx8vadL7jCxEvuM74+H93l/WfwbT4HnwCsgBQj4BXwFfgK/eQTeAqeAbNuW67pyXVe2bWtIeAq8m0TyEdDs7KxOTk7k+76MMTLGyPd9dTodJZPJkLD9EMkHy7K0ubmpSajX64rFYgI+A9Zdki1A1WpVj0W9Xg8ri2Q6wPfp6emRwMFgcC953DYzMxP27AlABlCr1ZIkBUGgg4MDra6uqlarqdfrSdKI7ezsTJLUbrfDqnKRLN/3JUnr6+uKx+Pa2dnRwsKCCoWCyuWybNvW7u6u0um08vm8JMn3/bBXDYBD13VljNHl5aVSqZQymUwUeH19rcXFRS0vL0uSbm5uImnGGLmuK+AwBiAp6rplWQRBAEAikeDq6grLsjDGRP7b29uR+HByb+5KW1tbUzwel+d5Wlpa0srKikqlkmzb1vb2trLZrHK5nIIg0GAwUCKREOABvAB0fHwclbu/v69isahGo6Hz83MZY7S3txfZLi4uJEndbjds9sto/I7j/Pf45+fnBfSGtwlADVClUlEQBI9aSM/z7i1kiPeAyuXyRLKNjY1w7J/GT2SEbGpqSkdHR+r3+9HR9vt9NZtNOY4TVtKa9AFqwLdhsObm5u5evIa+rfEk6wEyB3gGvB4+OIAfwJfhg/sznvAX84BJ9VztlGoAAAAASUVORK5CYII=';

CCButton.prototype.SHARE_18px = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASCAQAAAD4MpbhAAAAAmJLR0QA/4ePzL8AAAD2SURBVCjPbdFNK4RxFAXw3zwzjSZ5+QYzGGpWyqxYWEi+gShlQ8TaQjY2PoW9vGylpKxIoqyUkiikNAsWw4R4bJ5Gev73bk6d073n3pMVqoyyORVP6mF60LdY7Ew5JMjaFSc9GwUEP7qauDOXoosmtCe44SLzj8ybNqVhW8mYd5u2Mhi1qMW+R/NabdjzIKfbh3sxfWpisborqyppSytNx4dKaTry5+LFW+jqos9kQs2CQkhSdeDUjHG3TgwpIK9Dm2z6h2uereu37Nq5Sfn0vF476snSLyPBMF02L1uKgmHdNPFrOO7h5HlHejLBFZGqAQ3H7n4BpPJL0/n4/qAAAAAASUVORK5CYII=';

CCButton.prototype.ATTRIBUTION_BUTTON = '<button class="cc-attribution-button cc-attribution-copy-button" data-clipboard-action="copy"><span data-l10n-id="Share">Share</span> <img src="'
    + CCButton.prototype.CC_ICON_18px +
    '" class="cc-attribution-button-cc"> <img src="'
    + CCButton.prototype.SHARE_18px +
    '" class="cc-attribution-button-share"></button>\
<select class="cc-attribution-format-select">\
    <option data-l10n-id="HTML" value="html">HTML</option>\
    <option data-l10n-id="Text" value="text">Text</option>\
</select> \
<button class="cc-attribution-help-button" data-l10n-id="?">?</button>';

CCButton.prototype.ATTRIBUTION_HELP_MODAL = '<div class="cc-help-modal">You can use CC-licensed materials as long as you follow the license conditions.<br>One condition of all CC licenses is <em>attribution</em>.<br><a target="_blank", href="https://wiki.creativecommons.org/wiki/Best_practices_for_attribution">Read more about this</a>.</div>';

CCButton.prototype.insertAttributionHelpModal = function () {
    if (! jQuery('.cc-help-modal').length) {
	jQuery('body').append(this.ATTRIBUTION_HELP_MODAL);
	jQuery('.cc-help-modal').dialog({autoOpen: false});
    }
};

////////////////////////////////////////////////////////////////////////////////
// Utilities
////////////////////////////////////////////////////////////////////////////////

// Simple image node predicate

CCButton.prototype.isImage = function (node) {
    return node.nodeName == 'IMG';
};

// COPY and sanitize an image tag as text

CCButton.prototype.sanitizedImageTag = function (source) {
    var img = '<img src="' + source.src + '"';
    if (img.alt) {
	img += ' alt="' + img.alt + '"';
    }
    return img + '>';
};

// Insert newNode after referenceNode within the same parent node

CCButton.prototype.insertAfter = function (newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
};

// Search backwards and upwards from (and including) start for a node that
// causes predicate to return true.
// Return the matching node, or false on failure.

CCButton.prototype.searchBackwards = function (start, predicate) {
    var result = false;
    var node = start;
    // We need to break two loops deep, so we place a label here
    mainloop:
    while (node) {
	if (predicate(node)) {
	    result = node;
	    break;
	}
	// Walk back to the previous node
	var next = node.previousSibling;
	// If there are no more previous nodes in the current parent block...
	while (! next) {
	    // And there is no parent node we're at the very start, so finish
	    if (! node.parentNode) {
		break mainloop;
	    }
	    // Otherwise try the node prior to the current node's parent node
	    next = node.parentNode.previousSibling;
	}
	node = next;
    }
    return result;
};

CCButton.prototype.anyParentIsClass = function (node, className) {
    var result = false;
    // Support older IE and avoid JQuery here
    var paddedClassName = ' ' + className + ' ';
    // Walk up the parents
    for (var parent = node.parentNode;
	 parent !== null;
	 parent = parent.parentNode) {
	// If any have that class, note it
	if ((' ' + parent.className + ' ').indexOf(paddedClassName) > -1) {
	    result = true;
	    break;
	}
    }
    return result;
};

////////////////////////////////////////////////////////////////////////////////
// Get the link for the media.
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.pageTextLink = function (node) {
    return window.location;
};

// Get the first image above the license block on the page
// Returns the image node, or false on failure

CCButton.prototype.closestImageBefore = function (licenseBlock) {
    return this.searchBackwards(licenseBlock.start, isImage);
};

CCButton.prototype.mediaHTML = function (licenseBlock, type) {
    //TODO: type == true means auto, type == image etc means use that type
    type = 'img';
    var html = "";
    if (this.includeMediaLink == 'img') { 
	var image = this.closestImageBefore(licenseBlock);
	if (image) {
	    html = this.sanitizedImageTag(image);
	}
    }
    if (html != "") {
	html +=  + "<br>\n";
    }
    return html;
};

CCButton.prototype.mediaText = function (licenseBlock, type) {
    //TODO: type == true means auto, type == image etc means use that type
    type = 'img';
    var text = "";
    if (type == 'img') { 
	var image = this.closestImageBefore(licenseBlock);
	if (image) {
	    text += image.src;
	}
    }
    if (text != "") {
	text +=  + "\n";
    }
    return text;
}

////////////////////////////////////////////////////////////////////////////////
// License block location and extraction
////////////////////////////////////////////////////////////////////////////////

// Determine whether an <a> has rel="license" and contains an icon button

CCButton.prototype.isLicenseIconLink = function (element) {
    // The element has a license rel link
    return (element.getAttribute('rel') == 'license')
    // And is wrapping an image, so is the icon
    //TODO: check for cc icon url src OR don't, for icons cached elsewhere?
	&& ((! element.firstChild)
            || (element.firstChild.nodeName == 'IMG'));
};

CCButton.prototype.shouldUseLicenseIconLink = function (element) {
    var found = false;
    for (var i = 0; i < this.containerClassesToSkip.length; i++) {
	if (this.anyParentIsClass(element, this.containerClassesToSkip[i])) {
	    found = true;
	    break;
	}
    }
    return ! found;
};

// Get all <a rel="license"><img> elements in the document.

CCButton.prototype.getLicenseIconLinks = function () {
    var llinks = [];
    var as = document.getElementsByTagName('a');
    for (var i = 0; i < as.length; i++) {
	var a = as[i];
	if (this.isLicenseIconLink(a) && this.shouldUseLicenseIconLink(a)) {
	    llinks.push(a);
	}
    }
    return llinks;
};

// Content that *may* be in the license block after the initial icon link.
// This will overshoot and include any trailing text after the final "." .

CCButton.prototype.maybePartOfLicenseBlock = function (element) {
    var is = false;
    // As well as obvious license markup, we include line breaks and text.
    // Text is node type 3.
    if ((element.nodeName == 'BR')
	|| (element.nodeType == 3)) {
	is = true;
    } else if (element.nodeName == 'SPAN') {
	if ((element.getAttribute('property') == 'dct:title')
            || (element.getAttribute('property') == 'cc:attributionName')) {
	    is = true;
	}
    }
    else if (element.nodeName == 'A') {
	if ((element.getAttribute('rel') == 'cc:attributionURL')
            || (element.getAttribute('property') == 'cc:attributionName')
            || (element.getAttribute('rel') == 'license')
            || (element.getAttribute('rel') == 'dct:source')
            || (element.getAttribute('rel') == 'cc:morePermissions')) {
	    is = true;
	}
    }
    return is;
}

// Find the last possible element at the same level after start that *may* be in
// the license block. This will include any trailing non-license text after the
// final ".".

CCButton.prototype.growLicenseBlock = function (start) {
    var end = start;
    while (end.nextSibling && this.maybePartOfLicenseBlock(end.nextSibling)) {
	end = end.nextSibling;
    }
    return end;
};

// Find the last element within the license block before or including end.
// This will include the final "." of the license block, if present, but not
// any text elements after it.
// Doing this trims the license block to the correct length.

CCButton.prototype.shrinkLicenseBlock = function (end) {
    while (end.nodeType == 3) {
	end = end.previousSibling;
    }
    if (end.nextSibling.nodeValue.trim() == '.') {
	end = end.nextSibling;
    }
    return end;
};

// Grow and then shrink the range of elements after start to include just those
// we are reasonably sure are in the license block.

CCButton.prototype.findLicenseBlockEnd = function (start) {
    var end = this.growLicenseBlock(start);
    return this.shrinkLicenseBlock(end);
};

// Convert the DOM section representing the license block to HTML text

CCButton.prototype.extractLicenseBlockHTML = function (licenseBlock) {
    var text = "";
    if (this.includeMediaLink !== false) {
	text += this.mediaHTML(licenseBlock);
    }
    for (var node = licenseBlock.start;
	 node != licenseBlock.end;
	 node = node.nextSibling) {
	// Handle both tags (which have outerHTML) and just text (which doesn't)
	text += node.outerHTML || node.textContent;
    }
    // Our for loop won't be called for the last node, so handle it here
    if (licenseBlock.end != licenseBlock.start) {
	// Handle both tags (which have outerHTML) and just text (which doesn't)
	text += licenseBlock.end.outerHTML || licenseBlock.end.textContent;
    }
    return text.trim() + "\n";
};

// Convert the DOM section representing the license block to plain text

CCButton.prototype.extractLicenseBlockText = function (licenseBlock) {
    var text = "";
    if (this.includeMediaLink !== false) {
	text += this.mediaText(licenseBlock);
    }
    for (var node = licenseBlock.start;
	 node != licenseBlock.end;
	 node = node.nextSibling) {
	text += node.textContent + " ";
	// Make sure we keep a link to the license, even in the text version
	if (node.nodeName == 'A' && node.getAttribute('rel') == 'license') {
	    text += '<' + node.getAttribute('href') + '> ';
	}
    }
    // Our for loop won't be called for the last node, so handle it here
    if (licenseBlock.end != licenseBlock.start) {
	text += licenseBlock.end.textContent;
    }
    return text.trim() + "\n";
};

// Get the starts and ends of all the license blocks.

CCButton.prototype.getLicenseBlocks = function () {
    var blocks = [];
    var licenseIconLinks = this.getLicenseIconLinks();
    for (var i = 0; i < licenseIconLinks.length; i++) {
	var start = licenseIconLinks[i];
	//var parent = start.parentElement;
	var end = this.findLicenseBlockEnd(start);
	blocks.push({start:start, end:end});
    }
    return blocks;
};

////////////////////////////////////////////////////////////////////////////////
// Add the attribution buttons
////////////////////////////////////////////////////////////////////////////////

CCButton.prototype.configureAttributionCopyButton = function (buttonNode,
							      licenseHTML) {
    // Set the initial attribution text
    var copyAttributionButton =
	buttonNode.getElementsByClassName('cc-attribution-copy-button')[0];
    copyAttributionButton.setAttribute('data-clipboard-text', licenseHTML);
};

CCButton.prototype.configureAttributionSelect = function (buttonNode,
							  licenseHTML,
							  licenseText) {
    var copyAttributionButton =
	buttonNode.getElementsByClassName('cc-attribution-copy-button')[0];
    // Update the attribution text when the user selects a format
    var formatSelect = buttonNode.getElementsByTagName('SELECT')[0];
    formatSelect.addEventListener('change', function (event) {
	if (this.selectedIndex == 0) {
	    copyAttributionButton.setAttribute('data-clipboard-text',
					       licenseHTML);
	} else if (this.selectedIndex == 1) {
	    copyAttributionButton.setAttribute('data-clipboard-text',
					       licenseText);
	} 
    });
    // Copy the selected format for convenience
    formatSelect.addEventListener('click', function (event) {
	copyAttributionButton.click();
    });
};

CCButton.prototype.configureAttributionHelpButton = function (buttonNode) {
    var attributionHelpButton =
	buttonNode.getElementsByClassName('cc-attribution-help-button')[0];
    attributionHelpButton.addEventListener('click', function (event) {
	jQuery('.cc-help-modal').dialog("open");
    });
};

// Fill out the attribution button template and insert it after a node

CCButton.prototype.addButtonToLicenseBlock = function (block, after) {
    var buttonNode = document.createElement(this.nodeType);
    buttonNode.innerHTML = this.ATTRIBUTION_BUTTON;
    buttonNode.className = this.nodeClass;
    var licenseHTML = this.extractLicenseBlockHTML(block);
    var licenseText = this.extractLicenseBlockText(block);
    this.configureAttributionCopyButton(buttonNode, licenseHTML);
    this.configureAttributionSelect(buttonNode, licenseHTML, licenseText);
    this.configureAttributionHelpButton(buttonNode);
    this.insertAfter(buttonNode, block[after]);
};

// Fill out the attribution button template & insert it after the license block

CCButton.prototype.appendButtonToLicenseBlock = function (block) {
    this.addButtonToLicenseBlock (block, 'end');
};

// Fill out the attribution button template & insert it after the license icon

CCButton.prototype.insertButtonIntoLicenseBlock = function (block) {
    this.addButtonToLicenseBlock (block, 'start');
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

    this.clipboard.on('success', function(e) {
	e.clearSelection();
	toastr.success('Copied to clipboard!');
    });

    this.clipboard.on('error', function(e) {
	toastr.warning(this.clipboadFallbackMessage(e.action));
    });
};


////////////////////////////////////////////////////////////////////////////////
// PUBLIC API
////////////////////////////////////////////////////////////////////////////////

// Add the attribution buttons after the HTML+RDF license metadata blocks

CCButton.prototype.appendButtonToLicenseBlocks = function () {
    this.insertAttributionHelpModal();
    var licenseBlocks = this.getLicenseBlocks();
    for (var i = 0; i < licenseBlocks.length; i++) {
	var block = licenseBlocks[i];
	this.appendButtonToLicenseBlock(block);
    }
};

// Insert the attribution button into the HTML+RDF license metadata blocks,
// immediately next to the license buttons/icons.

CCButton.prototype.insertButtonIntoLicenseBlocks = function () {
    this.insertAttributionHelpModal();
    var licenseBlocks = this.getLicenseBlocks();
    for (var i = 0; i < licenseBlocks.length; i++) {
	var block = licenseBlocks[i];
	this.insertButtonIntoLicenseBlock(block);
    }
};
