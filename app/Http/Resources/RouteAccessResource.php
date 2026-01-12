<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteAccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'route_name' => $this->route_name,
            'route_uri' => $this->route_uri,
            'route_method' => $this->route_method,
            'permission_name' => $this->permission_name,
            'permission_id' => $this->permission_id,
            'is_active' => (bool) $this->is_active,
            'is_public' => (bool) $this->is_public,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'permission' => new PermissionResource($this->whenLoaded('permission')),
        ];
    }
}
