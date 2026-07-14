(function() {
  var cpfForm = document.querySelector('.precadastro-cpf-form');

  function normalizarCpf(cpf) {
    return (cpf || '').replace(/\D+/g, '');
  }

  function validarCpf(cpf) {
    cpf = normalizarCpf(cpf);

    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
      return false;
    }

    var soma = 0;
    var resto;

    for (var i = 1; i <= 9; i++) {
      soma += parseInt(cpf.substring(i - 1, i), 10) * (11 - i);
    }

    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) {
      resto = 0;
    }

    if (resto !== parseInt(cpf.substring(9, 10), 10)) {
      return false;
    }

    soma = 0;
    for (i = 1; i <= 10; i++) {
      soma += parseInt(cpf.substring(i - 1, i), 10) * (12 - i);
    }

    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) {
      resto = 0;
    }

    return resto === parseInt(cpf.substring(10, 11), 10);
  }

  function setCpfMessage(input, message) {
    var messageBox = document.querySelector('[data-precadastro-cpf-message]');

    if (!messageBox) {
      return;
    }

    messageBox.textContent = message || '';
    messageBox.classList.toggle('precadastro-message', Boolean(message));
    messageBox.classList.toggle('precadastro-message-danger', Boolean(message));
    input.classList.toggle('precadastro-input-invalid', Boolean(message));
  }

  function hasCpfMessage() {
    var messageBox = document.querySelector('[data-precadastro-cpf-message]');

    return messageBox && messageBox.textContent.trim() !== '';
  }

  function validarCpfPreCadastro(showEmpty) {
    if (!cpfForm) {
      return true;
    }

    var input = cpfForm.querySelector('[data-precadastro-cpf]');

    if (!input) {
      return true;
    }

    var cpf = normalizarCpf(input.value);

    if (!cpf) {
      setCpfMessage(input, showEmpty ? 'Informe o CPF.' : '');
      return !showEmpty;
    }

    if (!validarCpf(cpf)) {
      setCpfMessage(input, 'CPF inválido.');
      return false;
    }

    setCpfMessage(input, '');
    return true;
  }

  if (cpfForm) {
    var cpfInput = cpfForm.querySelector('[data-precadastro-cpf]');

    cpfForm.addEventListener('submit', function(event) {
      if (!validarCpfPreCadastro(true)) {
        event.preventDefault();
        if (cpfInput) {
          cpfInput.focus();
        }
      }
    });

    if (cpfInput) {
      var initialCpf = normalizarCpf(cpfInput.value);
      var keepInitialCpfMessage = hasCpfMessage();

      if (!keepInitialCpfMessage) {
        validarCpfPreCadastro(false);
      }

      cpfInput.addEventListener('input', function() {
        if (keepInitialCpfMessage && normalizarCpf(cpfInput.value) === initialCpf) {
          return;
        }

        keepInitialCpfMessage = false;
        validarCpfPreCadastro(false);
      });
      cpfInput.addEventListener('blur', function() {
        if (keepInitialCpfMessage && normalizarCpf(cpfInput.value) === initialCpf) {
          return;
        }

        keepInitialCpfMessage = false;
        validarCpfPreCadastro(false);
      });
    }
  }

  var forms = Array.prototype.slice.call(document.querySelectorAll('[data-precadastro-form]'));

  function setError(form, message) {
    var error = form.querySelector('[data-precadastro-error]');

    if (!error) {
      return;
    }

    error.textContent = message || '';
    error.hidden = !message;
  }

  forms.forEach(function(form) {
    var cadastroSaved = form.getAttribute('data-precadastro-saved') === '1';
    var cadastroCompleto = form.getAttribute('data-precadastro-completo') === '1';
    var cadastroDirty = false;

    function onlyDigits(value) {
      return String(value || '').replace(/\D/g, '');
    }

    function hasUsefulValue(value) {
      var normalized = String(value || '').trim();

      return normalized !== '' && normalized !== '0';
    }

    function getRequiredFields() {
      return Array.prototype.slice.call(form.querySelectorAll('input[required], select[required], textarea[required]')).filter(function(field) {
        return !field.disabled && field.type !== 'hidden';
      });
    }

    function clearRequiredState(field) {
      field.classList.remove('precadastro-input-invalid');
    }

    function markCadastroDirty() {
      if (!cadastroSaved) {
        return;
      }

      cadastroDirty = true;
      cadastroSaved = false;
      form.setAttribute('data-precadastro-saved', '0');
    }

    function updateCarteiraButton(percent) {
      var button = form.querySelector('[data-precadastro-carteira]');

      if (!button) {
        return;
      }

      var canGenerate = cadastroSaved && cadastroCompleto && !cadastroDirty && percent >= 100;

      button.disabled = !canGenerate;
      button.classList.toggle('is-disabled', !canGenerate);
      button.setAttribute(
        'title',
        canGenerate ? 'Gerar carteira' : 'Salve o cadastro com 100% de conclusão para gerar a carteira'
      );
    }

    function validateRequiredFields() {
      var requiredFields = getRequiredFields();
      var firstInvalid = null;

      requiredFields.forEach(function(field) {
        var isEmpty = String(field.value || '').trim() === '';
        field.classList.toggle('precadastro-input-invalid', isEmpty);

        if (isEmpty && !firstInvalid) {
          firstInvalid = field;
        }
      });

      if (!firstInvalid) {
        return true;
      }

      setError(form, 'Preencha todos os campos obrigatórios.');
      firstInvalid.focus();
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });

      return false;
    }

    function applyDocumentLoadedState(card) {
      var feedback = card.querySelector('[data-document-feedback]');
      var progress = card.querySelector('.aluno-document-progress span');

      if (feedback && String(feedback.textContent || '').trim()) {
        card.classList.add('is-loaded');

        if (progress) {
          progress.style.width = '100%';
        }
      }
    }

    function validateRequiredDocuments() {
      var firstInvalid = null;

      Array.prototype.slice.call(form.querySelectorAll('[data-document-required="1"]')).forEach(function(card) {
        var filled = card.classList.contains('is-loaded');
        card.classList.toggle('is-invalid', !filled);

        if (!filled && !firstInvalid) {
          firstInvalid = card;
        }
      });

      if (!firstInvalid) {
        return true;
      }

      setError(form, 'Envie Documento de Identificação e Comprovante de Residência.');
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });

      return false;
    }

    function validateUploadTotalSize() {
      var maxSize = parseInt(form.getAttribute('data-upload-max-size') || '0', 10);
      var maxSizeLabel = form.getAttribute('data-upload-max-size-label') || 'o limite permitido';
      var total = 0;
      var firstFileInput = null;

      if (maxSize <= 0) {
        return true;
      }

      Array.prototype.slice.call(form.querySelectorAll('input[type="file"]')).forEach(function(input) {
        if (!input.files || !input.files.length) {
          return;
        }

        Array.prototype.slice.call(input.files).forEach(function(file) {
          total += file.size || 0;
          if (!firstFileInput) {
            firstFileInput = input;
          }
        });
      });

      if (total <= maxSize) {
        return true;
      }

      setError(form, 'O envio total precisa ter até ' + maxSizeLabel + '.');
      if (firstFileInput) {
        var section = firstFileInput.closest('.precadastro-section');
        if (section) {
          section.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }

      return false;
    }

    function setupDocumentUploads() {
      Array.prototype.slice.call(form.querySelectorAll('.aluno-document-upload')).forEach(function(card) {
        var input = card.querySelector('input[type="file"]');
        var trigger = card.querySelector('[data-document-trigger]');
        var feedback = card.querySelector('[data-document-feedback]');
        var progress = card.querySelector('.aluno-document-progress span');
        var maxSize = parseInt(card.getAttribute('data-document-max-size') || '0', 10);
        var maxSizeLabel = card.getAttribute('data-document-max-size-label') || 'o limite permitido';

        applyDocumentLoadedState(card);

        if (trigger && input) {
          trigger.addEventListener('click', function() {
            input.click();
          });
        }

        if (!input) {
          return;
        }

        input.addEventListener('change', function() {
          var file = input.files && input.files[0] ? input.files[0] : null;
          var previousText = feedback ? (feedback.getAttribute('data-previous-text') || feedback.innerHTML) : '';

          if (feedback && !feedback.hasAttribute('data-previous-text')) {
            feedback.setAttribute('data-previous-text', previousText);
          }

          card.classList.remove('is-loading', 'is-loaded', 'is-invalid');
          if (progress) {
            progress.style.width = '0%';
          }

          if (!file) {
            if (feedback) {
              feedback.innerHTML = previousText;
            }
            applyDocumentLoadedState(card);
            setError(form, '');
            updateCadastroCompletion();
            return;
          }

          if (!/\.pdf$/i.test(file.name)) {
            input.value = '';
            card.classList.add('is-invalid');
            if (feedback) {
              feedback.textContent = 'Envie apenas arquivo PDF.';
            }
            setError(form, 'Envie os documentos somente em PDF.');
            updateCadastroCompletion();
            return;
          }

          if (maxSize > 0 && file.size > maxSize) {
            input.value = '';
            card.classList.add('is-invalid');
            if (feedback) {
              feedback.textContent = 'O PDF precisa ter até ' + maxSizeLabel + '.';
            }
            setError(form, 'O PDF precisa ter até ' + maxSizeLabel + '.');
            updateCadastroCompletion();
            return;
          }

          setError(form, '');
          card.classList.add('is-loading');
          if (feedback) {
            feedback.textContent = 'Carregando PDF...';
          }

          window.setTimeout(function() {
            if (progress) {
              progress.style.width = '100%';
            }
            card.classList.remove('is-loading');
            card.classList.add('is-loaded');
            if (feedback) {
              feedback.textContent = file.name;
            }
            updateCadastroCompletion();
          }, 180);
        });
      });
    }

    function isCompletionItemFilled(item) {
      var field = item.selector ? form.querySelector(item.selector) : null;

      if (item.type === 'document') {
        return field && field.closest('.aluno-document-upload').classList.contains('is-loaded');
      }

      if (item.type === 'selfie') {
        if (form.getAttribute('data-selfie-required') !== '1') {
          return true;
        }

        var image = form.querySelector('[data-photo-image]');
        var file = form.querySelector('[data-photo-file]');

        return Boolean((image && image.value) || (file && file.files && file.files.length));
      }

      if (!field) {
        return false;
      }

      var value = field.value;
      var digits = onlyDigits(value);

      if (item.type === 'phone') {
        return digits.length === 11 && !/^0+$/.test(digits);
      }

      if (item.type === 'cpf') {
        return digits.length === 11 && !/^0+$/.test(digits);
      }

      if (item.type === 'cep') {
        return digits.length >= 8 && !/^0+$/.test(digits);
      }

      return hasUsefulValue(value);
    }

    function updateCadastroCompletion() {
      var card = form.querySelector('[data-precadastro-completion]');

      if (!card) {
        return;
      }

      var completionItems = [
        { label: 'Nome', selector: '#nome' },
        { label: 'CPF', selector: '#cpf', type: 'cpf' },
        { label: 'Data de nascimento', selector: '#dataNasc' },
        { label: 'Sexo', selector: '#sexo' },
        { label: 'Telefone', selector: '#fone', type: 'phone' },
        { label: 'Turma', selector: '#turma' },
        { label: 'Mãe', selector: '#mae' },
        { label: 'Naturalidade', selector: '#naturalidade' },
        { label: 'Escolaridade', selector: '#escolaridade' },
        { label: 'Estado Civil', selector: '#estadoCivil' },
        { label: 'CEP', selector: '#cep', type: 'cep' },
        { label: 'Endereço', selector: '#endereco' },
        { label: 'Número', selector: '#numero' },
        { label: 'Bairro', selector: '#bairro' },
        { label: 'Cidade', selector: '#cidade' },
        { label: 'UF', selector: '#uf' },
        { label: 'Selfie', type: 'selfie' },
        { label: 'Documento de Identificação', selector: '#documentoIdentificacao', type: 'document' },
        { label: 'Comprovante de Residência', selector: '#documentoResidencia', type: 'document' }
      ];
      var completed = 0;
      var pending = [];
      var label = card.querySelector('[data-completion-label]');
      var bar = card.querySelector('[data-completion-bar]');
      var detail = card.querySelector('[data-completion-detail]');

      completionItems.forEach(function(item) {
        if (isCompletionItemFilled(item)) {
          completed++;
          return;
        }

        pending.push(item.label);
      });

      var total = completionItems.length;
      var percent = total ? Math.round((completed / total) * 100) : 0;

      card.classList.remove('is-low', 'is-medium', 'is-complete');
      card.classList.add(percent >= 100 ? 'is-complete' : (percent >= 70 ? 'is-medium' : 'is-low'));

      if (label) {
        label.textContent = percent + '%';
      }

      if (bar) {
        bar.style.width = percent + '%';
      }

      if (detail) {
        detail.textContent = pending.length
          ? completed + ' de ' + total + ' itens preenchidos. Pendentes: ' + pending.slice(0, 3).join(', ') + (pending.length > 3 ? '...' : '')
          : 'Cadastro completo nos dados principais e documentos.';
      }

      updateCarteiraButton(percent);

      return percent;
    }

    setupDocumentUploads();

    getRequiredFields().forEach(function(field) {
      field.addEventListener('input', function() {
        markCadastroDirty();
        clearRequiredState(field);
        updateCadastroCompletion();
      });
      field.addEventListener('change', function() {
        markCadastroDirty();
        clearRequiredState(field);
        updateCadastroCompletion();
      });
    });

    form.addEventListener('change', function(event) {
      if (event.target && event.target.matches('[data-precadastro-carteira]')) {
        return;
      }

      markCadastroDirty();
      updateCadastroCompletion();
    });

    updateCadastroCompletion();

    var carteiraButton = form.querySelector('[data-precadastro-carteira]');
    if (carteiraButton) {
      carteiraButton.addEventListener('click', function() {
        var url = carteiraButton.getAttribute('data-carteira-url');

        if (carteiraButton.disabled || !url) {
          return;
        }

        window.location.href = url;
      });
    }

    form.addEventListener('submit', function(event) {
      var requiresSelfie = form.getAttribute('data-selfie-required') === '1';
      var image = form.querySelector('[data-photo-image]');
      var file = form.querySelector('[data-photo-file]');
      var hasCapturedImage = image && image.value;
      var hasFile = file && file.files && file.files.length > 0;

      setError(form, '');

      if (!validateRequiredFields()) {
        event.preventDefault();
        return;
      }

      if (requiresSelfie && !hasCapturedImage && !hasFile) {
        event.preventDefault();
        setError(form, 'Envie uma selfie para concluir o pré-cadastro.');

        var photoSection = form.querySelector('[data-photo-capture]');
        var startButton = form.querySelector('[data-photo-start]');

        if (startButton && startButton.hidden === false) {
          startButton.click();
        }

        if (photoSection) {
          photoSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        updateCadastroCompletion();
        return;
      }

      if (!validateRequiredDocuments()) {
        event.preventDefault();
        updateCadastroCompletion();
        return;
      }

      if (!validateUploadTotalSize()) {
        event.preventDefault();
      }
    });
  });
})();
