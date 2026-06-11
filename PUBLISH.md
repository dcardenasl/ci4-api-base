# Plan de Publicación - CI4 API Base en Packagist

## Estado Actual ✅

La librería está **lista para publicación**. El proyecto tiene:

- ✅ `composer.json` completo y válido
- ✅ Documentación actualizada (README.md, INSTALLATION.md, CONTRIBUTING.md, CHANGELOG.md)
- ✅ Licencia MIT
- ✅ GitHub Actions configurado (tests multi-versión PHP, coverage, PHPStan)
- ✅ Repositorio Git: https://github.com/dcardenasl/ci4-api-base
- ✅ Código fuente bien estructurado
- ✅ Placeholders actualizados a `dcardenasl/ci4-api-base`
- ✅ CHANGELOG.md preparado para v1.0.1

---

## Pasos para Publicar (Manual)

### 1. Commit y Push de Cambios

```bash
# Revisar cambios pendientes
git status

# Agregar archivos modificados
git add README.md INSTALLATION.md CHANGELOG.md

# Crear commit
git commit -m "docs: update package name placeholders for publication

- Replace your-vendor/ci4-api-base with dcardenasl/ci4-api-base
- Replace YourVendor namespace with dcardenasl
- Update CHANGELOG for v1.0.1 with documentation fixes
- Update GitHub URLs to correct repository

Preparing for Packagist publication."

# Push a main
git push origin main
```

### 2. Crear y Publicar Tag v1.0.1

```bash
# Crear tag anotado
git tag -a v1.0.1 -m "Release version 1.0.1

Documentation fixes:
- Updated package name placeholders
- Corrected namespace references
- Fixed GitHub repository URLs"

# Verificar tag
git tag -l

# Push del tag a GitHub
git push origin v1.0.1

# Verificar en GitHub que el tag aparezca:
# https://github.com/dcardenasl/ci4-api-base/releases
```

### 3. Registrar en Packagist

**Primera vez (solo se hace una vez):**

1. Ve a https://packagist.org
2. Click en "Log in" y usa tu cuenta de GitHub
3. Una vez autenticado, click en "Submit" (parte superior derecha)
4. Ingresa la URL del repositorio: `https://github.com/dcardenasl/ci4-api-base`
5. Click en "Check" para validar el `composer.json`
6. Si todo está correcto (✓), click en "Submit"
7. Packagist indexará tu paquete y estará disponible en minutos

### 4. Configurar Auto-Update (Webhook)

Para que Packagist se actualice automáticamente con cada push/tag:

1. En Packagist, ve a tu paquete: https://packagist.org/packages/dcardenasl/ci4-api-base
2. Click en tu nombre de usuario → "My packages"
3. Click en el paquete `dcardenasl/ci4-api-base`
4. Click en el botón "Settings" o busca la sección de webhook
5. Copia el **webhook URL** que Packagist te proporciona (algo como: `https://packagist.org/api/github?username=...`)
6. Ve a GitHub: https://github.com/dcardenasl/ci4-api-base/settings/hooks
7. Click en "Add webhook"
8. Pega el webhook URL en el campo "Payload URL"
9. Content type: **`application/json`**
10. Which events: **"Just the push event"**
11. Active: ✓ (marcado)
12. Click en "Add webhook"

**Verificación del webhook:**
- Después de agregarlo, GitHub hará un ping
- Verifica que aparezca un ✓ verde junto al webhook
- Cualquier push futuro actualizará Packagist automáticamente

---

## Publicar Futuras Versiones

Para versiones posteriores (1.0.2, 1.1.0, etc.):

```bash
# 1. Actualizar CHANGELOG.md con los cambios
# Agregar nueva sección al inicio:
## [X.Y.Z] - 2026-MM-DD
### Added / Changed / Fixed / Removed
- Descripción de cambios

# 2. Commit de cambios
git add .
git commit -m "release: version X.Y.Z"

# 3. Crear tag
git tag -a vX.Y.Z -m "Release version X.Y.Z

Cambios principales:
- Cambio 1
- Cambio 2"

# 4. Push (el webhook actualizará Packagist automáticamente)
git push origin main
git push origin vX.Y.Z
```

**Nota:** Packagist detectará el nuevo tag automáticamente si configuraste el webhook.

---

## Verificación Post-Publicación

Después de publicar, verifica que todo funcione:

### 1. Verificar en Packagist

- URL: https://packagist.org/packages/dcardenasl/ci4-api-base
- Confirma que aparezca la versión v1.0.1
- Revisa que el README se vea correctamente
- Verifica el botón de descargas y estadísticas

### 2. Probar Instalación

Crea un proyecto de prueba:

```bash
# Crear proyecto CI4 nuevo (o usa uno existente)
composer create-project codeigniter4/appstarter test-ci4-api

# Entrar al proyecto
cd test-ci4-api

# Instalar tu paquete
composer require dcardenasl/ci4-api-base

# Verificar instalación
ls -la vendor/dcardenasl/ci4-api-base
```

### 3. Probar Funcionalidad

Crea un controlador de prueba usando la librería y verifica que funcione:

```php
<?php
namespace App\Controllers\Api;

use dcardenasl\CI4ApiBase\Controllers\ApiController;
use CodeIgniter\HTTP\ResponseInterface;

class TestController extends ApiController
{
    protected function getService(): object
    {
        return new class {
            public function index(array $data): array
            {
                return ['data' => ['message' => 'It works!']];
            }
        };
    }

    protected function getSuccessStatus(string $method): int
    {
        return ResponseInterface::HTTP_OK;
    }

    public function index(): ResponseInterface
    {
        return $this->handleRequest('index');
    }
}
```

---

## Badges Opcionales para README

Después de publicar, considera agregar estos badges al README.md:

```markdown
[![Packagist Version](https://img.shields.io/packagist/v/dcardenasl/ci4-api-base)](https://packagist.org/packages/dcardenasl/ci4-api-base)
[![Total Downloads](https://img.shields.io/packagist/dt/dcardenasl/ci4-api-base)](https://packagist.org/packages/dcardenasl/ci4-api-base)
[![License](https://img.shields.io/packagist/l/dcardenasl/ci4-api-base)](https://packagist.org/packages/dcardenasl/ci4-api-base)
```

---

## Archivos Modificados en esta Actualización

- `README.md` - Actualizado placeholder `your-vendor` → `dcardenasl`
- `INSTALLATION.md` - Actualizado placeholder `YourVendor` → `dcardenasl`
- `CHANGELOG.md` - Agregada versión 1.0.1 con correcciones de documentación

---

## Checklist Pre-Publicación

- [x] composer.json tiene el nombre correcto: `dcardenasl/ci4-api-base`
- [x] README.md sin placeholders
- [x] INSTALLATION.md sin placeholders
- [x] CHANGELOG.md actualizado para v1.0.1
- [ ] Commit realizado
- [ ] Tag v1.0.1 creado
- [ ] Push a GitHub completado
- [ ] Paquete registrado en Packagist
- [ ] Webhook configurado
- [ ] Instalación de prueba exitosa

---

## Recursos Útiles

- **Packagist**: https://packagist.org
- **Documentación Composer**: https://getcomposer.org/doc/
- **Semantic Versioning**: https://semver.org
- **Keep a Changelog**: https://keepachangelog.com

---

**¡Tu librería está lista para compartir con la comunidad de CodeIgniter! 🚀**
