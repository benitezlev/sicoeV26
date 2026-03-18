<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Asistencia - {{ $grupo->nombre }}</title>
    <style>
        @page {
            size: letter landscape;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
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
            font-size: 16px;
            padding: 10px 5px;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .metadata-container {
            width: 100%;
            margin: 10px 0;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .meta-row {
            margin-bottom: 2px;
        }
        .meta-row span {
            display: inline-block;
            margin: 0 5px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
            vertical-align: middle;
        }
        .main-table th {
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
        }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .sub-caption {
            font-size: 5px;
            font-weight: normal;
        }
        .name-cell {
            text-align: left !important;
            padding-left: 4px !important;
            font-size: 8px;
            font-weight: bold;
        }
        .attendance-col {
            width: 18px;
            font-size: 8px;
        }
        .grade-col {
            width: 50px;
            font-weight: bold;
            font-size: 9px;
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
    </style>
</head>
<body>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <td style="width: 80%; text-align: left; vertical-align: top;">
                <img src="{{ public_path('img/pleca.png') }}" style="height: 140px; width: auto; max-width: 100%;" alt="Institucional">
            </td>
            <td style="width: 20%; text-align: right; vertical-align: top; font-size: 10px; font-weight: bold; text-transform: uppercase; padding-top: 45px;">
                {{ $grupo->plantel->name }}
            </td>
        </tr>
    </table>

    <div style="text-align: center; margin-top: 2px; padding-top: 2px; margin-bottom: 5px;">
        <div style="font-weight: bold; font-size: 12px; text-transform: uppercase;  margin-bottom: 10px;">
            LISTA DE ASISTENCIA
        </div>
    </div>

    <div class="course-banner">
        {{ $grupo->curso->nombre }}
    </div>

    <div class="metadata-container">
        <div class="meta-row">
            <strong>DOCENTE:</strong> {{ $docente['data']['name'] ?? $docente['nombre'] ?? $docente['name'] ?? 'POR ASIGNAR' }}
        </div>
        <div class="meta-row">
            | <span><strong>FECHA DE INICIO:</strong> {{ $grupo->fecha_inicio?->format('d/m/Y') }}</span>
            | <span><strong>FECHA DE TÉRMINO:</strong> {{ $grupo->fecha_fin?->format('d/m/Y') }}</span>
            | <span><strong>HORA DE INICIO:</strong> {{ \Carbon\Carbon::parse($grupo->hora_inicio)->format('H:i') }}</span>
            | <span><strong>HORA DE TÉRMINO:</strong> {{ \Carbon\Carbon::parse($grupo->hora_fin)->format('H:i') }}</span> |
        </div>
        <div class="meta-row">
            | <span><strong>TOTAL DE HORAS:</strong> {{ $grupo->total_horas }}</span>
            | <span><strong>GRUPO:</strong> {{ $grupo->nombre }}</span> |
        </div>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No.</th>
                <th rowspan="2" style="width: 250px;">
                    NOMBRE COMPLETO <br>
                    <span class="sub-caption">| Apellido Paterno | Apellido Materno | Nombre(s) |</span>
                </th>
                <th rowspan="2" style="width: 120px;">CUIP</th>
                <th rowspan="2" style="width: 100px;">PERFIL</th>
                <th rowspan="2" style="width: 150px;">ADSCRIPCIÓN</th>
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
                <td style="font-size: 9px; font-weight: bold;">{{ $index + 1 }}</td>
                <td class="name-cell">
                    {{ strtoupper($alumno->paterno) }} {{ strtoupper($alumno->materno) }} {{ strtoupper($alumno->nombre) }}
                </td>
                <td style="font-size: 8px; font-family: monospace;">{{ $alumno->cuip }}</td>
                <td style="font-size: 7px;">{{ strtoupper($alumno->perfil ?? ($alumno->perfil_data['perfil'] ?? '')) }}</td>
                <td style="font-size: 6px; text-align: left; line-height: 1;">{{ strtoupper($alumno->adscripcion ?? ($alumno->perfil_data['adscripcion'] ?? '')) }}</td>
                <td class="attendance-col"> @if($alumno->asistencia_l) &bull; @endif </td>
                <td class="attendance-col"> @if($alumno->asistencia_m) &bull; @endif </td>
                <td class="attendance-col"> @if($alumno->asistencia_mi) &bull; @endif </td>
                <td class="attendance-col"> @if($alumno->asistencia_j) &bull; @endif </td>
                <td class="attendance-col"> @if($alumno->asistencia_v) &bull; @endif </td>
                <td class="grade-col" style="background-color: #f9f9f9;">{{ $alumno->nota_diagnostica == 10 ? '10' : ($alumno->nota_diagnostica ? number_format((float)$alumno->nota_diagnostica, 1) : '') }}</td>
                <td class="grade-col">{{ $alumno->nota_final == 10 ? '10' : ($alumno->nota_final ? number_format((float)$alumno->nota_final, 1) : '') }}</td>
            </tr>
            @endforeach
            
            {{-- Filas vacías optimizadas para evitar saltos de página --}}
            @php $maxRows = 12; @endphp
            @for($i = count($alumnos); $i < $maxRows; $i++)
            <tr style="height: 12px;">
                <td>{{ $i + 1 }}</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor
        </tbody>
    </table>

    <table class="signature-table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr>
                <th class="sig-header-black" style="width: 70%;">NOMBRE Y CARGO</th>
                <th class="sig-header-black" style="width: 30%;">FIRMA</th>
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

</body>
</html>
