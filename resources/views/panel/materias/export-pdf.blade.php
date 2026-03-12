<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tira Académica - {{ $curso->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #1e40af;
            font-size: 20px;
        }
        .header p {
            margin: 3px 0 0;
            color: #666;
            font-size: 10px;
            font-style: italic;
        }
        .course-info {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .course-info p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            text-align: center;
            padding: 10px 5px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 9px;
        }
        td {
            padding: 8px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            text-align: center;
        }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h1>SICOE - Sistema de Control Escolar</h1>
        <p>Documento Oficial de Tira Académica por Curso</p>
    </div>

    <div class="course-info">
        <p><strong>Curso:</strong> <span style="font-size: 14px; color: #1e40af;">{{ $curso->nombre }}</span></p>
        <p><strong>Identificador:</strong> {{ $curso->identificador }}</p>
        <p><strong>Tipo:</strong> {{ $curso->tipo }}</p>
        <p><strong>Fecha de Emisión:</strong> {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%">Sem.</th>
                <th width="8%">Orden</th>
                <th width="12%">Clave</th>
                <th width="40%">Asignatura / Materia</th>
                <th width="10%">Horas</th>
                <th width="10%">Créditos</th>
                <th width="12%">Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($curso->materias->sortBy(['pivot_semestre', 'pivot_orden']) as $materia)
                <tr>
                    <td class="font-bold">{{ $materia->pivot->semestre ?: '-' }}</td>
                    <td>{{ $materia->pivot->orden ?: '-' }}</td>
                    <td style="font-family: monospace;">{{ $materia->clave }}</td>
                    <td class="text-left font-bold">{{ $materia->nombre }}</td>
                    <td>{{ $materia->num_horas }} hrs</td>
                    <td>{{ $materia->pivot->creditos ?: '0' }}</td>
                    <td style="font-size: 8px; text-transform: uppercase;">{{ $materia->tipo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este documento es un reporte informativo del sistema SICOE | Página <span class="page-number"></span>
    </div>
</body>
</html>
