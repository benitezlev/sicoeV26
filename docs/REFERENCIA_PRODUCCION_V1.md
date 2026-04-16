# 📝 Referencia Técnica: Estado "Producción V1" - SICOE

Este documento certifica el estado funcional y técnico del sistema previo a su migración al servidor **Kraken**. El sistema se encuentra operando bajo estándares de producción en el entorno actual.

## 🚀 Estado de Módulos (Core)

### 1. Control de Asistencia (Finalizado)
- **PDFs Optimizados:** Los reportes de asistencia (40hrs y Horizontal) cuentan con carga masiva de datos, eliminando problemas de N+1 (Consultas reducidas de ~200 a 3 por reporte).
- **Justificaciones:** Sistema de 3 estados (Presente, Falta, Justificada) operativo con registro de motivos.

### 2. Expedientes Digitales (Finalizado & Optimizado)
- **Visor Integrado:** Previsualización de PDF/IMG sin salir de la plataforma.
- **Validación Institucional:** Flujo de validación/observación de documentos por administradores completamente funcional.
- **Kárdex Académico:** Sincronizado con calificaciones por unidad; fallback inteligente a nombres de cursos.
- **Historial de Movimientos:** Trazabilidad completa de adscripciones con tarjeta de "Situación Actual" destacada.

### 3. Gestión de Usuarios
- **Identidad Única:** Consolidación de CURP/CUIP como llaves primarias de búsqueda.
- **Seguridad Eloquent:** Implementación de null-safe operators en relaciones críticas para prevenir errores de servidor por datos huérfanos.

## 🛠️ Arquitectura Técnica Activa

- **PHP:** 8.4+ (Strict Types).
- **Framework:** Laravel 12 / Livewire 3 (Volt Functional).
- **Frontend:** Flux UI (Estándar) + Tailwind CSS + Alpine.js.
- **Base de Datos:** PostgreSQL (UUID v4/v7 para registros maestros).
- **Media:** Spatie Media Library para almacenamiento seguro de expedientes.

## 🐧 Configuración de Servidor (HestiaCP)
- **Web Templates:** El sistema requiere templates específicos de Nginx para manejar la carga de archivos de hasta 10MB (`client_max_body_size`).
- **Permisos:** Propiedad `www-data:www-data` y permisos `755/644` aplicados correctamente.

## 📋 Lista de Pendientes (Roadmap Kraken)
1. **Módulo de Firma:** Implementación de firmas digitales y códigos QR en actas definitivas.
2. **Reportes Fiscalía:** Generación de formatos específicos bajo normativa FGJEM.
3. **Mantenimiento:** Configuración de tareas programadas (`cron:run`) en Kraken para el procesamiento de media en segundo plano.

---
**Fecha de Referencia:** 16 de Abril de 2026  
**Estatus:** Producción Estable - Listo para Migración.
