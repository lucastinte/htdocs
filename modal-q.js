// Modal Q reutilizable para todos los formularios
function showModalQ(msg, isError = false, resetFormId = null, titleText = null) {
  const modal = document.getElementById('modal-q');
  const content = modal.querySelector('.modal-content');
  const title = document.getElementById('modal-q-title');
  const msgEl = document.getElementById('modal-q-msg');
  // Color blanco para ambos, solo cambia el color del texto y borde si es error
  content.style.background = '#fff';
  if (isError) {
    content.style.color = '#d32f2f';
    content.style.border = '2px solid #d32f2f';
    title.textContent = titleText || 'Error';
  } else {
    content.style.color = '#181828';
    content.style.border = '2px solid #6200ea';
    title.textContent = titleText;
  }
  msgEl.textContent = msg;
  modal.style.display = 'flex';
  modal.dataset.resetFormId = isError ? '' : (resetFormId || '');
}
function closeModalQ() {
  const modal = document.getElementById('modal-q');
  modal.style.display = 'none';ß
  if (modal.dataset.resetFormId) {
    const form = document.getElementById(modal.dataset.resetFormId);
    if (form) form.reset();
    modal.dataset.resetFormId = '';
  }
}
