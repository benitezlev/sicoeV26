<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Responsive;

final class UsuarioTable extends PowerGridComponent
{
    public string $tableName = 'usuario-table-th0dre-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::responsive(),
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with('roles', 'expediente.documentos');


    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('paterno')
            ->add('materno')
            ->add('nombre')
            ->add('email')
            ->add('username')
            ->add('name')
            ->add('email')
            ->add('curp')
            ->add('cuip')
            ->add('cup')
            ->add('dependencia')
            ->add('adscripcion')
            ->add('perfil')
            ->add('sexo')
            ->add('fotografia')
            ->add('tipo')
            ->add('roles', fn($user) => $user->roles->pluck('name')->implode(', '))
            ->add('expediente_estatus', fn($user) => $user->expediente?->estatus ?? 'Sin expediente')
            ->add('documentos_count', fn($user) => $user->expediente?->documentos->count() ?? 0)

            ->add('created_at_formatted', function($user){
                return Carbon::parse($user->created_at)->isoFormat('DD MMMM YYYY');
            });
    }

    public function columns(): array
    {
        return [

            Column::make('Nombre', 'nombre')
                ->sortable()
                ->searchable(),

            Column::make('Apellido Paterno', 'paterno')
                ->sortable()
                ->searchable(),
            Column::make('Apellido Materno', 'materno')
                ->sortable()
                ->searchable(),

            Column::make('Curp', 'curp')
                ->sortable()
                ->searchable(),

            Column::make('Correo', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Dependencia', 'dependencia')
                ->sortable()
                ->searchable(),

            Column::make('Adscripcion', 'adscripcion')
                ->sortable()
                ->searchable(),

            Column::make('Perfil', 'perfil')
                ->sortable()
                ->searchable(),


            Column::make('Rol', 'roles'),

            Column::make('Registro', 'created_at_formatted', 'created_at')
                ->sortable(),

                Column::make('Expediente', 'expediente_estatus')
            ->sortable()
            ->searchable(),

            Column::make('Documentos', 'documentos_count')
            ->sortable(),

            //Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }
/*
    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(User $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
        ];
    }
*/
    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
