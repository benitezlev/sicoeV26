# 📄 Documentación Técnica: Implementación de Spatie Media Library

## 🎯 Objetivo
Estandarizar la gestión de archivos y documentos en el sistema SICOE utilizando el paquete `spatie/laravel-medialibrary`. Esto permite una gestión polimórfica, segura y optimizada de expedientes, fotos y recursos.

## 🛠️ Cambios Realizados

### 1. Modelos Integrados
Se ha implementado la interfaz `HasMedia` y el trait `InteractsWithMedia` en los siguientes modelos críticos:
- **User:** Preparado para fotos de perfil y firmas digitales.
- **Expediente:** Base para la gestión documental de alumnos.
- **DocumentosExpediente:** Cada registro de documento ahora gestiona su archivo físico mediante Media Library.
- **Plantel:** Para logos institucionales y documentos de sede.
- **Materia:** Para subir recursos educativos y temarios.
- **Importacion:** Guarda una copia física de cada CSV/Excel procesado para auditoría.

### 2. Refactorización de Carga (Expedientes)
Se actualizó el componente `livewire/expedientes/show.blade.php`:
- **Anterior:** Guardado manual en `storage/expedientes/{curp}`.
- **Nuevo:** Uso de `$doc->addMedia($file)->toMediaCollection('archivo')`.
- **Compatibilidad:** El sistema detecta automáticamente si el archivo es antiguo (ruta manual) o nuevo (Media Library) para no romper expedientes existentes.

### 3. Importación Masiva (ZIP + Queues)
Se implementó un motor de procesamiento asíncrono para expedientes:
- **Ruta:** `/expedientes/importar-zip`
- **Funcionamiento:** Se sube un solo archivo ZIP. El sistema lo pone en una **Cola (Queue)** y lo procesa en segundo plano.
- **Nomenclatura Requerida:** `CURP_TIPO.pdf/jpg/png` (Ej: `ABCD123HDFRR01_ACTA.pdf`).
- **Beneficio:** No hay tiempos de espera ni errores de timeout para el usuario al subir cientos de documentos simultáneamente.

### 4. Auditoría de Importaciones
El módulo de importación de alumnos guarda el archivo fuente en la colección `archivo_importacion`.

## 🚀 Ventajas para SICOE
1. **Limpieza de BD:** Los archivos no están "hardcoded" en rutas de texto.
2. **Optimización:** Generación automática de thumbnails para PDFs y fotos.
3. **Seguridad:** Los archivos se pueden mover a discos privados o S3 sin cambiar una sola línea de lógica de negocio.
4. **Auditoría:** Es más fácil rastrear cuándo y quién subió un archivo específico.

## 📝 Notas de Uso
Para obtener la URL de un documento en cualquier vista:
```php
// Ejemplo en Blade
$url = $documento->getFirstMediaUrl('archivo');
```
