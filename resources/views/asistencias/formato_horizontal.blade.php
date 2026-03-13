<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista Mensual de Asistencia - {{ $grupo->curso->nombre }}</title>
    <style>
        body {
            font-family: 'Gotham', sans-serif;
            font-size: 10px;
            margin: 20px 30px;
            color: #333;
        }

        .encabezado {
            text-align: center;
            margin-bottom: 10px;
        }

        .encabezado img {
            display: block;
            margin: 0 auto;
            max-width: 600px;
            height: auto;
        }

        .encabezado h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }

        .datos {
            margin-top: 10px;
            font-size: 10px;
            padding: 6px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8.5px; /* más compacto */
        }

        th, td {
            border: 1px solid #444;
            padding: 2px 3px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .firma-docente, .sello {
            margin-top: 30px;
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: right;
            color: #666;
        }
        .signature-table {
            width: 100%;
            margin-top: 50px;
            border: none !important;
        }
        .signature-table td {
            border: none !important;
            padding: 10px;
            vertical-align: bottom;
            text-align: center;
        }
        .signature-box {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto;
            padding-top: 5px;
        }
        .seal-box {
            border: 1px solid #999;
            width: 110px;
            height: 110px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="encabezado">
        {{-- Pleca institucional centrada --}}
        <img src="{{ public_path('img/pleca.png') }}" alt="Pleca Institucional">

        {{-- Nombre del plantel arriba a la derecha --}}
        <div style="text-align:right; font-size:12px; font-weight:bold; margin-top:5px;">
            {{ strtoupper($grupo->plantel->name ?? $grupo->plantel->nombre ?? '---') }}
        </div>

        {{-- Nombre del curso con fondo institucional --}}
        <div style="background-color:#9f1239; color:#fff; text-align:center;
                    font-size:16px; font-weight:bold; padding:6px; margin-top:8px;">
            {{ strtoupper($grupo->curso->nombre) }}
        </div>

        <p style="text-align:center; margin:4px 0; font-size:11px;">
            <strong>Lista Mensual de Asistencia</strong>
        </p>
    </div>

    <div class="datos">
        <p style="margin:4px 0; text-align:center; font-weight:bold; font-size:11px;">
            {{ strtoupper($mes) }}
        </p>
        <p style="margin:4px 0; text-align:center;">
            <strong>Fecha de Inicio:</strong> {{ $grupo->fecha_inicio?->format('d/m/Y') ?? '---' }} |
            <strong>Fecha de Término:</strong> {{ $grupo->fecha_fin?->format('d/m/Y') ?? '---' }} |
            <strong>Hora de Inicio:</strong> {{ $grupo->hora_inicio ?? '09:00' }} |
            <strong>Hora de Término:</strong> {{ $grupo->hora_fin ?? '18:00' }} |
            <strong>Total de Horas:</strong> {{ $grupo->total_horas ?? '---' }} |
            <strong>Grupo:</strong> {{ $grupo->nombre ?? '---' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nombre Completo</th>
                <th>CURP</th>
                <th>Adscripción</th>
                <th width="150px">FIRMA DEL ALUMNO</th>
                @foreach($diasDelMes as $dia)
                    <th>
                        {{ $dia['abreviado'] }} {{ $dia['fecha']->format('d') }}<br>
                        <span style="font-size:8px">{{ $dia['hora_inicio'] }}-{{ $dia['hora_fin'] }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $index => $alumno)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ strtoupper($alumno->paterno) }} {{ strtoupper($alumno->materno) }} {{ strtoupper($alumno->nombre) }}</td>
                    <td>{{ $alumno->curp }}</td>
                    <td style="text-align:left; font-size:7px;">{{ $alumno->adscripcion }}</td>
                    <td style="height: 35px;"></td>
                    @foreach($diasDelMes as $dia)
                        <td></td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-box">
                    <p style="margin: 0; font-size: 8px;"><strong>NOMBRE Y FIRMA</strong></p>
                    <p style="margin: 0; font-size: 8px;">DOCENTE INSTRUCTOR</p>
                </div>
            </td>
            <td>
                <div class="seal-box">
                    <p style="margin: 0; color: #999; font-size: 7px; padding-top: 45px;">SELLO INSTITUCIONAL</p>
                </div>
            </td>
            <td>
                <div class="signature-box">
                    <p style="margin: 0; font-size: 8px;"><strong>NOMBRE Y FIRMA</strong></p>
                    <p style="margin: 0; font-size: 8px;">COORDINADOR DE PLANTEL</p>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>
</body>
</html>
