<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Académico - Grupo {{ $grupo->clave }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        .encabezado { text-align: center; margin-bottom: 20px; }
        .firma { text-align: right; margin-top: 40px; }
    </style>
</head>
<body>

    <div class="encabezado">
        <img src="{{ public_path('images/logo.png') }}" style="height: 60px;">
        <h2>Reporte Académico</h2>
        <p><strong>Grupo:</strong> {{ $grupo->clave }} | <strong>Ciclo:</strong> {{ $grupo->ciclo }}</p>
        <p><strong>Curso:</strong> {{ $grupo->curso->nombre }} | <strong>Plantel:</strong> {{ $grupo->plantel->nombre }}</p>
        <p><strong>Docente:</strong> {{ $grupo->docente->name }}</p>
    </div>

    <h3>Calificaciones</h3>
    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Unidad 1</th>
                <th>Unidad 2</th>
                <th>Final</th>
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grupo->alumnos as $alumno)
                <tr>
                    <td>{{ $alumno->name }}</td>
                    <td>{{ $alumno->calificacion('Unidad 1') }}</td>
                    <td>{{ $alumno->calificacion('Unidad 2') }}</td>
                    <td>{{ $alumno->calificacion('Final') }}</td>
                    <td>{{ $alumno->promedio() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Asistencias</h3>
    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                @foreach($fechas as $fecha)
                    <th>{{ $fecha->format('d/m') }}</th>
                @endforeach
                <th>% Asistencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grupo->alumnos as $alumno)
                <tr>
                    <td>{{ $alumno->name }}</td>
                    @foreach($fechas as $fecha)
                        <td>{{ $alumno->asistencia($fecha) }}</td>
                    @endforeach
                    <td>{{ $alumno->porcentajeAsistencia($fechas) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="firma">
        @if($grupo->docente->firma_digital)
            <img src="data:image/png;base64,{{ $grupo->docente->firma_digital }}" style="height: 80px;">
            <p>Validado por: {{ $grupo->docente->name }}</p>
        @else
            <p><em>Firma digital no registrada</em></p>
        @endif
    </div>

</body>
</html>
