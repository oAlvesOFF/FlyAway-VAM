<?php

use App\Models\Pirep;
use Livewire\Volt\Component;

?>

<div class="sp-wrap">
    <div class="sp-title-area">
        <div class="sp-title">
            <i class="ph-fill ph-map-trifold"></i> Flight History
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-airplane-tilt"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;"><?php echo e($stats['total_flights'] ?? 0); ?></div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Flights</div>
            </div>
        </div>

        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-clock"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;"><?php echo e(number_format($stats['total_hours'] ?? 0, 1)); ?></div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Hours</div>
            </div>
        </div>

        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-map-pin"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;"><?php echo e($stats['unique_destinations'] ?? 0); ?></div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Destinations</div>
            </div>
        </div>

        <div class="sp-card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-badge); color: var(--text-badge); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="ph-fill ph-path"></i>
            </div>
            <div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); line-height: 1.2;"><?php echo e(count($routes)); ?></div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Routes Flown</div>
            </div>
        </div>
    </div>

    <div class="sp-card" style="margin-bottom: 24px; padding: 20px;">
        <div id="flightHistoryMap" style="width: 100%; height: 500px; border-radius: 8px; z-index: 0;"></div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($stats['top_airports'])): ?>
    <div class="sp-card" style="margin-bottom: 24px;">
        <div class="sp-card-header">
            <div class="sp-card-title"><i class="ph-fill ph-star"></i> Most Visited Airports</div>
        </div>
        <div class="sp-card-body">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats['top_airports']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $icao => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-family: monospace; font-size: 14px; font-weight: 700; color: var(--text-primary);"><?php echo e($icao); ?></span>
                    <span style="font-size: 13px; font-weight: 600; color: var(--text-secondary);"><?php echo e($count); ?> <?php echo e(Str::plural('visit', $count)); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($routes) === 0): ?>
    <div class="sp-card">
        <div class="sp-empty">
            <i class="ph-fill ph-airplane-tilt"></i>
            <div style="font-size: 15px; font-weight: 700; color: var(--text-primary);">No completed flights yet</div>
            <div style="font-size: 13px; margin-top: 4px;">Complete flights to see your history here.</div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    const routes = <?php echo json_encode($routes, 15, 512) ?>;
    if (routes.length === 0) return;

    const map = L.map('flightHistoryMap').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = [];

    routes.forEach(function (r) {
        const dep = [r.dep_lat, r.dep_lng];
        const arr = [r.arr_lat, r.arr_lng];

        bounds.push(dep);
        bounds.push(arr);

        // Departure marker
        L.circleMarker(dep, {
            radius: 5,
            fillColor: '#10b981',
            color: '#fff',
            weight: 2,
            fillOpacity: 1,
        }).addTo(map).bindPopup(`<b>${r.departure}</b><br>${r.flight_number}`);

        // Arrival marker
        L.circleMarker(arr, {
            radius: 5,
            fillColor: '#e11d48',
            color: '#fff',
            weight: 2,
            fillOpacity: 1,
        }).addTo(map).bindPopup(`<b>${r.arrival}</b><br>${r.flight_number}`);

        // Arc line (great circle approximation)
        const midLat = (dep[0] + arr[0]) / 2;
        const midLng = (dep[1] + arr[1]) / 2;
        const midOffset = 0.2 * Math.sqrt(Math.pow(dep[0] - arr[0], 2) + Math.pow(dep[1] - arr[1], 2));
        const controlLat = midLat + (dep[0] < arr[0] ? 1 : -1) * midOffset;

        const curvePoints = [];
        for (let t = 0; t <= 1; t += 0.02) {
            const lat = (1 - t) * (1 - t) * dep[0] + 2 * (1 - t) * t * controlLat + t * t * arr[0];
            const lng = (1 - t) * (1 - t) * dep[1] + 2 * (1 - t) * t * midLng + t * t * arr[1];
            curvePoints.push([lat, lng]);
        }

        L.polyline(curvePoints, {
            color: '#e11d48',
            weight: 1.5,
            opacity: 0.5,
        }).addTo(map).bindPopup(`<b>${r.flight_number}</b><br>${r.departure} → ${r.arrival}<br>${r.date}`);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
});
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/flight-history.blade.php ENDPATH**/ ?>