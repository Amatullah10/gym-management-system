<?php
// ============================================================
//  NextGen Fitness — Gym Location Config
//  Used by customer/checkin.php for geolocation-based check-in
//
//  To find your gym's coordinates:
//  1. Open Google Maps → right-click your gym location
//  2. Click the coordinates shown at the top of the menu
//  3. Paste LAT and LNG values below
// ============================================================

define('GYM_LAT',    19.00612939260819);   // ← Your gym's latitude  (Worli, Mumbai)
define('GYM_LNG',    72.82341079310956);   // ← Your gym's longitude (Worli, Mumbai)
define('GYM_RADIUS', 500);       // ← Allowed radius in meters (500m recommended)