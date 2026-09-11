// Blog Modal Listener
const blogModal = document.getElementById('blogModal');
if (blogModal) {
  blogModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const title = button.getAttribute('data-title');
    const date = button.getAttribute('data-date');
    const image = button.getAttribute('data-image');
    const content = button.getAttribute('data-content');

    const modalTitle = blogModal.querySelector('#modalTitle');
    const modalDate = blogModal.querySelector('#modalDate');
    const modalImage = blogModal.querySelector('#modalImage');
    const modalContent = blogModal.querySelector('#modalContent');

    if (modalTitle) modalTitle.textContent = title;
    if (modalDate) {
      modalDate.textContent = 'Received ' + date;
      modalDate.setAttribute('datetime', date);
    }
    if (modalImage) {
      // Posts without an image should not show an empty 300px box
      modalImage.hidden = !image;
      if (image) modalImage.src = image;
      else modalImage.removeAttribute('src');
    }
    if (modalContent) modalContent.textContent = content;
  });
}
