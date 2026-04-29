document.addEventListener('DOMContentLoaded', () => {
    const authForm = document.getElementById('auth-form');

    // Handle form submission
    authForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent page reload
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const storedUserJSON = localStorage.getItem('user');
        if (storedUserJSON) {
            const storedUser = JSON.parse(storedUserJSON);
            if (storedUser.email === email && storedUser.password === password) {
                alert('Login successful!');
                window.location.href = 'index.html';
            } else {
                alert('Invalid email or password. Please try again.');
            }
        } else {
            alert('No account found. Please sign up first.');
        }
    });
});
