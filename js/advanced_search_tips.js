/**
 * @file
 * Opens and closes the Advanced Search Tips modal.
 *
 * Plain JavaScript instead of Bootstrap's modal plugin: Bootstrap 3 and 4
 * refuse to run with jQuery 4 (shipped by Drupal 11), and loading Bootstrap 5
 * here would duplicate it on themes that already include it. The markup keeps
 * Bootstrap's modal classes, so the theme's Bootstrap 4 CSS ("show") or this
 * module's Bootstrap 3 CSS ("in", css/bootstrap_modal.css) styles it.
 */
((Drupal, once) => {
  const SHOWN = ['show', 'in'];
  // Matches the Bootstrap 3 and 4 modal fade transition.
  const FADE_MS = 300;

  function backdropFor(modal) {
    return document.querySelector(
      `.modal-backdrop[data-ast-backdrop="${modal.id}"]`,
    );
  }

  function open(modal, trigger) {
    if (modal.classList.contains('show')) {
      return;
    }
    modal.astTrigger = trigger;
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade';
    backdrop.dataset.astBackdrop = modal.id;
    document.body.appendChild(backdrop);
    document.body.classList.add('modal-open');
    modal.style.display = 'block';
    modal.removeAttribute('aria-hidden');
    modal.setAttribute('aria-modal', 'true');
    // Reflow so the fade-in transition runs after display changes.
    // eslint-disable-next-line no-void
    void modal.offsetWidth;
    modal.classList.add(...SHOWN);
    backdrop.classList.add(...SHOWN);
    modal.focus();
  }

  function close(modal) {
    if (!modal.classList.contains('show')) {
      return;
    }
    const backdrop = backdropFor(modal);
    modal.classList.remove(...SHOWN);
    if (backdrop) {
      backdrop.classList.remove(...SHOWN);
    }
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    setTimeout(() => {
      modal.style.display = '';
      if (backdrop) {
        backdrop.remove();
      }
      document.body.classList.remove('modal-open');
    }, FADE_MS);
    if (modal.astTrigger) {
      modal.astTrigger.focus();
    }
  }

  Drupal.behaviors.advancedSearchTipsModal = {
    attach(context) {
      once('ast-modal-open', '[data-ast-modal-open]', context).forEach(
        (trigger) => {
          trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const modal = document.querySelector(trigger.dataset.astModalOpen);
            if (modal) {
              open(modal, trigger);
            }
          });
        },
      );
      once('ast-modal', '[data-ast-modal]', context).forEach((modal) => {
        modal.addEventListener('click', (event) => {
          // The close button, or a click outside the dialog (the modal element
          // itself covers the viewport above the backdrop).
          if (
            event.target === modal ||
            event.target.closest('[data-ast-modal-close]')
          ) {
            close(modal);
          }
        });
        modal.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') {
            close(modal);
          }
        });
      });
    },
  };
})(Drupal, once);
