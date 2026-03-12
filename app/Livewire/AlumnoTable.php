<?php

namespace App\Livewire;

use App\Models\Alumno;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class AlumnoTable extends PowerGridComponent
{
    public string $tableName = 'alumno-table-uznuyq-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Alumno::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('cuip')
            ->add('curp')
            ->add('cup')
            ->add('nombre')
            ->add('dependencia')
            ->add('adscripcion')
            ->add('perfil')
            ->add('sexo')
            ->add('fotografia')
            ->add('created_at')
            ->add('created_at_formatted', fn (Alumno $model) => Carbon::parse($model->created_at)->isoFormat('DD/MMMM/YYYY'));
    }

    public function columns(): array
    {
        return [
            Column::make('Nombre', 'nombre')
                ->sortable()
                ->searchable(),

            Column::make('Cuip', 'cuip')
                ->sortable()
                ->searchable(),

            Column::make('Curp', 'curp')
                ->sortable()
                ->searchable(),

            Column::make('Cup', 'cup')
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

            Column::make('Sexo', 'sexo')
                ->sortable()
                ->searchable(),



            Column::make('Rregistro', 'created_at_formatted', 'created_at')
                ->sortable(),


           // Column::action('Action')
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

    public function actions(Alumno $row): array
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
