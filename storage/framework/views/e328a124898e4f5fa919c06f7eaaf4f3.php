<?php

use App\Models\User;
use App\Models\Pirep;
use App\Models\Airport;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
/* ══ Pilot Profile — Dynamic Theme Colors ═════════════════════ */
:root {
    --bg-page: #f8f9fa;
    --bg-card: #ffffff;
    --bg-header: #f1f3f5;
    --border-card: #e9ebec;
    --text-primary: #212529;
    --text-secondary: #495057;
    --text-muted: #868e96;
    --bg-badge: rgba(81, 140, 229, 0.1);
    --border-badge: rgba(81, 140, 229, 0.25);
    --text-badge: #518ce5;
    --bg-search: #ffffff;
    --border-search: #ced4da;
    --text-search: #212529;
    --shadow-card: 0 2px 4px rgba(0, 0, 0, .04);
}

.dark {
    --bg-page: #1a1d2e;
    --bg-card: #2b2f3e;
    --bg-header: #23263a;
    --border-card: #3a3f54;
    --text-primary: #e2e8f0;
    --text-secondary: #a0a8c0;
    --text-muted: #6b7280;
    --bg-badge: rgba(81, 140, 229, 0.18);
    --border-badge: rgba(81, 140, 229, 0.35);
    --text-badge: #518ce5;
    --bg-search: #23263a;
    --border-search: #3a3f54;
    --text-search: #e2e8f0;
    --shadow-card: none;
}

body, main { background: var(--bg-page) !important; }

.dvap-wrap {
    display: grid;
    grid-template-columns: 310px 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) {
    .dvap-wrap { grid-template-columns: 1fr; }
}

/* ── Dark/Light card ────────────────────────────────────────── */
.dv-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 8px;
    box-shadow: var(--shadow-card);
    overflow: hidden;
}
.dv-card-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-card);
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .06em;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dv-card-body { padding: 16px; }

/* ── Left sidebar ───────────────────────────────────────────── */
.dv-sidebar { display: flex; flex-direction: column; gap: 16px; }

.dv-avatar-box {
    background: var(--bg-header);
    border: 1px solid var(--border-card);
    border-radius: 8px;
    padding: 20px 16px;
    text-align: center;
}
.dv-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #518ce5, #2f5abf);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 700; color: #fff;
    margin: 0 auto 12px;
    border: 3px solid var(--border-card);
}
.dv-pilot-name {
    font-size: 14px; font-weight: 700; color: var(--text-primary);
    margin: 0 0 4px;
}
.dv-pilot-rank {
    font-size: 12px; color: var(--text-muted);
    display: flex; align-items: center; justify-content: center; gap: 4px;
}

.dv-pilot-id-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(16,185,129,.15);
    border: 1px solid rgba(16,185,129,.3);
    color: #10b981;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 12px; font-weight: 700;
    margin: 12px 0;
    width: 100%;
    justify-content: center;
}

.dv-info-list { list-style: none; margin: 0; padding: 0; }
.dv-info-list li {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--border-card);
    font-size: 12px;
}
.dv-info-list li:last-child { border-bottom: 0; }
.dv-info-label { color: var(--text-muted); }
.dv-info-value { color: var(--text-primary); font-weight: 600; text-align: right; }
.dv-airport-badge {
    display: inline-flex; align-items: center;
    background: var(--bg-badge);
    border: 1px solid var(--border-badge);
    color: var(--text-badge);
    padding: 2px 8px; border-radius: 4px;
    font-size: 11px; font-weight: 700;
}

/* ── Stats widgets (2×3 grid) ───────────────────────────────── */
.dv-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.dv-stat-widget {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 8px;
    box-shadow: var(--shadow-card);
    padding: 14px 16px;
    display: flex; align-items: center; gap: 12px;
}
.dv-stat-icon {
    width: 42px; height: 42px; border-radius: 50%;
    background: var(--bg-badge);
    border: 1px solid var(--border-badge);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-badge); font-size: 18px; flex-shrink: 0;
}
.dv-stat-val { font-size: 16px; font-weight: 700; color: var(--text-primary); line-height: 1.2; }
.dv-stat-val.green { color: #10b981; }
.dv-stat-lbl { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* ── Tabs ───────────────────────────────────────────────────── */
.dv-tabs {
    display: flex; border-bottom: 1px solid var(--border-card);
    padding: 0; gap: 0; overflow-x: auto;
}
.dv-tab {
    padding: 12px 20px;
    font-size: 13px; font-weight: 600;
    color: var(--text-muted);
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    cursor: pointer;
    background: transparent;
    border-top: none; border-left: none; border-right: none;
    transition: all .18s; white-space: nowrap;
    display: flex; align-items: center; gap: 6px;
}
.dv-tab:hover:not(.active) { color: var(--text-secondary); }
.dv-tab.active { color: var(--text-badge); border-bottom-color: var(--text-badge); }

/* ── Biography ──────────────────────────────────────────────── */
.dv-bio {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 8px;
    box-shadow: var(--shadow-card);
    padding: 16px;
    font-size: 13px; line-height: 1.6; color: var(--text-secondary);
    margin-bottom: 16px;
}
.dv-bio h5 {
    font-size: 13px; font-weight: 700; color: var(--text-primary);
    margin: 0 0 10px; display: flex; align-items: center; gap: 6px;
}

/* ── PIREPs table ───────────────────────────────────────────── */
.dv-table { width: 100%; border-collapse: collapse; }
.dv-table th {
    padding: 9px 12px; text-align: left;
    font-size: 11px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .05em;
    border-bottom: 1px solid var(--border-card);
    background: var(--bg-header);
}
.dv-table td {
    padding: 11px 12px; font-size: 13px;
    color: var(--text-secondary); border-bottom: 1px solid var(--border-card);
    vertical-align: middle;
}
.dv-table tbody tr:last-child td { border-bottom: 0; }
.dv-table tbody tr:hover td { background: rgba(81, 140, 229, .02); }
.dv-icao {
    background: var(--bg-badge);
    border: 1px solid var(--border-badge);
    color: var(--text-badge);
    padding: 2px 7px; border-radius: 4px;
    font-size: 12px; font-weight: 700;
    font-family: monospace;
}

/* ── Awards grid ────────────────────────────────────────────── */
.dv-awards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}
.dv-award-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 8px;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.dv-award-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
}
.dark .dv-award-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
}
.dv-award-name {
    text-align: center;
    font-size: 13px; font-weight: 700; color: var(--text-primary);
    padding: 10px 12px 8px;
}
.dv-award-img {
    width: 100%; aspect-ratio: 1/1;
    background: var(--bg-header);
    display: flex; align-items: center; justify-content: center;
    color: var(--border-card); font-size: 13px;
}
.dv-award-img img {
    width: 100%; height: 100%; object-fit: cover;
}
.dv-award-footer {
    background: var(--bg-header);
    padding: 8px 12px; text-align: center;
    font-size: 11px; color: var(--text-muted);
}
.dv-award-footer strong { display: block; color: var(--text-secondary); margin-bottom: 2px; }

/* ── Passport stamps ────────────────────────────────────────── */
.dv-stamps-grid {
    display: flex; flex-wrap: wrap; gap: 8px;
}
.dv-stamp {
    background: var(--bg-header);
    border: 1.5px dashed var(--border-card);
    border-radius: 6px; padding: 8px 12px;
    text-align: center; min-width: 60px;
}
.dv-stamp-code { font-size: 13px; font-weight: 700; color: var(--text-primary); letter-spacing: 1px; }
.dv-stamp-count { font-size: 10px; font-weight: 700; color: var(--text-badge); margin-top: 2px; }

/* ── Pagination ─────────────────────────────────────────────── */
.dv-page-btn {
    padding: 5px 14px; border-radius: 5px; font-size: 12px; font-weight: 600;
    border: 1px solid var(--border-card); cursor: pointer; transition: all .15s;
    background: var(--bg-header); color: var(--text-secondary);
}
.dv-page-btn:hover:not(:disabled) { background: var(--text-badge); color: #fff; border-color: var(--text-badge); }
.dv-page-btn:disabled { opacity: .4; cursor: not-allowed; }

/* ── Map + Radar Grid ───────────────────────────────────────── */
.dv-map-radar-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 992px) {
    .dv-map-radar-grid { grid-template-columns: 2fr 1fr; }
}
#dvFlightMap { height: 280px; }
.leaflet-container { background: var(--bg-page) !important; }
.leaflet-bar a { background: var(--bg-card) !important; color: var(--text-secondary) !important; border-color: var(--border-card) !important; }
.leaflet-bar a:hover { background: var(--bg-header) !important; color: var(--text-primary) !important; }
</style>

<?php $__env->stopPush(); ?>

<div x-data="{ activeTab: <?php if ((object) ('activeTab') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('activeTab'->value()); ?>')<?php echo e('activeTab'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('activeTab'); ?>')<?php endif; ?> }" style="background:var(--bg-page);min-height:100vh;">
    <div style="padding:20px;max-width:1400px;margin:0 auto;">
        <div class="dvap-wrap">
            <!-- LEFT SIDEBAR -->
            <div class="dv-sidebar">
                
                <div class="dv-avatar-box">
                    <div class="dv-avatar">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->avatar): ?>
                            <img src="<?php echo e(Storage::url($user->avatar)); ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <?php echo e(strtoupper(substr($user->name,0,2))); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <p class="dv-pilot-name">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->rank?->name): ?>
                            <?php echo e($user->rank->name); ?>, <?php echo e($user->name); ?>

                        <?php else: ?>
                            <?php echo e($user->name); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                    <p class="dv-pilot-rank">
                        <i class="ph-fill ph-flag" style="color:var(--text-badge);"></i>
                        <?php echo e($user->country ?? 'International'); ?>

                    </p>

                    <div class="dv-pilot-id-badge">
                        <i class="ph-fill ph-check-circle"></i>
                        Pilot ID: <?php echo e($user->pilot_id); ?>

                    </div>

                    
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0;">
                        <span style="font-size:11px;color:var(--text-muted);">Rank</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->rank?->image): ?>
                            <img src="<?php echo e(Str::startsWith($user->rank->image, ['http://', 'https://', '/']) ? $user->rank->image : Storage::url($user->rank->image)); ?>"
                                 alt=""
                                 style="height:20px;max-width:80px;object-fit:contain;border-radius:2px;">
                        <?php else: ?>
                            <span style="display:flex;gap:3px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<4;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <span style="width:8px;height:20px;border-radius:2px;background:<?php echo e($i < ($user->rank?->level ?? 1) ? 'var(--text-badge)' : 'var(--border-card)'); ?>;"></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div class="dv-card">
                    <div class="dv-card-body" style="padding:0 16px;">
                        <ul class="dv-info-list">
                            <li>
                                <span class="dv-info-label">Home Airport</span>
                                <span class="dv-info-value">
                                    <span class="dv-airport-badge"><?php echo e($user->home_airport ?? 'N/A'); ?></span>
                                </span>
                            </li>
                            <li>
                                <span class="dv-info-label">Current Location</span>
                                <span class="dv-info-value">
                                    <span class="dv-airport-badge"><?php echo e($user->last_location ?? 'N/A'); ?></span>
                                </span>
                            </li>
                            <li>
                                <span class="dv-info-label">Status</span>
                                <span class="dv-info-value">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->status==='active'): ?>
                                        <span style="color:#10b981;font-weight:700;">● Active</span>
                                    <?php else: ?>
                                        <span style="color:#f59e0b;font-weight:700;">● <?php echo e(ucfirst($user->status)); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </li>
                            <li>
                                <span class="dv-info-label">Member since</span>
                                <span class="dv-info-value"><?php echo e($stats['joined']); ?> ago</span>
                            </li>
                            <?php $lastFlight = $user->pireps()->latest()->first(); ?>
                            <li>
                                <span class="dv-info-label">Last flight</span>
                                <span class="dv-info-value"><?php echo e($lastFlight ? $lastFlight->created_at->diffForHumans() : 'Never'); ?></span>
                            </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->simbrief_username): ?>
                            <li>
                                <span class="dv-info-label">SimBrief</span>
                                <span class="dv-info-value" style="color:var(--text-badge);"><?php echo e($user->simbrief_username); ?></span>
                            </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($passportStats['airports'])): ?>
                <div class="dv-card">
                    <div class="dv-card-header">
                        <i class="ph-fill ph-globe-simple" style="color:var(--text-badge);"></i> Airports Visited
                    </div>
                    <div class="dv-card-body">
                        <div class="dv-stamps-grid">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($passportStats['airports'],0,20,true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $icao => $visits): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="dv-stamp">
                                    <div class="dv-stamp-code"><?php echo e($icao); ?></div>
                                    <div class="dv-stamp-count"><?php echo e($visits); ?>×</div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($passportStats['airports']) > 20): ?>
                                <div class="dv-stamp" style="color:var(--text-muted);">
                                    <div class="dv-stamp-code">+<?php echo e(count($passportStats['airports'])-20); ?></div>
                                    <div class="dv-stamp-count">more</div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- RIGHT MAIN CONTENT -->
            <div style="min-width:0;display:flex;flex-direction:column;gap:16px;">
                
                <div style="background:var(--bg-header);border:1px solid var(--border-card);border-radius:8px;height:120px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:13px;letter-spacing:.05em;overflow:hidden;">
                    <img src="https://iili.io/C3xUzLF.png" alt="Profile Banner" style="width:100%;height:100%;object-fit:cover;">
                </div>

                
                <div class="dv-bio">
                    <h5><i class="ph-fill ph-article" style="color:var(--text-badge);"></i> Pilot's Biography</h5>
                    <p style="margin:0;">
                        <?php echo e($user->name); ?> joined on <?php echo e($user->created_at->format('F j, Y')); ?> and is based
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->home_airport): ?> at <strong style="color:var(--text-primary);"><?php echo e($user->home_airport); ?></strong>.<?php else: ?> internationally.<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        Currently holding the rank of <strong style="color:var(--text-primary);"><?php echo e($user->rank?->name ?? 'Candidate'); ?></strong>,
                        they have completed <strong style="color:var(--text-primary);"><?php echo e($stats['totalFlights']); ?></strong> flight(s)
                        totalling <strong style="color:var(--text-primary);"><?php echo e(number_format($stats['totalHours'],1)); ?></strong> flight hours.
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastFlight): ?> The last flight was <?php echo e($lastFlight->created_at->diffForHumans()); ?>.<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>

                
                <div class="dv-stats-grid">
                    <div class="dv-stat-widget">
                        <div class="dv-stat-icon"><i class="ph-fill ph-airplane-tilt"></i></div>
                        <div>
                            <div class="dv-stat-val"><?php echo e($stats['totalFlights']); ?></div>
                            <div class="dv-stat-lbl">Flights</div>
                        </div>
                    </div>
                    <div class="dv-stat-widget">
                        <div class="dv-stat-icon"><i class="ph-fill ph-clock"></i></div>
                        <div>
                            <div class="dv-stat-val"><?php echo e(number_format($stats['totalHours'],1)); ?>h</div>
                            <div class="dv-stat-lbl">Flight Hours</div>
                        </div>
                    </div>
                    <div class="dv-stat-widget">
                        <div class="dv-stat-icon" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.25);color:#10b981;"><i class="ph-fill ph-map-pin"></i></div>
                        <div>
                            <div class="dv-stat-val green"><?php echo e($user->last_location ?? 'N/A'); ?></div>
                            <div class="dv-stat-lbl">Current Airport</div>
                        </div>
                    </div>
                    <div class="dv-stat-widget">
                        <div class="dv-stat-icon"><i class="ph-fill ph-timer"></i></div>
                        <div>
                            <div class="dv-stat-val"><?php echo e($stats['bestStreak']); ?></div>
                            <div class="dv-stat-lbl">Best Streak (days)</div>
                        </div>
                    </div>
                    <div class="dv-stat-widget">
                        <div class="dv-stat-icon" style="background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.2);color:#f59e0b;"><i class="ph-fill ph-arrows-down-up"></i></div>
                        <div>
                            <div class="dv-stat-val" style="color:#f59e0b;"><?php echo e($stats['avgLanding']); ?> fpm</div>
                            <div class="dv-stat-lbl">Avg Landing Rate</div>
                        </div>
                    </div>
                    <div class="dv-stat-widget">
                        <div class="dv-stat-icon" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.2);color:#10b981;"><i class="ph-fill ph-ruler"></i></div>
                        <div>
                            <div class="dv-stat-val green"><?php echo e(number_format($passportStats['totalDistance'])); ?> nm</div>
                            <div class="dv-stat-lbl">Total Distance</div>
                        </div>
                    </div>
                </div>

                
                <div class="dv-card" style="overflow:hidden;">
                    <div class="dv-tabs">
                        <button wire:click="$set('activeTab','Flights')" class="dv-tab <?php echo e($activeTab==='Flights'?'active':''); ?>">
                            <i class="ph-fill ph-list-bullets"></i> Your Flights
                        </button>
                        <button wire:click="$set('activeTab','Awards')" class="dv-tab <?php echo e($activeTab==='Awards'?'active':''); ?>">
                            <i class="ph-fill ph-trophy"></i> Your Awards
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($user->achievements)>0): ?>
                                <span style="background:var(--text-badge);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;"><?php echo e(count($user->achievements)); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                        <button wire:click="$set('activeTab','Tours')" class="dv-tab <?php echo e($activeTab==='Tours'?'active':''); ?>">
                            <i class="ph-fill ph-path"></i> Tours
                        </button>
                        <button wire:click="$set('activeTab','Pireps')" class="dv-tab <?php echo e($activeTab==='Pireps'?'active':''); ?>">
                            <i class="ph-fill ph-airplane-takeoff"></i> PIREPs
                        </button>
                        <button wire:click="$set('activeTab','Passport')" class="dv-tab <?php echo e($activeTab==='Passport'?'active':''); ?>">
                            <i class="ph-fill ph-globe-simple"></i> Passport
                        </button>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'Flights'): ?>
                    <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">
                        
                        <div>
                            <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;display:flex;justify-content:space-between;">
                                <span><i class="ph-fill ph-chart-bar" style="color:var(--text-badge);"></i> Flight Activity</span>
                                <span style="font-weight:400;"><?php echo e($stats['flightsYear']); ?> flights this year</span>
                            </div>
                            <div style="overflow-x:auto;padding-bottom:8px;">
                                <div style="display:flex;flex-direction:column;gap:3px;min-width:max-content;">
                                    <div style="display:flex;gap:3px;font-size:10px;color:var(--text-muted);padding-left:22px;margin-bottom:4px;">
                                        <?php $months=['Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May']; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div style="width:45px;"><?php echo e($m); ?></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                    <?php $maxVal=max(array_map(function($w){return max(array_column($w,'count')?:[0]);},$activityGrid))?:1; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<7;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div style="display:flex;align-items:center;gap:3px;">
                                        <div style="width:20px;font-size:10px;color:var(--text-muted);text-align:right;">
                                            <?php echo e($i==1?'Mon':($i==3?'Wed':($i==5?'Fri':''))); ?>

                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activityGrid; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php
                                                $day=$week[$i]??null;$lvl=0;
                                                if($day&&$day['count']>0){
                                                    $pct=$day['count']/$maxVal;
                                                    $lvl=$pct<=.25?1:($pct<=.5?2:($pct<=.75?3:4));
                                                }
                                            ?>
                                            <div style="width:12px;height:12px;border-radius:2px;flex-shrink:0;
                                                background:<?php echo e(['var(--bg-header)','#1e3a5f','#1d4ed8','#2563eb','#3b82f6'][$lvl]); ?>;"
                                                title="<?php echo e($day['date']??''); ?>: <?php echo e($day['count']??0); ?> flights"></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <div class="dv-map-radar-grid">
                            
                            <div x-data x-init="dvInitMap(<?php echo json_encode($chartData, 15, 512) ?>)">
                                <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">
                                    <i class="ph-fill ph-globe-hemisphere-west" style="color:var(--text-badge);"></i> Flight Routes Map
                                </div>
                                <div style="border-radius:8px;overflow:hidden;border:1px solid var(--border-card);">
                                    <div id="dvFlightMap"></div>
                                </div>
                            </div>

                            
                            <div>
                                <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">
                                    <i class="ph-fill ph-chart-polar" style="color:var(--text-badge);"></i> Destinations
                                </div>
                                <div style="border-radius:8px;overflow:hidden;border:1px solid var(--border-card);background:var(--bg-card);padding:12px;height:280px;display:flex;align-items:center;justify-content:center;">
                                    <canvas id="dvRadarChart" style="max-height:220px;width:100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <?php elseif($activeTab === 'Awards'): ?>
                    <div style="padding:20px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($user->achievements)>0): ?>
                            <div class="dv-awards-grid">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $user->achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ach): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="dv-award-card">
                                    <div class="dv-award-name"><?php echo e($ach->name); ?></div>
                                    <div class="dv-award-img">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ach->image ?? false): ?>
                                            <img src="<?php echo e($ach->image); ?>" alt="<?php echo e($ach->name); ?>">
                                        <?php else: ?>
                                            <div style="text-align:center;padding:16px;">
                                                <i class="ph-fill ph-trophy" style="font-size:48px;color:var(--border-card);display:block;margin-bottom:6px;"></i>
                                                <span style="font-size:11px;color:var(--text-muted);">Award</span>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="dv-award-footer">
                                        <strong><?php echo e($ach->description); ?></strong>
                                        <?php echo e(\Carbon\Carbon::parse($ach->pivot->unlocked_at)->format('j. F Y')); ?>

                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align:center;padding:48px;">
                                <i class="ph-fill ph-trophy" style="font-size:48px;color:var(--border-card);display:block;margin-bottom:12px;"></i>
                                <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">No Awards Yet</div>
                                <div style="font-size:13px;color:var(--text-muted);">Complete flights and milestones to earn awards.</div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <?php elseif($activeTab === 'Tours'): ?>
                    <div style="padding:20px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($user->tours) > 0): ?>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $user->tours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $progress = $tourProgress[$tour->id] ?? ['pct'=>0,'completed'=>false]; ?>
                                <div class="dv-card">
                                    <div class="dv-card-body" style="padding:16px;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                                            <div>
                                                <span style="background:var(--bg-badge);color:var(--text-badge);border:1px solid var(--border-badge);font-size:10px;font-weight:700;text-transform:uppercase;padding:2px 8px;border-radius:10px;">
                                                    <?php echo e($tour->category); ?>

                                                </span>
                                                <h3 style="font-size:15px;font-weight:700;color:var(--text-primary);margin:8px 0 4px;"><?php echo e($tour->name); ?></h3>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progress['completed']): ?>
                                                <span style="background:rgba(16,185,129,.15);color:#10b981;font-size:10px;font-weight:700;text-transform:uppercase;padding:3px 9px;border-radius:10px;border:1px solid rgba(16,185,129,.3);white-space:nowrap;">
                                                    <i class="ph-fill ph-check-circle"></i> Completed
                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <p style="font-size:12px;color:var(--text-secondary);margin:0 0 14px;line-height:1.5;"><?php echo e($tour->description ?: 'No description provided.'); ?></p>
                                        <div style="margin-bottom:14px;">
                                            <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:700;margin-bottom:6px;">
                                                <span style="color:var(--text-muted);">Progress</span>
                                                <span style="color:var(--text-badge);"><?php echo e($progress['pct']); ?>%</span>
                                            </div>
                                            <div style="height:6px;background:var(--bg-header);border-radius:3px;overflow:hidden;border:1px solid var(--border-card);">
                                                <div style="height:100%;background:var(--text-badge);width:<?php echo e($progress['pct']); ?>%;border-radius:3px;"></div>
                                            </div>
                                        </div>
                                        <?php $wp = is_array($tour->waypoints) ? $tour->waypoints : json_decode($tour->waypoints,true); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($wp) && count($wp) > 0): ?>
                                            <div style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                                <i class="ph-fill ph-map-trifold" style="color:var(--text-badge);"></i>
                                                <?php echo e(count($wp)); ?> waypoints: <?php echo e(implode(' → ', array_slice($wp,0,4))); ?><?php echo e(count($wp)>4 ? ' ...' : ''); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align:center;padding:48px;">
                                <i class="ph-fill ph-path" style="font-size:48px;color:var(--border-card);display:block;margin-bottom:12px;"></i>
                                <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">No Tours Enrolled</div>
                                <div style="font-size:13px;color:var(--text-muted);">Enroll in a tour to start flying the route.</div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <?php elseif($activeTab === 'Pireps'): ?>
                    <div style="overflow-x:auto;">
                        <table class="dv-table">
                            <thead>
                                <tr>
                                    <th>Flight #</th>
                                    <th>DEP</th>
                                    <th>ARR</th>
                                    <th>Aircraft</th>
                                    <th>Duration</th>
                                    <th>Landing</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $pireps = $user->pireps()->latest()->paginate(15); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pireps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td style="color:var(--text-primary);font-weight:600;font-family:monospace;"><?php echo e($f->flight_number); ?></td>
                                    <td><span class="dv-icao"><?php echo e($f->departure); ?></span></td>
                                    <td><span class="dv-icao"><?php echo e($f->arrival); ?></span></td>
                                    <td><?php echo e($f->aircraft_icao ?: '—'); ?></td>
                                    <td><?php echo e($f->flight_time ? $f->flight_time.'h' : '—'); ?></td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($f->landing_rate): ?>
                                            <span style="font-weight:600;color:<?php echo e(abs($f->landing_rate)<200?'#10b981':(abs($f->landing_rate)<400?'#f59e0b':'#ef4444')); ?>;">
                                                <?php echo e($f->landing_rate); ?> fpm
                                            </span>
                                        <?php else: ?> <span style="color:var(--text-muted);">—</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($f->status==='approved'): ?>
                                            <span style="background:rgba(16,185,129,.15);color:#10b981;font-size:10px;font-weight:700;text-transform:uppercase;padding:2px 8px;border-radius:10px;border:1px solid rgba(16,185,129,.3);">Approved</span>
                                        <?php elseif($f->status==='rejected'): ?>
                                            <span style="background:rgba(239,68,68,.15);color:#ef4444;font-size:10px;font-weight:700;text-transform:uppercase;padding:2px 8px;border-radius:10px;border:1px solid rgba(239,68,68,.3);">Rejected</span>
                                        <?php else: ?>
                                            <span style="background:rgba(245,158,11,.15);color:#f59e0b;font-size:10px;font-weight:700;text-transform:uppercase;padding:2px 8px;border-radius:10px;border:1px solid rgba(245,158,11,.3);">Pending</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td style="font-size:11px;color:var(--text-muted);white-space:nowrap;"><?php echo e($f->created_at->format('Y-m-d')); ?></td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">
                                        <i class="ph-fill ph-airplane" style="font-size:32px;display:block;margin-bottom:8px;color:var(--border-card);"></i>
                                        No PIREPs recorded yet.
                                    </td>
                                </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pireps->hasPages()): ?>
                        <div style="padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--border-card);font-size:12px;color:var(--text-muted);">
                            <span>Showing <?php echo e($pireps->firstItem()); ?>–<?php echo e($pireps->lastItem()); ?> of <?php echo e($pireps->total()); ?></span>
                            <div style="display:flex;gap:8px;">
                                <button class="dv-page-btn" <?php echo e($pireps->onFirstPage()?'disabled':''); ?> wire:click="previousPage">
                                    <i class="ph-fill ph-caret-left"></i> Prev
                                </button>
                                <button class="dv-page-btn" <?php echo e(!$pireps->hasMorePages()?'disabled':''); ?> wire:click="nextPage">
                                    Next <i class="ph-fill ph-caret-right"></i>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <?php elseif($activeTab === 'Passport'): ?>
                    <div style="padding:20px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                        <div style="background:var(--bg-header);border:1px solid var(--border-card);border-radius:8px;padding:16px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:var(--text-badge);"><?php echo e($passportStats['uniqueAirports']); ?></div>
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;margin-top:4px;">Airports</div>
                        </div>
                        <div style="background:var(--bg-header);border:1px solid var(--border-card);border-radius:8px;padding:16px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:#10b981;"><?php echo e($passportStats['countries']); ?></div>
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;margin-top:4px;">Countries</div>
                        </div>
                        <div style="background:var(--bg-header);border:1px solid var(--border-card);border-radius:8px;padding:16px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:#f59e0b;"><?php echo e(number_format($passportStats['totalDistance'])); ?></div>
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;margin-top:4px;">nm Flown</div>
                        </div>
                        <div style="background:var(--bg-header);border:1px solid var(--border-card);border-radius:8px;padding:16px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:var(--text-primary);"><?php echo e($passportStats['mostVisited'] ?: '—'); ?></div>
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;margin-top:4px;">Top Airport</div>
                        </div>
                    </div>
                    <div style="padding:0 20px 20px;">
                        <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">
                            <i class="ph-fill ph-stamp" style="color:var(--text-badge);"></i> Visited Airports
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($passportStats['airports'])): ?>
                            <div class="dv-stamps-grid">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $passportStats['airports']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $icao => $visits): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="dv-stamp">
                                        <div class="dv-stamp-code"><?php echo e($icao); ?></div>
                                        <div class="dv-stamp-count"><?php echo e($visits); ?>×</div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p style="color:var(--text-muted);font-size:13px;">No airports visited yet.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let dvRadarChart = null;

function dvInitMap(data) {
    const mapEl = document.getElementById('dvFlightMap');
    const isDark = document.documentElement.classList.contains('dark');
    
    if (mapEl && typeof L !== 'undefined') {
        // avoid re-init
        if (!mapEl._leaflet_id) {
            const map = L.map('dvFlightMap', { zoomControl: true }).setView([20, 0], 2);
            
            const tileUrl = isDark 
                ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
                
            L.tileLayer(tileUrl, {
                subdomains: 'abcd', maxZoom: 19
            }).addTo(map);

            if (data.routes && data.routes.length) {
                const bounds = [];
                data.routes.forEach(r => {
                    const from = L.latLng(r.from.lat, r.from.lng);
                    const to   = L.latLng(r.to.lat,   r.to.lng);
                    L.polyline([from, to], { color: '#518ce5', weight: 1.5, opacity: 0.7 }).addTo(map);
                    bounds.push(from, to);
                });
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        }
    }

    const radarEl = document.getElementById('dvRadarChart');
    if (radarEl && typeof Chart !== 'undefined') {
        if (dvRadarChart) {
            dvRadarChart.destroy();
        }
        
        const gridColor = isDark ? '#3a3f54' : '#e9ebec';
        const labelColor = isDark ? '#a0a8c0' : '#495057';
        
        dvRadarChart = new Chart(radarEl, {
            type: 'radar',
            data: {
                labels: Object.keys(data.destinations).map(l => l.length > 10 ? l.substring(0,10)+'…' : l),
                datasets: [{
                    data: Object.values(data.destinations),
                    backgroundColor: 'rgba(81,140,229,0.18)',
                    borderColor: '#518ce5',
                    borderWidth: 2,
                    pointBackgroundColor: '#518ce5',
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    r: {
                        grid: { color: gridColor },
                        angleLines: { color: gridColor },
                        ticks: { display: false },
                        pointLabels: { color: labelColor, font: { size: 10, weight: '600' } }
                    }
                }
            }
        });
    }
}

document.addEventListener('livewire:initialized',  () => dvInitMap(<?php echo json_encode($chartData, 15, 512) ?>));
document.addEventListener('livewire:navigated',     () => dvInitMap(<?php echo json_encode($chartData, 15, 512) ?>));
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\Users\noxxr\Desktop\FlyAway-VAM\resources\views\livewire/pilot-profile.blade.php ENDPATH**/ ?>