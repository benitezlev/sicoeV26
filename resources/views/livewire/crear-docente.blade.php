<div class="mx-auto mt-10 max-w-7xl">
    <div class="p-6 mt-10 mb-10 sm:ml-64">
        <div class="p-4 mt-10">
            <div class="flex items-center justify-center mb-4 rounded">
                <p class="text-2xl text-gray-400 dark:text-gray-500">
                   <span>Nuevo Docente</span>
                </p>
            </div>
            <div>
                    <form wire:submit.prevent="registrar" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label>Nombre</label>
                                <input type="text" wire:model.defer="nombre" class="input">
                                @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label>CURP</label>
                                <input type="text" wire:model.defer="curp" class="input">
                                @error('curp') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label>Email</label>
                                <input type="email" wire:model.defer="email" class="input">
                                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label>CUIP</label>
                                <input type="text" wire:model.defer="cuip" class="input">
                            </div>

                            <div>
                                <label>Adscripción</label>
                                <input type="text" wire:model.defer="adscripcion" class="input">
                            </div>
                            <div>
                                <label for="">Plantel</label>
                                <select name="" id="">
                                    <option value="">Seleccione un Plantel</option>
                                    @foreach ($planteles as $plantel )
                                    <option value="{{ $plantel->id }}">{{ $plantel->name }}</option>

                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label>Dependencia</label>
                                <input type="text" wire:model.defer="dependencia" class="input">
                            </div>

                            <div>
                                <label>Tipo</label>
                                <input type="text" wire:model.defer="tipo" class="input">
                            </div>

                            <div>
                                <label>Perfil</label>
                                <input type="text" wire:model.defer="perfil" class="input">
                            </div>

                            <div class="col-span-2 mt-4">
                                <h3 class="text-md font-bold text-gray-600 dark:text-gray-300 mb-2">📚 Cardex Académico</h3>
                            </div>

                            <div>
                                <label>Grado académico</label>
                                <input type="text" wire:model.defer="grado_academico" class="input">
                            </div>

                            <div>
                                <label>Especialidad</label>
                                <input type="text" wire:model.defer="especialidad" class="input">
                            </div>

                            <div>
                                <label>Experiencia docente</label>
                                <input type="text" wire:model.defer="experiencia_docente" class="input">
                            </div>

                            <div>
                                <label>Certificaciones</label>
                                <input type="text" wire:model.defer="certificaciones" class="input">
                            </div>

                            <div>
                                <label>Formación complementaria</label>
                                <input type="text" wire:model.defer="formacion_complementaria" class="input">
                            </div>

                            <div class="col-span-2">
                                <label>Observaciones</label>
                                <textarea wire:model.defer="observaciones" class="input"></textarea>
                            </div>

                            <div class="col-span-2 mt-4">
                                <button type="submit" class="btn-primary">Registrar Docente</button>
                            </div>
                    </form>

            </div>
        </div>
    </div>
</div>
