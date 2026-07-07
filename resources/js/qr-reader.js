(function() {
  var roots = Array.prototype.slice.call(document.querySelectorAll('[data-qr-reader]'));

  if (!roots.length) {
    return;
  }

  function ready(callback) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      window.setTimeout(callback, 1);
      return;
    }

    document.addEventListener('DOMContentLoaded', callback);
  }

  function appendIcon(parent, iconName) {
    var icon = document.createElement('i');
    icon.className = 'material-icons';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = iconName;
    parent.appendChild(icon);

    return icon;
  }

  function appendText(parent, tagName, text) {
    var element = document.createElement(tagName);
    element.textContent = text || '';
    parent.appendChild(element);

    return element;
  }

  function sanitizeFotoName(fileName) {
    var name = String(fileName || 'profile.png').replace(/[\/\\]/g, '');

    return name || 'profile.png';
  }

  function getCameraErrorMessage(error) {
    var message = String(error && (error.message || error.name || error) || '');

    if (/notallowed|permission|denied/i.test(message)) {
      return 'Permita o acesso à câmera e tente novamente.';
    }

    if (/notfound|device|no camera|not found/i.test(message)) {
      return 'Nenhuma câmera foi encontrada neste dispositivo.';
    }

    if (/notreadable|trackstart|in use/i.test(message)) {
      return 'A câmera está em uso por outro aplicativo.';
    }

    return 'Não foi possível iniciar a câmera.';
  }

  function initQrReader(root) {
    var reader = root.querySelector('.qr-reader');
    var results = root.querySelector('.qr-result');
    var title = root.querySelector('#qrPageTitle');
    var switchCameraButton = root.querySelector('[data-qr-switch-camera]');
    var torchButton = root.querySelector('[data-qr-toggle-torch]');
    var cameraLabel = root.querySelector('[data-qr-camera-label]');
    var baseUrl = root.getAttribute('data-qr-base-url') || '';
    var endpoint = baseUrl + '/app/Controller/Ajax/authenticateAluno.php';
    var currentIdAula = Number(root.getAttribute('data-qr-id-aula')) || 0;
    var aulaGenerica = root.getAttribute('data-qr-aula-generica') === '1';
    var cacheBust = Math.random();
    var fps = parseInt(root.getAttribute('data-qr-fps') || '10', 10);
    var qrbox = parseInt(root.getAttribute('data-qr-qrbox') || '', 10);
    var cameraConfig = { fps: fps || 10 };
    var html5qrcode;
    var cameras = [];
    var activeCameraIndex = -1;
    var scanning = false;
    var paused = false;
    var lastMessage = null;
    var returnTimer = null;
    var countdownTimer = null;
    var torchOn = false;
    var feedbackReturnDelay = 2000;

    if (!reader || !results) {
      return;
    }

    if (!reader.id) {
      reader.id = 'reader-' + Math.random().toString(36).slice(2);
    }

    if (qrbox) {
      cameraConfig.qrbox = qrbox;
    }

    function setCameraLabel(message) {
      if (cameraLabel) {
        cameraLabel.textContent = message || '';
      }
    }

    function setScannerCallback(callback) {
      if (html5qrcode && html5qrcode.qrcode) {
        html5qrcode.qrcode.callback = callback;
      }
    }

    function clearTimers() {
      window.clearTimeout(returnTimer);
      window.clearTimeout(countdownTimer);
      returnTimer = null;
      countdownTimer = null;
    }

    function clearResults() {
      results.textContent = '';
    }

    function pauseReader() {
      paused = true;
      reader.hidden = true;
      setScannerCallback(function() {});
    }

    function resumeReader() {
      clearTimers();
      clearResults();
      paused = false;
      lastMessage = null;
      reader.hidden = false;

      if (scanning) {
        setScannerCallback(onScanSuccess);
        return;
      }

      startPreferredCamera();
    }

    function scheduleReaderReturn(delay) {
      delay = delay || feedbackReturnDelay;

      var seconds = Math.max(1, Math.ceil(delay / 1000));
      var timer = results.querySelector('.qr-timer');

      clearTimers();

      function tick() {
        if (timer) {
          timer.value = seconds > 0 ? 'Aguarde: ' + seconds : 'Retornando...';
        }

        if (seconds <= 0) {
          resumeReader();
          return;
        }

        seconds -= 1;
        countdownTimer = window.setTimeout(tick, 1000);
      }

      tick();
      returnTimer = window.setTimeout(resumeReader, delay);
    }

    function createFeedback(type, iconName, heading, message, options) {
      var section = document.createElement('section');
      var content = document.createElement('div');

      options = options || {};
      section.className = 'qr-feedback qr-feedback-' + type + (options.extraClass ? ' ' + options.extraClass : '');

      if (iconName) {
        appendIcon(section, iconName);
      }

      content.className = 'qr-feedback-content';
      appendText(content, 'strong', heading);
      appendText(content, 'span', message);
      section.appendChild(content);

      if (options.timer) {
        var timer = document.createElement('input');
        timer.className = 'qr-timer';
        timer.readOnly = true;
        timer.value = 'Aguarde';
        section.appendChild(timer);
      }

      return section;
    }

    function showFeedback(type, iconName, heading, message, options) {
      clearResults();
      results.appendChild(createFeedback(type, iconName, heading, message, options));
    }

    function showRetryFeedback(heading, message) {
      var section = createFeedback('danger', 'error_outline', heading, message);
      var actions = document.createElement('div');
      var button = document.createElement('button');

      actions.className = 'qr-feedback-actions';
      button.type = 'button';
      button.className = 'btn btn-default qr-retry-button';
      appendIcon(button, 'refresh');
      appendText(button, 'span', 'Tentar novamente');
      button.addEventListener('click', function() {
        resumeReader();
      });

      actions.appendChild(button);
      section.appendChild(actions);
      clearResults();
      results.appendChild(section);
      button.focus();
    }

    function showInvalidCode() {
      pauseReader();
      showFeedback(
        'danger',
        'error_outline',
        'Código inválido',
        'Tente aproximar novamente o QR Code da câmera.',
        { timer: true }
      );
      scheduleReaderReturn();
    }

    function getFotoUrl(fileName) {
      return baseUrl + '/app/Controller/File/files/fotos/' + encodeURIComponent(sanitizeFotoName(fileName)) + '?var=' + cacheBust;
    }

    function createStudentResult(data, imageId) {
      var wrapper = document.createElement('div');
      var photo = document.createElement('div');
      var img = document.createElement('img');
      var dataBox = document.createElement('div');

      wrapper.className = 'qr-student-result';
      photo.className = 'qr-student-photo';
      dataBox.className = 'qr-student-data';

      img.id = imageId || '';
      img.alt = 'Foto cadastrada';
      img.src = getFotoUrl(data && data.foto);
      photo.appendChild(img);

      appendText(dataBox, 'span', data && data.matricula ? data.matricula : '');
      appendText(dataBox, 'strong', data && data.nome ? data.nome : 'Aluno');
      appendText(dataBox, 'p', data && data.mensagem ? data.mensagem : '');

      wrapper.appendChild(photo);
      wrapper.appendChild(dataBox);

      return wrapper;
    }

    function showStudentMessage(data, type) {
      var section = document.createElement('section');
      var timer = document.createElement('input');
      var feedbackType = type === 'warning' ? 'warning' : (type === 'danger' ? 'danger' : 'success');

      pauseReader();
      clearResults();

      section.className = 'qr-feedback qr-feedback-' + feedbackType;
      section.appendChild(createStudentResult(data, 'foto'));

      timer.className = 'qr-timer';
      timer.readOnly = true;
      timer.value = 'Aguarde';
      section.appendChild(timer);
      results.appendChild(section);

      scheduleReaderReturn();
    }

    function captureAuditPhoto() {
      var video = reader.querySelector('video');

      if (!video || !video.videoWidth || !video.videoHeight) {
        return '';
      }

      var maxWidth = 480;
      var scale = Math.min(1, maxWidth / video.videoWidth);
      var canvas = document.createElement('canvas');
      canvas.width = Math.round(video.videoWidth * scale);
      canvas.height = Math.round(video.videoHeight * scale);
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

      try {
        return canvas.toDataURL('image/jpeg', 0.72);
      } catch (error) {
        return '';
      }
    }

    function postQr(payload) {
      if (!window.fetch && window.$ && $.ajax) {
        return new Promise(function(resolve, reject) {
          $.ajax({
            type: 'POST',
            url: endpoint,
            data: payload,
            dataType: 'json',
            cache: false,
            success: resolve,
            error: function() {
              reject(new Error('Erro de conexão com o servidor.'));
            }
          });
        });
      }

      var body = Object.keys(payload).map(function(key) {
        return encodeURIComponent(key) + '=' + encodeURIComponent(payload[key]);
      }).join('&');

      return fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body,
        cache: 'no-store'
      }).then(function(response) {
        if (!response.ok) {
          throw new Error('Erro de conexão com o servidor.');
        }

        return response.json();
      });
    }

    function updateCurrentLesson(data) {
      if (data && Object.prototype.hasOwnProperty.call(data, 'idAula')) {
        currentIdAula = Number(data.idAula) || 0;
      }

      if (data && data.aulaGenerica) {
        aulaGenerica = true;
      }

      if (data && data.aulaTitulo && title) {
        title.textContent = data.aulaTitulo;
      }
    }

    function registerPresence(qrCodeMessage) {
      var fotoAuditoria = captureAuditPhoto();

      pauseReader();
      showFeedback(
        'success',
        'photo_camera',
        'Registrando presença',
        'Mantenha o rosto visível para a foto de auditoria.'
      );

      postQr({
        send: 'true',
        modo: 'registrar',
        matricula: qrCodeMessage,
        idAula: currentIdAula,
        aulaGenerica: aulaGenerica ? 1 : 0,
        fotoAuditoria: fotoAuditoria
      }).then(function(data) {
        if (!data.successExiste || typeof data.nome === 'undefined') {
          showInvalidCode();
          return;
        }

        updateCurrentLesson(data);

        if (data.successPresenca) {
          showStudentMessage(data, 'warning');
          return;
        }

        showStudentMessage(data, data.successUpdate || data.successInsert ? 'success' : 'danger');
      }).catch(function() {
        showRetryFeedback('Erro de conexão', 'Não foi possível registrar a presença. Verifique a internet e tente novamente.');
      });
    }

    function showConfirmation(data, qrCodeMessage) {
      var section = document.createElement('section');
      var actions = document.createElement('div');
      var confirmButton = document.createElement('button');
      var cancelButton = document.createElement('button');
      var resolved = false;

      pauseReader();
      clearResults();

      section.className = 'qr-feedback qr-feedback-success qr-confirmation';
      section.appendChild(createStudentResult({
        matricula: data.matricula || '',
        nome: data.nome || 'Aluno',
        mensagem: 'Deseja confirmar esta presença?',
        foto: data.foto
      }, 'fotoConfirmacao'));

      actions.className = 'qr-confirm-actions';

      confirmButton.type = 'button';
      confirmButton.className = 'btn btn-success qr-confirm-button';
      confirmButton.id = 'confirmarPresencaQr';
      appendIcon(confirmButton, 'check');
      appendText(confirmButton, 'span', 'Confirmar');

      cancelButton.type = 'button';
      cancelButton.className = 'btn btn-default qr-confirm-button qr-confirm-secondary';
      cancelButton.id = 'cancelarPresencaQr';
      appendIcon(cancelButton, 'close');
      appendText(cancelButton, 'span', 'Cancelar');

      actions.appendChild(confirmButton);
      actions.appendChild(cancelButton);
      section.appendChild(actions);
      results.appendChild(section);

      function closeKeyHandler() {
        document.removeEventListener('keydown', confirmWithEnter);
      }

      function resolveOnce(callback) {
        if (resolved) {
          return;
        }

        resolved = true;
        closeKeyHandler();
        callback();
      }

      function confirmWithEnter(event) {
        if (event.key !== 'Enter') {
          return;
        }

        event.preventDefault();
        resolveOnce(function() {
          registerPresence(qrCodeMessage);
        });
      }

      confirmButton.addEventListener('click', function() {
        resolveOnce(function() {
          registerPresence(qrCodeMessage);
        });
      });

      cancelButton.addEventListener('click', function() {
        resolveOnce(resumeReader);
      });

      document.addEventListener('keydown', confirmWithEnter);
      confirmButton.focus();
    }

    function onScanSuccess(qrCodeMessage) {
      if (paused || lastMessage === qrCodeMessage) {
        return;
      }

      lastMessage = qrCodeMessage;
      pauseReader();
      showFeedback('info', 'manage_search', 'Consultando QR Code', 'Validando aluno e aula no sistema.');

      postQr({
        send: 'true',
        modo: 'consultar',
        matricula: qrCodeMessage,
        idAula: currentIdAula,
        aulaGenerica: aulaGenerica ? 1 : 0
      }).then(function(data) {
        if (!data.successExiste || typeof data.nome === 'undefined') {
          showInvalidCode();
          return;
        }

        updateCurrentLesson(data);

        if (!data.successAtivo) {
          showStudentMessage(data, 'danger');
          return;
        }

        if (data.successPresenca) {
          showStudentMessage(data, 'warning');
          return;
        }

        showConfirmation(data, qrCodeMessage);
      }).catch(function() {
        showRetryFeedback('Erro de conexão', 'Não foi possível validar o QR Code. Verifique a internet e tente novamente.');
      });
    }

    function getRearCameraIndex(list) {
      var index = -1;

      list.some(function(camera, currentIndex) {
        var label = String(camera.label || '').toLowerCase();
        var match = label.indexOf('back') !== -1 ||
          label.indexOf('rear') !== -1 ||
          label.indexOf('environment') !== -1 ||
          label.indexOf('traseira') !== -1 ||
          label.indexOf('trasera') !== -1 ||
          label.indexOf('world') !== -1;

        if (match) {
          index = currentIndex;
        }

        return match;
      });

      return index;
    }

    function updateTorchButton() {
      var capabilities;
      var supported = false;

      torchOn = false;

      if (!torchButton || !html5qrcode || typeof html5qrcode.getRunningTrackCapabilities !== 'function') {
        return;
      }

      try {
        capabilities = html5qrcode.getRunningTrackCapabilities();
        supported = !!(capabilities && capabilities.torch);
      } catch (error) {
        supported = false;
      }

      torchButton.hidden = !supported;
      torchButton.setAttribute('aria-pressed', 'false');
      if (supported) {
        torchButton.querySelector('span').textContent = 'Lanterna';
      }
    }

    function updateCameraControls() {
      if (switchCameraButton) {
        switchCameraButton.hidden = cameras.length < 2;
      }

      if (activeCameraIndex >= 0 && cameras[activeCameraIndex]) {
        setCameraLabel(cameras[activeCameraIndex].label || 'Câmera ativa');
      } else {
        setCameraLabel('Câmera ativa');
      }

      updateTorchButton();
    }

    function startCamera(target, index) {
      reader.hidden = false;
      setCameraLabel('Iniciando câmera...');

      return html5qrcode.start(target, cameraConfig, onScanSuccess, function() {})
        .then(function() {
          scanning = true;
          paused = false;
          activeCameraIndex = typeof index === 'number' ? index : activeCameraIndex;
          setScannerCallback(onScanSuccess);
          updateCameraControls();
        });
    }

    function startPreferredCamera() {
      if (typeof Html5Qrcode === 'undefined') {
        showRetryFeedback('Leitor indisponível', 'A biblioteca de leitura do QR Code não foi carregada.');
        return Promise.reject(new Error('Html5Qrcode indisponível'));
      }

      if (!html5qrcode) {
        html5qrcode = new Html5Qrcode(reader.id, true);
      }

      return Html5Qrcode.getCameras()
        .then(function(list) {
          cameras = Array.isArray(list) ? list : [];
          activeCameraIndex = getRearCameraIndex(cameras);

          if (activeCameraIndex === -1 && cameras.length) {
            activeCameraIndex = 0;
          }

          if (activeCameraIndex >= 0 && cameras[activeCameraIndex]) {
            return startCamera(cameras[activeCameraIndex].id, activeCameraIndex);
          }

          return startCamera({ facingMode: { exact: 'environment' } }, -1)
            .catch(function() {
              return startCamera({ facingMode: 'user' }, -1);
            });
        })
        .catch(function(error) {
          showRetryFeedback('Câmera indisponível', getCameraErrorMessage(error));
          throw error;
        });
    }

    function restartWithCamera(index) {
      var camera = cameras[index];

      if (!camera || !html5qrcode) {
        return;
      }

      clearTimers();
      clearResults();
      paused = false;
      lastMessage = null;
      setCameraLabel('Trocando câmera...');

      var startNext = function() {
        return startCamera(camera.id, index).catch(function(error) {
          showRetryFeedback('Não foi possível trocar a câmera', getCameraErrorMessage(error));
        });
      };

      if (scanning) {
        scanning = false;
        html5qrcode.stop().then(startNext).catch(startNext);
        return;
      }

      startNext();
    }

    function toggleTorch() {
      var nextState = !torchOn;

      if (!html5qrcode || typeof html5qrcode.applyVideoConstraints !== 'function') {
        return;
      }

      html5qrcode.applyVideoConstraints({
        advanced: [{ torch: nextState }]
      }).then(function() {
        torchOn = nextState;
        torchButton.setAttribute('aria-pressed', torchOn ? 'true' : 'false');
        torchButton.querySelector('span').textContent = torchOn ? 'Desligar lanterna' : 'Lanterna';
      }).catch(function() {
        setCameraLabel('Lanterna indisponível nesta câmera.');
        torchButton.hidden = true;
      });
    }

    function scheduleMidnightRedirect() {
      var now = new Date();
      var nextMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 5);
      var delay = Math.max(1000, nextMidnight.getTime() - now.getTime());

      window.setTimeout(function() {
        window.location.replace(baseUrl + '/frequencias/geral');
      }, delay);
    }

    if (switchCameraButton) {
      switchCameraButton.addEventListener('click', function() {
        if (cameras.length < 2) {
          return;
        }

        restartWithCamera((activeCameraIndex + 1) % cameras.length);
      });
    }

    if (torchButton) {
      torchButton.addEventListener('click', toggleTorch);
    }

    window.addEventListener('pagehide', function() {
      clearTimers();
      if (html5qrcode && scanning) {
        html5qrcode.stop().catch(function() {});
      }
    });

    scheduleMidnightRedirect();
    startPreferredCamera().catch(function() {});
  }

  ready(function() {
    roots.forEach(initQrReader);
  });
})();
