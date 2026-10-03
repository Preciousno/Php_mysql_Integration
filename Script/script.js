document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const confirmation = form.dataset.confirm;
    if (confirmation && !window.confirm(confirmation)) {
        event.preventDefault();
        return;
    }

    if (!form.hasAttribute('data-submit-feedback')) {
        return;
    }

    const submitButton = event.submitter;
    if (!(submitButton instanceof HTMLButtonElement) || submitButton.disabled) {
        return;
    }

    submitButton.disabled = true;
    submitButton.textContent = form.dataset.loadingText || 'Please wait...';
}, true);