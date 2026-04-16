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
@foreach($semanas as $semanaIndex => $semana)
    <div style="{{ $semanaIndex > 0 ? 'page-break-before: always;' : '' }}">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tr>
                <td style="text-align: left; vertical-align: middle; padding-bottom: 5px;">
                    @php
                        $config = \App\Models\ConfiguracionInstitucional::first();
                        $r1_path = ($config && $config->pleca_recurso_1 && Storage::disk('public')->exists('plecas/' . $config->pleca_recurso_1)) 
                                    ? storage_path('app/public/plecas/' . $config->pleca_recurso_1) 
                                    : public_path('img/pleca.png');
                    @endphp
                    <img src="{{ $r1_path }}" style="height: 35px; width: auto; max-width: 100%;" alt="Recurso 1">
                </td>
            </tr>
            @php
                $r2_path = ($config && $config->pleca_recurso_2 && Storage::disk('public')->exists('plecas/' . $config->pleca_recurso_2)) 
                            ? storage_path('app/public/plecas/' . $config->pleca_recurso_2) 
                            : null;
            @endphp
            @if($r2_path)
            <tr>
                <td style="text-align: center; vertical-align: middle; padding-bottom: 5px;">
                    <img src="{{ $r2_path }}" style="height: 35px; width: auto; max-width: 100%;" alt="Recurso 2">
                </td>
            </tr>
            @endif
            <tr>
                <td style="text-align: right; font-size: 10px; font-weight: bold; text-transform: uppercase;">
                    {{ $grupo->plantel->name }}
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-top: 2px;">
            <div style="font-weight: bold; font-size: 11px; text-transform: uppercase; margin-bottom: 5px;">
                LISTA DE ASISTENCIA - SEMANA {{ $semanaIndex + 1 }}
                <br>
                <span style="font-size: 9px; color: #666;">
                    PERIODO: {{ $semana['dias']->first()['fecha']->format('d/m/Y') }} AL {{ $semana['dias']->last()['fecha']->format('d/m/Y') }}
                </span>
            </div>
        </div>

        <div class="course-banner" style="font-size: 14px; padding: 6px;">
            {{ $grupo->curso->nombre }}
        </div>

        <div class="metadata-container" style="margin: 5px 0;">
            <div class="meta-row">
                <strong>DOCENTE:</strong> {{ $docente['data']['name'] ?? $docente['nombre'] ?? $docente['name'] ?? 'POR ASIGNAR' }}
                | <strong>GRUPO:</strong> {{ $grupo->nombre }}
                | <strong>TOTAL HORAS:</strong> {{ $grupo->total_horas }}
            </div>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20px;">No.</th>
                    <th rowspan="2">
                        NOMBRE COMPLETO <br>
                        <span class="sub-caption">| PATERNO | MATERNO | NOMBRE |</span>
                    </th>
                    <th rowspan="2" style="width: 110px;">CUIP</th>
                    <th rowspan="2" style="width: 80px;">PERFIL</th>
                    <th rowspan="2" style="width: 130px;">ADSCRIPCIÓN</th>
                    <th colspan="{{ count($semana['dias']) }}">ASISTENCIA SEMANAL</th>
                    <th rowspan="2">DIAG.</th>
                    <th rowspan="2">FINAL</th>
                </tr>
                <tr>
                    @foreach($semana['dias'] as $dia)
                        <th class="attendance-col">
                            {{ $dia['abreviado'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($alumnos as $index => $alumno)
                <tr>
                    <td style="font-size: 8px;">{{ $index + 1 }}</td>
                    <td class="name-cell" style="font-size: 7px;">
                        {{ strtoupper($alumno->paterno) }} {{ strtoupper($alumno->materno) }} {{ strtoupper($alumno->nombre) }}
                    </td>
                    <td style="font-size: 7px;">{{ $alumno->cuip ?: ($alumno->curp ?: 'S/ID') }}</td>
                    <td style="font-size: 7px;">{{ strtoupper($alumno->perfil ?? ($alumno->perfil_data['perfil'] ?? ($alumno->perfil_data['area_especializada'] ?? 'ALUMNO'))) }}</td>
                    <td style="font-size: 7px; text-align: left;">{{ strtoupper($alumno->adscripcion ?? ($alumno->perfil_data['adscripcion'] ?? ($alumno->perfil_data['dependencia'] ?? 'S/A'))) }}</td>
                    
                    @foreach($semana['dias'] as $dia)
                        @php 
                            $esInhabil = ($semana['es_feriado'])($dia['fecha']);
                            $presente = in_array($dia['fecha']->format('Y-m-d'), $alumno->asistencias_registradas);
                        @endphp
                        <td class="attendance-col" {!! $esInhabil ? 'style="background-color: #eee; font-size: 5px; color: #777;"' : '' !!}>
                            @if($esInhabil)
                                INHÁBIL
                            @else
                                @php
                                    $fechaKey = $dia['fecha']->format('Y-m-d');
                                    $asistencia = $alumno->mapa_asistencia->get($fechaKey);
                                    $estatus = $asistencia ? $asistencia->estatus : 'falta';
                                @endphp
                                
                                @if($estatus === 'presente')
                                    &bull;
                                @elseif($estatus === 'justificado')
                                    J
                                @else
                                    F
                                @endif
                            @endif
                        </td>
                    @endforeach

                    @php
                        $formatGrade = function($v) {
                            if ($v === '' || $v === null) return '';
                            $val = (float) $v;
                            return $val == 10 ? '10' : number_format($val, 1);
                        };
                    @endphp
                    <td class="grade-col" style="background-color: #f9f9f9; width: 30px;">{{ $formatGrade($alumno->nota_diagnostica) }}</td>
                    <td class="grade-col" style="width: 30px;">{{ $formatGrade($alumno->nota_final) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
            <tr>
                <td style="width: 50%; padding-right: 5px;">
                    <div class="sig-header-black">RECTORÍA UMS</div>
                    <div class="sig-identity" style="height: 30px;">
                        DR. GONZALO HERNÁNDEZ DURAZO<br>
                        RECTOR DE LA UMS
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="sig-header-black">CAPACITACIÓN Y PROFESIONALIZACIÓN</div>
                    <div class="sig-identity" style="height: 30px;">
                        LCDO. CHRISTIAN M. JIMÉNEZ MORALES<br>
                        DIRECTOR DE CAPACITACIÓN
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endforeach

</body>
</html>
