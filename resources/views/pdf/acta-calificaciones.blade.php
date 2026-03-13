<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Calificaciones</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { width: 150px; }
        .title { font-size: 14px; font-weight: bold; margin-top: 10px; color: #1a1a1a; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; border: 1px solid #ddd; }
        .label { font-weight: bold; background-color: #f9f9f9; width: 150px; }
        .grades-table { width: 100%; border-collapse: collapse; }
        .grades-table th { background-color: #f2f2f2; border: 1px solid #333; padding: 6px; text-align: center; }
        .grades-table td { border: 1px solid #333; padding: 6px; }
        .footer { margin-top: 50px; width: 100%; }
        .signature-box { text-align: center; width: 45%; display: inline-block; }
        .signature-line { border-top: 1px solid #000; width: 80%; margin: 40px auto 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">ACTA DE CALIFICACIONES - UNIDAD {{ $unidad }}</h1>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">GRUPO:</td>
            <td>{{ $grupo->nombre }} ({{ $grupo->periodo }})</td>
            <td class="label">PLANTEL:</td>
            <td>{{ $grupo->plantel->name }}</td>
        </tr>
        <tr>
            <td class="label">MATERIA:</td>
            <td colspan="3">{{ $materia->nombre }}</td>
        </tr>
    </table>

    <table class="grades-table">
        <thead>
            <tr>
                <th width="30">#</th>
                <th width="150">CUIP / CURP</th>
                <th>NOMBRE DEL ELEMENTO</th>
                <th width="80">CALIFICACIÓN</th>
                <th>OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $i => $alumno)
            @php
                $nota = $calificaciones->where('user_id', $alumno->id)->first();
            @endphp
            <tr>
                <td align="center">{{ $i + 1 }}</td>
                <td align="center">{{ $alumno->curp }}</td>
                <td>{{ $alumno->nombre_completo }}</td>
                <td align="center" style="font-weight: bold; font-size: 12px; {{ ($nota->calificacion ?? 0) < 6 ? 'color: red;' : '' }}">
                    {{ isset($nota) ? number_format($nota->calificacion, 1) : 'N/P' }}
                </td>
                <td>{{ $nota->observaciones ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box" style="float: left;">
            <div class="signature-line"></div>
            <strong>DOCENTE / INSTRUCTOR</strong><br>
            Nombre y Firma
        </div>
        <div class="signature-box" style="float: right;">
            <div class="signature-line"></div>
            <strong>DEPARTAMENTO DE CONTROL ESCOLAR</strong><br>
            Sello y Firma
        </div>
    </div>

    <div style="clear: both; margin-top: 100px; text-align: center; font-size: 8px; color: #999;">
        Fecha de generación: {{ date('d/m/Y H:i') }} - Sistema SICOE V2.0
    </div>
</body>
</html>
