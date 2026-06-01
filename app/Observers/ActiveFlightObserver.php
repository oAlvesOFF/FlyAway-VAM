<?php

namespace App\Observers;

use App\Models\ActiveFlight;

class ActiveFlightObserver
{
    // O webhook de fase do voo é disparado diretamente no FlightTrackingController,
    // pois o isDirty() não funciona corretamente após o update() do Eloquent.
    // Este Observer é mantido para uso futuro.
}
