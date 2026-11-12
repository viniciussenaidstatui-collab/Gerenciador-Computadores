#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// ====== LIGAÇÕES DO RC522 NO ESP32 ======
// SDA (SS) -> GPIO 5
// SCK      -> GPIO 18
// MOSI     -> GPIO 23
// MISO     -> GPIO 19
// RST      -> GPIO 22
// GND      -> GND
// 3.3V     -> 3V3
// IRQ      -> não conecta
#define SS_PIN 5
#define RST_PIN 22

// ====== CONFIGURAÇÃO ======
// Wi-Fi: tem que ser a MESMA rede do computador com o Laravel.
const char* ssid = "WIFI_ADM_CAT850";
const char* senha = "5351C@T850%Adm";

// IP do COMPUTADOR que roda o Laravel (rode `ipconfig` no Windows).
// O IP muda por DHCP: se mudar, atualize aqui.
const char* hostLaravel = "10.122.35.73";
const int portaLaravel = 8000;
const char* rotaLaravel = "/api/rfid/ler";

// Mesmo valor de ESP32_DEVICE_TOKEN do .env do Laravel.
const char* tokenDispositivo = "70409fa3d331615fffa95edcc38e1cfd1d4de2ed16ba2944c0a6f93b2e48f8f2";
// ==========================

String urlLaravel = String("http://") + hostLaravel + ":" + portaLaravel + rotaLaravel;

MFRC522 rfid(SS_PIN, RST_PIN);

String ultimoUID = "";
unsigned long ultimaLeitura = 0;
unsigned long ultimaTentativaWiFi = 0;
const unsigned long intervaloEnvio = 3000;
const unsigned long intervaloReconexaoWiFi = 10000;

String lerUID() {
  String uid = "";

  for (byte indice = 0; indice < rfid.uid.size; indice++) {
    if (rfid.uid.uidByte[indice] < 0x10) {
      uid += "0";
    }
    uid += String(rfid.uid.uidByte[indice], HEX);
  }

  uid.toUpperCase();
  return uid;
}

void reconectarWiFiSeNecessario() {
  if (WiFi.status() == WL_CONNECTED || millis() - ultimaTentativaWiFi < intervaloReconexaoWiFi) {
    return;
  }

  ultimaTentativaWiFi = millis();
  Serial.println("Wi-Fi desconectado. Tentando reconectar...");
  WiFi.reconnect();
}

// Testa só a conexão de rede com o servidor (sem HTTP/rota/token).
// Se falhar aqui, o problema é rede/servidor, não o código do ESP32.
bool testarConexaoServidor() {
  WiFiClient teste;
  Serial.print("Testando conexao com ");
  Serial.print(hostLaravel);
  Serial.print(":");
  Serial.println(portaLaravel);

  if (teste.connect(hostLaravel, portaLaravel, 5000)) {
    Serial.println(">> Servidor ALCANCADO.");
    teste.stop();
    return true;
  }

  Serial.println(">> NAO alcancou o servidor. Verifique:");
  Serial.println("   1) php artisan serve --host=0.0.0.0 --port=8000");
  Serial.println("   2) IP do computador (ipconfig) igual ao hostLaravel");
  Serial.println("   3) Firewall do Windows liberando a porta 8000");
  Serial.println("   4) ESP32 e computador na mesma rede Wi-Fi");
  return false;
}

void enviarUID(const String& uid) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Sem Wi-Fi. Confira se ESP32 e computador estao na mesma rede.");
    return;
  }

  HTTPClient http;
  http.setTimeout(8000);

  if (!http.begin(urlLaravel)) {
    Serial.println("Nao foi possivel iniciar a conexao HTTP.");
    return;
  }

  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.addHeader("X-DEVICE-TOKEN", tokenDispositivo);

  const String json = "{\"UID\":\"" + uid + "\"}";
  Serial.println("Enviando UID para o Laravel...");
  Serial.print("URL: ");
  Serial.println(urlLaravel);
  Serial.print("JSON: ");
  Serial.println(json);

  const int codigoHTTP = http.POST(json);
  Serial.print("Codigo HTTP: ");
  Serial.println(codigoHTTP);

  if (codigoHTTP > 0) {
    const String resposta = http.getString();
    Serial.println("Resposta do Laravel:");
    Serial.println(resposta);

    // O Laravel responde sempre JSON: {"sucesso": true/false, "mensagem": "..."}
    JsonDocument doc;
    const DeserializationError erro = deserializeJson(doc, resposta);

    if (!erro) {
      const bool sucesso = doc["sucesso"] | false;
      const String mensagem = doc["mensagem"] | "Resposta sem mensagem do Laravel.";

      Serial.print(sucesso ? "[OK] " : "[NEGADO] ");
      Serial.println(mensagem);
    } else {
      Serial.print("Resposta nao e JSON (");
      Serial.print(erro.c_str());
      Serial.println("). Provavel rota inexistente ou erro 500: veja php artisan route:list e storage/logs/laravel.log.");
    }
  } else {
    Serial.print("Falha HTTP: ");
    Serial.println(http.errorToString(codigoHTTP));
    if (codigoHTTP == -1) {
      Serial.println("Dica: conexao recusada (servidor fora do ar, IP/porta errados, ou serve em 127.0.0.1).");
    } else if (codigoHTTP == -11) {
      Serial.println("Dica: timeout lendo a resposta (Laravel demorou ou travou).");
    }
  }

  http.end();
}

void setup() {
  Serial.begin(115200);

  SPI.begin(18, 19, 23, SS_PIN);
  rfid.PCD_Init();
  Serial.println("Leitor RFID RC522 iniciado.");

  WiFi.mode(WIFI_STA);
  WiFi.setAutoReconnect(true);
  WiFi.begin(ssid, senha);

  Serial.print("Conectando ao Wi-Fi");
  const unsigned long inicioWiFi = millis();

  while (WiFi.status() != WL_CONNECTED && millis() - inicioWiFi < 20000) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("Wi-Fi conectado!");
    Serial.print("IP do ESP32: ");
    Serial.println(WiFi.localIP());
    Serial.print("Gateway: ");
    Serial.println(WiFi.gatewayIP());

    testarConexaoServidor();
  } else {
    Serial.println("Wi-Fi ainda desconectado. O ESP32 continuara tentando.");
  }

  Serial.println("Selecione um computador no site e aproxime o cartao do responsavel.");
}

void loop() {
  reconectarWiFiSeNecessario();

  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  const String uid = lerUID();
  Serial.print("UID detectado: ");
  Serial.println(uid);

  if (uid != ultimoUID || millis() - ultimaLeitura >= intervaloEnvio) {
    enviarUID(uid);
    ultimoUID = uid;
    ultimaLeitura = millis();
  }

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  delay(100);
}
