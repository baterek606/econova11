document.addEventListener('DOMContentLoaded', () => {
    const authForm = document.getElementById('auth-form');

    // Handle form submission
    authForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent page reload
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const remember = document.getElementById('remember').checked;

        // In a real application, you would send this data to a server here
        console.log('Login attempt:', { email, remember });
        
        alert('Form submitted successfully!');
    });
});
