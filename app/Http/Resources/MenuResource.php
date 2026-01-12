<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'name' => $this->name,
            'route_name' => $this->route_name,
            'url' => $this->url,
            'icon' => $this->icon,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'permission_name' => $this->permission_name,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'children' => MenuResource::collection($this->whenLoaded('children')),
            'parent' => new MenuResource($this->whenLoaded('parent')),
        ];
    }
}
