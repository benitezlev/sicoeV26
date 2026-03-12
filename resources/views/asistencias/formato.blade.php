<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Asistencia - {{ $grupo->curso->nombre }}</title>
    <style>
        @font-face {
            font-family: 'Gotham';
            src: url('/fonts/Gotham-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Gotham';
            src: url('/fonts/Gotham-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        body { font-family: 'Gotham', sans-serif; }
        /* ...resto de estilos... */
    </style>
</head>
<body>
    <div class="header">
        <img src="/images/logo_institucional.png" alt="Logo Institucional">
        <h1>{{ $grupo->plantel->nombre ?? $grupo->plantel->name ?? 'Plantel' }}</h1>
    </div>

    <h2>Lista de Asistencia</h2>
    <p><strong>Curso:</strong> {{ $grupo->curso->nombre }}</p>
    <p><strong>Plantel:</strong> {{ $grupo->plantel->nombre ?? $grupo->plantel->name }}</p>
    <p><strong>Fecha:</strong> __________________________</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nombre del Alumno</th>
                <th>Firma</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grupo->alumnos as $index => $alumno)
                <tr>
                    <td style="text-align:center;">{{ $index + 1 }}</td>
                    <td>{{ $alumno->nombre }} {{ $alumno->paterno }} {{ $alumno->materno }}</td>
                    <td style="height:30px;"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="firma-docente">
        <p>__________________________________________</p>
        <p><strong>Nombre y Firma del Docente</strong></p>
    </div>

    <div class="sello">
        <p>__________________________________________</p>
        <p><strong>Sello Institucional</strong></p>
    </div>

    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>
</body>
</html>
