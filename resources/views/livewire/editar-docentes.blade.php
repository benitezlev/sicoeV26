<div class="mx-auto mt-10 max-w-7xl">
    <div class="p-6 mt-10 mb-10 sm:ml-64">
        <div class="p-4 mt-10">
            <div class="flex items-center justify-center mb-4 rounded">
                <p class="text-2xl text-gray-400 dark:text-gray-500">
                   <span>Editar Docente</span>
                </p>
            </div>
            <div>
                <form wire:submit.prevent='editarDocente' novalidate>
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Name') }}</label>
                            <input type="text" id="name" wire:model="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="cuid" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Clave Unica de Identificación Docente</label>
                            <input type="text" id="cuid" wire:model="cuid" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                        </div>

                    </div>

                    <div class="grid gap-6 mb-3 md:grid-cols-2">
                        <div>
                            <label for="sexo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sexo</label>
                                <select id="sexo" wire:model="sexo"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        <option>Seleccione uno...</option>
                                        <option value="Hombre">Hombre</option>
                                        <option value="Mujer">Mujer</option>
                                </select>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Correo</label>
                            <input type="email" id="email" wire:model="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                    </div>

                    <div class="grid gap-6 mb-3 md:grid-cols-2">
                        <div class="mb-3">
                            <label for="curp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">CURP</label>
                            <input type="text" id="curp" wire:model="curp" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
                        </div>

                        <div class="mb-3">
                            <label for="cuip" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">CUIP</label>
                            <input type="text" id="cuip" wire:model="cuip" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
                        </div>
                        <div class="mb-3">
                            <label for="tel" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Telefono</label>
                            <input type="text" id="tel" wire:model="tel" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
                        </div>

                        <div class="mb-3">
                            <label for="cve_servidor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Clave de Servidor Público</label>
                            <input type="text" id="cve_servidor" wire:model="cve_servidor" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="adscrip" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Adscripcion</label>
                            <input type="text" id="adscrip" wire:model="adscrip" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="plantel" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Plantel de Adscripción</label>
                            <select id="plantel" wire:model="plantel"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option>Seleccione uno...</option>
                                <option value="Lerma">Lerma</option>
                                <option value="Plantel de Formación y Actualización Toluca">Plantel de Formación y Actualización Toluca</option>
                                <option value="Plantel de Formación y Actualización Tlalnepantla">Plantel de Formación y Actualización Tlalnepantla</option>
                                <option value="Plantel de Formación y Actualización Nezahualcóyotl">Plantel de Formación y Actualización Nezahualcóyotl</option>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="cargo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cargo</label>
                            <input type="text" id="cargo" wire:model="cargo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="puesto" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Puesto Nominal</label>
                            <input type="text" id="puesto" wire:model="puesto" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="ingreso" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Fecha de Ingreso</label>
                            <input type="date" id="ingreso" wire:model="ingreso" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="grado_estudio" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Último Grado de Estudios</label>
                            <input type="text" id="grado_estudio" wire:model="grado_estudio" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="acredita" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Documento que Acredita</label>
                            <input type="text" id="acredita" wire:model="acredita" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="cedula" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cédula Profecional</label>
                            <input type="text" id="cedula" wire:model="cedula" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="campo_estudio" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Campo de Estudio</label>
                            <input type="text" id="campo_estudio" wire:model="campo_estudio" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                    </div>

                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Registrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
