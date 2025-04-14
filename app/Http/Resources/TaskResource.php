<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "created_date" => $this->created_at->format('Y-m-d H:i:s'),
            "expiration_date" => $this->expiration_date,
            "status" => $this->status->name, // Evita error si no hay status
            "user" => $this->user->name, // Evita error si no hay usuario
        ];
    }
}
