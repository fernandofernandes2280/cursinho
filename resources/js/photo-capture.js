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

  function dispatchFieldChange(field) {
    if (!field) {
      return;
    }

    field.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function setPreview(context, src, message) {
    if (!context.preview || !context.placeholder) {
      return;
    }

    context.preview.src = src;
    context.preview.hidden = false;
    context.placeholder.hidden = true;
    setText(context.previewStatus, message || 'Foto pronta para salvar');
  }

  function clearPreview(context) {
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

    var targetWidth = parseInt(context.root.getAttribute('data-photo-width') || '390', 10);
    var targetHeight = parseInt(context.root.getAttribute('data-photo-height') || '460', 10);
    var targetRatio = targetWidth / targetHeight;
    var videoRatio = video.videoWidth / video.videoHeight;
    var sourceWidth = video.videoWidth;
    var sourceHeight = video.videoHeight;
    var sourceX = 0;
    var sourceY = 0;

    if (videoRatio > targetRatio) {
      sourceWidth = Math.round(video.videoHeight * targetRatio);
      sourceX = Math.round((video.videoWidth - sourceWidth) / 2);
    } else {
      sourceHeight = Math.round(video.videoWidth / targetRatio);
      sourceY = Math.round((video.videoHeight - sourceHeight) / 2);
    }

    canvas.width = targetWidth;
    canvas.height = targetHeight;
    canvas.getContext('2d').drawImage(
      video,
      sourceX,
      sourceY,
      sourceWidth,
      sourceHeight,
      0,
      0,
      targetWidth,
      targetHeight
    );

    var dataUri = canvas.toDataURL('image/png');
    context.imageInput.value = dataUri;
    dispatchFieldChange(context.imageInput);

    if (context.fileInput) {
      context.fileInput.value = '';
      dispatchFieldChange(context.fileInput);
    }

    setPreview(context, dataUri, 'Foto capturada');
    setText(context.status, 'Foto capturada. Você pode salvar ou tirar outra.');
  }

  function handleFile(context) {
    var file = context.fileInput && context.fileInput.files ? context.fileInput.files[0] : null;
    var maxSize = parseInt(context.root.getAttribute('data-photo-max-size') || '5242880', 10);

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
      setText(context.previewStatus, 'Imagem muito grande. Envie até 5 MB.');
      return;
    }

    var reader = new FileReader();
    reader.onload = function(event) {
      context.imageInput.value = '';
      dispatchFieldChange(context.imageInput);
      setPreview(context, event.target.result, file.name);
      setText(context.status, 'Arquivo selecionado. Você pode salvar ou escolher outro.');
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
      stream: null
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

    window.addEventListener('pagehide', function() {
      stopStream(context);
    });
  });
})();
