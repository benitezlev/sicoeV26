<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Cursos - SICOE</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #1e40af;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-style: italic;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 10px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .text-mono {
            font-family: 'Courier', monospace;
            font-size: 11px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            background-color: #e2e8f0;
            color: #475569;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h1>SICOE - Sistema de Control Escolar</h1>
        <p>Catálogo Institucional de Oferta Académica</p>
    </div>

    <div style="margin-bottom: 15px;">
        <h2 style="margin: 0; color: #334155;">Listado General de Cursos</h2>
        <p style="margin: 5px 0; color: #64748b; font-size: 10px;">Fecha de generación: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">ID/Clave</th>
                <th width="45%">Nombre del Curso</th>
                <th width="25%">Tipo</th>
                <th width="15%" style="text-align: center;">Horas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cursos as $curso)
                <tr>
                    <td class="text-mono">{{ $curso->identificador }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $curso->nombre }}</div>
                        <div style="font-size: 9px; color: #64748b; margin-top: 2px;">{{ $curso->descripcion ?: 'Sin descripción registrada' }}</div>
                    </td>
                    <td><span class="badge">{{ $curso->tipo }}</span></td>
                    <td style="text-align: center;">{{ $curso->num_horas }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Total de Cursos Registrados:</td>
                <td style="text-align: center;">{{ count($cursos) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Documento generado automáticamente por el sistema SICOE | Página <span class="page-number"></span>
    </div>
</body>
</html>
