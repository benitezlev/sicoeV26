<div class="mx-auto mt-10 max-w-7xl">
    <div class="p-6 mt-10 mb-10 sm:ml-64">
        <div class="p-4 mt-10">
            <div class="flex items-center justify-center mb-4 rounded">
                <p class="text-2xl text-gray-400 dark:text-gray-500">
                   <span>Nuevo Rol</span>
                </p>
            </div>
            <div>
               <form wire:submit.prevent="crearRol" class="space-y-4">
                    <input type="text" wire:model="name" placeholder="Nombre del rol" class="input">

                    <button type="submit" class="btn-primary">Crear Rol</button>
                </form>


            </div>
        </div>
    </div>
</div>
