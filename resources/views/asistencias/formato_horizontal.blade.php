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
        .sig-header-black {
            background-color: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            padding: 5px;
            border: 1px solid #000;
        }
        .sig-identity {
            padding: 15px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
            font-weight: bold;
            border: 1px solid #000;
        }
        .empty-row {
            height: 15px;
        }
    </style>
</head>
<body>
    <div style="width: 100%; margin-bottom: 10px;">
        <img src="{{ public_path('img/pleca.png') }}" style="width: 100%; height: auto;" alt="Institucional">
    </div>


    <div class="header-title">
        LISTA MENSUAL DE ASISTENCIA
    </div>

    <div class="course-banner">
        {{ $grupo->curso->nombre }}
    </div>

    <div class="mes-titulo">
        {{ $docente['nombre'] ?? $docente['name'] ?? strtoupper($mes) }}
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
                <td style="font-size: 6px; text-align: left; line-height: 1;">{{ strtoupper($alumno->nivel) }}</td>
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

    <table class="signature-table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr>
                <th class="sig-header-black" style="width: 50%;">NOMBRE Y CARGO</th>
                <th class="sig-header-black">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="sig-identity">
                    DR. GONZALO HERNÁNDEZ DURAZO<br>
                    RECTOR DE LA UNIVERSIDAD MEXIQUENSE DE SEGURIDAD
                </td>
                <td style="border: 1px solid #000;"></td>
            </tr>
            <tr>
                <td class="sig-identity">
                    LCDO. CHRISTIAN M. JIMÉNEZ MORALES<br>
                    DIRECTOR DE CAPACITACIÓN, PROFESIONALIZACIÓN Y ESPECIALIZACIÓN
                </td>
                <td style="border: 1px solid #000;"></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 10px; text-align: center; border-top: 1px dotted #000; padding-top: 5px; font-weight: bold; width: 40%; margin-left: 30%;">
        {{ $docente['nombre'] ?? $docente['name'] ?? '---' }}<br>
        <span style="font-size: 7px;">DOCENTE INSTRUCTOR</span>
    </div>
</body>
</html>
