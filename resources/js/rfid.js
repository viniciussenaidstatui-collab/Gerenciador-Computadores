const form = document.querySelector('#rfid-form');

if (form) {
    const submitButton = document.querySelector('#rfid-submit');
    const feedback = document.querySelector('#rfid-feedback');
    const initialButtonText = submitButton.textContent.trim();

    const showFeedback = (message, type = 'error') => {
        feedback.textContent = message;
        feedback.className = type === 'success'
            ? 'rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700'
            : type === 'pending'
                ? 'rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-800'
                : 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700';
    };

    const clearErrors = () => {
        form.querySelectorAll('[data-error]').forEach((element) => {
            element.textContent = '';
            element.classList.add('hidden');
        });
    };

    const messageForDeviceStatus = (status) => {
        const messages = {
            timeout: 'O leitor não detectou uma etiqueta em 20 segundos. Tente novamente e mantenha a tag junto ao RC522.',
            tag_incompativel: 'A etiqueta não é compatível. Use uma MIFARE Classic Mini, 1K ou 4K.',
            falha_autenticacao: 'O ESP32 não conseguiu autenticar a etiqueta.',
            falha_gravacao: 'O ESP32 não conseguiu gravar o patrimônio.',
            falha_leitura: 'A etiqueta foi detectada, mas o ESP32 não conseguiu ler o bloco RFID.',
            falha_verificacao: 'O ESP32 não conseguiu confirmar a gravação.',
            divergencia_verificacao: 'O valor lido após a gravação não corresponde ao patrimônio enviado.',
            dados_invalidos: 'O ESP32 rejeitou o número de patrimônio.',
        };

        return messages[status] || `O ESP32 não confirmou a operação (${status || 'sem status'}).`;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        const esp32Url = form.dataset.esp32Url;

        if (!esp32Url) {
            showFeedback('Configure ESP32_BASE_URL no .env e recarregue esta página.');
            return;
        }

        const patrimonio = form.querySelector('[name="numero_patrimonio"]').value.trim();
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 30000);

        submitButton.disabled = true;
        submitButton.textContent = 'Aguardando resposta do leitor...';
        showFeedback('Aproxime e mantenha a etiqueta no RC522. O ESP32 aguarda por até 20 segundos.', 'pending');

        try {
            const deviceResponse = await fetch(esp32Url, {
                method: 'POST',
                mode: 'cors',
                credentials: 'omit',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ patrimonio }),
                signal: controller.signal,
            });
            const deviceResult = await deviceResponse.json();

            if (!deviceResponse.ok || deviceResult.status !== 'sucesso') {
                showFeedback(messageForDeviceStatus(deviceResult.status));
                return;
            }

            if (deviceResult.patrimonio !== patrimonio || !deviceResult.uid) {
                showFeedback('O ESP32 respondeu, mas os dados confirmados não correspondem ao formulário.');
                return;
            }

            showFeedback('Etiqueta gravada. Salvando o computador no sistema...', 'pending');
            submitButton.textContent = 'Salvando cadastro...';

            const data = new FormData(form);
            data.append('rfid_uid', deviceResult.uid);
            data.append('status_esp32', deviceResult.status);
            data.append('patrimonio_esp32', deviceResult.patrimonio);

            const saveResponse = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: data,
            });
            const saveResult = await saveResponse.json();

            if (!saveResponse.ok) {
                if (saveResult.errors) {
                    Object.entries(saveResult.errors).forEach(([field, messages]) => {
                        const error = form.querySelector(`[data-error="${field}"]`);

                        if (error) {
                            error.textContent = messages[0];
                            error.classList.remove('hidden');
                        }
                    });
                }

                showFeedback(`${saveResult.message || 'Não foi possível salvar o computador.'} A etiqueta já foi gravada.`);
                return;
            }

            showFeedback(`${saveResult.message} Patrimônio: ${saveResult.computador.numero_patrimonio} · UID: ${saveResult.computador.rfid_uid}`, 'success');
            form.reset();
            form.querySelector('[name="numero_patrimonio"]').focus();
        } catch (error) {
            const message = error.name === 'AbortError'
                ? 'O ESP32 não respondeu no prazo. Confira o Serial e tente novamente.'
                : 'O navegador não conseguiu acessar o ESP32. Confirme que o firmware com CORS foi gravado no dispositivo.';

            showFeedback(message);
        } finally {
            window.clearTimeout(timeout);
            submitButton.disabled = false;
            submitButton.textContent = initialButtonText;
        }
    });
}
