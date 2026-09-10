const token = localStorage.getItem('token_usuario');
const logoutButton = document.querySelector('#logout-button');

console.log('Token atual:', token);

if (!token) {
    window.location.replace('/login');
} else {
    fetch('/api/token/validate', {
        headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
        },
    }).then((response) => {
        if (!response.ok) {
            localStorage.removeItem('token_usuario');
            window.location.replace('/login');
        }
    }).catch(() => {
        localStorage.removeItem('token_usuario');
        window.location.replace('/login');
    });
}

logoutButton?.addEventListener('click', async () => {
    logoutButton.disabled = true;

    try {
        const response = await fetch('/logout', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        if (!response.ok) {
            throw new Error('Não foi possível sair.');
        }

        localStorage.removeItem('token_usuario');
        window.location.replace('/login');
    } catch (error) {
        logoutButton.disabled = false;
        console.error(error);
    }
});
