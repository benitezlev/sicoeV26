<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tira Académica - {{ $curso->nombre }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        h2 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 10px;
            color: #222;
        }
        p {
            margin: 5px 0;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #222;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        tr:hover {
            background-color: #e6f7ff;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <h2>Tira Académica</h2>
    <p><strong>Curso:</strong> {{ $curso->nombre }}</p>

    <table>
        <thead>
            <tr>
                <th>Orden</th>
                <th>Materia</th>
                <th>Horas</th>
                <th>Semestre</th>
                <th>Créditos</th>
                <th>Obligatoria</th>
            </tr>
        </thead>
        <tbody>
            @foreach($curso->materias as $materia)
                <tr>
                    <td>{{ $materia->pivot->orden }}</td>
                    <td style="text-align:left;">{{ $materia->nombre }}</td>
                    <td>{{ $materia->num_horas }}</td>
                    <td>{{ $materia->pivot->semestre }}</td>
                    <td>{{ $materia->pivot->creditos }}</td>
                    <td>{{ $materia->pivot->obligatoria ? 'Sí' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>
</body>
</html>
