<div>
   <table>
    <thead>
        <tr>
            <th>Alumno</th>
            <th>Unidad</th>
            <th>Calificación</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alumnos as $alumno)
            <tr>
                <td>{{ $alumno->name }}</td>
                <td>
                    <select wire:model="unidad.{{ $alumno->id }}">
                        <option value="Unidad 1">Unidad 1</option>
                        <option value="Unidad 2">Unidad 2</option>
                        <option value="Final">Final</option>
                    </select>
                </td>
                <td>
                    <input type="number" wire:model="calificaciones.{{ $alumno->id }}" min="0" max="10" step="0.1">
                </td>
                <td>
                    <input type="text" wire:model="observaciones.{{ $alumno->id }}">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
