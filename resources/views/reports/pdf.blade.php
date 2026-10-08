<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Espelho de Ponto - {{ $monthName }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }
        .info-grid {
            margin-bottom: 15px;
            width: 100%;
        }
        .info-grid td {
            padding: 3px 0;
        }
        .metrics-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .metrics-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: center;
            background-color: #f8fafc;
        }
        .metrics-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .metrics-value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            padding: 6px 4px;
            border-bottom: 1px solid #cbd5e1;
            text-align: center;
        }
        .table td {
            padding: 5px 4px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
        }
        .table td.left {
            text-align: left;
        }
        .table td.right {
            text-align: right;
        }
        .positive { color: #16a34a; font-weight: bold; }
        .negative { color: #dc2626; font-weight: bold; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }
        .sign-line {
            border-top: 1px solid #0f172a;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1 class="title">PontoSync — Espelho de Ponto</h1>
        <div class="subtitle">Relatório Mensal Oficial de Frequência e Jornada</div>
    </div>

    <!-- Colaborador & Período -->
    <table class="info-grid">
        <tr>
            <td><strong>Colaborador:</strong> {{ $user->name }}</td>
            <td style="text-align: right;"><strong>Competência:</strong> {{ ucfirst($monthName) }}</td>
        </tr>
        <tr>
            <td><strong>E-mail:</strong> {{ $user->email }}</td>
            <td style="text-align: right;"><strong>Emissão:</strong> {{ $generatedAt }}</td>
        </tr>
    </table>

    <!-- Resumo Mensal -->
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metrics-label">Dias Trabalhados</div>
                <div class="metrics-value">{{ $workedDaysCount }} dias</div>
            </td>
            <td>
                <div class="metrics-label">Previsto Total</div>
                <div class="metrics-value">{{ $totalExpectedFormatted }}</div>
            </td>
            <td>
                <div class="metrics-label">Trabalhado Total</div>
                <div class="metrics-value">{{ $totalWorkedFormatted }}</div>
            </td>
            <td>
                <div class="metrics-label">Saldo do Mês</div>
                <div class="metrics-value {{ str_starts_with($monthBalanceFormatted, '+') ? 'positive' : 'negative' }}">
                    {{ $monthBalanceFormatted }}
                </div>
            </td>
            <td>
                <div class="metrics-label">Banco Acumulado</div>
                <div class="metrics-value {{ str_starts_with($accumulatedBalanceFormatted, '+') ? 'positive' : 'negative' }}">
                    {{ $accumulatedBalanceFormatted }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabela de Marcações -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 14%;">Data</th>
                <th style="width: 12%;">Entrada</th>
                <th style="width: 12%;">Saída Alm.</th>
                <th style="width: 12%;">Ret. Alm.</th>
                <th style="width: 12%;">Saída</th>
                <th style="width: 12%;">Previsto</th>
                <th style="width: 12%;">Trabalhado</th>
                <th style="width: 14%;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($workDays as $wd)
                @php
                    $records = $wd->pointRecords->keyBy('type');
                    $entry = $records->get('entry');
                    $lStart = $records->get('lunch_start');
                    $lEnd = $records->get('lunch_end');
                    $exit = $records->get('exit');
                @endphp
                <tr>
                    <td class="left"><strong>{{ $wd->date->format('d/m/Y') }}</strong> ({{ $wd->date->locale('pt_BR')->translatedFormat('D') }})</td>
                    <td>{{ $entry ? $entry->formatted_time : '--:--' }}</td>
                    <td>{{ $lStart ? $lStart->formatted_time : '--:--' }}</td>
                    <td>{{ $lEnd ? $lEnd->formatted_time : '--:--' }}</td>
                    <td>{{ $exit ? $exit->formatted_time : '--:--' }}</td>
                    <td>{{ $wd->formatted_expected_minutes }}</td>
                    <td><strong>{{ $wd->formatted_worked_minutes }}</strong></td>
                    <td class="right {{ ($wd->balance_minutes ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ $wd->formatted_balance }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Assinaturas -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sign-line">{{ $user->name }}<br><span style="font-weight: normal; font-size: 8px;">Colaborador</span></div>
            </td>
            <td>
                <div class="sign-line">Responsável RH / Gestor<br><span style="font-weight: normal; font-size: 8px;">Assinatura da Empresa</span></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento gerado pelo sistema PontoSync em {{ $generatedAt }}. Válido conforme legislação trabalhista vigente.
    </div>

</body>
</html>
