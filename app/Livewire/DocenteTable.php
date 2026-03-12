<?php

namespace App\Livewire;

use App\Models\Docente;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class DocenteTable extends PowerGridComponent
{
    public string $tableName = 'docente-table-mjeooz-table';

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
        return Docente::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('cuid')
            ->add('sexo')
            ->add('curp')
            ->add('cuip')
            ->add('tel')
            ->add('email')
            ->add('cve_servidor')
            ->add('adscrip')
            ->add('plantel')
            ->add('cargo')
            ->add('puesto')
            ->add('grado_estudio')
            ->add('acredita')
            ->add('cedula')
            ->add('campo_estudio')
            ->add('status')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Cuid', 'cuid')
                ->sortable()
                ->searchable(),

            Column::make('Cargo', 'cargo')
                ->sortable()
                ->searchable(),



                Column::make('Plantel', 'plantel')
                ->sortable()
                ->searchable(),


            Column::make('Status', 'status')
                ->sortable()
                ->searchable()
                ->toggleable(hasPermission:true),



            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('ingreso'),
        ];
    }
/*
    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }
*/
    public function actions(Docente $docente): array
    {
        return [
            Button::add('edit')
                ->slot('<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/>
                        <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->route('docentes.show', ['docente' => $docente->id]),

                Button::add('edit')
                ->slot('<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                        </svg>')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->route('docentes.edit', ['docente' => $docente->id]),



        ];
    }

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

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {

        Docente::query()->find($id)->update([
            $field => $value
        ]);
    }
}
