(function() {
  var components = Array.prototype.slice.call(document.querySelectorAll('[data-photo-capture]'));

  if (!components.length) {
    return;
  }

  function setText(element, message) {
    if (element) {
      element.textContent = message;
    }
  }

  function formatBytes(bytes) {
    bytes = parseInt(bytes || 0, 10);

    if (bytes >= 1024 * 1024) {
      return (bytes / 1024 / 1024).toLocaleString('pt-BR', {
        maximumFractionDigits: 1
      }).replace(',0', '') + ' MB';
    }

    if (bytes >= 1024) {
      return (bytes / 1024).toLocaleString('pt-BR', {
        maximumFractionDigits: 1
      }).replace(',0', '') + ' KB';
    }

    return bytes + ' bytes';
  }

  function dispatchFieldChange(field) {
    if (!field) {
      return;
    }

    field.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
  }

  function getTargetSize(context) {
    return {
      width: parseInt(context.root.getAttribute('data-photo-width') || '390', 10),
      height: parseInt(context.root.getAttribute('data-photo-height') || '460', 10)
    };
  }

  function setEditorTransform(context) {
    var editor = context.editor;

    if (!context.preview || !editor || !editor.ready) {
      return;
    }

    var stageWidth = context.previewStage.clientWidth || 1;
    var stageHeight = context.previewStage.clientHeight || 1;
    var scaledWidth = editor.baseWidth * editor.scale;
    var scaledHeight = editor.baseHeight * editor.scale;
    var maxX = Math.max(0, (scaledWidth - stageWidth) / 2);
    var maxY = Math.max(0, (scaledHeight - stageHeight) / 2);

    editor.x = clamp(editor.x, -maxX, maxX);
    editor.y = clamp(editor.y, -maxY, maxY);

    context.preview.style.position = 'absolute';
    context.preview.style.width = editor.baseWidth + 'px';
    context.preview.style.height = editor.baseHeight + 'px';
    context.preview.style.left = 'calc(50% + ' + editor.x + 'px)';
    context.preview.style.top = 'calc(50% + ' + editor.y + 'px)';
    context.preview.style.maxWidth = 'none';
    context.preview.style.maxHeight = 'none';
    context.preview.style.objectFit = 'fill';
    context.preview.style.pointerEvents = 'none';
    context.preview.style.userSelect = 'none';
    context.preview.style.transformOrigin = 'center center';
    context.preview.style.transform = 'translate(-50%, -50%) scale(' + editor.scale + ')';
  }

  function renderEditedImage(context) {
    var editor = context.editor;
    var canvas = context.canvas;
    var preview = context.preview;

    if (!editor || !editor.ready || !canvas || !preview || !preview.naturalWidth || !preview.naturalHeight) {
      return;
    }

    setEditorTransform(context);

    var targetSize = getTargetSize(context);
    var stageWidth = context.previewStage.clientWidth || targetSize.width;
    var stageHeight = context.previewStage.clientHeight || targetSize.height;
    var scaledWidth = editor.baseWidth * editor.scale;
    var scaledHeight = editor.baseHeight * editor.scale;
    var left = (stageWidth - scaledWidth) / 2 + editor.x;
    var top = (stageHeight - scaledHeight) / 2 + editor.y;
    var sourceX = (0 - left) * preview.naturalWidth / scaledWidth;
    var sourceY = (0 - top) * preview.naturalHeight / scaledHeight;
    var sourceWidth = stageWidth * preview.naturalWidth / scaledWidth;
    var sourceHeight = stageHeight * preview.naturalHeight / scaledHeight;

    canvas.width = targetSize.width;
    canvas.height = targetSize.height;
    canvas.getContext('2d').drawImage(
      preview,
      sourceX,
      sourceY,
      sourceWidth,
      sourceHeight,
      0,
      0,
      targetSize.width,
      targetSize.height
    );

    context.imageInput.value = canvas.toDataURL('image/png');
    dispatchFieldChange(context.imageInput);
  }

  function initializeEditor(context) {
    var preview = context.preview;
    var stage = context.previewStage;

    if (!preview || !stage || !preview.naturalWidth || !preview.naturalHeight) {
      return;
    }

    var stageWidth = stage.clientWidth || 1;
    var stageHeight = stage.clientHeight || 1;
    var imageRatio = preview.naturalWidth / preview.naturalHeight;
    var stageRatio = stageWidth / stageHeight;
    var baseWidth = stageWidth;
    var baseHeight = stageHeight;

    if (imageRatio > stageRatio) {
      baseHeight = stageHeight;
      baseWidth = stageHeight * imageRatio;
    } else {
      baseWidth = stageWidth;
      baseHeight = stageWidth / imageRatio;
    }

    context.editor = {
      ready: true,
      baseWidth: baseWidth,
      baseHeight: baseHeight,
      scale: 1,
      minScale: 1,
      maxScale: 3,
      x: 0,
      y: 0,
      pointers: {},
      lastPinchDistance: 0
    };

    context.preview.classList.add('is-editing');
    context.previewStage.classList.add('is-editing');
    setEditorTransform(context);
    renderEditedImage(context);
  }

  function beginEdit(context, src, message) {
    if (!context.preview || !context.placeholder || !context.previewStage) {
      return;
    }

    clearEditor(context);
    context.imageInput.value = '';
    dispatchFieldChange(context.imageInput);
    var imageLoaded = false;
    var handleImageLoad = function() {
      if (imageLoaded) {
        return;
      }

      imageLoaded = true;
      initializeEditor(context);
      setText(context.previewStatus, message || 'Foto pronta para salvar');
    };
    context.preview.onload = handleImageLoad;
    context.preview.src = src;
    context.preview.hidden = false;
    context.placeholder.hidden = true;

    if (context.preview.complete) {
      window.setTimeout(handleImageLoad, 0);
    }
  }

  function clearEditor(context) {
    context.editor = null;

    if (context.preview) {
      context.preview.onload = null;
      context.preview.style.removeProperty('width');
      context.preview.style.removeProperty('height');
      context.preview.style.removeProperty('left');
      context.preview.style.removeProperty('top');
      context.preview.style.removeProperty('max-width');
      context.preview.style.removeProperty('max-height');
      context.preview.style.removeProperty('object-fit');
      context.preview.style.removeProperty('pointer-events');
      context.preview.style.removeProperty('position');
      context.preview.style.removeProperty('transform');
      context.preview.style.removeProperty('transform-origin');
      context.preview.style.removeProperty('user-select');
      context.preview.classList.remove('is-editing');
    }

    if (context.previewStage) {
      context.previewStage.classList.remove('is-editing', 'is-dragging');
    }
  }

  function clearPreview(context) {
    clearEditor(context);
    context.imageInput.value = '';
    dispatchFieldChange(context.imageInput);

    if (context.fileInput) {
      context.fileInput.value = '';
      dispatchFieldChange(context.fileInput);
    }

    if (context.preview) {
      context.preview.removeAttribute('src');
      context.preview.hidden = true;
    }

    if (context.placeholder) {
      context.placeholder.hidden = false;
    }

    setText(context.previewStatus, 'Aguardando captura');
  }

  function getPointerDistance(pointers) {
    var values = Object.keys(pointers).map(function(key) {
      return pointers[key];
    });

    if (values.length < 2) {
      return 0;
    }

    var dx = values[0].clientX - values[1].clientX;
    var dy = values[0].clientY - values[1].clientY;

    return Math.sqrt(dx * dx + dy * dy);
  }

  function setScale(context, nextScale) {
    var editor = context.editor;

    if (!editor || !editor.ready) {
      return;
    }

    editor.scale = clamp(nextScale, editor.minScale, editor.maxScale);
    setEditorTransform(context);
    renderEditedImage(context);
  }

  function setupEditorInteractions(context) {
    var stage = context.previewStage;

    if (!stage) {
      return;
    }

    stage.style.touchAction = 'none';

    stage.addEventListener('pointerdown', function(event) {
      if (!context.editor || !context.editor.ready) {
        return;
      }

      event.preventDefault();
      stage.setPointerCapture(event.pointerId);
      context.editor.pointers[event.pointerId] = {
        clientX: event.clientX,
        clientY: event.clientY
      };
      context.editor.lastPinchDistance = getPointerDistance(context.editor.pointers);
      stage.classList.add('is-dragging');
    });

    stage.addEventListener('pointermove', function(event) {
      var editor = context.editor;

      if (!editor || !editor.ready || !editor.pointers[event.pointerId]) {
        return;
      }

      event.preventDefault();

      var previous = editor.pointers[event.pointerId];
      var pointerCount = Object.keys(editor.pointers).length;

      editor.pointers[event.pointerId] = {
        clientX: event.clientX,
        clientY: event.clientY
      };

      if (pointerCount >= 2) {
        var distance = getPointerDistance(editor.pointers);
        if (editor.lastPinchDistance > 0 && distance > 0) {
          setScale(context, editor.scale * (distance / editor.lastPinchDistance));
        }
        editor.lastPinchDistance = distance;
        return;
      }

      editor.x += event.clientX - previous.clientX;
      editor.y += event.clientY - previous.clientY;
      setEditorTransform(context);
      renderEditedImage(context);
    });

    function releasePointer(event) {
      if (!context.editor) {
        return;
      }

      delete context.editor.pointers[event.pointerId];
      context.editor.lastPinchDistance = getPointerDistance(context.editor.pointers);

      if (!Object.keys(context.editor.pointers).length) {
        stage.classList.remove('is-dragging');
      }
    }

    stage.addEventListener('pointerup', releasePointer);
    stage.addEventListener('pointercancel', releasePointer);
    stage.addEventListener('lostpointercapture', releasePointer);

    stage.addEventListener('wheel', function(event) {
      if (!context.editor || !context.editor.ready) {
        return;
      }

      event.preventDefault();
      setScale(context, context.editor.scale + (event.deltaY < 0 ? .08 : -.08));
    }, { passive: false });
  }

  function getCameraMessage(error) {
    if (!error) {
      return 'Não foi possível iniciar a câmera.';
    }

    if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
      return 'Permita o acesso à câmera ou escolha um arquivo.';
    }

    if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
      return 'Nenhuma câmera encontrada. Você pode escolher um arquivo.';
    }

    if (error.name === 'NotReadableError') {
      return 'A câmera está em uso por outro aplicativo.';
    }

    return 'Não foi possível iniciar a câmera. Você pode escolher um arquivo.';
  }

  function stopStream(context) {
    if (!context.stream) {
      return;
    }

    context.stream.getTracks().forEach(function(track) {
      track.stop();
    });
    context.stream = null;
  }

  function startCamera(context) {
    if (context.stream) {
      setText(context.status, 'Câmera pronta');
      return;
    }

    if (!context.video || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      setText(context.status, 'Câmera indisponível. Escolha uma imagem do dispositivo.');
      return;
    }

    setText(context.status, 'Solicitando acesso à câmera...');

    navigator.mediaDevices.getUserMedia({
      audio: false,
      video: {
        facingMode: 'user',
        width: { ideal: 1280 },
        height: { ideal: 720 }
      }
    }).then(function(stream) {
      context.stream = stream;
      context.video.srcObject = stream;
      setText(context.status, 'Câmera pronta');
    }).catch(function(error) {
      setText(context.status, getCameraMessage(error));
    });
  }

  function showCaptureAreas(context) {
    if (context.areas) {
      context.areas.hidden = false;
    }

    if (context.startButton) {
      context.startButton.hidden = true;
    }

    startCamera(context);
  }

  function capture(context) {
    var video = context.video;
    var canvas = context.canvas;

    if (!video || !canvas || !video.videoWidth || !video.videoHeight) {
      setText(context.status, 'Aguarde a câmera carregar ou escolha um arquivo.');
      return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, video.videoWidth, video.videoHeight);

    if (context.fileInput) {
      context.fileInput.value = '';
      dispatchFieldChange(context.fileInput);
    }

    beginEdit(context, canvas.toDataURL('image/png'), 'Foto capturada');
    setText(context.status, 'Foto capturada.');
  }

  function handleFile(context) {
    var file = context.fileInput && context.fileInput.files ? context.fileInput.files[0] : null;
    var maxSize = parseInt(context.root.getAttribute('data-photo-max-size') || '20971520', 10);

    if (!file) {
      return;
    }

    if (!file.type || file.type.indexOf('image/') !== 0) {
      context.fileInput.value = '';
      setText(context.previewStatus, 'Escolha um arquivo de imagem.');
      return;
    }

    if (file.size > maxSize) {
      context.fileInput.value = '';
      setText(context.previewStatus, 'Imagem muito grande. Envie até ' + formatBytes(maxSize) + '.');
      return;
    }

    var reader = new FileReader();
    reader.onload = function(event) {
      beginEdit(context, event.target.result, file.name);
      context.fileInput.value = '';
      dispatchFieldChange(context.fileInput);
      setText(context.status, 'Arquivo selecionado.');
    };
    reader.onerror = function() {
      context.fileInput.value = '';
      setText(context.previewStatus, 'Não foi possível carregar a imagem.');
    };
    reader.readAsDataURL(file);
  }

  components.forEach(function(root) {
    var context = {
      root: root,
      video: root.querySelector('[data-photo-video]'),
      canvas: root.querySelector('[data-photo-canvas]'),
      preview: root.querySelector('[data-photo-preview]'),
      previewStage: root.querySelector('.photo-capture-preview'),
      placeholder: root.querySelector('[data-photo-placeholder]'),
      imageInput: root.querySelector('[data-photo-image]'),
      fileInput: root.querySelector('[data-photo-file]'),
      status: root.querySelector('[data-photo-status]'),
      previewStatus: root.querySelector('[data-photo-preview-status]'),
      captureButton: root.querySelector('[data-photo-capture-action]'),
      fileButton: root.querySelector('[data-photo-file-trigger]'),
      retakeButton: root.querySelector('[data-photo-retake]'),
      startButton: root.querySelector('[data-photo-start]'),
      areas: root.querySelector('[data-photo-areas]'),
      manualStart: root.getAttribute('data-photo-manual-start') === '1',
      stream: null,
      editor: null
    };

    if (!context.imageInput) {
      return;
    }

    if (context.manualStart) {
      if (context.areas) {
        context.areas.hidden = true;
      }
      setText(context.status, 'Câmera desligada');
    }

    if (context.startButton) {
      context.startButton.addEventListener('click', function() {
        showCaptureAreas(context);
      });
    }

    setupEditorInteractions(context);

    if (context.captureButton) {
      context.captureButton.addEventListener('click', function() {
        capture(context);
      });
    }

    if (context.fileButton && context.fileInput) {
      context.fileButton.addEventListener('click', function() {
        context.fileInput.click();
      });
    }

    if (context.fileInput) {
      context.fileInput.addEventListener('change', function() {
        handleFile(context);
      });
    }

    if (context.retakeButton) {
      context.retakeButton.addEventListener('click', function() {
        clearPreview(context);
        setText(context.status, context.stream ? 'Câmera pronta' : 'Escolha uma imagem ou aguarde a câmera.');
      });
    }

    if (!context.manualStart) {
      startCamera(context);
    }

    var form = root.tagName && root.tagName.toLowerCase() === 'form' ? root : root.closest('form');
    if (form) {
      form.addEventListener('submit', function() {
        renderEditedImage(context);
      });
    }

    window.addEventListener('pagehide', function() {
      stopStream(context);
    });
  });
})();
