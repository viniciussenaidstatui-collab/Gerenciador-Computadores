const $ = window.jQuery;
const token = $.cookie('token_usuario');
const logoutButton = document.querySelector('#logout-button');

if (!token) {
    window.location.replace('/login');
} else {
    fetch('/api/token/validate', {
        headers: {
            Accept: 'application/json',
        },
    }).then((response) => {
        if (!response.ok) {
            $.cookie('token_usuario', '', { expires: -1, path: '/' });
            window.location.replace('/login');
        }
    }).catch(() => {
        $.cookie('token_usuario', '', { expires: -1, path: '/' });
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

        $.cookie('token_usuario', '', { expires: -1, path: '/' });
        window.location.replace('/login');
    } catch (error) {
        logoutButton.disabled = false;
        console.error(error);
    }
});
