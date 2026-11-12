const buttons = document.querySelectorAll('.rfid-select-button');
const feedback = document.querySelector('#rfid-feedback');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const showFeedback = (message, type = 'error') => {
    feedback.textContent = message;
    feedback.className = type === 'success'
        ? 'rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700'
        : type === 'pending'
            ? 'rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-800'
            : 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700';
};

const wait = (milliseconds) => new Promise((resolve) => window.setTimeout(resolve, milliseconds));

const readJson = async (response) => {
    const result = await response.json();

    if (!response.ok) {
        throw new Error(result.mensagem || result.message || 'Não foi possível iniciar a seleção.');
    }

    return result;
};

const pollStatus = async (button, deadline) => {
    while (Date.now() < deadline) {
        await wait(2000);

        const response = await fetch('/rfid/status', {
            headers: { Accept: 'application/json' },
        });
        const result = await readJson(response);

        if (result.resultado) {
            showFeedback(result.resultado.mensagem, result.resultado.sucesso ? 'success' : 'error');

            if (result.resultado.sucesso) {
                await wait(1200);
                window.location.reload();
                return;
            }
        }

        if (!result.pendente) {
            if (!result.resultado) {
                showFeedback('A seleção foi encerrada. Selecione o computador novamente.');
            }
            button.disabled = false;
            return;
        }
    }

    showFeedback('Tempo esgotado. Selecione o computador novamente.');
    button.disabled = false;
};

buttons.forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;
        showFeedback('Preparando a leitura. Aproxime o cartão do responsável em até 60 segundos.', 'pending');

        try {
            const response = await fetch(button.dataset.selectUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const result = await readJson(response);

            showFeedback(result.mensagem, 'pending');
            await pollStatus(button, Date.now() + 60000);
        } catch (error) {
            showFeedback(error.message || 'Não foi possível iniciar a leitura.');
            button.disabled = false;
        }
    });
});
