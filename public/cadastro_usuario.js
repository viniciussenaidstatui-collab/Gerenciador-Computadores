$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#form-cadastro-usuario").on("submit", function (e) {
        e.preventDefault(); // impede o form de recarregar a página via GET

        $.ajax({
            url: "/api/cadastro_usuario",
            method: "POST",
            data: {
                nome: $("#nome").val(),
                email: $("#email").val(),
                senha: $("#senha").val(),
                data_nascimento: $("#data_nascimento").val(),
                cpf: $("#cpf").val(),
            },
            success: function (response) {
                if (response.erro == 'n') {
                    window.location.href = "/login";
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.mensagem,
                    });
                }
            },
            error: function (xhr) {
                let mensagem = "Erro inesperado ao cadastrar. Tente novamente.";

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Erros de validação do Laravel (campo obrigatório, formato, etc.)
                    mensagem = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: mensagem,
                });
            }
        });
    });
});