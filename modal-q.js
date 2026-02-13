let modalCallback = null;

function showModalQ(msg, isError = false, resetFormIdOrCallback = null, titleText = null, type = 'default') {
  const modal = document.getElementById('modal-q');
  const content = modal.querySelector('.modal-content');
  const title = document.getElementById('modal-q-title');
  const msgEl = document.getElementById('modal-q-msg');
  content.style.background = '#fff';

  modalCallback = null; // Reset

  // Determinar color según el tipo
  if (type === 'success') {
    content.style.color = '#388e3c'; // verde
    content.style.border = '2px solid #388e3c';
    title.textContent = titleText || 'Éxito';
  } else if (isError || type === 'error') {
    content.style.color = '#d32f2f'; // rojo
    content.style.border = '2px solid #d32f2f';
    title.textContent = titleText || 'Error';
  } else {
    content.style.color = '#181828'; // morado por defecto
    content.style.border = '2px solid #6200ea';
    title.textContent = titleText;
  }

  msgEl.textContent = msg;
  modal.style.display = 'flex';

  if (typeof resetFormIdOrCallback === 'function') {
    modalCallback = resetFormIdOrCallback;
    modal.dataset.resetFormId = '';
  } else {
    modal.dataset.resetFormId = (isError || type === 'error') ? '' : (resetFormIdOrCallback || '');
  }
}

function closeModalQ() {
  const modal = document.getElementById('modal-q');
  modal.style.display = 'none';

  if (modal.dataset.resetFormId) {
    const form = document.getElementById(modal.dataset.resetFormId);
    if (form) form.reset();
    modal.dataset.resetFormId = '';
  }

  if (modalCallback) {
    modalCallback();
    modalCallback = null;
  }
}
