<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var User $this */
        return [
            'status ' => true,
            'user' => [
                "id" => $this->id,
                "socket_id" => $this->socket_id,
                "username" => $this->username,
                "email" => $this->email,
                "folder" => $this->folder,
                "active" => $this->active,
                "last_login" => $this->last_login,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ]
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(200);
    }
}
