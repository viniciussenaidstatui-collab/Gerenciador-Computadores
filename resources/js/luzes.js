$(document).ready(function () {

  const ESP = 'http://10.122.41.240';

  // Envia uma requisição e retorna a promessa (jqXHR)
  function requisicao(rota, metodo = 'POST') {
    return $.ajax({ url: ESP + rota, method: metodo, dataType: 'json' });
  }

  function sucesso(titulo) {
    Swal.fire({ icon: 'success', title: titulo, timer: 1200, showConfirmButton: false });
  }

  function erro() {
    Swal.fire({ icon: 'error', title: 'Falha ao comunicar com o ESP32' });
  }

  // Uma luz: ligar ou desligar
  function acaoLuz(rota, mensagem) {
    requisicao(rota)
      .done(() => sucesso(mensagem))
      .fail((xhr, status, err) => {
        console.error('Erro:', rota, status, err);
        erro();
      });
  }

  // Várias luzes de uma vez, com um único aviso no final
  function acaoTodas(rotas, mensagem) {
    $.when(...rotas.map(r => requisicao(r)))
      .done(() => sucesso(mensagem))
      .fail((xhr, status, err) => {
        console.error('Erro:', status, err);
        erro();
      });
  }

  // ---------- VERMELHA ----------
  $('#ligar_vermelha').click(() => acaoLuz('/led/ligarvermelho', 'Luz vermelha ligada!'));
  $('#desligar_vermelha').click(() => acaoLuz('/led/desligarvermelho', 'Luz vermelha desligada!'));

  // ---------- AMARELA ----------
  $('#ligar_amarela').click(() => acaoLuz('/led/ligaramarelo', 'Luz amarela ligada!'));
  $('#desligar_amarela').click(() => acaoLuz('/led/desligaramarelo', 'Luz amarela desligada!'));

  // ---------- AZUL (no ESP32 é o LED verde) ----------
  $('#ligar_azul').click(() => acaoLuz('/led/ligarverde', 'Luz verde ligada!'));
  $('#desligar_azul').click(() => acaoLuz('/led/desligarverde', 'Luz verde desligada!'));

  // ---------- TODAS ----------
  $('#ligar_todas').click(() => acaoTodas(
    ['/led/ligarvermelho', '/led/ligaramarelo', '/led/ligarverde'],
    'Todas as luzes ligadas!'
  ));

  $('#desligar_todas').click(() => acaoTodas(
    ['/led/desligarvermelho', '/led/desligaramarelo', '/led/desligarverde'],
    'Todas as luzes desligadas!'
  ));

  // ---------- STATUS (opcional) ----------
  // Precisa de um botão com id="status_todas" no HTML
  $('#status_todas').click(function () {
    requisicao('/led/status', 'GET')
      .done(function (resp) {
        Swal.fire({
          title: 'Status das luzes',
          html:
            'Vermelha: <b>' + resp.Leds.Vermelho + '</b><br>' +
            'Amarela: <b>' + resp.Leds.Amarelo + '</b><br>' +
            'Verde: <b>' + resp.Leds.Verde + '</b>'
        });
      })
      .fail(erro);
  });

});