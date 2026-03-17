<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista Mensual de Asistencia - {{ $grupo->nombre }}</title>
    <style>
        @page {
            size: letter landscape;
            margin: 0.5cm 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7px;
            color: #000;
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }
        .top-slogan {
            text-align: right;
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .course-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 8px 5px;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .metadata-line {
            width: 100%;
            text-align: center;
            font-size: 7.5px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
            background-color: #f8f8f8;
            padding: 4px 0;
            border-top: 0.5px solid #eee;
            border-bottom: 0.5px solid #eee;
        }
        .metadata-line span {
            display: inline-block;
            margin: 0 4px;
        }
        .mes-titulo {
            text-align: center;
            font-weight: black;
            font-size: 11px;
            margin: 3px 0;
            color: #1a1a1a;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 3px 1px;
            text-align: center;
            vertical-align: middle;
        }
        .main-table th {
            font-weight: bold;
            font-size: 6.5px;
            text-transform: uppercase;
            background-color: #f2f2f2;
        }
        .name-cell {
            text-align: left !important;
            padding-left: 4px !important;
            font-size: 7.5px;
            font-weight: bold;
            white-space: nowrap;
        }
        .attendance-col {
            width: 15px;
            font-size: 6px;
        }
        .curp-col {
            width: 90px;
            font-size: 7px;
            font-family: monospace;
        }
        .footer-container {
            width: 100%;
            margin-top: 10px;
        }
        .signature-section {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .sig-block {
            display: table-cell;
            width: 35%;
            text-align: center;
            vertical-align: top;
            padding-top: 20px;
        }
        .sig-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
        .sig-title {
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .logo-block {
            display: table-cell;
            width: 30%;
            text-align: left;
            vertical-align: bottom;
        }
        .ums-logo {
            width: 80px;
            margin-bottom: 3px;
        }
        .ums-info {
            font-size: 5.5px;
            font-weight: bold;
            color: #444;
            line-height: 1.1;
        }
        .label-vertical {
            font-size: 6px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #666;
        }
        .empty-row {
            height: 15px;
        }
    </style>
</head>
<body>
    <div class="top-slogan">
        "2026. Bicentenario de la vida municipal en el Estado de México"
    </div>

    <div class="header-title">
        LISTA MENSUAL DE ASISTENCIA
    </div>

    <div class="course-banner">
        {{ $grupo->curso->nombre }}
    </div>

    <div class="mes-titulo">
        {{ strtoupper($mes) }}
    </div>

    <div class="metadata-line">
        <span>| <strong>DOCENTE:</strong> {{ $docente['nombre'] ?? $docente['name'] ?? 'POR ASIGNAR' }} |</span>
        <span>| <strong>FECHA DE INICIO:</strong> {{ $grupo->fecha_inicio?->format('d/m/Y') }} |</span>
        <span>| <strong>FECHA DE TÉRMINO:</strong> {{ $grupo->fecha_fin?->format('d/m/Y') }} |</span> 
        <span>| <strong>HORA DE INICIO:</strong> {{ \Carbon\Carbon::parse($grupo->hora_inicio)->format('H:i') }} |</span>
        <span>| <strong>HORA DE TÉRMINO:</strong> {{ \Carbon\Carbon::parse($grupo->hora_fin)->format('H:i') }} |</span>
        <span>| <strong>TOTAL DE HORAS:</strong> {{ $grupo->total_horas }} |</span>
        <span>| <strong>GRUPO:</strong> {{ $grupo->nombre }} |</span>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 15px;">No.</th>
                <th style="width: 250px;">
                    NOMBRE COMPLETO <br>
                    <span class="sub-caption">| Apellido Paterno | Apellido Materno | Nombre(s) |</span>
                </th>
                <th class="curp-col">CURP</th>
                <th style="width: 100px;">ADSCRIPCIÓN</th>
                <th style="width: 100px;">FIRMA DEL ALUMNO</th>
                @foreach($diasDelMes as $dia)
                    <th class="attendance-col">
                        {{ $dia['abreviado'] }}<br>{{ $dia['fecha']->format('d') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $index => $alumno)
            <tr>
                <td style="font-weight: bold;">{{ $index + 1 }}</td>
                <td class="name-cell">
                    {{ strtoupper($alumno->paterno) }} {{ strtoupper($alumno->materno) }} {{ strtoupper($alumno->nombre) }}
                </td>
                <td class="curp-col">{{ $alumno->curp }}</td>
                <td style="font-size: 6px; text-align: left; line-height: 1;">{{ strtoupper($alumno->adscripcion) }}</td>
                <td style="min-height: 25px;"></td>
                @foreach($diasDelMes as $dia)
                    <td class="attendance-col"></td>
                @endforeach
            </tr>
            @endforeach

            @if(count($alumnos) < 15)
                @for($i = count($alumnos); $i < 15; $i++)
                    <tr class="empty-row">
                        <td>{{ $i + 1 }}</td>
                        <td></td><td></td><td></td><td></td>
                        @foreach($diasDelMes as $dia) <td></td> @endforeach
                    </tr>
                @endfor
            @endif
        </tbody>
    </table>

    <div class="footer-container">
        <div class="signature-section">
            <div class="logo-block">
                <img src="{{ public_path('img/Logo-UMS-1.png') }}" class="ums-logo" alt="UMS Logo">
                <div class="ums-info">
                   UNIVERSIDAD MEXIQUENSE DE SEGURIDAD<br>
                   DIRECCIÓN GENERAL
                </div>
            </div>
            
            <div class="sig-block">
                <div class="label-vertical">VALIDACIÓN DOCENTE</div>
                <div class="sig-line"></div>
                <div class="sig-title">
                    {{ $docente['nombre'] ?? $docente['name'] ?? 'DOCENTE INSTRUCTOR' }}<br>
                    DOCENTE INSTRUCTOR
                </div>
            </div>

            <div class="sig-block" style="padding-left: 20px;">
                <div class="label-vertical">VALIDACIÓN INSTITUCIONAL</div>
                <div class="sig-line"></div>
                <div class="sig-title">
                    LCDO. CHRISTIAN M. JIMÉNEZ MORALES<br>
                    DIRECTOR DE CAPACITACIÓN, PROFESIONALIZACIÓN Y ESPECIALIZACIÓN
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 10px; text-align: right; font-size: 6px; color: #777;">
        Este listado es un registro oficial de asistencia académica. Generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
