-- ============================================================
--  NextGen Fitness — Seed Password Hash Update
--  Run this AFTER importing your main gym.sql file.
--
--  These are bcrypt hashes (PHP password_hash() compatible).
--  Login credentials remain the same:
--    admin        → admin123
--    customer     → customer123
--    trainer      → trainer123
--    accountant   → accountant123
--    receptionist → reception123
-- ============================================================

UPDATE `users` SET `password`="$2b$12$B4msZIofWKDxxH5HjPzSku3GAZw7AAFS09xgCXJs5/jC6pSLX53mS" WHERE `id`=1;  -- admin / admin123
UPDATE `users` SET `password`="$2b$12$4YUup/uRZss2T6GX7C9PX.NDBVgvIKWTbcTNuGvGSJeRoYO7PYKna" WHERE `id`=2;  -- customer / customer123
UPDATE `users` SET `password`="$2b$12$TBnmcJGZT8nVkQQifuCuReLEY0siQV1ZdUw31/jU/SpeC2s.mM4WC" WHERE `id`=5;  -- trainer / trainer123
UPDATE `users` SET `password`="$2b$12$j7aA1D.1GPzBp78jNsQ0suWKYnuMPQh0819ifg4cH22L6f19luRWq"  WHERE `id`=6;  -- accountant / accountant123
UPDATE `users` SET `password`="$2b$12$RcH.2ecQzvJyo2dwNIIie.M1FmCGG/f3EDkXiGHLqbaNDcKG2mqHi" WHERE `id`=7;  -- receptionist / reception123
