# Prompt — Sistema Web de Registro de Ponto com OCR

## 1. Objetivo

Criar um sistema web simples, responsivo e mobile-first para registrar e acompanhar jornadas de trabalho.

O principal objetivo é permitir que o usuário utilize o celular para tirar uma foto do seu espelho/cartão de ponto. O sistema deverá utilizar OCR para identificar automaticamente:

* Data
* Horário da marcação

Após o OCR, o sistema deverá validar os dados encontrados e permitir que o usuário confirme ou corrija as informações antes de salvar.

Também deverá ser possível realizar o registro manualmente, sem necessidade de fotografia.

O sistema deverá trabalhar com quatro marcações diárias:

1. Entrada
2. Saída para almoço
3. Retorno do almoço
4. Saída/Fim do expediente

O sistema deverá calcular automaticamente as horas trabalhadas, saldo diário, saldo semanal e saldo mensal, mantendo um banco de horas.

---

# 2. Nome do sistema

Sugestões:

* PontoOCR
* Meu Ponto OCR
* Ponto Fácil
* PontoSmart
* TimeOCR
* Meu Espelho
* Ponto Digital

Nome provisório do projeto:

**PontoOCR**

---

# 3. Stack tecnológica

Utilizar uma arquitetura simples, organizada e preparada para evolução.

### Backend

* PHP 8.3+
* Laravel 11+
* Laravel Sanctum
* MySQL 8+
* Laravel Storage

### Frontend

Preferencialmente:

* Blade
* Livewire
* Tailwind CSS

O sistema deve ser **mobile-first**, pois a principal utilização será pelo celular.

### OCR

Criar uma camada de abstração para OCR.

Interface:

```php
interface OcrServiceInterface
{
    public function extract(string $imagePath): OcrResult;
}
```

Implementação inicial:

```text
GoogleCloudVisionOcrService
```

Também criar:

```text
FakeOcrService
```

para desenvolvimento e testes.

O provedor de OCR deverá ser configurável através do `.env`.

Exemplo:

```env
OCR_PROVIDER=google_vision
OCR_CONFIDENCE_HIGH=0.90
OCR_CONFIDENCE_MEDIUM=0.70
OCR_AUTO_CONFIRM=true
```

---

# 4. Fluxo principal

O fluxo principal deverá ser:

```text
Usuário
   ↓
Abrir "Registrar Ponto"
   ↓
Escolher:
   ├── Tirar foto
   └── Registrar manualmente
   ↓
OCR
   ↓
Identificar data e horário
   ↓
Validar dados
   ↓
Usuário confirma/corrige
   ↓
Salvar marcação
   ↓
Atualizar jornada
   ↓
Atualizar banco de horas
```

---

# 5. Registro utilizando fotografia

No celular, disponibilizar um botão:

**📷 Registrar ponto com foto**

Ao clicar:

```text
Abrir câmera
     ↓
Capturar imagem
     ↓
Pré-visualizar
     ↓
Enviar para OCR
     ↓
Extrair data/hora
     ↓
Mostrar resultado
```

Exemplo:

```text
Data encontrada:
07/10/2026

Horário encontrado:
08:03

Confiança:
96%
```

Mostrar:

```text
Data
[ 07/10/2026 ]

Hora
[ 08:03 ]

Tipo de marcação
[ Entrada ▼ ]

[ Confirmar registro ]
[ Tirar outra foto ]
```

O usuário poderá corrigir a data ou horário antes de confirmar.

---

# 6. Registro manual

Também disponibilizar:

**✏️ Registrar manualmente**

Formulário:

```text
Data
[ 07/10/2026 ]

Hora
[ 08:03 ]

Tipo
[ Entrada ▼ ]

[ Salvar ]
```

A fotografia não será obrigatória.

Porém, quando existir uma foto, ela deverá ser armazenada junto à marcação.

---

# 7. Validação de data

Nunca permitir datas inválidas.

Utilizar o padrão:

```text
DD/MM/YYYY
```

Exemplo válido:

```text
07/10/2026
```

Exemplos inválidos:

```text
32/10/2026
07/13/2026
31/02/2026
```

No backend utilizar validação real de data, não apenas regex.

Exemplo conceitual:

```php
'date' => [
    'required',
    'date_format:d/m/Y',
]
```

---

# 8. Validação de horário

O horário deverá obrigatoriamente utilizar formato 24 horas:

```text
HH:mm
```

Exemplos válidos:

```text
08:00
08:30
12:00
13:15
18:05
23:59
```

Exemplos inválidos:

```text
8:00
25:00
13:70
8 PM
```

Backend e frontend devem validar o horário.

---

# 9. Tipos de marcação

Cada jornada diária poderá possuir quatro marcações:

```text
ENTRY
LUNCH_START
LUNCH_END
EXIT
```

Interface amigável:

```text
🟢 Entrada
🟠 Saída para almoço
🔵 Retorno do almoço
🔴 Saída
```

O sistema deverá impedir marcações duplicadas do mesmo tipo no mesmo dia, salvo se houver uma funcionalidade específica de correção.

---

# 10. Jornada semanal

O usuário deverá conseguir cadastrar sua jornada semanal.

Exemplo:

```text
Segunda-feira
Entrada:       08:00
Saída almoço:  12:00
Retorno:       13:00
Saída:         18:00

Terça-feira
Entrada:       08:00
Saída almoço:  12:00
Retorno:       13:00
Saída:         18:00

Quarta-feira
Entrada:       08:00
Saída almoço:  12:00
Retorno:       13:00
Saída:         18:00

Quinta-feira
Entrada:       08:00
Saída almoço:  12:00
Retorno:       13:00
Saída:         18:00

Sexta-feira
Entrada:       08:00
Saída almoço:  12:00
Retorno:       13:00
Saída:         17:00
```

Permitir configurar também:

```text
Sábado
Domingo
```

como dias de descanso.

---

# 11. Jornada esperada

O sistema deverá calcular automaticamente a jornada esperada.

Exemplo:

```text
Entrada       08:00
Almoço        12:00
Retorno       13:00
Saída         18:00
```

Resultado:

```text
Período manhã:
08:00 → 12:00 = 4h

Período tarde:
13:00 → 18:00 = 5h

Total:
9h trabalhadas
```

---

# 12. Cálculo das horas trabalhadas

Para uma jornada completa:

```text
horas_manha =
saida_almoco - entrada

horas_tarde =
saida - retorno_almoco

horas_trabalhadas =
horas_manha + horas_tarde
```

Exemplo:

```text
Entrada: 08:02
Almoço: 12:01
Retorno: 13:03
Saída: 18:07
```

Resultado:

```text
03:59
+
05:04
=
09:03
```

---

# 13. Banco de horas

O sistema deverá manter um banco de horas.

Para cada dia:

```text
Horas previstas: 08:00
Horas trabalhadas: 09:03

Saldo: +01:03
```

Se trabalhar menos:

```text
Horas previstas: 08:00
Horas trabalhadas: 07:30

Saldo: -00:30
```

O saldo poderá ser:

```text
POSITIVO
NEGATIVO
ZERO
```

O sistema deverá manter o acumulado:

```text
Banco de horas

Saldo anterior: +02:15
Saldo do dia:   +01:03
-----------------------
Saldo atual:    +03:18
```

---

# 14. Regra importante para cálculo

Não utilizar valores de horas como `float`.

Evitar:

```php
8.5
```

Preferir armazenar os minutos ou segundos.

Exemplo:

```text
08:30 = 510 minutos
```

Banco:

```text
saldo_minutes = 198
```

Na apresentação converter:

```text
198 minutos
↓
03:18
```

Isso evita erros matemáticos com números decimais.

---

# 15. Dashboard

Criar um dashboard simples.

Exibir:

```text
Olá, Luiz!

Hoje
07/10/2026
```

### Jornada de hoje

```text
🟢 Entrada
08:03

🟠 Saída almoço
12:02

🔵 Retorno almoço
13:01

🔴 Saída
18:05
```

### Resumo

```text
Horas trabalhadas
08:... 

Horas previstas
08:00

Saldo de hoje
+00:...

Banco de horas
+03:18
```

---

# 16. Status da jornada

Mostrar visualmente o status.

### Jornada incompleta

```text
⚠ Jornada incompleta

Entrada registrada
Almoço registrado
Retorno registrado

Aguardando saída
```

### Jornada completa

```text
✓ Jornada completa
```

### Jornada com saldo positivo

```text
+01:15
```

### Jornada com saldo negativo

```text
-00:45
```

---

# 17. Histórico

Criar tela:

**Histórico de ponto**

Filtros:

```text
Data inicial
Data final
Mês
Ano
```

Tabela:

| Data  | Entrada | Almoço | Retorno | Saída | Trabalhadas | Saldo  |
| ----- | ------- | ------ | ------- | ----- | ----------- | ------ |
| 07/10 | 08:03   | 12:02  | 13:01   | 18:05 | 09:03       | +01:03 |
| 06/10 | 08:01   | 12:00  | 13:00   | 17:45 | 08:44       | +00:44 |

---

# 18. Relatórios

Criar relatórios:

### Relatório diário

```text
Data
Jornada prevista
Entrada
Saída almoço
Retorno
Saída
Horas trabalhadas
Saldo
```

### Relatório semanal

```text
Semana: 05/10/2026 - 11/10/2026

Horas previstas: 40:00
Horas trabalhadas: 42:15
Saldo: +02:15
```

### Relatório mensal

```text
Outubro/2026

Dias trabalhados: 22
Horas previstas: 176:00
Horas trabalhadas: 181:35
Saldo do mês: +05:35

Banco de horas acumulado:
+08:20
```

Permitir exportação inicialmente para:

```text
PDF
CSV
Excel
```

---

# 19. Armazenamento das fotografias

As imagens originais deverão ser armazenadas utilizando Laravel Storage.

Estrutura:

```text
storage/
    app/
        point-records/
            2026/
                10/
                    07/
```

Nome sugerido:

```text
2026-10-07_0803_entry.jpg
```

O sistema deverá permitir configurar o storage.

Exemplo:

```env
FILESYSTEM_DISK=local
```

Posteriormente poderá suportar:

```text
Google Drive
S3
MinIO
```

Não acoplar a aplicação diretamente ao Google Drive.

Criar uma abstração:

```php
FileStorageInterface
```

---

# 20. Otimização das fotografias

Como o sistema será utilizado principalmente pelo celular:

1. Capturar imagem
2. Reduzir resolução quando possível
3. Comprimir JPEG
4. Manter qualidade suficiente para OCR
5. Armazenar imagem otimizada

Manter opcionalmente a imagem original.

Configuração:

```env
IMAGE_MAX_WIDTH=1600
IMAGE_JPEG_QUALITY=75
```

---

# 21. OCR

O OCR deverá retornar uma estrutura padronizada.

Exemplo:

```json
{
    "date": "2026-10-07",
    "time": "08:03",
    "confidence": 0.96,
    "raw_text": "07/10/2026 08:03"
}
```

Criar DTO/Value Object específico para o resultado do OCR caso isso faça sentido arquiteturalmente.

O sistema deverá separar:

```text
Imagem
   ↓
OCR
   ↓
Texto bruto
   ↓
Parser
   ↓
Data/Hora
   ↓
Validação
   ↓
Confirmação do usuário
```

O OCR nunca deverá salvar automaticamente uma marcação sem validação, exceto se a configuração:

```env
OCR_AUTO_CONFIRM=true
```

estiver habilitada e a confiança estiver acima do limite configurado.

---

# 22. Confiança do OCR

Definir:

```env
OCR_CONFIDENCE_HIGH=0.90
OCR_CONFIDENCE_MEDIUM=0.70
```

Regras:

### Alta confiança

```text
>= 90%
```

Pode sugerir confirmação automática.

### Média confiança

```text
70% - 89%
```

Mostrar alerta:

```text
⚠ Verifique os dados encontrados pelo OCR.
```

### Baixa confiança

```text
< 70%
```

Não permitir confirmação automática.

Solicitar revisão manual.

---

# 23. Correção do OCR

Sempre permitir que o usuário altere:

```text
Data
Hora
Tipo da marcação
```

Exemplo:

```text
OCR encontrou:

07/10/2026
08:83

O horário é inválido.

[ Hora: 08:03 ]

[ Confirmar ]
```

O sistema deve validar novamente antes de salvar.

---

# 24. Banco de dados

Criar as principais tabelas:

```text
users
work_schedules
work_schedule_days
work_days
point_records
point_images
hour_bank_transactions
```

### point_records

Campos sugeridos:

```text
id
user_id
work_day_id
type
recorded_at
source
ocr_confidence
notes
created_at
updated_at
```

`source`:

```text
ocr
manual
```

---

# 25. Auditoria

É importante diferenciar:

```text
Registro feito por OCR
Registro feito manualmente
Registro alterado posteriormente
```

Manter:

```text
created_at
updated_at
```

e, se possível:

```text
confirmed_at
edited_at
```

Registrar também o valor original encontrado pelo OCR quando houver alteração.

Exemplo:

```text
OCR:
08:83

Corrigido para:
08:03
```

---

# 26. Segurança

Implementar:

* Autenticação
* Autorização
* CSRF
* Validação de entrada
* Rate limiting
* Proteção de upload
* Validação MIME
* Limite de tamanho da imagem
* Sanitização
* Storage privado
* URLs temporárias para imagens quando necessário

Nunca confiar na extensão do arquivo enviada pelo usuário.

---

# 27. UX Mobile

A tela principal deve possuir botões grandes:

```text
┌─────────────────────────┐
│      MEU PONTO          │
├─────────────────────────┤
│                         │
│  Hoje                   │
│  07/10/2026             │
│                         │
│  08:03  Entrada         │
│  12:02  Almoço          │
│  13:01  Retorno         │
│  --:--  Saída           │
│                         │
│ ┌─────────────────────┐ │
│ │ 📷 REGISTRAR PONTO  │ │
│ └─────────────────────┘ │
│                         │
│ ✏ Registrar manualmente │
│                         │
│ Banco de horas          │
│ +03:18                  │
└─────────────────────────┘
```

---

# 28. Regras de negócio

Implementar pelo menos:

### RN01

Uma jornada pertence a um usuário.

### RN02

Uma data possui no máximo uma jornada diária.

### RN03

Uma jornada possui no máximo quatro marcações padrão.

### RN04

A sequência padrão é:

```text
Entrada
→ Almoço
→ Retorno
→ Saída
```

### RN05

Não permitir retorno do almoço sem saída para almoço.

### RN06

Não permitir saída final antes da entrada.

### RN07

Validar horários cronologicamente.

Exemplo inválido:

```text
Entrada: 08:00
Almoço: 07:30
```

### RN08

Uma marcação manual deve ser identificada como `manual`.

### RN09

Uma marcação originada do OCR deve ser identificada como `ocr`.

### RN10

Toda marcação com imagem deverá possuir referência para a fotografia.

### RN11

O cálculo do banco de horas deve utilizar minutos inteiros.

### RN12

O sistema deve permitir corrigir uma marcação.

### RN13

Alterações devem manter histórico/auditoria.

---

# 29. Arquitetura

Utilizar princípios de:

* Clean Code
* SOLID
* Separation of Concerns
* Service Layer
* Repository quando realmente necessário
* DTO/Value Objects quando agregarem valor
* Interfaces para integrações externas

Evitar overengineering.

O sistema é inicialmente simples e deve permanecer simples.

Separar claramente:

```text
Domain
Application
Infrastructure
Presentation
```

quando isso fizer sentido dentro do Laravel.

---

# 30. Estrutura conceitual

```text
app/
├── Domain/
│   ├── Attendance/
│   ├── WorkSchedule/
│   ├── HourBank/
│   └── Ocr/
│
├── Application/
│   ├── Attendance/
│   ├── Ocr/
│   └── Reports/
│
├── Infrastructure/
│   ├── Ocr/
│   └── Storage/
│
└── Http/
    ├── Controllers/
    ├── Requests/
    └── Resources/
```

Não criar classes apenas por criar. A arquitetura deve ser proporcional ao tamanho do projeto.

---

# 31. Testes

Criar testes automatizados.

### Unit Tests

Testar:

```text
Cálculo das horas
Cálculo do intervalo
Cálculo do saldo
Banco de horas
Validação de sequência
Parser de OCR
Validação de data
Validação de hora
```

### Feature Tests

Testar:

```text
Registrar ponto manual
Registrar ponto com OCR
Upload de imagem
Correção de OCR
Cadastro de jornada
Consulta do histórico
Relatório mensal
```

Exemplos:

```text
08:00 → 12:00 → 13:00 → 18:00
= 09:00 trabalhadas
```

Se a jornada prevista for 08:00:

```text
saldo = +01:00
```

---

# 32. Dashboard de banco de horas

Criar visualização:

```text
Banco de horas

Saldo atual

+05:35
```

Histórico:

```text
Data        Saldo       Acumulado

01/10       +00:30      +00:30
02/10       +00:45      +01:15
03/10       -00:20      +00:55
04/10       +01:10      +02:05
```

---

# 33. Relatório visual

Adicionar gráficos simples:

```text
Horas trabalhadas por dia
```

e:

```text
Evolução do banco de horas
```

Evitar gráficos complexos na primeira versão.

---

# 34. Responsividade

O sistema deverá funcionar em:

* Smartphone
* Tablet
* Desktop

Prioridade:

```text
Mobile > Tablet > Desktop
```

A câmera deve ser facilmente acessível em dispositivos móveis.

---

# 35. PWA

Preparar a aplicação para futuramente funcionar como PWA.

Criar:

```text
manifest.json
service worker
ícones
```

A instalação como aplicativo poderá ser implementada posteriormente.

---

# 36. MVP

A primeira versão deve conter somente:

1. Login
2. Cadastro de jornada semanal
3. Dashboard
4. Registro por foto
5. OCR
6. Registro manual
7. Quatro marcações diárias
8. Validação de data/hora
9. Cálculo de horas
10. Banco de horas
11. Histórico
12. Relatório mensal
13. Armazenamento das fotografias

Não implementar funcionalidades desnecessárias inicialmente.

---

# 37. Fases de desenvolvimento

## Fase 1 — Fundação

* Criar Laravel
* Configurar banco
* Autenticação
* Usuário
* Layout mobile
* `.env.example`

## Fase 2 — Jornada

* Cadastro de jornada semanal
* Jornada diária
* Regras de negócio

## Fase 3 — Registro manual

* Criar marcação
* Validar data
* Validar horário
* Validar sequência

## Fase 4 — OCR

* Upload/câmera
* Storage
* Integração OCR
* Parser
* Confiança
* Confirmação

## Fase 5 — Cálculos

* Horas trabalhadas
* Horas previstas
* Saldo diário
* Banco de horas

## Fase 6 — Relatórios

* Diário
* Semanal
* Mensal
* CSV
* PDF

## Fase 7 — Testes

* Unitários
* Feature
* Integração OCR

## Fase 8 — Produção

* Docker
* Nginx
* PHP-FPM
* MySQL
* HTTPS
* Backup
* Logs
* Monitoramento

---

# 38. Docker

Criar ambiente Docker para desenvolvimento.

Serviços:

```text
app
nginx
mysql
```

Opcionalmente:

```text
redis
```

quando realmente necessário.

---

# 39. Variáveis de ambiente

Criar `.env.example` contendo:

```env
APP_NAME=PontoOCR
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=pontoocr
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=local

OCR_PROVIDER=fake

OCR_CONFIDENCE_HIGH=0.90
OCR_CONFIDENCE_MEDIUM=0.70
OCR_AUTO_CONFIRM=false

IMAGE_MAX_WIDTH=1600
IMAGE_JPEG_QUALITY=75
```

---

# 40. Critérios de aceite

O sistema será considerado funcional quando:

### Cenário 1 — Registro manual

O usuário consegue registrar:

```text
07/10/2026
08:03
Entrada
```

e o registro aparece no dashboard.

### Cenário 2 — Registro com OCR

Usuário fotografa o ponto.

OCR encontra:

```text
07/10/2026 08:03
```

O sistema apresenta os dados para confirmação.

### Cenário 3 — OCR inválido

OCR encontra:

```text
07/10/2026 08:83
```

O sistema rejeita o horário e solicita correção.

### Cenário 4 — Jornada completa

```text
08:00
12:00
13:00
18:00
```

O sistema calcula corretamente:

```text
09:00 trabalhadas
```

### Cenário 5 — Banco de horas

Jornada prevista:

```text
08:00
```

Jornada trabalhada:

```text
09:00
```

Resultado:

```text
+01:00
```

### Cenário 6 — Jornada incompleta

Se existir somente:

```text
08:00
12:00
13:00
```

mostrar:

```text
Jornada incompleta
Aguardando saída
```

---

# 41. Diretriz para a IA de desenvolvimento

Não gerar todo o sistema de uma única vez.

Desenvolver incrementalmente.

Para cada etapa:

1. Explicar brevemente o que será implementado.
2. Criar os arquivos necessários.
3. Implementar o código.
4. Criar os testes.
5. Executar/verificar os testes.
6. Corrigir eventuais erros.
7. Somente depois avançar para a próxima etapa.

Antes de criar uma solução, verificar se já existe implementação semelhante no projeto.

Não duplicar código.

Priorizar:

```text
Simplicidade
Legibilidade
Testabilidade
Segurança
Manutenibilidade
```

Evitar:

```text
Overengineering
Microserviços desnecessários
Abstrações excessivas
Dependências desnecessárias
```

O sistema deve ser construído como um **MVP profissional**, mas com arquitetura suficientemente organizada para posteriormente adicionar:

* Google Drive
* AWS S3
* PWA
* notificações
* múltiplos usuários
* empresas
* equipes
* aprovação de ajustes
* integração com sistemas de RH
* reconhecimento mais avançado por IA

---

# 42. Resultado esperado

Ao final, o usuário deverá conseguir fazer praticamente toda a operação pelo celular:

```text
📱 Abrir sistema
       ↓
📷 Fotografar cartão/espelho de ponto
       ↓
🤖 OCR identifica data e hora
       ↓
✓ Usuário confirma
       ↓
💾 Registro salvo
       ↓
⏱ Sistema calcula jornada
       ↓
📊 Atualiza banco de horas
       ↓
📈 Relatórios atualizados
```

O lançamento manual continua disponível como alternativa:

```text
✏️ Registrar manualmente
```

A fotografia deve ser o fluxo **preferencial**, mas nunca uma obrigação.
