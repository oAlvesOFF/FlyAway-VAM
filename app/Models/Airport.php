<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Airport",
    description: "Airport model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "icao", type: "string", example: "KJFK"),
        new OA\Property(property: "name", type: "string", example: "John F. Kennedy International Airport"),
        new OA\Property(property: "city", type: "string", example: "New York"),
        new OA\Property(property: "country", type: "string", example: "USA"),
        new OA\Property(property: "lat", type: "number", format: "float", example: 40.6413),
        new OA\Property(property: "lng", type: "number", format: "float", example: -73.7781),
        new OA\Property(property: "elevation", type: "number", format: "float", example: 13.0),
    ]
)]
class Airport extends Model
{
    protected $fillable = [
        'icao',
        'name',
        'city',
        'country',
        'lat',
        'lng',
        'elevation',
    ];
}
