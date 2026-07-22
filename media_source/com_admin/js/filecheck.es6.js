/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {
  if (!Joomla) {
    throw new Error('core.js was not properly initialised');
  }

  const path = 'index.php?option=com_admin&tmpl=component&format=json';
  const getToken = () => `&${document.getElementById('filecheck-token').getAttribute('name')}=1`;

  const statusBadgeClass = {
    changed: 'bg-danger',
    missing: 'bg-warning',
    unknown: 'bg-info',
    invalid: 'bg-secondary',
  };

  const escapeHtml = (value) => {
    const div = document.createElement('div');
    div.textContent = value === null || value === undefined ? '' : value;
    return div.innerHTML;
  };

  const renderRow = (row) => {
    const tr = document.createElement('tr');
    tr.dataset.status = row.status;
    const statusKey = `COM_ADMIN_FILECHECK_STATUS_${row.status.toUpperCase()}`;
    const statusLabel = Joomla.Text._(statusKey) || row.status;
    const badgeClass = statusBadgeClass[row.status] || 'bg-secondary';
    tr.innerHTML = Joomla.sanitizeHtml([
      `<td><span class="badge ${badgeClass}">${escapeHtml(statusLabel)}</span></td>`,
      `<td>${escapeHtml(row.path)}</td>`,
      `<td>${escapeHtml(row.expected)}</td>`,
      `<td>${escapeHtml(row.actual)}</td>`,
    ].join(''));
    return tr;
  };

  Joomla.filecheckRunner = (mode) => {
    const els = {
      modalHeader: document.getElementById('filecheck-progress-header'),
      modalMessage: document.getElementById('filecheck-progress-message'),
      progressBar: document.getElementById('filecheck-progress-bar'),
      modal: document.getElementById('filecheck-progress-modal'),
      resultsSection: document.getElementById('filecheck-results-section'),
      resultsBody: document.getElementById('filecheck-results-body'),
      generateDownload: document.getElementById('filecheck-generate-download'),
      generateDownloadLink: document.getElementById('filecheck-generate-download-link'),
    };

    const accumulatedResults = [];
    const counts = {
      ok: 0, changed: 0, missing: 0, unknown: 0, invalid: 0,
    };

    const updateCounts = () => {
      Object.keys(counts).forEach((key) => {
        const el = document.querySelector(`[data-filecheck-count="${key}"]`);
        if (el) {
          el.textContent = counts[key];
        }
      });
    };

    const updateProgress = (offset, total) => {
      const progress = total > 0 ? Math.min(100, Math.round((offset / total) * 100)) : 100;
      if (els.progressBar) {
        els.progressBar.style.width = `${progress}%`;
        els.progressBar.setAttribute('aria-valuenow', progress);
      }
    };

    const setMessage = (key) => {
      if (els.modalMessage) {
        els.modalMessage.textContent = Joomla.Text._(key);
      }
    };

    const showError = (message) => {
      if (els.modalHeader) {
        els.modalHeader.textContent = Joomla.Text._('COM_ADMIN_FILECHECK_CHECKER_HEADER_ERROR');
        els.modalHeader.classList.add('text-danger');
      }
      if (els.modalMessage) {
        els.modalMessage.innerHTML = Joomla.sanitizeHtml(message);
        els.modalMessage.classList.add('text-danger');
      }
    };

    const addInvalidLines = (invalidLines) => {
      (invalidLines || []).forEach((line) => {
        accumulatedResults.push({
          status: 'invalid',
          path: line.raw,
          expected: Joomla.Text._(line.reason) || line.reason,
          actual: null,
        });
      });
    };

    const finishCheck = () => {
      if (els.modal && typeof els.modal.close === 'function') {
        els.modal.close();
      }
      if (els.resultsBody) {
        accumulatedResults.forEach((row) => {
          els.resultsBody.appendChild(renderRow(row));
        });
      }
      updateCounts();
      if (els.resultsSection) {
        els.resultsSection.classList.remove('d-none');
      }
    };

    const finishGenerate = () => {
      if (els.modal && typeof els.modal.close === 'function') {
        els.modal.close();
      }
      if (els.generateDownload && els.generateDownloadLink) {
        els.generateDownloadLink.href = `index.php?option=com_admin&task=generate.download${getToken()}`;
        els.generateDownload.classList.remove('d-none');
      }
    };

    const handleFailure = (error) => {
      let message = Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER');
      if (error instanceof SyntaxError) {
        message = Joomla.Text._('JLIB_JS_AJAX_ERROR_PARSE');
      } else if (error && error.responseText) {
        try {
          const data = JSON.parse(error.responseText);
          message = data.message || message;
        } catch (parseError) {
          // Keep the default message.
        }
      }
      showError(message);
    };

    const requestBatch = (task, data) => {
      Joomla.request({
        url: `${path}&task=${task}${getToken()}`,
        method: 'POST',
        data: data || null,
        promise: true,
        // eslint-disable-next-line no-use-before-define
      }).then((xhr) => handleResponse(JSON.parse(xhr.responseText))).catch((error) => handleFailure(error));
    };

    function handleResponse(json) {
      if (!json || json.success === false) {
        showError((json && json.message) || Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER'));
        return;
      }

      if (mode === 'generate') {
        updateProgress(json.offset, json.total);
        setMessage('COM_ADMIN_FILECHECK_CHECKER_MESSAGE_GENERATE_RUNNING');

        if (!json.done) {
          requestBatch('generate.batch');
        } else {
          setMessage('COM_ADMIN_FILECHECK_CHECKER_MESSAGE_GENERATE_COMPLETE');
          finishGenerate();
        }
        return;
      }

      addInvalidLines(json.invalidLines);

      if (Array.isArray(json.results)) {
        accumulatedResults.push(...json.results);
      }

      if (json.counts) {
        Object.assign(counts, json.counts);
      }

      updateProgress(json.grandOffset || 0, json.grandTotal || 0);
      setMessage(
        json.phase === 'unknown'
          ? 'COM_ADMIN_FILECHECK_CHECKER_MESSAGE_UNKNOWN_PHASE'
          : 'COM_ADMIN_FILECHECK_CHECKER_MESSAGE_VERIFY_PHASE',
      );

      if (!json.done) {
        requestBatch('check.batch');
      } else {
        if (els.modalHeader) {
          els.modalHeader.textContent = Joomla.Text._('COM_ADMIN_FILECHECK_CHECKER_HEADER_COMPLETE');
        }
        setMessage('COM_ADMIN_FILECHECK_CHECKER_MESSAGE_COMPLETE');
        finishCheck();
      }
    }

    const resetModal = () => {
      if (els.modalHeader) {
        els.modalHeader.textContent = Joomla.Text._('COM_ADMIN_FILECHECK_CHECKER_HEADER_INIT');
        els.modalHeader.classList.remove('text-danger');
      }
      if (els.modalMessage) {
        els.modalMessage.textContent = '';
        els.modalMessage.classList.remove('text-danger');
      }
      updateProgress(0, 1);
      if (els.resultsBody) {
        els.resultsBody.innerHTML = '';
      }
      if (els.resultsSection) {
        els.resultsSection.classList.add('d-none');
      }
      if (els.generateDownload) {
        els.generateDownload.classList.add('d-none');
      }
      if (els.modal && typeof els.modal.open === 'function') {
        els.modal.open();
      }
    };

    const startDiscovery = () => {
      resetModal();
      requestBatch('check.start');
    };

    const startGenerate = () => {
      resetModal();
      requestBatch('generate.start');
    };

    const startUpload = (fileInput) => {
      resetModal();
      const formData = new FormData();
      formData.append('checksumsfile', fileInput.files[0]);
      Joomla.request({
        url: `${path}&task=check.upload${getToken()}`,
        method: 'POST',
        data: formData,
        promise: true,
      }).then((xhr) => handleResponse(JSON.parse(xhr.responseText))).catch((error) => handleFailure(error));
    };

    const exportResults = () => {
      const lines = accumulatedResults.map((row) => [
        row.status.toUpperCase(),
        row.path,
        row.expected || '',
        row.actual || '',
      ].join('\t'));
      const blob = new Blob([lines.join('\n')], { type: 'text/plain' });
      const link = document.createElement('a');
      const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
      link.href = URL.createObjectURL(blob);
      link.download = `filecheck-results-${stamp}.txt`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(link.href);
    };

    return {
      startDiscovery, startGenerate, startUpload, exportResults,
    };
  };

  document.addEventListener('DOMContentLoaded', () => {
    const checkButton = document.querySelector('.button-filecheck-check');
    const generateButton = document.querySelector('.button-filecheck-generate');
    const uploadForm = document.getElementById('filecheck-upload-form');
    const exportButton = document.getElementById('filecheck-export-button');

    let activeCheckRunner = null;

    if (checkButton) {
      checkButton.addEventListener('click', (event) => {
        event.preventDefault();
        activeCheckRunner = Joomla.filecheckRunner('discovery');
        activeCheckRunner.startDiscovery();
      });
    }

    if (generateButton) {
      generateButton.addEventListener('click', (event) => {
        event.preventDefault();
        Joomla.filecheckRunner('generate').startGenerate();
      });
    }

    if (uploadForm) {
      uploadForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const fileInput = document.getElementById('filecheck-upload-input');
        if (!fileInput || !fileInput.files.length) {
          return;
        }
        activeCheckRunner = Joomla.filecheckRunner('upload');
        activeCheckRunner.startUpload(fileInput);
      });
    }

    document.querySelectorAll('[data-filecheck-filter]').forEach((button) => {
      button.addEventListener('click', () => {
        button.classList.toggle('active');
        const status = button.dataset.filecheckFilter;
        const show = button.classList.contains('active');
        document.querySelectorAll(`#filecheck-results-body tr[data-status="${status}"]`).forEach((row) => {
          row.classList.toggle('d-none', !show);
        });
      });
    });

    if (exportButton) {
      exportButton.addEventListener('click', () => {
        if (activeCheckRunner) {
          activeCheckRunner.exportResults();
        }
      });
    }
  });
})(Joomla, document);
