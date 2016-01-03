// Some fields
var maxVisualHeight = 400;
var minDimension = 64;
var multiplier = 1;
var minVisualDim;

var fixLeft = false;
var fixRight = false;
var fixTop = false;
var fixBottom = false;

var visualHeight;
var visualWidth;

var maxRes = mw.config.get('wgMaxAvatarResolution');

var startOffset;
var startX;
var startY;

// Objects
var submitButton = $('[type=submit]');
var container = $('<div class="cropper-container" disabled=""/>');
var imageObj = $('<img src=""></img>');
var selector = $('<div class="cropper"><div class="tl-resizer"/><div class="tr-resizer"/><div class="bl-resizer"/><div class="br-resizer"/></div>');
var hiddenField = $('[name=avatar]');
var pickfile = $('#pickfile');
var errorMsg = $('#errorMsg');

// Helper function to limit the selection clip
function normalizeBound(inner, outer) {
  if (inner.left < outer.left) {
    inner.left = outer.left;
  }
  if (inner.left + inner.width > outer.left + outer.width) {
    inner.left = outer.left + outer.width - inner.width;
  }
  if (inner.top < outer.top) {
    inner.top = outer.top;
  }
  if (inner.top + inner.height > outer.top + outer.height) {
    inner.top = outer.top + outer.height - inner.height;
  }
}

// Helper function to easily get bound
function getBound(obj) {
  var bound = obj.offset();
  bound.width = obj.width();
  bound.height = obj.height();
  return bound;
}

function setBound(obj, bound) {
  obj.offset(bound);
  obj.width(bound.width);
  obj.height(bound.height);
}

function cropImage(image, x, y, dim, targetDim) {
  if (dim > 2 * targetDim) {
    var crop = cropImage(image, x, y, dim, 2 * targetDim);
    return cropImage(crop, 0, 0, 2 * targetDim, targetDim);
  } else {
    var buffer = $('<canvas/>')
      .attr('width', targetDim)
      .attr('height', targetDim)[0];
    buffer
      .getContext('2d')
      .drawImage(image, x, y, dim, dim, 0, 0, targetDim, targetDim);
    return buffer;
  }
}

// Event listeners
function updateHidden() {
  var bound = getBound(selector);
  var outer = getBound(container);
  // When window is zoomed,
  // width set != width get, so we do some nasty trick here to counter the effect
  var dim = Math.round((bound.width - container.width() + visualWidth) * multiplier);
  var res = dim;
  if (res > maxRes) {
    res = maxRes;
  }
  var image = cropImage(imageObj[0],
    (bound.left - outer.left) * multiplier,
    (bound.top - outer.top) * multiplier,
    dim, res)
  hiddenField.val(image.toDataURL());
}

function onDragStart(event) {
  startOffset = getBound(selector);
  startX = event.pageX;
  startY = event.pageY;
  event.preventDefault();

  $('body').on('mousemove', onDrag).on('mouseup', onDragEnd);
}

function onDrag(event) {
  var bound = getBound(selector);
  var outer = getBound(container);
  var point = {
    left: event.pageX,
    top: event.pageY,
    width: 0,
    height: 0
  };
  normalizeBound(point, outer);
  var deltaX = point.left - startX;
  var deltaY = point.top - startY;

  if (fixLeft && fixTop) {
    deltaX = deltaY = Math.max(deltaX, deltaY);
  } else if (fixRight && fixBottom) {
    deltaX = deltaY = Math.min(deltaX, deltaY);
  } else if (fixLeft && fixBottom) {
    deltaY = -(deltaX = Math.max(deltaX, -deltaY));
  } else if (fixRight && fixTop) {
    deltaY = -(deltaX = Math.min(deltaX, -deltaY));
  }

  if (fixLeft) {
    bound.width = startOffset.width + deltaX;
    if (bound.width < minVisualDim) {
      bound.width = minVisualDim;
    } else if (bound.width > outer.width) {
      bound.width = outer.width;
    }
  } else if (fixRight) {
    bound.width = startOffset.width - deltaX;
    if (bound.width < minVisualDim) {
      bound.width = minVisualDim;
    } else if (bound.width > outer.width) {
      bound.width = outer.width;
    }
    bound.left = startOffset.left + startOffset.width - bound.width;
  } else {
    bound.left = startOffset.left + deltaX;
  }

  if (fixTop) {
    bound.height = startOffset.height + deltaY;
    if (bound.height < minVisualDim) {
      bound.height = minVisualDim;
    } else if (bound.height > outer.height) {
      bound.height = outer.height;
    }
  } else if (fixBottom) {
    bound.height = startOffset.height - deltaY;
    if (bound.height < minVisualDim) {
      bound.height = minVisualDim;
    } else if (bound.height > outer.height) {
      bound.height = outer.height;
    }
    bound.top = startOffset.top + startOffset.height - bound.height;
  } else {
    bound.top = startOffset.top + deltaY;
  }

  normalizeBound(bound, outer);
  setBound(selector, bound);
  event.preventDefault();
}

function onDragEnd(event) {
  $('body').off('mousemove', onDrag).off('mouseup', onDragEnd);
  fixLeft = false;
  fixRight = false;
  fixTop = false;
  fixBottom = false;
  event.preventDefault();

  updateHidden();
}

function onImageLoaded() {
  var width = imageObj.width();
  var height = imageObj.height();

  if (width < minDimension || height < minDimension) {
    errorMsg.text(mw.msg('avatar-toosmall'));
    imageObj.attr('src', '');
    container.attr('disabled', '');
    submitButton.attr('disabled', '');
    return;
  }

  errorMsg.text('');

  container.removeAttr('disabled');
  submitButton.removeAttr('disabled');
  visualHeight = height;
  visualWidth = width;

  if (visualHeight > maxVisualHeight) {
    visualHeight = maxVisualHeight;
    visualWidth = visualHeight * width / height;
  }

  multiplier = width / visualWidth;
  minVisualDim = minDimension / multiplier;

  container.width(visualWidth);
  container.height(visualHeight);
  imageObj.width(visualWidth);
  imageObj.height(visualHeight);

  var bound = getBound(container);
  bound.width = bound.height = Math.min(bound.width, bound.height);
  setBound(selector, bound);
  updateHidden();
}

function onImageLoadingFailed() {
  if(!imageObj.attr('src')) {
    return;
  }

  errorMsg.text(mw.msg('avatar-invalid'));
  imageObj.attr('src', '');
  container.attr('disabled', '');
  submitButton.attr('disabled', '');
  return;
}


// Event registration
selector.on('mousedown', onDragStart);
selector.find('.tl-resizer').on('mousedown', function(event) {
  fixRight = true;
  fixBottom = true;
  onDragStart(event);
});
selector.find('.tr-resizer').on('mousedown', function(event) {
  fixLeft = true;
  fixBottom = true;
  onDragStart(event);
});
selector.find('.bl-resizer').on('mousedown', function(event) {
  fixRight = true;
  fixTop = true;
  onDragStart(event);
});
selector.find('.br-resizer').on('mousedown', function(event) {
  fixLeft = true;
  fixTop = true;
  onDragStart(event);
});

pickfile.click(function(event) {
  var picker = $('<input type="file"/>');
  picker.change(function(event) {
    var file = event.target.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onloadend = function() {
        imageObj.width('auto').height('auto');
        imageObj.attr('src', reader.result);
      }
      reader.readAsDataURL(file);
    }
  });
  picker.click();
  event.preventDefault();
});

imageObj
  .on('load', onImageLoaded)
  .on('error', onImageLoadingFailed);


// UI modification
submitButton.attr('disabled', '');
container.append(imageObj);
container.append(selector);
hiddenField.before(container);