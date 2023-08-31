<?php

namespace App\Http\Resources;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerResource extends BaseResource
{
    /**
     * @var Banner
     */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'image' => asset('storage/' . $this->resource->image[self::$locale]),
            'type' => $this->resource->type,
            'type_value' => $this->resource->type_value,
            'start_date' => $this->resource->start_date,
            'end_date' => $this->resource->end_date,
        ];
    }
}
