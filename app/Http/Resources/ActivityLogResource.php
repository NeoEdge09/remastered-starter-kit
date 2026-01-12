<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
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
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'properties' => $this->properties,
            'batch_uuid' => $this->batch_uuid,
            'event' => $this->event,
            'event_label' => $this->event_label,
            'event_color' => $this->event_color,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'causer' => new UserResource($this->whenLoaded('causer')),
            'subject_description' => $this->subject_type ? class_basename($this->subject_type) . ' #' . $this->subject_id : null,
        ];
    }
}
