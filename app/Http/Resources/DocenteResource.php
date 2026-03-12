<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocenteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'cuid'           => $this->cuid,
            'sexo'           => $this->sexo,
            'curp'           => $this->curp,
            'cuip'           => $this->cuip,
            'tel'            => $this->tel,
            'email'          => $this->email,
            'cve_servidor'   => $this->cve_servidor,
            'adscrip'        => $this->adscrip,
            'plantel'        => $this->plantel,
            'cargo'          => $this->cargo,
            'puesto'         => $this->puesto,
            'ingreso'        => $this->ingreso,
            'grado_estudio'  => $this->grado_estudio,
            'acredita'       => $this->acredita,
            'cedula'         => $this->cedula,
            'campo_estudio'  => $this->campo_estudio,
            'status'         => $this->status,
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
