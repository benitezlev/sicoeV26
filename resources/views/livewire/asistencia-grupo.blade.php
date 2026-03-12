<div>
   <table>
    <thead>
        <tr>
            <th>Alumno</th>
            <th>Asistencia</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alumnos as $alumno)
            <tr>
                <td>{{ $alumno->name }}</td>
                <td>
                    <select wire:model="asistencias.{{ $alumno->id }}">
                        <option value="presente">Presente</option>
                        <option value="ausente">Ausente</option>
                        <option value="justificado">Justificado</option>
                    </select>
                </td>
                <td>
                    <input type="text" wire:model="observaciones.{{ $alumno->id }}">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
