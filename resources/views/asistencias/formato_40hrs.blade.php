<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Asistencia 40 Horas - {{ $grupo->nombre }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #1a1a1a;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            border: none;
            padding: 2px;
        }
        .pleca {
            width: 100%;
            height: auto;
            max-height: 50px;
        }
        .course-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 8px;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9px;
            text-transform: uppercase;
        }
        .info-grid td {
            padding: 3px 0;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
        }
        .main-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 7px;
        }
        .name-cell {
            text-align: left !important;
            padding-left: 5px !important;
            white-space: nowrap;
        }
        .attendance-col {
            width: 20px;
        }
        .grade-col {
            width: 45px;
            font-weight: bold;
        }
        .footer-signatures {
            width: 100%;
            margin-top: 30px;
            text-align: center;
        }
        .signature-box {
            width: 30%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin: 40px 10px 5px 10px;
        }
        .signature-text {
            font-size: 7px;
            font-weight: bold;
        }
        .seal-box {
            width: 80px;
            height: 80px;
            border: 1px solid #ccc;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 6px;
        }
        .diagonal-text {
            transform: rotate(-45deg);
        }
    </style>
</head>
<body>
    <img src="{{ public_path('img/pleca.png') }}" class="pleca" alt="Institucional">

    <div class="course-banner">
        {{ $grupo->curso->nombre }}
    </div>

    <div style="text-align: center; font-weight: bold; font-size: 11px; margin-bottom: 15px;">
        LISTA DE ASISTENCIA
    </div>

    <table class="info-grid">
        <tr>
            <td colspan="2"><strong>DOCENTE:</strong> {{ $docente['nombre'] ?? $docente['name'] ?? 'POR ASIGNAR' }}</td>
            <td style="text-align: right;"><strong>TOTAL DE HORAS:</strong> {{ $grupo->total_horas }}</td>
        </tr>
        <tr>
            <td><strong>FECHA DE INICIO:</strong> {{ $grupo->fecha_inicio?->format('d/m/Y') }}</td>
            <td><strong>FECHA DE TÉRMINO:</strong> {{ $grupo->fecha_fin?->format('d/m/Y') }}</td>
            <td style="text-align: right;"><strong>GRUPO:</strong> {{ $grupo->nombre }}</td>
        </tr>
        <tr>
            <td><strong>HORA DE INICIO:</strong> {{ $grupo->hora_inicio }}</td>
            <td><strong>HORA DE TÉRMINO:</strong> {{ $grupo->hora_fin }}</td>
            <td></td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">NOMBRE COMPLETO<br><span style="font-weight:normal; font-size:6px;">(Apellido Paterno | Apellido Materno | Nombre(s))</span></th>
                <th rowspan="2">CUIP</th>
                <th rowspan="2">PERFIL</th>
                <th rowspan="2">ADSCRIPCIÓN</th>
                <th colspan="5">ASISTENCIA</th>
                <th rowspan="2">CALIFICACIÓN<br>DIAGNÓSTICA</th>
                <th rowspan="2">CALIFICACIÓN FINAL</th>
            </tr>
            <tr>
                <th class="attendance-col">L</th>
                <th class="attendance-col">M</th>
                <th class="attendance-col">M</th>
                <th class="attendance-col">J</th>
                <th class="attendance-col">V</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $index => $alumno)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="name-cell">
                    {{ strtoupper($alumno->paterno) }} {{ strtoupper($alumno->materno) }} {{ strtoupper($alumno->nombre) }}
                </td>
                <td style="font-size: 7px;">{{ $alumno->cuip }}</td>
                <td style="font-size: 7px;">{{ $alumno->perfil }}</td>
                <td style="font-size: 6px; text-align: left;">{{ $alumno->adscripcion }}</td>
                <td class="attendance-col"></td>
                <td class="attendance-col"></td>
                <td class="attendance-col"></td>
                <td class="attendance-col"></td>
                <td class="attendance-col"></td>
                <td class="grade-col">{{ $alumno->nota_diagnostica }}</td>
                <td class="grade-col">{{ $alumno->nota_final }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-text">
                DR. GONZALO HERNÁNDEZ DURAZO<br>
                RECTOR DE LA UNIVERSIDAD MEXIQUENSE DE SEGURIDAD
            </div>
        </div>

        <div class="signature-box">
            <div class="seal-box">
                <span class="diagonal-text">SELLO INSTITUCIONAL</span>
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-text">
                LCDO. CHRISTIAN M. JIMÉNEZ MORALES<br>
                DIRECTOR DE CAPACITACIÓN, PROFESIONALIZACIÓN Y ESPECIALIZACIÓN
            </div>
        </div>
    </div>

    <div style="margin-top: 20px; text-align: center; border-top: 1px solid #000; padding-top: 10px; font-weight: bold; width: 50%; margin-left: 25%;">
        {{ $docente['nombre'] ?? $docente['name'] ?? '---' }}<br>
        DOCENTE INSTRUCTOR
    </div>
</body>
</html>
