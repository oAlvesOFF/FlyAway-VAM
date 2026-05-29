<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Atlantic Star Airways API', description: 'REST API for the Atlantic Star Airways Virtual Airline Management System. Provides endpoints for PIREP management, aircraft/schedule data, and real-time flight tracking.')]
#[OA\Server(url: '/', description: 'Local Development Server')]
#[OA\SecurityScheme(type: 'http', scheme: 'bearer', securityScheme: 'apiAuth')]

#[OA\Schema(schema: 'Pirep', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'user_id', type: 'integer'),
    new OA\Property(property: 'flight_number', type: 'string'),
    new OA\Property(property: 'departure', type: 'string', maxLength: 4),
    new OA\Property(property: 'arrival', type: 'string', maxLength: 4),
    new OA\Property(property: 'aircraft_registration', type: 'string'),
    new OA\Property(property: 'aircraft_icao', type: 'string'),
    new OA\Property(property: 'flight_time', type: 'number', format: 'float'),
    new OA\Property(property: 'landing_rate', type: 'integer', nullable: true),
    new OA\Property(property: 'score', type: 'integer'),
    new OA\Property(property: 'route', type: 'string', nullable: true),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected']),
    new OA\Property(property: 'log', type: 'string', nullable: true),
    new OA\Property(property: 'submitted_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
])]

#[OA\Schema(schema: 'Aircraft', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'registration', type: 'string'),
    new OA\Property(property: 'icao', type: 'string'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'location', type: 'string'),
    new OA\Property(property: 'status', type: 'string'),
    new OA\Property(property: 'category', type: 'string'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
])]

#[OA\Schema(schema: 'Schedule', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'flight_number', type: 'string'),
    new OA\Property(property: 'departure', type: 'string', maxLength: 4),
    new OA\Property(property: 'arrival', type: 'string', maxLength: 4),
    new OA\Property(property: 'route', type: 'string'),
    new OA\Property(property: 'aircraft_type', type: 'string'),
    new OA\Property(property: 'flight_time', type: 'number', format: 'float'),
    new OA\Property(property: 'departure_time', type: 'string', nullable: true),
    new OA\Property(property: 'altitude', type: 'integer', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
])]

#[OA\Schema(schema: 'ActiveFlight', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'flight_number', type: 'string'),
    new OA\Property(property: 'aircraft_registration', type: 'string'),
    new OA\Property(property: 'aircraft_icao', type: 'string'),
    new OA\Property(property: 'aircraft_type', type: 'string'),
    new OA\Property(property: 'departure', type: 'string'),
    new OA\Property(property: 'arrival', type: 'string'),
    new OA\Property(property: 'current_lat', type: 'number', format: 'float'),
    new OA\Property(property: 'current_lng', type: 'number', format: 'float'),
    new OA\Property(property: 'heading', type: 'integer'),
    new OA\Property(property: 'altitude', type: 'integer'),
    new OA\Property(property: 'ground_speed', type: 'integer'),
    new OA\Property(property: 'phase', type: 'string', enum: ['preflight', 'boarding', 'departed', 'enroute', 'onapproach', 'landed']),
    new OA\Property(property: 'status', type: 'string', enum: ['active', 'completed']),
    new OA\Property(property: 'started_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'position_updated_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
])]
class SwaggerSchemas
{
}
