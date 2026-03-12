<div class="mx-auto mt-10 max-w-7xl">
    <div class="p-6 mt-10 mb-10 sm:ml-64">
        <div class="p-4 mt-10">
            <div class="flex items-center justify-center mb-4 rounded">
                <p class="text-2xl text-gray-400 dark:text-gray-500">
                   <span>Nuevo Plantel</span>
                </p>
            </div>
            <div>
                <form wire:submit.prevent='crearPlantel' novalidate>
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-800">Nombre</label>
                            <input type="text" id="name" wire:model="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-800">Dirección</label>
                            <input type="text" id="direccion" wire:model="direccion" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                        </div>

                        <div class="mb-3">
                            <label for="tel" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-800">Telefono</label>
                            <input type="text" id="tel" wire:model="tel" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
                        </div>

                         <div class="mb-3">
                            <label for="titular" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-800">Titular</label>
                            <input type="text" id="titular" wire:model="titular" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
                        </div>


                    </div>

                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Registrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
