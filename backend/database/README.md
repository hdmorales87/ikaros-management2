# Esquemas y migraciones Ikaros

Los esquemas ya existen en producción. Sus fuentes de referencia son:

- `D:\DESARROLLO\ikaros-management\ikarosof_management_acceso.sql`: esquema global.
- `D:\DESARROLLO\ikaros-management\ikarosof_cliente.sql`: esquema tenant.

Las migraciones `2026_08_28_000003` y `2026_08_28_000004` son líneas base no destructivas. Al ejecutarse, únicamente verifican las tablas requeridas; no crean, alteran ni eliminan tablas o datos. `composer setup` y `post-create-project-cmd` no ejecutan migraciones. No ejecutar `php artisan migrate` en producción para adoptar estos esquemas existentes.

Las modificaciones futuras del esquema deben añadirse como migraciones incrementales nuevas, con una cláusula de seguridad como `Schema::hasTable()` o `Schema::hasColumn()` cuando deban convivir con instalaciones existentes. Cada migración debe probarse primero en una copia de la base tenant y debe desplegarse mediante el proceso operativo aprobado.