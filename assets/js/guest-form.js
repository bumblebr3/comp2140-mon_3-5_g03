
// Controls the three-step guest sign-in form, including per-step validation.
(function () {
    const form = document.getElementById('guestForm');
    if (!form) return;

    const sections = Array.from(form.querySelectorAll('.form-section'));
    const backButton = document.getElementById('backBtn');
    const nextButton = document.getElementById('nextBtn');
    const submitButton = document.getElementById('submitBtn');
    const errorBox = document.getElementById('formError');

    let currentPage = 0;

    // Display one form section and update navigation buttons.
    function showPage(index) {
        sections.forEach((section, i) => {
            section.hidden = i !== index;
        });

        backButton.hidden = index === 0;
        nextButton.hidden = index === sections.length - 1;
        submitButton.hidden = index !== sections.length - 1;
        errorBox.hidden = true;

        currentPage = index;
    }

    // Validate the visible step using native browser validation.
    function validateCurrentPage() {
        const fields = sections[currentPage].querySelectorAll('input, select');

        for (const field of fields) {
            if (field.disabled || field.type === 'checkbox') continue;

            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }

        return true;
    }

    nextButton.addEventListener('click', function () {
        if (!validateCurrentPage()) return;

        showPage(Math.min(currentPage + 1, sections.length - 1));
    });

    backButton.addEventListener('click', function () {
        showPage(Math.max(currentPage - 1, 0));
    });

    // Validate every step before submitting the form.
    form.addEventListener('submit', function (event) {
        for (let i = 0; i < sections.length; i++) {
            showPage(i);

            if (!validateCurrentPage()) {
                event.preventDefault();
                return;
            }
        }

        showPage(sections.length - 1);
    });

    showPage(0);
}());
