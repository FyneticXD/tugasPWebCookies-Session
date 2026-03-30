document.addEventListener('DOMContentLoaded', () => {


    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePasswordBtn.style.opacity = type === 'text' ? '0.5' : '1';
        });
    }


    const formActionMap = {
        myForm: 'login',
        mySignupForm: 'signup_student',
        myTutorForm: 'signup_tutor',
    };

    ['myForm', 'mySignupForm', 'myTutorForm'].forEach(formId => {
        const formElement = document.getElementById(formId);
        if (!formElement) return;

        formElement.addEventListener('submit', async (e) => {
            e.preventDefault();

            const action = formActionMap[formId];
            const formData = new FormData(formElement);
            formData.append('action', action);

            try {
                const res = await fetch('auth.php', { method: 'POST', body: formData });
                const data = await res.json();

                const isLoginSuccess = action === 'login' && data.success;
                if (!isLoginSuccess) {
                    alert(data.message);
                }

                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch (err) {
                alert('Terjadi kesalahan. Pastikan server PHP sudah berjalan.');
                console.error(err);
            }
        });
    });
});