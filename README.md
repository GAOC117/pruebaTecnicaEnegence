# Prueba Técnica – ENEGENCE

**Desarrollado por:** Guillermo Antonio Ortiz

**Repositorio:** [https://github.com/GAOC117/pruebaTecnicaEnegence](https://github.com/GAOC117/pruebaTecnicaEnegence)

---

## Descripción

Aplicación web desarrollada en **Laravel 12** que consume el API de **COPOMEX** para obtener el catálogo de los 32 estados de México y sus municipios.

Los datos se almacenan en MySQL y se muestran en una interfaz interactiva con búsqueda, ordenamiento y paginación utilizando **Livewire** y **Bootstrap 5**.

El objetivo es demostrar manejo de Laravel tanto en backend como frontend y el consumo de APIs externas en un flujo completo de integración.

---

## Stack Tecnológico

* Laravel 12
* PHP 8+
* MySQL
* Livewire
* Bootstrap 5
* Arquitectura MVC
* Laravel HTTP Client
* Deploy en Railway

---

## Demo en línea

Aplicación desplegada en Railway:

[https://x01enegence.up.railway.app/](https://x01enegence.up.railway.app/)

---

## Instalación del proyecto

### 1. Clonar repositorio

```bash
git clone https://github.com/GAOC117/pruebaTecnicaEnegence.git
cd pruebaTecnicaEnegence
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Crear base de datos

Crear manualmente una base de datos en MySQL.

Ejemplo:

```
prueba_enegence
```

### 4. Configurar archivo .env

Copiar archivo de ejemplo y generar llave de aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

El archivo `.env.example` ya incluye las variables necesarias.

Configurar:

**Datos de base de datos**

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_base_de_datos
DB_USERNAME=usuario_base_de_datos
DB_PASSWORD=password_base_de_datos
```

**Datos de API COPOMEX**

```
COPOMEX_TOKEN=token_copomex_aqui
COPOMEX_BASE_URL=https://api.copomex.com/query
```

**Nota:** Se recomienda utilizar un token real de COPOMEX para obtener datos reales de estados y municipios.
El token de prueba puede devolver datos aleatorios, lo que puede generar registros distintos en cada sincronización.


Para obtener un token se debe crear una cuenta nueva, después un proyecto nuevo con el nombre que se desee y dominio cualquiera, el link para crear una cuenta es el siguiente:
[https://api.copomex.com/panel](https://api.copomex.com/panel)

### 5. Ejecutar migraciones

```bash
php artisan migrate
```

### 6. Ejecutar proyecto

```bash
php artisan serve
```

Abrir en navegador:

```
http://127.0.0.1:8000
```

---

## Decisiones técnicas

### Sincronización de datos

Aunque la instrucción indicaba consultar municipios al hacer clic en cada estado desde el API, se optó por realizar una sincronización inicial completa y almacenar la información en base de datos.

Esto permite:

* Reducir consumo de créditos del API
* Mejor rendimiento
* Menor dependencia de la API en cada interacción
* Arquitectura más cercana a producción
* Disponibilidad de datos aun si el API no responde

La sincronización se ejecuta manualmente mediante botón para facilitar pruebas del evaluador.

Aunque se sugería el uso de DataTables, se optó por utilizar **Laravel Livewire** para la construcción de la interfaz interactiva, esto permite una integración más natural con el ecosistema de Laravel y evita la necesidad de depender de plugins externos de jQuery para la manipulación de tablas.

### Idempotencia

La sincronización utiliza `firstOrCreate` para evitar duplicados.

Si se ejecuta nuevamente el botón de sincronización:

* No se duplican registros
* Solo se insertan nuevos si no existen
* Si para el consumo de API ya no se tiene créditos, se muestra el mensaje correspondiente

### Nota sobre token de pruebas

Si se utiliza el token de pruebas de COPOMEX:

* Puede devolver datos de ejemplo o aleatorios y generar nuevos registros

Aun así, la aplicación mantiene integridad evitando duplicados.

---

## Funcionalidades

* Listado de estados con paginación
* Búsqueda en tiempo real
* Ordenamiento
* Visualización de municipios por estado
* Sincronización desde API
* Manejo de errores
* Interfaz dinámica con Livewire

---

## Base de datos

### Tabla estados

* id
* nombre
* timestamps

### Tabla municipios

* id
* nombre
* estado_id
* timestamps

**Relaciones:**

* Estado tiene muchos municipios
* Municipio pertenece a estado

---

## Arquitectura

Se utilizó arquitectura MVC junto con Livewire para mejorar la experiencia de usuario sin recargas de página y mantener integración directa con Laravel.

---

## Autor

**Guillermo Antonio Ortiz**
Prueba técnica ENEGENCE – 2026
