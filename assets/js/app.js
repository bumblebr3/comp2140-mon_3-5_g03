// Shared click handler for elements that ask for confirmation before continuing.
// Attach the same confirmation behavior to every element marked with data-confirm.
document.querySelectorAll('[data-confirm]').forEach(function (element) {
    element.addEventListener('click', function (event) {
        if (!window.confirm(element.getAttribute('data-confirm'))) event.preventDefault();
    });
});
